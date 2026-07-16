<?php
/**
 * Add approved site-frame and surface markers to DB-owned block roots.
 *
 * Usage:
 *   wp eval-file scripts/migrate-site-frame-panels.php
 *   wp eval-file scripts/migrate-site-frame-panels.php apply
 *
 * The dry run is read-only. Apply writes a rollback JSON export before
 * updating published content, templates, and saved synced sections.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_site_frame_panel_args       = isset( $args ) && is_array( $args ) ? $args : array();
$pns_site_frame_panel_apply      = in_array( 'apply', $pns_site_frame_panel_args, true ) || in_array( '--apply', $pns_site_frame_panel_args, true );
$pns_site_frame_panel_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/site-frame-panel-db-backups';

/**
 * Return whether a saved class string contains one exact token.
 *
 * @param string $class_name Saved block class string.
 * @param string $token Target class token.
 * @return bool
 */
function pns_site_frame_panel_has_class( string $class_name, string $token ): bool {
	$tokens = preg_split( '/\s+/', trim( $class_name ) );

	return is_array( $tokens ) && in_array( $token, $tokens, true );
}

/**
 * Add a class token once while preserving the existing class order.
 *
 * @param string $class_name Saved block class string.
 * @param string $token Target class token.
 * @return string
 */
function pns_site_frame_panel_add_class( string $class_name, string $token ): string {
	if ( pns_site_frame_panel_has_class( $class_name, $token ) ) {
		return $class_name;
	}

	return trim( $class_name . ' ' . $token );
}

/**
 * Identify approved panel roots by block type and existing semantic class.
 *
 * @param string $block_name Parsed block name.
 * @param string $class_name Saved block class string.
 * @return string|null Human-readable panel role or null when not a target.
 */
function pns_site_frame_panel_role( string $block_name, string $class_name ): ?string {
	if ( pns_site_frame_panel_has_class( $class_name, 'pns-page-hero' ) ) {
		return 'Page hero';
	}

	if ( pns_site_frame_panel_has_class( $class_name, 'pns-welcome-header' ) ) {
		return 'Welcome hero';
	}

	if ( 'core/cover' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-suffragette-hero' ) ) {
		return 'Herstory hero cover';
	}

	if ( 'core/cover' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-quotes' ) ) {
		return 'Quote cover';
	}

	if ( 'core/columns' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-suffragette-image-strip' ) ) {
		return 'Suffragette image strip';
	}

	if ( 'core/columns' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-image-strip' ) ) {
		return 'Image strip';
	}

	if ( 'core/group' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-suffragette-facts' ) ) {
		return 'Suffragette Facts panel';
	}

	if ( 'core/group' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-suffragette-quote' ) ) {
		return 'Suffragette quote surface';
	}

	if ( 'core/group' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-contact-form' ) ) {
		return 'Stay in Touch panel';
	}

	if ( 'core/group' === $block_name && pns_site_frame_panel_has_class( $class_name, 'pns-connect-social' ) ) {
		return 'Connect Social panel';
	}

	$group_roles = array(
		'pns-basic-centred-content' => 'Basic centred content panel',
		'pns-text-only-section'     => 'Text-only section panel',
		'pns-two-columns'           => 'Two-columns panel',
		'pns-entry-navigation'      => 'Entry navigation panel',
		'pns-suffragette-stats'     => 'Suffragette stats panel',
		'pns-news-more-section'     => 'More News panel',
		'pns-shop-storefront'       => 'Shop storefront panel',
		'pns-read-all-about-it'     => 'Read All About It panel',
		'pns-shop-intro'            => 'Shop intro panel',
	);

	foreach ( $group_roles as $class => $role ) {
		if ( pns_site_frame_panel_has_class( $class_name, $class ) ) {
			return $role;
		}
	}

	return null;
}

/**
 * Update the saved wrapper HTML that parse_blocks retains alongside attrs.
 *
 * @param array<string,mixed> $block Parsed block.
 * @param string              $old_class_name Original className attribute.
 * @param string              $new_class_name Replacement className attribute.
 * @return array<string,mixed>
 */
function pns_site_frame_panel_replace_saved_html( array $block, string $old_class_name, string $new_class_name ): array {
	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$block['innerHTML'] = str_replace( $old_class_name, $new_class_name, $block['innerHTML'] );
	}

	if ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$inner_content ) {
			if ( is_string( $inner_content ) ) {
				$inner_content = str_replace( $old_class_name, $new_class_name, $inner_content );
			}
		}
		unset( $inner_content );
	}

	return $block;
}

