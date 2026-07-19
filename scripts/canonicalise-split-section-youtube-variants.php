<?php
/**
 * Canonicalise legacy Split Section Video pattern blocks.
 *
 * The retired `pns/split-section-video` pattern inserted a Split Section
 * without a `mediaType`. This migration records the dedicated YouTube
 * variation explicitly for its YouTube embeds, and the existing Video
 * variation for its one native Video block. It retains every layout, copy,
 * embed URL, and block structure.
 *
 * Usage:
 *   wp eval-file scripts/canonicalise-split-section-youtube-variants.php
 *   wp eval-file scripts/canonicalise-split-section-youtube-variants.php apply
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_youtube_variant_args = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();

foreach ( $pns_youtube_variant_args as $pns_youtube_variant_arg ) {
	if ( ! in_array( $pns_youtube_variant_arg, array( 'apply', '--apply' ), true ) ) {
		WP_CLI::error( sprintf( 'Unexpected argument: %s. Use only apply.', $pns_youtube_variant_arg ) );
	}
}

$pns_youtube_variant_apply        = in_array( 'apply', $pns_youtube_variant_args, true ) || in_array( '--apply', $pns_youtube_variant_args, true );
$pns_youtube_variant_project_root = dirname( get_stylesheet_directory(), 5 );
$pns_youtube_variant_backup_dir   = $pns_youtube_variant_project_root . '/docs/jobs/split-section-youtube-variant-db-backups';
$pns_youtube_variant_posts        = get_posts(
	array(
		'post_type'              => 'any',
		'post_status'            => array( 'publish', 'private', 'draft', 'pending', 'future' ),
		'posts_per_page'         => -1,
		'orderby'                => 'ID',
		'order'                  => 'ASC',
		'suppress_filters'       => true,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);
$pns_youtube_variant_updates      = array();

foreach ( $pns_youtube_variant_posts as $pns_youtube_variant_post ) {
	if ( ! $pns_youtube_variant_post instanceof WP_Post || ! str_contains( $pns_youtube_variant_post->post_content, 'pns/split-section' ) ) {
		continue;
	}

	$pns_youtube_variant_blocks = parse_blocks( $pns_youtube_variant_post->post_content );
	$pns_youtube_variant_stats  = array(
		'legacy_sections'   => 0,
		'canonical_youtube' => 0,
		'canonical_video'   => 0,
		'changed'           => 0,
	);

	pns_youtube_variant_process_blocks( $pns_youtube_variant_blocks, $pns_youtube_variant_stats, true );

	if ( 0 === $pns_youtube_variant_stats['changed'] ) {
		continue;
	}

	$pns_youtube_variant_updated_content = serialize_blocks( $pns_youtube_variant_blocks );
	$pns_youtube_variant_check_blocks    = parse_blocks( $pns_youtube_variant_updated_content );
	$pns_youtube_variant_check_stats     = array(
		'legacy_sections'   => 0,
		'canonical_youtube' => 0,
		'canonical_video'   => 0,
		'changed'           => 0,
	);

	pns_youtube_variant_process_blocks( $pns_youtube_variant_check_blocks, $pns_youtube_variant_check_stats, false );

	if (
		0 !== $pns_youtube_variant_check_stats['legacy_sections'] ||
		$pns_youtube_variant_stats['canonical_youtube'] !== $pns_youtube_variant_check_stats['canonical_youtube'] ||
		$pns_youtube_variant_stats['canonical_video'] !== $pns_youtube_variant_check_stats['canonical_video']
	) {
		WP_CLI::error( sprintf( 'Post #%d did not satisfy the canonical video block contract after serialization.', $pns_youtube_variant_post->ID ) );
	}

	$pns_youtube_variant_updates[] = array(
		'post'            => $pns_youtube_variant_post,
		'updated_content' => $pns_youtube_variant_updated_content,
		'legacy_sections' => $pns_youtube_variant_stats['changed'],
	);
}

if ( empty( $pns_youtube_variant_updates ) ) {
	WP_CLI::success( 'No legacy Split Section Video pattern blocks require canonicalisation.' );
	return;
}

WP_CLI::log( sprintf( 'Found %d post(s) containing legacy Split Section Video pattern blocks.', count( $pns_youtube_variant_updates ) ) );

foreach ( $pns_youtube_variant_updates as $pns_youtube_variant_update ) {
	$pns_youtube_variant_post = $pns_youtube_variant_update['post'];

	WP_CLI::log(
		sprintf(
			'#%1$d %2$s (%3$s): %4$d section(s)',
			$pns_youtube_variant_post->ID,
			$pns_youtube_variant_post->post_title,
			$pns_youtube_variant_post->post_status,
			$pns_youtube_variant_update['legacy_sections']
		)
	);
}

if ( ! $pns_youtube_variant_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write rollback exports and update the saved blocks.' );
	return;
}

if ( ! is_dir( $pns_youtube_variant_backup_dir ) && ! wp_mkdir_p( $pns_youtube_variant_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_youtube_variant_backup_dir ) );
}

foreach ( $pns_youtube_variant_updates as $pns_youtube_variant_update ) {
	$pns_youtube_variant_post = $pns_youtube_variant_update['post'];
	$pns_youtube_variant_lock = (string) get_post_meta( $pns_youtube_variant_post->ID, '_edit_lock', true );

	if ( '' !== $pns_youtube_variant_lock ) {
		$pns_youtube_variant_lock_parts = explode( ':', $pns_youtube_variant_lock, 2 );
		$pns_youtube_variant_lock_time  = isset( $pns_youtube_variant_lock_parts[0] ) ? (int) $pns_youtube_variant_lock_parts[0] : 0;

		if ( $pns_youtube_variant_lock_time > time() - 150 ) {
			WP_CLI::error( sprintf( 'Post #%d has an active editor lock. Close the editor and retry after the lock expires.', $pns_youtube_variant_post->ID ) );
		}
	}

	$pns_youtube_variant_live_content = get_post_field( 'post_content', $pns_youtube_variant_post->ID );

	if ( ! is_string( $pns_youtube_variant_live_content ) || ! hash_equals( hash( 'sha256', $pns_youtube_variant_post->post_content ), hash( 'sha256', $pns_youtube_variant_live_content ) ) ) {
		WP_CLI::error( sprintf( 'Post #%d changed after the dry-run read; refusing to overwrite it.', $pns_youtube_variant_post->ID ) );
	}

	$pns_youtube_variant_backup = array(
		'schema_version'    => 1,
		'created_at_gmt'    => gmdate( 'c' ),
		'purpose'           => 'Canonicalise legacy Split Section Video pattern blocks after retiring pns/split-section-video.',
		'post_id'           => $pns_youtube_variant_post->ID,
		'post_type'         => $pns_youtube_variant_post->post_type,
		'post_title'        => $pns_youtube_variant_post->post_title,
		'post_name'         => $pns_youtube_variant_post->post_name,
		'post_status'       => $pns_youtube_variant_post->post_status,
		'post_modified_gmt' => $pns_youtube_variant_post->post_modified_gmt,
		'content_sha256'    => hash( 'sha256', $pns_youtube_variant_post->post_content ),
		'post_content'      => $pns_youtube_variant_post->post_content,
	);
	$pns_youtube_variant_backup_json = wp_json_encode( $pns_youtube_variant_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	$pns_youtube_variant_backup_path = trailingslashit( $pns_youtube_variant_backup_dir ) . gmdate( 'Ymd-His' ) . '-pre-youtube-variant-' . $pns_youtube_variant_post->ID . '.json';
	$pns_youtube_variant_temp_path   = $pns_youtube_variant_backup_path . '.tmp';

	if (
		! is_string( $pns_youtube_variant_backup_json ) ||
		false === file_put_contents( $pns_youtube_variant_temp_path, $pns_youtube_variant_backup_json . "\n" ) ||
		! rename( $pns_youtube_variant_temp_path, $pns_youtube_variant_backup_path )
	) {
		WP_CLI::error( sprintf( 'Could not write rollback data for post #%d.', $pns_youtube_variant_post->ID ) );
	}

	$pns_youtube_variant_updated_id = wp_update_post(
		array(
			'ID'           => $pns_youtube_variant_post->ID,
			'post_content' => wp_slash( $pns_youtube_variant_update['updated_content'] ),
		),
		true
	);

	if ( is_wp_error( $pns_youtube_variant_updated_id ) ) {
		WP_CLI::error( $pns_youtube_variant_updated_id->get_error_message() );
	}

	clean_post_cache( $pns_youtube_variant_post->ID );
	$pns_youtube_variant_saved_content = get_post_field( 'post_content', $pns_youtube_variant_post->ID );

	if ( ! is_string( $pns_youtube_variant_saved_content ) || ! hash_equals( hash( 'sha256', $pns_youtube_variant_update['updated_content'] ), hash( 'sha256', $pns_youtube_variant_saved_content ) ) ) {
		WP_CLI::error( sprintf( 'WordPress saved content does not match the generated migration for post #%d.', $pns_youtube_variant_post->ID ) );
	}
}

WP_CLI::success( sprintf( 'Canonicalised legacy Split Section Video pattern blocks in %d post(s). Rollback exports: %s', count( $pns_youtube_variant_updates ), $pns_youtube_variant_backup_dir ) );

/**
 * Traverse a block tree and update only blocks inserted by the retired pattern.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks, passed by reference.
 * @param array<string,int>              $stats Mutable counters.
 * @param bool                           $mutate Whether to write attributes.
 */
