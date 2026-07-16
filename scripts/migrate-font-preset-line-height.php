<?php
/**
 * Migrate saved title font-size presets to explicit line-height block styles.
 *
 * Usage:
 *   wp eval-file scripts/migrate-font-preset-line-height.php
 *   wp eval-file scripts/migrate-font-preset-line-height.php apply
 *
 * Dry-run is the default. Apply writes rollback exports before mutating current
 * non-revision rows. Revisions are counted for evidence but never mutated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$arguments = isset( $args ) && is_array( $args ) ? $args : array();
$apply     = in_array( '--apply', $arguments, true ) || in_array( 'apply', $arguments, true );
$backup_dir = dirname( ABSPATH, 2 ) . '/docs/jobs/font-preset-line-height-db-backups';

$line_height_by_font_size = array(
	'title-large'   => 'var(--wp--custom--typography--line-height--heading-compact)',
	'title-display' => 'var(--wp--custom--typography--line-height--display-tight)',
);

$heading_like_blocks = array(
	'core/heading'     => true,
	'core/post-title'  => true,
	'core/query-title' => true,
	'core/site-title'  => true,
);

/**
 * Return true when content contains a target title preset.
 */
function pns_font_preset_line_height_contains_target( string $content ): bool {
	return str_contains( $content, 'has-title-large-font-size' )
		|| str_contains( $content, 'has-title-display-font-size' )
		|| str_contains( $content, '"fontSize":"title-large"' )
		|| str_contains( $content, '"fontSize":"title-display"' );
}

/**
 * Recursively add explicit line-height to title preset blocks.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,array<string,string>> $changes Change records.
 * @param array<string,string> $line_height_by_font_size Preset map.
 * @return array<int,array<string,mixed>>
 */
function pns_migrate_font_preset_line_height_blocks( array $blocks, array &$changes, array $line_height_by_font_size, array $heading_like_blocks ): array {
	foreach ( $blocks as &$block ) {
		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_migrate_font_preset_line_height_blocks(
				$block['innerBlocks'],
				$changes,
				$line_height_by_font_size,
				$heading_like_blocks
			);
		}

		$block_name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : 'unknown';
		$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$font_size = isset( $attrs['fontSize'] ) && is_string( $attrs['fontSize'] ) ? $attrs['fontSize'] : '';

		if ( ! isset( $line_height_by_font_size[ $font_size ] ) ) {
			continue;
		}

		if ( isset( $heading_like_blocks[ $block_name ] ) ) {
			continue;
		}

		$style     = isset( $attrs['style'] ) && is_array( $attrs['style'] ) ? $attrs['style'] : array();
		$typography = isset( $style['typography'] ) && is_array( $style['typography'] ) ? $style['typography'] : array();

		if ( ! empty( $typography['lineHeight'] ) ) {
			continue;
		}

		$typography['lineHeight'] = $line_height_by_font_size[ $font_size ];
		$style['typography']     = $typography;
		$attrs['style']          = $style;
		$block['attrs']          = $attrs;

		$changes[] = array(
			'block'       => $block_name,
			'fontSize'    => $font_size,
			'lineHeight'  => $line_height_by_font_size[ $font_size ],
		);
	}

	return $blocks;
}

global $wpdb;

$rows = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title, post_content
	FROM {$wpdb->posts}
	WHERE post_content LIKE '%title-large%'
		OR post_content LIKE '%title-display%'
	ORDER BY post_type, ID"
);

$revision_count = 0;
$targets        = array();
$backups        = array();

foreach ( $rows as $row ) {
	if ( ! pns_font_preset_line_height_contains_target( $row->post_content ) ) {
		continue;
	}

	if ( 'revision' === $row->post_type ) {
		$revision_count++;
		continue;
	}

	$changes = array();
	$blocks  = parse_blocks( $row->post_content );
	$updated = serialize_blocks(
		pns_migrate_font_preset_line_height_blocks(
			$blocks,
			$changes,
			$line_height_by_font_size,
			$heading_like_blocks
		)
	);

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
		WP_CLI::log(
			sprintf(
				'  - %s fontSize=%s lineHeight=%s',
				$change['block'],
				$change['fontSize'],
				$change['lineHeight']
			)
		);
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

$backup_path = trailingslashit( $backup_dir ) . gmdate( 'Ymd-His' ) . '-font-preset-line-height.json';
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
