<?php
/**
 * Normalize content-owned page-specific section classes.
 *
 * Usage:
 *   wp eval-file scripts/migrate-page-specific-content-ownership.php
 *   wp eval-file scripts/migrate-page-specific-content-ownership.php apply
 *
 * Dry-run is the default. Apply writes a JSON rollback export before mutating
 * current non-revision rows. Revisions are counted for evidence but not edited.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$arguments  = isset( $args ) && is_array( $args ) ? $args : array();
$apply      = in_array( '--apply', $arguments, true ) || in_array( 'apply', $arguments, true );
$project_root = dirname( get_stylesheet_directory(), 5 );
$backup_dir   = $project_root . '/docs/jobs/page-specific-css-db-backups';

/**
 * Determine whether content may include the migrated Mary section.
 *
 * @param string $content Post content.
 * @return bool
 */
function pns_page_specific_content_contains_target( string $content ): bool {
	$contains_mary_target = str_contains( $content, 'shop-intro pns-section pns-synced-section pns-shop-intro' )
		&& str_contains( $content, 'mw-intro-box' )
		&& str_contains( $content, 'More about Mary' );
	$contains_mary_repair = str_contains( $content, 'pns-herstory-more-about' )
		&& str_contains( $content, 'mw-intro-box' )
		&& str_contains( $content, 'More about Mary' );
	$contains_shop_target = str_contains( $content, 'pns-shop-storefront' )
		&& (
			! str_contains( $content, 'pns-shop-storefront__intro' )
			|| ! str_contains( $content, 'pns-shop-storefront__frame' )
		);

	return $contains_mary_target || $contains_mary_repair || $contains_shop_target;
}

/**
 * Return a class string with target tokens removed and replacement tokens added.
 *
 * @param string $class_name Original class string.
 * @param array<int,string> $remove Tokens to remove.
 * @param array<int,string> $add Tokens to add.
 * @return string
 */
function pns_page_specific_content_reclass( string $class_name, array $remove, array $add ): string {
	$tokens = preg_split( '/\s+/', trim( $class_name ) );
	$tokens = is_array( $tokens ) ? $tokens : array();
	$remove = array_fill_keys( $remove, true );
	$output = array();

	foreach ( $tokens as $token ) {
		if ( '' === $token || isset( $remove[ $token ] ) ) {
			continue;
		}

		$output[ $token ] = true;
	}

	foreach ( $add as $token ) {
		$output[ $token ] = true;
	}

	return implode( ' ', array_keys( $output ) );
}

/**
 * Replace static block wrapper HTML fragments retained by parse_blocks().
 *
 * WordPress serializes block comments from attrs but preserves static block
 * innerHTML/innerContent. When changing class/style attrs we must also rewrite
 * the saved wrapper HTML or the editor sees invalid block markup.
 *
 * @param array<string,mixed> $block Parsed block.
 * @param array<string,string> $replacements Exact string replacements.
 * @return array<string,mixed>
 */
function pns_page_specific_content_replace_saved_html( array $block, array $replacements ): array {
	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$block['innerHTML'] = strtr( $block['innerHTML'], $replacements );
	}

	if ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$inner_content ) {
			if ( is_string( $inner_content ) ) {
				$inner_content = strtr( $inner_content, $replacements );
			}
		}
		unset( $inner_content );
	}

	return $block;
}

/**
 * Repair exact stale wrapper strings that can survive parsed-block migration.
 *
 * @param string $content Serialized block content.
 * @param array<int,array<string,string>> $changes Change records.
 * @return string
 */
function pns_page_specific_content_repair_serialized_content( string $content, array &$changes ): string {
	$replacements = array(
		'<div class="wp-block-group mw-intro-box pns-copy-column">' => '<div class="wp-block-group pns-herstory-more-about__copy">',
	);

	$updated = strtr( $content, $replacements );

	if ( $updated !== $content ) {
		$changes[] = array(
			'block'  => 'core/group',
			'change' => 'Repaired stale serialized mw-intro-box wrapper class.',
		);
	}

	return $updated;
}

