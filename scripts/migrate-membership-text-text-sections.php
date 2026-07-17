<?php
/**
 * Migrate the two Membership Text | Text sections to pns/split-section.
 *
 * Usage:
 *   wp eval-file scripts/migrate-membership-text-text-sections.php 7020
 *   wp eval-file scripts/migrate-membership-text-text-sections.php 7020 apply
 *
 * Dry-run is the default. Apply writes a complete JSON rollback export before
 * updating the exact, recognized legacy sections.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_membership_split_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_membership_split_id    = isset( $pns_membership_split_args[0] ) ? (int) $pns_membership_split_args[0] : 0;
$pns_membership_split_apply = in_array( 'apply', $pns_membership_split_args, true ) || in_array( '--apply', $pns_membership_split_args, true );
$pns_membership_split_page  = get_post( $pns_membership_split_id );

if ( 7020 !== $pns_membership_split_id ) {
	WP_CLI::error( 'Pass the explicit Membership page ID: 7020 [apply].' );
}

if ( ! $pns_membership_split_page instanceof WP_Post || 'page' !== $pns_membership_split_page->post_type || 'publish' !== $pns_membership_split_page->post_status || 'membership' !== $pns_membership_split_page->post_name ) {
	WP_CLI::error( 'Page 7020 is no longer the expected published Membership page.' );
}

$pns_membership_split_template = get_post_meta( $pns_membership_split_id, '_wp_page_template', true );

if ( 'page-light-surface-wide-content' !== $pns_membership_split_template ) {
	WP_CLI::error( sprintf( 'Membership uses "%s", not the expected wide-content template.', $pns_membership_split_template ) );
}

$pns_membership_split_blocks        = parse_blocks( $pns_membership_split_page->post_content );
$pns_membership_split_legacy_indexes = array();
$pns_membership_split_current_count  = 0;

foreach ( $pns_membership_split_blocks as $pns_membership_split_index => $pns_membership_split_block ) {
	if (
		'pns/split-section' === $pns_membership_split_block['blockName']
		&& (
			'text' === ( $pns_membership_split_block['attrs']['mediaType'] ?? '' )
			|| 'text-text' === ( $pns_membership_split_block['attrs']['layoutVariant'] ?? '' )
		)
	) {
		++$pns_membership_split_current_count;
		continue;
	}

	if ( pns_membership_split_is_legacy_section( $pns_membership_split_block ) ) {
		$pns_membership_split_legacy_indexes[] = $pns_membership_split_index;
	}
}

if ( 2 === $pns_membership_split_current_count && 0 === count( $pns_membership_split_legacy_indexes ) ) {
	WP_CLI::success( 'Membership already contains two Text | Text Split Sections; nothing to migrate.' );
	return;
}

if ( 0 !== $pns_membership_split_current_count || 2 !== count( $pns_membership_split_legacy_indexes ) ) {
	WP_CLI::error(
		sprintf(
			'Expected exactly two legacy sections or two migrated sections; found %d legacy and %d migrated.',
			count( $pns_membership_split_legacy_indexes ),
			$pns_membership_split_current_count
		)
	);
}

$pns_membership_split_expected_headings = array(
	array( 'Our Voice', 'Challenge through truth' ),
	array( 'Why Monthly Giving Matters', 'Join Today' ),
);

foreach ( $pns_membership_split_legacy_indexes as $pns_membership_split_position => $pns_membership_split_index ) {
	$pns_membership_split_headings = pns_membership_split_section_headings( $pns_membership_split_blocks[ $pns_membership_split_index ] );

	foreach ( $pns_membership_split_expected_headings[ $pns_membership_split_position ] as $pns_membership_split_expected_heading ) {
		if ( ! str_contains( implode( ' ', $pns_membership_split_headings ), $pns_membership_split_expected_heading ) ) {
			WP_CLI::error( sprintf( 'Legacy section %d no longer contains expected heading "%s".', $pns_membership_split_position + 1, $pns_membership_split_expected_heading ) );
		}
	}
}

WP_CLI::log( sprintf( 'Membership #%d: exactly two recognized legacy Text | Text sections will be migrated.', $pns_membership_split_id ) );

if ( ! $pns_membership_split_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with 7020 apply to migrate.' );
	return;
}

$pns_membership_split_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/membership-split-section-db-backups';

if ( ! is_dir( $pns_membership_split_backup_dir ) && ! wp_mkdir_p( $pns_membership_split_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_membership_split_backup_dir ) );
}

$pns_membership_split_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'post_id'           => $pns_membership_split_id,
	'post_name'         => $pns_membership_split_page->post_name,
	'post_status'       => $pns_membership_split_page->post_status,
	'post_modified_gmt' => $pns_membership_split_page->post_modified_gmt,
	'wp_page_template'  => $pns_membership_split_template,
	'content_sha256'    => hash( 'sha256', $pns_membership_split_page->post_content ),
	'post_content'      => $pns_membership_split_page->post_content,
);
$pns_membership_split_backup_path = trailingslashit( $pns_membership_split_backup_dir ) . gmdate( 'Ymd-His' ) . '-membership-7020.json';

if ( false === file_put_contents( $pns_membership_split_backup_path, wp_json_encode( $pns_membership_split_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_membership_split_backup_path ) );
}

foreach ( $pns_membership_split_legacy_indexes as $pns_membership_split_index ) {
	$pns_membership_split_blocks[ $pns_membership_split_index ] = pns_membership_split_migrated_block( $pns_membership_split_blocks[ $pns_membership_split_index ] );
}

$pns_membership_split_new_content = serialize_blocks( $pns_membership_split_blocks );
$pns_membership_split_updated_id  = wp_update_post(
	array(
		'ID'           => $pns_membership_split_id,
		'post_content' => wp_slash( $pns_membership_split_new_content ),
	),
	true
);

if ( is_wp_error( $pns_membership_split_updated_id ) ) {
	WP_CLI::error( $pns_membership_split_updated_id->get_error_message() );
}

clean_post_cache( $pns_membership_split_id );
WP_CLI::success( sprintf( 'Migrated two Membership Text | Text sections. Rollback export: %s', $pns_membership_split_backup_path ) );

/**
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_membership_split_is_legacy_section( $block ) {
	if ( 'core/columns' !== ( $block['blockName'] ?? '' ) || 2 !== count( $block['innerBlocks'] ?? array() ) ) {
		return false;
	}

	foreach ( $block['innerBlocks'] as $column ) {
		if ( 'core/column' !== ( $column['blockName'] ?? '' ) || 1 !== count( $column['innerBlocks'] ?? array() ) ) {
			return false;
		}

		$group = $column['innerBlocks'][0];
		if ( 'core/group' !== ( $group['blockName'] ?? '' ) || ! str_contains( $group['attrs']['className'] ?? '', 'pns-text-only-section__inner' ) ) {
			return false;
		}
	}

	return true;
}

/**
 * @param array<string,mixed> $block Legacy Columns block.
 * @return array<int,string>
 */
