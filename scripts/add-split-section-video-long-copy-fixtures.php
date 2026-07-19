<?php
/**
 * Add long-copy Video and YouTube Split Section fixtures to the private guide.
 *
 * The fixtures reuse the exact long-copy text panel from the Text | Text
 * stress matrix, so their media panels can be checked against a deliberately
 * tall neighbouring column in both left and right arrangements.
 *
 * Usage:
 *   wp eval-file scripts/add-split-section-video-long-copy-fixtures.php
 *   wp eval-file scripts/add-split-section-video-long-copy-fixtures.php apply
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID = 5654;

$pns_video_long_copy_args = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();

foreach ( $pns_video_long_copy_args as $pns_video_long_copy_arg ) {
	if ( ! in_array( $pns_video_long_copy_arg, array( 'apply', '--apply' ), true ) ) {
		WP_CLI::error( sprintf( 'Unexpected argument: %s. Use only apply.', $pns_video_long_copy_arg ) );
	}
}

$pns_video_long_copy_apply       = in_array( 'apply', $pns_video_long_copy_args, true ) || in_array( '--apply', $pns_video_long_copy_args, true );
$pns_video_long_copy_page        = get_post( PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID );
$pns_video_long_copy_project_root = dirname( get_stylesheet_directory(), 5 );
$pns_video_long_copy_backup_dir  = $pns_video_long_copy_project_root . '/docs/jobs/split-section-video-long-copy-db-backups';
$pns_video_long_copy_fixtures    = array(
	array(
		'anchor' => 'pns-split-section-video-long-copy-media-left',
		'type'   => 'video',
		'layout' => 'media-left',
		'label'  => 'Hosted video · Media left · Long-copy stress test',
	),
	array(
		'anchor' => 'pns-split-section-video-long-copy-media-right',
		'type'   => 'video',
		'layout' => 'media-right',
		'label'  => 'Hosted video · Media right · Long-copy stress test',
	),
	array(
		'anchor' => 'pns-split-section-youtube-long-copy-media-left',
		'type'   => 'youtube',
		'layout' => 'media-left',
		'label'  => 'YouTube embed · Media left · Long-copy stress test',
	),
	array(
		'anchor' => 'pns-split-section-youtube-long-copy-media-right',
		'type'   => 'youtube',
		'layout' => 'media-right',
		'label'  => 'YouTube embed · Media right · Long-copy stress test',
	),
);

if (
	! $pns_video_long_copy_page instanceof WP_Post ||
	'page' !== $pns_video_long_copy_page->post_type ||
	'private' !== $pns_video_long_copy_page->post_status ||
	'pns-style-guide-split-section-components' !== $pns_video_long_copy_page->post_name
) {
	WP_CLI::error( 'The expected private Split Section Style Guide page is not available.' );
}

$pns_video_long_copy_blocks       = parse_blocks( $pns_video_long_copy_page->post_content );
$pns_video_long_copy_existing     = pns_video_long_copy_get_fixture_count( $pns_video_long_copy_blocks, $pns_video_long_copy_fixtures );
$pns_video_long_copy_expected     = count( $pns_video_long_copy_fixtures );

if ( $pns_video_long_copy_expected === $pns_video_long_copy_existing ) {
	pns_video_long_copy_assert_fixtures( $pns_video_long_copy_blocks, $pns_video_long_copy_fixtures );
	WP_CLI::success( 'The private Style Guide already has the four long-copy Video and YouTube fixtures.' );
	return;
}

if ( 0 !== $pns_video_long_copy_existing ) {
	WP_CLI::error( 'Refusing to alter a partially added long-copy Video/YouTube fixture set.' );
}

$pns_video_long_copy_source = pns_video_long_copy_find_by_anchor(
	$pns_video_long_copy_blocks,
	'pns-text-text-demo-text-text-long-long'
);

if ( ! is_array( $pns_video_long_copy_source ) || 'text' !== ( $pns_video_long_copy_source['attrs']['mediaType'] ?? '' ) ) {
	WP_CLI::error( 'The canonical Text | Text long-copy fixture is not available.' );
}

$pns_video_long_copy_source_column = pns_video_long_copy_get_column( $pns_video_long_copy_source, 'pns-split-section__copy-column' );
$pns_video_long_copy_source_group  = is_array( $pns_video_long_copy_source_column ) ? pns_video_long_copy_get_copy_group( $pns_video_long_copy_source_column ) : null;

if ( ! is_array( $pns_video_long_copy_source_group ) || ! str_contains( serialize_blocks( array( $pns_video_long_copy_source_group ) ), 'Long-form lead copy tests how the panel establishes hierarchy' ) ) {
	WP_CLI::error( 'The canonical Text | Text long-copy column does not have the expected content.' );
}

$pns_video_long_copy_new_blocks = array();

foreach ( $pns_video_long_copy_fixtures as $pns_video_long_copy_fixture ) {
	$pns_video_long_copy_template = pns_video_long_copy_find_by_media(
		$pns_video_long_copy_blocks,
		$pns_video_long_copy_fixture['type'],
		$pns_video_long_copy_fixture['layout']
	);

	if ( ! is_array( $pns_video_long_copy_template ) ) {
		WP_CLI::error( sprintf( 'The existing %s %s fixture is not available to clone.', $pns_video_long_copy_fixture['type'], $pns_video_long_copy_fixture['layout'] ) );
	}

	$pns_video_long_copy_copy_group = $pns_video_long_copy_source_group;

	if ( ! pns_video_long_copy_replace_heading( $pns_video_long_copy_copy_group, $pns_video_long_copy_fixture['label'] ) ) {
		WP_CLI::error( 'Could not update the copied long-text fixture heading.' );
	}

	$pns_video_long_copy_template['attrs']            = is_array( $pns_video_long_copy_template['attrs'] ?? null ) ? $pns_video_long_copy_template['attrs'] : array();
	$pns_video_long_copy_template['attrs']['align']   = 'full';
	$pns_video_long_copy_template['attrs']['anchor']  = $pns_video_long_copy_fixture['anchor'];
	$pns_video_long_copy_template['attrs']['mediaType'] = $pns_video_long_copy_fixture['type'];
	$pns_video_long_copy_template['attrs']['layoutVariant'] = $pns_video_long_copy_fixture['layout'];
	unset( $pns_video_long_copy_template['attrs']['metadata'] );

	$pns_video_long_copy_copy_column = pns_video_long_copy_get_column( $pns_video_long_copy_template, 'pns-split-section__copy-column' );

	if ( ! is_array( $pns_video_long_copy_copy_column ) || ! pns_video_long_copy_replace_copy_group_contents( $pns_video_long_copy_template, $pns_video_long_copy_copy_group ) ) {
		WP_CLI::error( 'Could not replace the short copy content in a long-copy fixture.' );
	}

	$pns_video_long_copy_new_blocks[] = $pns_video_long_copy_template;
}

$pns_video_long_copy_heading_blocks = parse_blocks(
	'<!-- wp:group {"className":"pns-split-section-video-long-copy-heading","layout":{"type":"constrained"}} -->'
	. '<div class="wp-block-group pns-split-section-video-long-copy-heading">'
	. '<!-- wp:heading --><h2 class="wp-block-heading">Video and YouTube long-copy stress tests</h2><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>These left and right pairings reuse the Text | Text long-copy fixture to expose overflow, alignment, and media-panel sizing issues.</p><!-- /wp:paragraph -->'
	. '</div><!-- /wp:group -->'
);

if ( 1 !== count( $pns_video_long_copy_heading_blocks ) ) {
	WP_CLI::error( 'Could not build the long-copy fixture heading.' );
}

$pns_video_long_copy_last_media_index = pns_video_long_copy_last_media_fixture_index( $pns_video_long_copy_blocks );

if ( null === $pns_video_long_copy_last_media_index ) {
	WP_CLI::error( 'Could not find the existing Video and YouTube fixture section.' );
}

array_splice(
	$pns_video_long_copy_blocks,
	$pns_video_long_copy_last_media_index + 1,
	0,
	array_merge( $pns_video_long_copy_heading_blocks, $pns_video_long_copy_new_blocks )
);

$pns_video_long_copy_updated_content = serialize_blocks( $pns_video_long_copy_blocks );
$pns_video_long_copy_updated_blocks  = parse_blocks( $pns_video_long_copy_updated_content );

pns_video_long_copy_assert_fixtures( $pns_video_long_copy_updated_blocks, $pns_video_long_copy_fixtures );

WP_CLI::log( 'Prepared four long-copy fixtures: native Video left/right and YouTube left/right.' );

if ( ! $pns_video_long_copy_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and update the private Style Guide.' );
	return;
}

$pns_video_long_copy_lock = (string) get_post_meta( PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID, '_edit_lock', true );

if ( '' !== $pns_video_long_copy_lock ) {
	$pns_video_long_copy_lock_parts = explode( ':', $pns_video_long_copy_lock, 2 );
	$pns_video_long_copy_lock_time  = isset( $pns_video_long_copy_lock_parts[0] ) ? (int) $pns_video_long_copy_lock_parts[0] : 0;

	if ( $pns_video_long_copy_lock_time > time() - 150 ) {
		WP_CLI::error( 'The private Style Guide has an active editor lock. Close the editor and retry after the lock expires.' );
	}
}

$pns_video_long_copy_live_content = get_post_field( 'post_content', PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID );

if ( ! is_string( $pns_video_long_copy_live_content ) || ! hash_equals( hash( 'sha256', $pns_video_long_copy_page->post_content ), hash( 'sha256', $pns_video_long_copy_live_content ) ) ) {
	WP_CLI::error( 'The private Style Guide changed after it was read; refusing to overwrite it.' );
}

if ( ! is_dir( $pns_video_long_copy_backup_dir ) && ! wp_mkdir_p( $pns_video_long_copy_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_video_long_copy_backup_dir ) );
}

$pns_video_long_copy_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'purpose'           => 'Add native Video and YouTube long-copy stress fixtures to the private Split Section Style Guide.',
	'post_id'           => PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID,
	'post_title'        => $pns_video_long_copy_page->post_title,
	'post_name'         => $pns_video_long_copy_page->post_name,
	'post_status'       => $pns_video_long_copy_page->post_status,
	'post_modified_gmt' => $pns_video_long_copy_page->post_modified_gmt,
	'content_sha256'    => hash( 'sha256', $pns_video_long_copy_page->post_content ),
	'post_content'      => $pns_video_long_copy_page->post_content,
);
$pns_video_long_copy_backup_json = wp_json_encode( $pns_video_long_copy_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$pns_video_long_copy_backup_path = trailingslashit( $pns_video_long_copy_backup_dir ) . gmdate( 'Ymd-His' ) . '-pre-video-long-copy-5654.json';
$pns_video_long_copy_temp_path   = $pns_video_long_copy_backup_path . '.tmp';

if (
	! is_string( $pns_video_long_copy_backup_json ) ||
	false === file_put_contents( $pns_video_long_copy_temp_path, $pns_video_long_copy_backup_json . "\n" ) ||
	! rename( $pns_video_long_copy_temp_path, $pns_video_long_copy_backup_path )
) {
	WP_CLI::error( sprintf( 'Could not write rollback data: %s', $pns_video_long_copy_backup_path ) );
}

$pns_video_long_copy_updated_id = wp_update_post(
	array(
		'ID'           => PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID,
		'post_content' => wp_slash( $pns_video_long_copy_updated_content ),
	),
	true
);

if ( is_wp_error( $pns_video_long_copy_updated_id ) ) {
	WP_CLI::error( $pns_video_long_copy_updated_id->get_error_message() );
}

clean_post_cache( PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID );
$pns_video_long_copy_saved = get_post( PNS_VIDEO_LONG_COPY_STYLE_GUIDE_ID );

if ( ! $pns_video_long_copy_saved instanceof WP_Post || ! hash_equals( hash( 'sha256', $pns_video_long_copy_updated_content ), hash( 'sha256', $pns_video_long_copy_saved->post_content ) ) ) {
	WP_CLI::error( 'WordPress saved content does not match the generated long-copy fixtures.' );
}

WP_CLI::success( sprintf( 'Added long-copy Video and YouTube fixtures to the private Style Guide. Rollback export: %s', $pns_video_long_copy_backup_path ) );

/**
 * Count saved fixture anchors.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,array<string,string>> $fixtures Expected fixture definitions.
 * @return int
 */