function pns_youtube_variant_process_blocks( array &$blocks, array &$stats, bool $mutate ): void {
	foreach ( $blocks as &$block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && 'pns/split-section-video' === ( $block['attrs']['metadata']['patternName'] ?? '' ) ) {
			$media_type = pns_youtube_variant_get_media_type( $block['innerBlocks'] ?? array() );

			if ( null === $media_type ) {
				WP_CLI::error( 'A legacy Split Section Video pattern block does not contain a supported YouTube embed or native Video block.' );
			}

			++$stats['legacy_sections'];

			if ( $mutate ) {
				$block['attrs']              = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$block['attrs']['mediaType'] = $media_type;
				unset( $block['attrs']['metadata'] );

				++$stats['changed'];

				if ( 'youtube' === $media_type ) {
					++$stats['canonical_youtube'];
				} else {
					++$stats['canonical_video'];
				}
			}
		} elseif ( 'pns/split-section' === ( $block['blockName'] ?? '' ) ) {
			$media_type = $block['attrs']['mediaType'] ?? '';

			if ( 'youtube' === $media_type ) {
				++$stats['canonical_youtube'];
			} elseif ( 'video' === $media_type ) {
				++$stats['canonical_video'];
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			pns_youtube_variant_process_blocks( $block['innerBlocks'], $stats, $mutate );
		}
	}
	unset( $block );
}

/**
 * Return the appropriate canonical media type for a legacy pattern's media.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @return string|null
 */
function pns_youtube_variant_get_media_type( array $blocks ): ?string {
	foreach ( $blocks as $block ) {
		if ( 'core/embed' === ( $block['blockName'] ?? '' ) ) {
			$url      = (string) ( $block['attrs']['url'] ?? '' );
			$provider = (string) ( $block['attrs']['providerNameSlug'] ?? '' );
			$host     = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

			if ( 'youtube' === $provider || in_array( $host, array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com' ), true ) ) {
				return 'youtube';
			}
		}

		if ( 'core/video' === ( $block['blockName'] ?? '' ) ) {
			return 'video';
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$media_type = pns_youtube_variant_get_media_type( $block['innerBlocks'] );

			if ( null !== $media_type ) {
				return $media_type;
			}
		}
	}

	return null;
}
