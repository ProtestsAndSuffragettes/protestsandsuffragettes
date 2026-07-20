<?php
/**
 * Canonicalise the private Split Section Style Guide video fixtures.
 *
 * The page predates the distinct Video file and YouTube Split Section
 * variations. It currently has four YouTube embeds with no saved mediaType
 * and no native-video examples. This script keeps the four layout examples as
 * canonical YouTube variants and adds four matching native Video variants.
 *
 * Usage:
 *   wp eval-file scripts/refactor-split-section-style-guide-video-variants.php
 *   wp eval-file scripts/refactor-split-section-style-guide-video-variants.php apply
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_style_guide_args = isset( $args ) && is_array( $args ) ? $args : array();

foreach ( $pns_style_guide_args as $pns_style_guide_arg ) {
	if ( ! in_array( $pns_style_guide_arg, array( 'apply', '--apply' ), true ) ) {
		WP_CLI::error( sprintf( 'Unexpected argument: %s. Use only apply.', $pns_style_guide_arg ) );
	}
}

$pns_style_guide_apply       = in_array( 'apply', $pns_style_guide_args, true ) || in_array( '--apply', $pns_style_guide_args, true );
$pns_style_guide_post_id     = 5654;
$pns_style_guide_video_id    = 1296;
$pns_style_guide_video_url   = 'http://localhost:10008/wp-content/uploads/2022/09/ezgif.com-gif-maker.mp4';
$pns_style_guide_backup_dir  = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/split-section-style-guide-db-backups';

/**
 * Return the nested media block from a Split Section fixture.
 *
 * @param array<string,mixed> $block Parsed Split Section block.
 * @return array<string,mixed>
 */
function pns_style_guide_get_split_media_block( array $block ): array {
	return $block['innerBlocks'][0]['innerBlocks'][1]['innerBlocks'][0] ?? array();
}

/**
 * Return a human-readable label for one saved layout option.
 *
 * @param array<string,mixed> $block Parsed Split Section block.
 * @return string
 */
function pns_style_guide_layout_label( array $block ): string {
	$layout = $block['attrs']['layoutVariant'] ?? 'media-right';
	$labels = array(
		'edge-media-left'  => 'Edge media left',
		'edge-media-right' => 'Edge media right',
		'media-left'       => 'Media left',
		'media-right'      => 'Media right',
	);

	return $labels[ $layout ] ?? 'Media right';
}

/**
 * Replace the first heading and paragraph in a fixture's copy column.
 *
 * @param array<string,mixed> $block Parsed Split Section block.
 * @param string              $heading New heading.
 * @param string              $paragraph New paragraph.
 * @return array<string,mixed>
 */
function pns_style_guide_label_split_fixture( array $block, string $heading, string $paragraph ): array {
	$copy_blocks = &$block['innerBlocks'][0]['innerBlocks'][0]['innerBlocks'][0]['innerBlocks'];

	foreach ( $copy_blocks as &$copy_block ) {
		if ( 'core/heading' === ( $copy_block['blockName'] ?? '' ) ) {
			$markup                     = '<h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2>';
			$copy_block['innerHTML']    = $markup;
			$copy_block['innerContent'] = array( $markup );
			continue;
		}

		if ( 'core/paragraph' === ( $copy_block['blockName'] ?? '' ) && empty( $copy_block['attrs']['className'] ) ) {
			$markup                     = '<p>' . esc_html( $paragraph ) . '</p>';
			$copy_block['innerHTML']    = $markup;
			$copy_block['innerContent'] = array( $markup );
		}
	}
	unset( $copy_block );

	return $block;
}

/**
 * Build a canonical Video-file media column from the variation's structure.
 *
 * @param int    $video_id Local Video attachment ID.
 * @param string $video_url Local Video URL.
 * @return array<string,mixed>
 */
function pns_style_guide_get_video_media_column( int $video_id, string $video_url ): array {
	$markup = sprintf(
		'<!-- wp:column {"className":"pns-split-section__media-column pns-split-section__media-column--video"} --><div class="wp-block-column pns-split-section__media-column pns-split-section__media-column--video"><!-- wp:video {"id":%1$d,"src":"%2$s"} --><figure class="wp-block-video"><video controls src="%2$s"></video></figure><!-- /wp:video --></div><!-- /wp:column -->',
		$video_id,
		esc_url( $video_url )
	);
	$blocks = parse_blocks( $markup );

	if ( 1 !== count( $blocks ) || 'core/column' !== ( $blocks[0]['blockName'] ?? '' ) ) {
		WP_CLI::error( 'Could not construct the native Video fixture.' );
	}

	return $blocks[0];
}

/**
 * Add canonical media type state and collect the current YouTube fixtures.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed page blocks.
 * @param array<int,array<string,mixed>> $youtube_fixtures Canonical fixtures.
 * @return array<int,array<string,mixed>>
 */