function pns_video_long_copy_get_fixture_count( array $blocks, array $fixtures ): int {
	$count = 0;

	foreach ( $fixtures as $fixture ) {
		if ( is_array( pns_video_long_copy_find_by_anchor( $blocks, $fixture['anchor'] ) ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Find a Split Section by anchor.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param string                         $anchor Expected block anchor.
 * @return array<string,mixed>|null
 */
function pns_video_long_copy_find_by_anchor( array $blocks, string $anchor ): ?array {
	foreach ( $blocks as $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && $anchor === ( $block['attrs']['anchor'] ?? '' ) ) {
			return $block;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$found = pns_video_long_copy_find_by_anchor( $block['innerBlocks'], $anchor );

			if ( is_array( $found ) ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Find an existing canonical Video or YouTube Split Section fixture.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param string                         $type Media type.
 * @param string                         $layout Layout variant.
 * @return array<string,mixed>|null
 */
function pns_video_long_copy_find_by_media( array $blocks, string $type, string $layout ): ?array {
	foreach ( $blocks as $block ) {
		if (
			'pns/split-section' === ( $block['blockName'] ?? '' ) &&
			$type === ( $block['attrs']['mediaType'] ?? '' ) &&
			(
				$layout === ( $block['attrs']['layoutVariant'] ?? '' ) ||
				( 'media-right' === $layout && '' === ( $block['attrs']['layoutVariant'] ?? '' ) )
			)
		) {
			return $block;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$found = pns_video_long_copy_find_by_media( $block['innerBlocks'], $type, $layout );

			if ( is_array( $found ) ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Get a direct split-section column by its class marker.
 *
 * @param array<string,mixed> $block Parsed Split Section.
 * @param string              $class_marker Required class marker.
 * @return array<string,mixed>|null
 */
function pns_video_long_copy_get_column( array $block, string $class_marker ): ?array {
	$columns = $block['innerBlocks'][0]['innerBlocks'] ?? array();

	foreach ( $columns as $column ) {
		if ( 'core/column' === ( $column['blockName'] ?? '' ) && str_contains( (string) ( $column['attrs']['className'] ?? '' ), $class_marker ) ) {
			return $column;
		}
	}

	return null;
}

/**
 * Find the copy Group inside a text column.
 *
 * @param array<string,mixed> $block Parsed text column.
 * @return array<string,mixed>|null
 */
function pns_video_long_copy_get_copy_group( array $block ): ?array {
	if ( 'core/group' === ( $block['blockName'] ?? '' ) && str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'pns-split-section__copy' ) ) {
		return $block;
	}

	foreach ( $block['innerBlocks'] as $inner_block ) {
		$found = pns_video_long_copy_get_copy_group( $inner_block );

		if ( is_array( $found ) ) {
			return $found;
		}
	}

	return null;
}

/**
 * Replace a text column's copy Group children while retaining its wrapper styles.
 *
 * @param array<string,mixed> $block Replacement target, passed by reference.
 * @param array<string,mixed> $source_group Canonical Text | Text copy Group.
 * @return bool
 */
function pns_video_long_copy_replace_copy_group_contents( array &$block, array $source_group ): bool {
	if ( 'core/group' === ( $block['blockName'] ?? '' ) && str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'pns-split-section__copy' ) ) {
		$inner_content = $block['innerContent'] ?? array();
		$opening       = $inner_content[0] ?? '';
		$closing       = $inner_content[ count( $inner_content ) - 1 ] ?? '';
		$children      = $source_group['innerBlocks'] ?? array();

		if ( ! is_array( $children ) || '' === $opening || '' === $closing ) {
			return false;
		}

		$block['innerBlocks']  = $children;
		$block['innerContent'] = array_merge( array( $opening ), array_fill( 0, count( $children ), null ), array( $closing ) );
		return true;
	}

	foreach ( $block['innerBlocks'] as &$inner_block ) {
		if ( pns_video_long_copy_replace_copy_group_contents( $inner_block, $source_group ) ) {
			unset( $inner_block );
			return true;
		}
	}
	unset( $inner_block );

	return false;
}

/**
 * Replace the first heading in a copied text column.
 *
 * @param array<string,mixed> $block Parsed block, passed by reference.
 * @param string              $label New heading label.
 * @return bool
 */
function pns_video_long_copy_replace_heading( array &$block, string $label ): bool {
	if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
		$markup                 = '<h3 class="wp-block-heading">' . esc_html( $label ) . '</h3>';
		$block['innerHTML']     = $markup;
		$block['innerContent']  = array( $markup );
		$block['attrs']['level'] = 3;
		return true;
	}

	foreach ( $block['innerBlocks'] as &$inner_block ) {
		if ( pns_video_long_copy_replace_heading( $inner_block, $label ) ) {
			unset( $inner_block );
			return true;
		}
	}
	unset( $inner_block );

	return false;
}

/**
 * Find the final top-level canonical media fixture.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed page blocks.
 * @return int|null
 */
function pns_video_long_copy_last_media_fixture_index( array $blocks ): ?int {
	$last_index = null;

	foreach ( $blocks as $index => $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && in_array( $block['attrs']['mediaType'] ?? '', array( 'video', 'youtube' ), true ) ) {
			$last_index = $index;
		}
	}

	return $last_index;
}

/**
 * Verify the expected fixtures retain their intended media type, layout and long copy.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed page blocks.
 * @param array<int,array<string,string>> $fixtures Expected fixture definitions.
 */
function pns_video_long_copy_assert_fixtures( array $blocks, array $fixtures ): void {
	foreach ( $fixtures as $fixture ) {
		$block = pns_video_long_copy_find_by_anchor( $blocks, $fixture['anchor'] );

		if (
			! is_array( $block ) ||
			$fixture['type'] !== ( $block['attrs']['mediaType'] ?? '' ) ||
			$fixture['layout'] !== ( $block['attrs']['layoutVariant'] ?? '' )
		) {
			WP_CLI::error( sprintf( 'Fixture %s does not have its expected media type and layout.', $fixture['anchor'] ) );
		}

		$copy_column = pns_video_long_copy_get_column( $block, 'pns-split-section__copy-column' );
		$serialized  = is_array( $copy_column ) ? serialize_blocks( array( $copy_column ) ) : '';

		if ( ! str_contains( $serialized, 'Long-form lead copy tests how the panel establishes hierarchy' ) || ! str_contains( $serialized, 'Third point confirms the list inherits the panel text colour.' ) ) {
			WP_CLI::error( sprintf( 'Fixture %s does not retain the canonical long-copy stress text.', $fixture['anchor'] ) );
		}
	}
}