/**
 * Give the editor-owned welcome hero its explicit purple dark-surface contract.
 *
 * Gutenberg keeps the opening cover markup in both innerHTML and innerContent,
 * so retain the serialized markup while updating its semantic block attributes.
 *
 * @param array<string,mixed> $block Parsed cover block.
 * @param string              $class_name Saved className including pns-dark-surface.
 * @return array<string,mixed>
 */
function pns_site_frame_panel_apply_welcome_hero_surface( array $block, string $class_name ): array {
	$replace_html = static function ( string $html ) use ( $class_name ): string {
		$html = str_replace( ' is-light ', ' ', $html );
		$html = str_replace(
			$class_name . '"',
			$class_name . ' has-brand-purple-background-color has-background"',
			$html
		);

		return str_replace(
			'wp-block-cover__background has-background-dim-0 has-background-dim',
			'wp-block-cover__background has-background-dim-0 has-background-dim has-brand-purple-background-color has-background',
			$html
		);
	};

	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$block['innerHTML'] = $replace_html( $block['innerHTML'] );
	}

	if ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$inner_content ) {
			if ( is_string( $inner_content ) ) {
				$inner_content = $replace_html( $inner_content );
			}
		}
		unset( $inner_content );
	}

	return $block;
}

/**
 * Recursively add the panel marker to approved saved block roots.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,string>              $changes Change descriptions.
 * @return array<int,array<string,mixed>>
 */
function pns_site_frame_panel_migrate_blocks( array $blocks, array &$changes ): array {
	foreach ( $blocks as &$block ) {
		$block_name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';
		$role       = pns_site_frame_panel_role( $block_name, $class_name );

		if ( null !== $role && ! pns_site_frame_panel_has_class( $class_name, 'pns-site-frame-panel' ) ) {
			$new_class_name    = pns_site_frame_panel_add_class( $class_name, 'pns-site-frame-panel' );
			$attrs['className'] = $new_class_name;
			$block['attrs']     = $attrs;
			$block              = pns_site_frame_panel_replace_saved_html( $block, $class_name, $new_class_name );
			$changes[]          = $role;
			$class_name         = $new_class_name;
		}

		if (
			'core/cover' === $block_name
			&& pns_site_frame_panel_has_class( $class_name, 'pns-welcome-header' )
			&& ! pns_site_frame_panel_has_class( $class_name, 'pns-dark-surface' )
		) {
			$new_class_name          = pns_site_frame_panel_add_class( $class_name, 'pns-dark-surface' );
			$attrs['className']      = $new_class_name;
			$attrs['backgroundColor'] = 'brand-purple';
			$attrs['isDark']         = true;
			$block['attrs']          = $attrs;
			$block                   = pns_site_frame_panel_replace_saved_html( $block, $class_name, $new_class_name );
			$block                   = pns_site_frame_panel_apply_welcome_hero_surface( $block, $new_class_name );
			$changes[]               = 'Welcome hero dark surface';
		}

		if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_site_frame_panel_migrate_blocks( $block['innerBlocks'], $changes );
		}
	}
	unset( $block );

	return $blocks;
}

/**
 * Ensure the DB-owned Home template has the same light page surface as its
 * file-backed source before its child panels are capped.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,string>              $changes Change descriptions.
 * @return array<int,array<string,mixed>>
 */
function pns_site_frame_panel_migrate_template_surface( array $blocks, array &$changes ): array {
	foreach ( $blocks as &$block ) {
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';

		if ( pns_site_frame_panel_has_class( $class_name, 'pns-template-home' ) && ! pns_site_frame_panel_has_class( $class_name, 'pns-light-surface' ) ) {
			$new_class_name    = pns_site_frame_panel_add_class( $class_name, 'pns-light-surface' );
			$attrs['className'] = $new_class_name;
			$block['attrs']     = $attrs;
			$block              = pns_site_frame_panel_replace_saved_html( $block, $class_name, $new_class_name );
			$changes[]          = 'Home template light surface';
		}

		if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_site_frame_panel_migrate_template_surface( $block['innerBlocks'], $changes );
		}
	}
	unset( $block );

	return $blocks;
}

global $wpdb;

