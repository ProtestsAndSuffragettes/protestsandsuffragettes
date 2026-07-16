<?php
/**
 * Apply the shared brand-purple 60 percent wash to saved post Enhanced Covers.
 *
 * Usage:
 *   wp eval-file scripts/apply-enhanced-cover-purple-wash.php
 *   wp eval-file scripts/apply-enhanced-cover-purple-wash.php apply
 *   wp eval-file scripts/apply-enhanced-cover-purple-wash.php rollback <manifest>
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

const PNS_PURPLE_WASH_BLOCK       = 'ran/enhanced-cover';
const PNS_PURPLE_WASH_SCHEMA       = 1;
const PNS_PURPLE_WASH_OVERLAY      = 'brand-purple';
const PNS_PURPLE_WASH_OPACITY      = 60;

$pns_purple_wash_args = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_purple_wash_mode = $pns_purple_wash_args[0] ?? 'dry-run';

if ( 'rollback' === $pns_purple_wash_mode ) {
	pns_purple_wash_rollback( $pns_purple_wash_args[1] ?? '' );
	return;
}

if ( ! in_array( $pns_purple_wash_mode, array( 'dry-run', 'apply' ), true ) ) {
	WP_CLI::error( 'Use no argument for a dry run, apply, or rollback <manifest-path>.' );
}

$pns_purple_wash_rows = pns_purple_wash_rows();
$pns_purple_wash_invalid = array_filter(
	$pns_purple_wash_rows,
	static function ( $row ) {
		return 'invalid' === $row['state'];
	}
);

foreach ( $pns_purple_wash_rows as $pns_purple_wash_row ) {
	WP_CLI::log( sprintf( '#%d %s: %s', $pns_purple_wash_row['post_id'], $pns_purple_wash_row['post_name'], $pns_purple_wash_row['state'] ) );
}

if ( ! empty( $pns_purple_wash_invalid ) ) {
	WP_CLI::error( 'Wash stopped because a post does not have exactly one safely serializable Enhanced Cover.' );
}

$pns_purple_wash_pending = array_values(
	array_filter(
		$pns_purple_wash_rows,
		static function ( $row ) {
			return 'would-update' === $row['state'];
		}
	)
);

if ( 'dry-run' === $pns_purple_wash_mode ) {
	WP_CLI::success( sprintf( 'Dry run complete. %d cover(s) would receive the brand-purple %d%% wash; %d already match.', count( $pns_purple_wash_pending ), PNS_PURPLE_WASH_OPACITY, count( $pns_purple_wash_rows ) - count( $pns_purple_wash_pending ) ) );
	return;
}

if ( empty( $pns_purple_wash_pending ) ) {
	WP_CLI::success( 'Every saved post Enhanced Cover already has the requested wash.' );
	return;
}

$pns_purple_wash_manifest = pns_purple_wash_write_manifest( $pns_purple_wash_pending );

foreach ( $pns_purple_wash_pending as $pns_purple_wash_row ) {
	$result = wp_update_post(
		array(
			'ID'           => $pns_purple_wash_row['post_id'],
			'post_content' => $pns_purple_wash_row['after_content'],
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::error( sprintf( 'Could not update post %d: %s', $pns_purple_wash_row['post_id'], $result->get_error_message() ) );
	}

	clean_post_cache( $pns_purple_wash_row['post_id'] );
}

WP_CLI::success( sprintf( 'Applied the brand-purple %d%% wash to %d post cover(s). Rollback manifest: %s', PNS_PURPLE_WASH_OPACITY, count( $pns_purple_wash_pending ), $pns_purple_wash_manifest ) );

/**
 * Build the complete approved post set and target replacement content.
 *
 * @return array<int,array<string,mixed>>
 */