/**
 * Determine whether a parsed block has a direct child Columns block.
 *
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_page_specific_content_has_direct_columns_child( array $block ): bool {
	if ( empty( $block['innerBlocks'] ) || ! is_array( $block['innerBlocks'] ) ) {
		return false;
	}

	foreach ( $block['innerBlocks'] as $inner_block ) {
		if ( is_array( $inner_block ) && 'core/columns' === ( $inner_block['blockName'] ?? '' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Recursively normalize target blocks.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,array<string,mixed>> $changes Change records.
 * @param bool $inside_target_section Whether the current subtree is inside the target section.
 * @return array<int,array<string,mixed>>
 */
function pns_migrate_page_specific_content_blocks( array $blocks, array &$changes, bool $inside_target_section = false ): array {
	foreach ( $blocks as &$block ) {
		$block_name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';
		$is_target  = false;
		$is_shop_storefront = 'core/group' === $block_name
			&& str_contains( $class_name, 'pns-shop-storefront' );
		$is_herstory_more_about = 'core/group' === $block_name
			&& str_contains( $class_name, 'pns-herstory-more-about' );

		if (
			'core/group' === $block_name
			&& str_contains( $class_name, 'shop-intro' )
			&& str_contains( $class_name, 'pns-synced-section' )
			&& str_contains( $class_name, 'pns-shop-intro' )
		) {
			$attrs['className'] = pns_page_specific_content_reclass(
				$class_name,
				array( 'shop-intro', 'pns-synced-section', 'pns-shop-intro' ),
				array( 'pns-layout', 'pns-text-only-section', 'pns-saved-section', 'pns-herstory-more-about' )
			);
			$block['attrs'] = $attrs;
			$block          = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'class="wp-block-group alignfull shop-intro pns-section pns-synced-section pns-shop-intro has-neutral-0-background-color has-background"' => 'class="wp-block-group alignfull pns-section pns-layout pns-text-only-section pns-saved-section pns-herstory-more-about has-neutral-0-background-color has-background"',
				)
			);
			$is_target      = true;
			$changes[]      = array(
				'block'  => 'core/group',
				'change' => 'Reclassified local Mary section from Shop Intro synced-section to Herstory text-only section.',
			);
		}

		if ( $is_herstory_more_about ) {
			$before_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);
			$block = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'class="wp-block-group alignfull shop-intro pns-section pns-synced-section pns-shop-intro has-neutral-0-background-color has-background"' => 'class="wp-block-group alignfull pns-section pns-layout pns-text-only-section pns-saved-section pns-herstory-more-about has-neutral-0-background-color has-background"',
				)
			);
			$after_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);

			if ( $after_html_repair !== $before_html_repair ) {
				$changes[] = array(
					'block'  => 'core/group',
					'change' => 'Repaired stale saved HTML for Herstory more-about section.',
				);
			}
		}

		if ( $is_shop_storefront || $is_herstory_more_about ) {
			$is_target = true;
		}

		if (
			$inside_target_section
			&& 'core/group' === $block_name
			&& '' === trim( $class_name )
			&& empty( $attrs['style'] )
			&& pns_page_specific_content_has_direct_columns_child( $block )
		) {
			$attrs['className'] = 'pns-section-frame pns-shop-storefront__frame';
			$block['attrs']    = $attrs;
			$block             = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group">' => '<div class="wp-block-group pns-section-frame pns-shop-storefront__frame">',
				)
			);
			$changes[]         = array(
				'block'  => 'core/group',
				'change' => 'Added semantic Shop storefront frame class.',
			);
		}

		if (
			$inside_target_section
			&& 'core/group' === $block_name
			&& isset( $attrs['style']['spacing']['padding']['left'], $attrs['style']['spacing']['padding']['right'] )
			&& '1rem' === $attrs['style']['spacing']['padding']['left']
			&& '1rem' === $attrs['style']['spacing']['padding']['right']
		) {
			$attrs['className'] = pns_page_specific_content_reclass(
				$class_name,
				array(),
				array( 'pns-shop-storefront__content' )
			);
			unset( $attrs['style'] );
			$block['attrs'] = $attrs;
			$block          = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group" style="padding-right:1rem;padding-left:1rem">' => '<div class="wp-block-group pns-shop-storefront__content">',
				)
			);
			$changes[]      = array(
				'block'  => 'core/group',
				'change' => 'Moved Shop storefront wrapper padding to semantic frame ownership.',
			);
		}

		if (
			$inside_target_section
			&& 'core/group' === $block_name
			&& isset( $attrs['layout']['type'], $attrs['layout']['justifyContent'] )
			&& 'constrained' === $attrs['layout']['type']
			&& 'left' === $attrs['layout']['justifyContent']
		) {
			$attrs['className'] = pns_page_specific_content_reclass(
				$class_name,
				array(),
				array( 'pns-shop-storefront__intro' )
			);
			$block['attrs'] = $attrs;
			$block          = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group">' => '<div class="wp-block-group pns-shop-storefront__intro">',
				)
			);
			$changes[]      = array(
				'block'  => 'core/group',
				'change' => 'Added semantic Shop storefront intro class.',
			);
		}

		if (
			( $inside_target_section || $is_target )
			&& 'core/group' === $block_name
			&& str_contains( $class_name, 'pns-section-inner' )
		) {
			$attrs['className'] = pns_page_specific_content_reclass(
				$class_name,
				array( 'pns-section-inner' ),
				array( 'pns-text-only-section__inner' )
			);
			$block['attrs'] = $attrs;
			$block          = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group pns-section-inner">' => '<div class="wp-block-group pns-text-only-section__inner">',
				)
			);
			$changes[]      = array(
				'block'  => 'core/group',
				'change' => 'Replaced section-inner with text-only section inner.',
			);
		}

		if (
			( $inside_target_section || $is_target )
			&& 'core/group' === $block_name
			&& str_contains( $class_name, 'pns-text-only-section__inner' )
		) {
			$before_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);
			$block = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group pns-section-inner">' => '<div class="wp-block-group pns-text-only-section__inner">',
				)
			);
			$after_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);

			if ( $after_html_repair !== $before_html_repair ) {
				$changes[] = array(
					'block'  => 'core/group',
					'change' => 'Repaired stale saved HTML for text-only section inner.',
				);
			}
		}

		if (
			( $inside_target_section || $is_target )
			&& 'core/group' === $block_name
			&& str_contains( $class_name, 'mw-intro-box' )
		) {
			$attrs['className'] = pns_page_specific_content_reclass(
				$class_name,
				array( 'mw-intro-box', 'pns-copy-column' ),
				array( 'pns-herstory-more-about__copy' )
			);
			unset( $attrs['style'] );
			$block['attrs'] = $attrs;
			$block          = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group mw-intro-box pns-copy-column" style="margin-top:var(--wp--preset--spacing--generous);margin-bottom:var(--wp--preset--spacing--generous);padding-top:var(--wp--preset--spacing--spacious);padding-bottom:var(--wp--preset--spacing--spacious)">' => '<div class="wp-block-group pns-herstory-more-about__copy">',
					'<div class="wp-block-group mw-intro-box pns-copy-column">' => '<div class="wp-block-group pns-herstory-more-about__copy">',
				)
			);
			$changes[]      = array(
				'block'  => 'core/group',
				'change' => 'Removed page-local intro-box spacing and copy-column class.',
			);
		}

		if (
			( $inside_target_section || $is_target )
			&& 'core/group' === $block_name
			&& str_contains( $class_name, 'pns-herstory-more-about__copy' )
		) {
			$before_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);
			$block = pns_page_specific_content_replace_saved_html(
				$block,
				array(
					'<div class="wp-block-group mw-intro-box pns-copy-column" style="margin-top:var(--wp--preset--spacing--generous);margin-bottom:var(--wp--preset--spacing--generous);padding-top:var(--wp--preset--spacing--spacious);padding-bottom:var(--wp--preset--spacing--spacious)">' => '<div class="wp-block-group pns-herstory-more-about__copy">',
				)
			);
			$after_html_repair = wp_json_encode(
				array(
					$block['innerHTML'] ?? '',
					$block['innerContent'] ?? array(),
				)
			);

			if ( $after_html_repair !== $before_html_repair ) {
				$changes[] = array(
					'block'  => 'core/group',
					'change' => 'Repaired stale saved HTML for Herstory more-about copy.',
				);
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_migrate_page_specific_content_blocks(
				$block['innerBlocks'],
				$changes,
				$inside_target_section || $is_target
			);
		}
	}

	return $blocks;
}