$pns_site_frame_panel_rows = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title, post_content
	FROM {$wpdb->posts}
	WHERE post_status = 'publish'
		AND post_type IN ('page', 'post', 'herstory', 'wp_template', 'wp_block')
		AND (
			post_content LIKE '%pns-page-hero%'
			OR post_content LIKE '%pns-welcome-header%'
			OR post_content LIKE '%pns-suffragette-hero%'
			OR post_content LIKE '%pns-quotes%'
			OR post_content LIKE '%pns-suffragette-image-strip%'
			OR post_content LIKE '%pns-image-strip%'
			OR post_content LIKE '%pns-suffragette-facts%'
			OR post_content LIKE '%pns-suffragette-quote%'
			OR post_content LIKE '%pns-contact-form%'
			OR post_content LIKE '%pns-connect-social%'
			OR post_content LIKE '%pns-basic-centred-content%'
			OR post_content LIKE '%pns-text-only-section%'
			OR post_content LIKE '%pns-two-columns%'
			OR post_content LIKE '%pns-entry-navigation%'
			OR post_content LIKE '%pns-suffragette-stats%'
			OR post_content LIKE '%pns-news-more-section%'
			OR post_content LIKE '%pns-shop-storefront%'
			OR post_content LIKE '%pns-read-all-about-it%'
			OR post_content LIKE '%pns-shop-intro%'
		)
	ORDER BY post_type, ID"
);

$pns_site_frame_panel_targets = array();
$pns_site_frame_panel_backups = array();

foreach ( $pns_site_frame_panel_rows as $pns_site_frame_panel_row ) {
	$changes = array();
	$blocks  = parse_blocks( $pns_site_frame_panel_row->post_content );
	$blocks  = pns_site_frame_panel_migrate_blocks( $blocks, $changes );
	$blocks  = pns_site_frame_panel_migrate_template_surface( $blocks, $changes );
	$updated = serialize_blocks( $blocks );

	if ( empty( $changes ) || $updated === $pns_site_frame_panel_row->post_content ) {
		continue;
	}

	$pns_site_frame_panel_targets[] = array(
		'ID'           => (int) $pns_site_frame_panel_row->ID,
		'post_type'    => $pns_site_frame_panel_row->post_type,
		'post_status'  => $pns_site_frame_panel_row->post_status,
		'post_title'   => $pns_site_frame_panel_row->post_title,
		'changes'      => $changes,
		'post_content' => $updated,
	);
	$pns_site_frame_panel_backups[] = array(
		'ID'           => (int) $pns_site_frame_panel_row->ID,
		'post_type'    => $pns_site_frame_panel_row->post_type,
		'post_status'  => $pns_site_frame_panel_row->post_status,
		'post_title'   => $pns_site_frame_panel_row->post_title,
		'post_content' => $pns_site_frame_panel_row->post_content,
	);
}

WP_CLI::log( sprintf( 'Current rows requiring site-frame panel migration: %d', count( $pns_site_frame_panel_targets ) ) );

foreach ( $pns_site_frame_panel_targets as $pns_site_frame_panel_target ) {
	WP_CLI::log(
		sprintf(
			'#%d [%s/%s] %s',
			$pns_site_frame_panel_target['ID'],
			$pns_site_frame_panel_target['post_type'],
			$pns_site_frame_panel_target['post_status'],
			'' !== $pns_site_frame_panel_target['post_title'] ? $pns_site_frame_panel_target['post_title'] : '(no title)'
		)
	);

	foreach ( $pns_site_frame_panel_target['changes'] as $pns_site_frame_panel_change ) {
		WP_CLI::log( sprintf( '  - %s', $pns_site_frame_panel_change ) );
	}
}

if ( ! $pns_site_frame_panel_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and update current rows.' );
	return;
}

if ( empty( $pns_site_frame_panel_targets ) ) {
	WP_CLI::success( 'No rows require migration.' );
	return;
}

if ( ! is_dir( $pns_site_frame_panel_backup_dir ) && ! wp_mkdir_p( $pns_site_frame_panel_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_site_frame_panel_backup_dir ) );
}

$pns_site_frame_panel_backup_path = trailingslashit( $pns_site_frame_panel_backup_dir ) . gmdate( 'Ymd-His' ) . '-site-frame-panels.json';
file_put_contents(
	$pns_site_frame_panel_backup_path,
	wp_json_encode(
		array(
			'created_at_gmt' => gmdate( 'c' ),
			'rows'           => $pns_site_frame_panel_backups,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	)
);

foreach ( $pns_site_frame_panel_targets as $pns_site_frame_panel_target ) {
	$wpdb->update(
		$wpdb->posts,
		array( 'post_content' => $pns_site_frame_panel_target['post_content'] ),
		array( 'ID' => $pns_site_frame_panel_target['ID'] ),
		array( '%s' ),
		array( '%d' )
	);
	clean_post_cache( $pns_site_frame_panel_target['ID'] );
}

WP_CLI::success( sprintf( 'Migrated %d current rows. Rollback export: %s', count( $pns_site_frame_panel_targets ), $pns_site_frame_panel_backup_path ) );