function pns_purple_wash_rows() {
	$posts = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'any',
			'posts_per_page'          => -1,
			'orderby'                 => 'ID',
			'order'                   => 'ASC',
			'no_found_rows'           => true,
			'ignore_sticky_posts'     => true,
			'update_post_meta_cache'  => false,
			'update_post_term_cache'  => false,
		)
	);
	$rows = array();

	foreach ( $posts as $post ) {
		$blocks = parse_blocks( $post->post_content );
		$covers = array();
		pns_purple_wash_find_covers( $blocks, $covers );

		if ( empty( $covers ) ) {
			continue;
		}

		$permalink = wp_make_link_relative( get_permalink( $post ) );

		if ( 'publish' !== $post->post_status || 1 !== count( $covers ) || ! str_starts_with( $permalink, '/news/' ) ) {
			$rows[] = array(
				'post_id'   => $post->ID,
				'post_name' => $post->post_name,
				'state'     => 'invalid',
			);
			continue;
		}

		$cover = &$covers[0];
		$attrs = isset( $cover['attrs'] ) && is_array( $cover['attrs'] ) ? $cover['attrs'] : array();
		$matches = PNS_PURPLE_WASH_OVERLAY === ( $attrs['overlayColor'] ?? '' ) && empty( $attrs['customOverlayColor'] ) && PNS_PURPLE_WASH_OPACITY === absint( $attrs['overlayOpacity'] ?? 0 );

		if ( ! $matches ) {
			$cover['attrs']['overlayColor']       = PNS_PURPLE_WASH_OVERLAY;
			$cover['attrs']['customOverlayColor'] = '';
			$cover['attrs']['overlayOpacity']     = PNS_PURPLE_WASH_OPACITY;
		}

		$rows[] = array(
			'post_id'       => $post->ID,
			'post_name'     => $post->post_name,
			'state'         => $matches ? 'already-matching' : 'would-update',
			'before_content' => $post->post_content,
			'after_content'  => $matches ? $post->post_content : serialize_blocks( $blocks ),
		);
	}

	return $rows;
}

/**
 * Locate Enhanced Covers while retaining references for in-place updates.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,array<string,mixed>> $covers Matched block references.
 * @return void
 */
function pns_purple_wash_find_covers( &$blocks, &$covers ) {
	foreach ( $blocks as &$block ) {
		if ( PNS_PURPLE_WASH_BLOCK === ( $block['blockName'] ?? '' ) ) {
			$covers[] = &$block;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			pns_purple_wash_find_covers( $block['innerBlocks'], $covers );
		}
	}
	unset( $block );
}

/**
 * Write an atomic, content-exact rollback manifest before any updates.
 *
 * @param array<int,array<string,mixed>> $rows Pending rows.
 * @return string
 */
function pns_purple_wash_write_manifest( $rows ) {
	$project_root = dirname( get_stylesheet_directory(), 5 );
	$backup_dir   = $project_root . '/docs/jobs/enhanced-cover-purple-wash-db-backups';

	if ( ! is_dir( $backup_dir ) && ! wp_mkdir_p( $backup_dir ) ) {
		WP_CLI::error( sprintf( 'Could not create rollback directory: %s', $backup_dir ) );
	}

	$manifest = array(
		'schema_version' => PNS_PURPLE_WASH_SCHEMA,
		'created_at'     => gmdate( 'c' ),
		'operation'      => 'post-enhanced-cover-brand-purple-60-percent-wash',
		'rows'           => array_map(
			static function ( $row ) {
				return array(
					'post_id'       => $row['post_id'],
					'post_name'     => $row['post_name'],
					'before_content' => $row['before_content'],
				);
			},
			$rows
		),
	);
	$json = wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( ! is_string( $json ) ) {
		WP_CLI::error( 'Could not encode the rollback manifest.' );
	}

	$path = sprintf( '%s/%s-before-brand-purple-wash.json', $backup_dir, gmdate( 'Ymd\\THis\\Z' ) );
	$temp = $path . '.tmp';

	if ( false === file_put_contents( $temp, $json . "\n" ) || ! rename( $temp, $path ) ) {
		WP_CLI::error( sprintf( 'Could not write rollback manifest: %s', $path ) );
	}

	return $path;
}

/**
 * Restore the exact pre-wash post content from a generated manifest.
 *
 * @param string $path Manifest path.
 * @return void
 */
function pns_purple_wash_rollback( $path ) {
	if ( '' === $path || ! is_readable( $path ) ) {
		WP_CLI::error( 'Rollback requires a readable manifest path.' );
	}

	$manifest = json_decode( (string) file_get_contents( $path ), true );

	if ( ! is_array( $manifest ) || PNS_PURPLE_WASH_SCHEMA !== (int) ( $manifest['schema_version'] ?? 0 ) || ! is_array( $manifest['rows'] ?? null ) ) {
		WP_CLI::error( 'Rollback manifest has an unsupported schema.' );
	}

	foreach ( $manifest['rows'] as $row ) {
		$post_id = absint( $row['post_id'] ?? 0 );
		$content = $row['before_content'] ?? null;

		if ( ! $post_id || ! is_string( $content ) || 'post' !== get_post_type( $post_id ) ) {
			WP_CLI::error( 'Rollback manifest contains an invalid target.' );
		}

		$result = wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ), true );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( sprintf( 'Could not restore post %d: %s', $post_id, $result->get_error_message() ) );
		}

		clean_post_cache( $post_id );
	}

	WP_CLI::success( sprintf( 'Restored %d post(s) from %s.', count( $manifest['rows'] ), $path ) );
}