global $wpdb;

$rows = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title, post_content
	FROM {$wpdb->posts}
	WHERE post_content LIKE '%shop-intro pns-section pns-synced-section pns-shop-intro%'
		OR post_content LIKE '%mw-intro-box%'
		OR post_content LIKE '%pns-herstory-more-about%'
		OR post_content LIKE '%pns-shop-storefront%'
	ORDER BY post_type, ID"
);

$revision_count = 0;
$targets        = array();
$backups        = array();

foreach ( $rows as $row ) {
	if ( 'revision' === $row->post_type ) {
		$revision_count++;
		continue;
	}

	if ( in_array( $row->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
		continue;
	}

	if ( ! pns_page_specific_content_contains_target( $row->post_content ) ) {
		continue;
	}

	$changes = array();
	$blocks  = parse_blocks( $row->post_content );
	$updated = serialize_blocks( pns_migrate_page_specific_content_blocks( $blocks, $changes ) );
	$updated = pns_page_specific_content_repair_serialized_content( $updated, $changes );

	if ( empty( $changes ) || $updated === $row->post_content ) {
		continue;
	}

	$targets[] = array(
		'ID'           => (int) $row->ID,
		'post_type'    => $row->post_type,
		'post_status'  => $row->post_status,
		'post_title'   => $row->post_title,
		'changes'      => $changes,
		'post_content' => $updated,
	);

	$backups[] = array(
		'ID'           => (int) $row->ID,
		'post_type'    => $row->post_type,
		'post_status'  => $row->post_status,
		'post_title'   => $row->post_title,
		'post_content' => $row->post_content,
	);
}

WP_CLI::log( sprintf( 'Revision rows counted but not mutated: %d', $revision_count ) );
WP_CLI::log( sprintf( 'Current non-revision rows requiring migration: %d', count( $targets ) ) );

foreach ( $targets as $target ) {
	WP_CLI::log(
		sprintf(
			'#%d [%s/%s] %s',
			$target['ID'],
			$target['post_type'],
			$target['post_status'],
			'' !== $target['post_title'] ? $target['post_title'] : '(no title)'
		)
	);

	foreach ( $target['changes'] as $change ) {
		WP_CLI::log( sprintf( '  - %s', $change['change'] ) );
	}
}

if ( ! $apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write rollback export and mutate current rows.' );
	return;
}

if ( empty( $targets ) ) {
	WP_CLI::success( 'No DB rows require migration.' );
	return;
}

if ( ! is_dir( $backup_dir ) && ! wp_mkdir_p( $backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $backup_dir ) );
}

$backup_path = trailingslashit( $backup_dir ) . gmdate( 'Ymd-His' ) . '-page-specific-content-ownership.json';
file_put_contents(
	$backup_path,
	wp_json_encode(
		array(
			'created_at_gmt' => gmdate( 'c' ),
			'rows'           => $backups,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	)
);

foreach ( $targets as $target ) {
	$wpdb->update(
		$wpdb->posts,
		array( 'post_content' => $target['post_content'] ),
		array( 'ID' => $target['ID'] ),
		array( '%s' ),
		array( '%d' )
	);
	clean_post_cache( $target['ID'] );
}

WP_CLI::success( sprintf( 'Migrated %d rows. Rollback export: %s', count( $targets ), $backup_path ) );