function pns_style_guide_canonicalise_youtube_fixtures( array $blocks, array &$youtube_fixtures ): array {
	foreach ( $blocks as &$block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) ) {
			$media = pns_style_guide_get_split_media_block( $block );
			$url   = $media['attrs']['url'] ?? '';

			if ( 'core/embed' === ( $media['blockName'] ?? '' ) && str_contains( $url, 'youtube.com/' ) ) {
				$block['attrs']              = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$block['attrs']['align']     = 'full';
				$block['attrs']['mediaType'] = 'youtube';
				$block                       = pns_style_guide_label_split_fixture(
					$block,
					'YouTube embed — ' . pns_style_guide_layout_label( $block ),
					'Use this Split Section variation with a YouTube URL. Replace this starter text before publishing.'
				);
				$youtube_fixtures[]          = $block;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_style_guide_canonicalise_youtube_fixtures( $block['innerBlocks'], $youtube_fixtures );
		}
	}
	unset( $block );

	return $blocks;
}

/**
 * Build matching native Video fixtures from canonical YouTube fixtures.
 *
 * @param array<int,array<string,mixed>> $youtube_fixtures Canonical YouTube fixtures.
 * @param int    $video_id Local Video attachment ID.
 * @param string $video_url Local Video URL.
 * @return array<int,array<string,mixed>>
 */
function pns_style_guide_build_native_video_fixtures( array $youtube_fixtures, int $video_id, string $video_url ): array {
	$video_fixtures = array();

	foreach ( $youtube_fixtures as $youtube_fixture ) {
		$video_fixture                       = $youtube_fixture;
		$video_fixture['attrs']['mediaType'] = 'video';
		$video_fixture                       = pns_style_guide_label_split_fixture(
			$video_fixture,
			'Hosted video — ' . pns_style_guide_layout_label( $video_fixture ),
			'Use this Split Section variation with a video file from the Media Library. Replace this starter text before publishing.'
		);
		$video_fixture['innerBlocks'][0]['innerBlocks'][1] = pns_style_guide_get_video_media_column( $video_id, $video_url );
		$video_fixtures[]                                   = $video_fixture;
	}

	return $video_fixtures;
}

/**
 * Count canonical media fixtures recursively.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed page blocks.
 * @param string                         $media_type Media type to count.
 * @return int
 */
function pns_style_guide_count_media_type( array $blocks, string $media_type ): int {
	$count = 0;

	foreach ( $blocks as $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && $media_type === ( $block['attrs']['mediaType'] ?? '' ) ) {
			++$count;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$count += pns_style_guide_count_media_type( $block['innerBlocks'], $media_type );
		}
	}

	return $count;
}

/**
 * Return whether every native Video fixture points at the intended local file.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed page blocks.
 * @param string                         $video_url Expected local Video URL.
 * @return int Number of matching Video fixtures.
 */
function pns_style_guide_count_matching_native_videos( array $blocks, string $video_url ): int {
	$count = 0;

	foreach ( $blocks as $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && 'video' === ( $block['attrs']['mediaType'] ?? '' ) ) {
			$media = pns_style_guide_get_split_media_block( $block );

			if ( 'core/video' === ( $media['blockName'] ?? '' ) && str_contains( $media['innerHTML'] ?? '', $video_url ) ) {
				++$count;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$count += pns_style_guide_count_matching_native_videos( $block['innerBlocks'], $video_url );
		}
	}

	return $count;
}

$pns_style_guide_post = get_post( $pns_style_guide_post_id );

if ( ! $pns_style_guide_post || 'page' !== $pns_style_guide_post->post_type || 'pns-style-guide-split-section-components' !== $pns_style_guide_post->post_name ) {
	WP_CLI::error( 'The expected private Split Section Style Guide page is not available.' );
}

if ( 'private' !== $pns_style_guide_post->post_status ) {
	WP_CLI::error( 'Refusing to alter a non-private Style Guide page.' );
}

$pns_style_guide_existing_blocks   = parse_blocks( $pns_style_guide_post->post_content );
$pns_style_guide_existing_youtube  = pns_style_guide_count_media_type( $pns_style_guide_existing_blocks, 'youtube' );
$pns_style_guide_existing_video    = pns_style_guide_count_media_type( $pns_style_guide_existing_blocks, 'video' );

if ( 4 === $pns_style_guide_existing_youtube && 4 === $pns_style_guide_existing_video && 4 === pns_style_guide_count_matching_native_videos( $pns_style_guide_existing_blocks, $pns_style_guide_video_url ) ) {
	WP_CLI::success( 'The private Style Guide already has four canonical Video and four canonical YouTube fixtures.' );
	return;
}

if ( 4 === $pns_style_guide_existing_youtube && 4 === $pns_style_guide_existing_video ) {
	$pns_style_guide_existing_blocks = array_values(
		array_filter(
			$pns_style_guide_existing_blocks,
			static function ( array $block ): bool {
				return !( 'pns/split-section' === ( $block['blockName'] ?? '' ) && 'video' === ( $block['attrs']['mediaType'] ?? '' ) );
			}
		)
	);
} elseif ( 0 !== $pns_style_guide_existing_youtube || 0 !== $pns_style_guide_existing_video ) {
	WP_CLI::error( 'Refusing to alter a partially migrated Style Guide page.' );
}