function pns_membership_split_section_headings( $block ) {
	$headings = array();

	foreach ( $block['innerBlocks'] as $column ) {
		foreach ( $column['innerBlocks'][0]['innerBlocks'] as $inner_block ) {
			if ( 'core/heading' === ( $inner_block['blockName'] ?? '' ) ) {
				$headings[] = wp_strip_all_tags( $inner_block['innerHTML'] ?? '' );
			}
		}
	}

	return $headings;
}

/**
 * @param array<string,mixed> $legacy Legacy Columns block.
 * @return array<string,mixed>
 */
function pns_membership_split_migrated_block( $legacy ) {
	$panel_backgrounds = array( 'brand-purple', 'heritage-green' );
	$panel_blocks      = array();

	foreach ( $legacy['innerBlocks'] as $panel_index => $legacy_column ) {
		$legacy_group = $legacy_column['innerBlocks'][0];
		$leaf_markup  = serialize_blocks( $legacy_group['innerBlocks'] );
		$background   = $panel_backgrounds[ $panel_index ];
		$column_attrs = array(
			'backgroundColor' => $background,
			'textColor'       => 'neutral-0',
			'className'       => 'pns-split-section__copy-column pns-split-section__text-column',
		);
		$column_html  = '<div class="wp-block-column pns-split-section__copy-column pns-split-section__text-column has-neutral-0-color has-' . esc_attr( $background ) . '-background-color has-text-color has-background">'
			. '<!-- wp:group {"className":"pns-split-section__copy"} --><div class="wp-block-group pns-split-section__copy">'
			. $leaf_markup
			. '</div><!-- /wp:group --></div>';
		$panel_blocks[] = parse_blocks(
			'<!-- wp:column ' . wp_json_encode( $column_attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' -->'
			. $column_html
			. '<!-- /wp:column -->'
		)[0];
	}

	$columns_markup = '<!-- wp:columns {"align":"full","className":"pns-split-section__columns"} -->'
		. '<div class="wp-block-columns alignfull pns-split-section__columns">'
		. serialize_blocks( $panel_blocks )
		. '</div><!-- /wp:columns -->';
	$split_markup   = '<!-- wp:pns/split-section {"mediaType":"text","layoutVariant":"edge-media-right","align":"full"} -->'
		. $columns_markup
		. '<!-- /wp:pns/split-section -->';

	return parse_blocks( $split_markup )[0];
}