$pns_style_guide_youtube_fixtures = array();
$pns_style_guide_blocks           = pns_style_guide_canonicalise_youtube_fixtures(
	$pns_style_guide_existing_blocks,
	$pns_style_guide_youtube_fixtures
);

if ( 4 !== count( $pns_style_guide_youtube_fixtures ) ) {
	WP_CLI::error( sprintf( 'Expected four legacy YouTube fixtures; found %d.', count( $pns_style_guide_youtube_fixtures ) ) );
}

$pns_style_guide_video_fixtures = pns_style_guide_build_native_video_fixtures(
	$pns_style_guide_youtube_fixtures,
	$pns_style_guide_video_id,
	$pns_style_guide_video_url
);
$pns_style_guide_youtube_positions = array();

foreach ( $pns_style_guide_blocks as $pns_style_guide_index => $pns_style_guide_block ) {
	if ( 'pns/split-section' === ( $pns_style_guide_block['blockName'] ?? '' ) && 'youtube' === ( $pns_style_guide_block['attrs']['mediaType'] ?? '' ) ) {
		$pns_style_guide_youtube_positions[] = $pns_style_guide_index;
	}
}

if ( 4 !== count( $pns_style_guide_youtube_positions ) ) {
	WP_CLI::error( 'Expected four top-level YouTube fixtures in the Style Guide.' );
}

for ( $pns_style_guide_index = count( $pns_style_guide_youtube_positions ) - 1; $pns_style_guide_index >= 0; --$pns_style_guide_index ) {
	array_splice(
		$pns_style_guide_blocks,
		$pns_style_guide_youtube_positions[ $pns_style_guide_index ] + 1,
		0,
		array( $pns_style_guide_video_fixtures[ $pns_style_guide_index ] )
	);
}
$pns_style_guide_updated_content = serialize_blocks( $pns_style_guide_blocks );

if (
	4 !== pns_style_guide_count_media_type( parse_blocks( $pns_style_guide_updated_content ), 'youtube' ) ||
	4 !== pns_style_guide_count_media_type( parse_blocks( $pns_style_guide_updated_content ), 'video' ) ||
	4 !== pns_style_guide_count_matching_native_videos( parse_blocks( $pns_style_guide_updated_content ), $pns_style_guide_video_url )
) {
	WP_CLI::error( 'Refusing to write: the serialized page does not contain four canonical fixtures for each video variation.' );
}

WP_CLI::log( 'YouTube fixtures: 4 canonical core/embed blocks.' );
WP_CLI::log( sprintf( 'Native Video fixtures: 4 core/video blocks using attachment #%d.', $pns_style_guide_video_id ) );

if ( ! $pns_style_guide_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and update the private Style Guide.' );
	return;
}

$pns_style_guide_current_content = get_post_field( 'post_content', $pns_style_guide_post_id );

if ( ! is_string( $pns_style_guide_current_content ) || ! hash_equals( hash( 'sha256', $pns_style_guide_post->post_content ), hash( 'sha256', $pns_style_guide_current_content ) ) ) {
	WP_CLI::error( 'Refusing to write: the Style Guide changed after it was read.' );
}

if ( ! is_dir( $pns_style_guide_backup_dir ) && ! wp_mkdir_p( $pns_style_guide_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_style_guide_backup_dir ) );
}

$pns_style_guide_backup_json = wp_json_encode(
	array(
		'created_at_gmt' => gmdate( 'c' ),
		'purpose'        => 'Canonicalise the private Split Section Style Guide Video and YouTube fixtures.',
		'ID'             => $pns_style_guide_post_id,
		'post_content'   => $pns_style_guide_post->post_content,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ( ! is_string( $pns_style_guide_backup_json ) ) {
	WP_CLI::error( 'Could not encode the rollback export.' );
}

$pns_style_guide_backup_path = trailingslashit( $pns_style_guide_backup_dir ) . gmdate( 'Ymd-His' ) . '-split-section-style-guide.json';

if ( false === file_put_contents( $pns_style_guide_backup_path, $pns_style_guide_backup_json, LOCK_EX ) ) {
	WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_style_guide_backup_path ) );
}

$pns_style_guide_update = wp_update_post(
	array(
		'ID'           => $pns_style_guide_post_id,
		'post_content' => $pns_style_guide_updated_content,
	),
	true
);

if ( is_wp_error( $pns_style_guide_update ) ) {
	WP_CLI::error( $pns_style_guide_update->get_error_message() );
}

WP_CLI::success( sprintf( 'Updated private Style Guide #%d. Rollback export: %s', $pns_style_guide_post_id, $pns_style_guide_backup_path ) );
