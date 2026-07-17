<?php
/**
 * Align existing Text | Text demos with the shared variant UI and flip one.
 *
 * Usage:
 *   wp eval-file scripts/update-split-section-text-demo-flip.php 5654
 *   wp eval-file scripts/update-split-section-text-demo-flip.php 5654 apply
 *
 * Dry-run is the default. Apply writes a complete JSON rollback export before
 * changing only the eight demo Split Section opening comments.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_split_flip_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_split_flip_id    = isset( $pns_split_flip_args[0] ) ? (int) $pns_split_flip_args[0] : 0;
$pns_split_flip_apply = in_array( 'apply', $pns_split_flip_args, true ) || in_array( '--apply', $pns_split_flip_args, true );
$pns_split_flip_page  = get_post( $pns_split_flip_id );

if ( 5654 !== $pns_split_flip_id ) {
	WP_CLI::error( 'Pass the explicit Split Section style-guide page ID: 5654 [apply].' );
}

if (
	! $pns_split_flip_page instanceof WP_Post
	|| 'page' !== $pns_split_flip_page->post_type
	|| 'private' !== $pns_split_flip_page->post_status
	|| 'pns-style-guide-split-section-components' !== $pns_split_flip_page->post_name
	|| 'PNS Style Guide: Split Section Components' !== $pns_split_flip_page->post_title
) {
	WP_CLI::error( 'Page 5654 is no longer the expected private Split Section style-guide page.' );
}

$pns_split_flip_blocks = array_values(
	array_filter(
		parse_blocks( $pns_split_flip_page->post_content ),
		static function ( $block ) {
			return 'pns/split-section' === ( $block['blockName'] ?? '' )
				&& str_starts_with( $block['attrs']['anchor'] ?? '', 'pns-text-text-demo-' );
		}
	)
);

if ( 8 !== count( $pns_split_flip_blocks ) ) {
	WP_CLI::error( sprintf( 'Expected eight Text | Text demo blocks, found %d.', count( $pns_split_flip_blocks ) ) );
}

$pns_split_flip_is_current = array_reduce(
	$pns_split_flip_blocks,
	static function ( $is_current, $block ) {
		$anchor = $block['attrs']['anchor'] ?? '';
		$layout = $block['attrs']['layoutVariant'] ?? '';

		return $is_current
			&& 'text' === ( $block['attrs']['mediaType'] ?? '' )
			&& ( 'pns-text-text-demo-text-text-long-short' === $anchor ? 'edge-media-left' === $layout : in_array( $layout, array( 'edge-media-right', 'media-right' ), true ) );
	},
	true
);

if ( $pns_split_flip_is_current ) {
	WP_CLI::success( 'The demo matrix already uses the shared variant UI, including one Edge media left example.' );
	return;
}

$pns_split_flip_current_sha  = hash( 'sha256', $pns_split_flip_page->post_content );
$pns_split_flip_expected_sha = 'f7c5763151f8f2625cddd1db9f934254542c9c28c246d539ee50da51dd16d740';

if ( $pns_split_flip_expected_sha !== $pns_split_flip_current_sha ) {
	WP_CLI::error(
		sprintf(
			'Style-guide content changed: expected SHA-256 %s, found %s.',
			$pns_split_flip_expected_sha,
			$pns_split_flip_current_sha
		)
	);
}

$pns_split_flip_pattern      = '/<!-- wp:pns\/split-section (\{[^\r\n}]*"anchor":"pns-text-text-demo-[^"]+"[^\r\n}]*\}) -->/';
$pns_split_flip_replacements = 0;
$pns_split_flip_new_content  = preg_replace_callback(
	$pns_split_flip_pattern,
	static function ( $matches ) use ( &$pns_split_flip_replacements ) {
		$attributes = json_decode( $matches[1], true );

		if ( ! is_array( $attributes ) ) {
			WP_CLI::error( 'Could not decode a Text | Text demo block comment.' );
		}

		$anchor = $attributes['anchor'] ?? '';
		$layout = $attributes['layoutVariant'] ?? '';

		if ( 'text-text' === $layout ) {
			$layout = 'edge-media-right';
		} elseif ( 'text-text-constrained' === $layout ) {
			$layout = 'media-right';
		} else {
			WP_CLI::error( sprintf( 'Unexpected saved demo layout "%s".', $layout ) );
		}

		if ( 'pns-text-text-demo-text-text-long-short' === $anchor ) {
			$layout = 'edge-media-left';
		}

		++$pns_split_flip_replacements;
		$updated_attributes = array(
			'mediaType'     => 'text',
			'layoutVariant' => $layout,
			'align'         => $attributes['align'] ?? 'full',
			'anchor'        => $anchor,
		);

		return '<!-- wp:pns/split-section ' . wp_json_encode( $updated_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' -->';
	},
	$pns_split_flip_page->post_content
);

if ( ! is_string( $pns_split_flip_new_content ) || 8 !== $pns_split_flip_replacements ) {
	WP_CLI::error( sprintf( 'Expected eight targeted replacements, made %d.', $pns_split_flip_replacements ) );
}

WP_CLI::log( 'Style-guide #5654: all eight demos are ready for the shared variant UI.' );
WP_CLI::log( 'The full-edge long / short demo will use Edge media left to swap its text panels.' );

if ( ! $pns_split_flip_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with 5654 apply to update the matrix.' );
	return;
}

$pns_split_flip_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/split-section-text-demo-backups';

if ( ! is_dir( $pns_split_flip_backup_dir ) && ! wp_mkdir_p( $pns_split_flip_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_split_flip_backup_dir ) );
}

$pns_split_flip_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'post_id'           => $pns_split_flip_id,
	'post_title'        => $pns_split_flip_page->post_title,
	'post_name'         => $pns_split_flip_page->post_name,
	'post_status'       => $pns_split_flip_page->post_status,
	'post_modified_gmt' => $pns_split_flip_page->post_modified_gmt,
	'wp_page_template'  => get_post_meta( $pns_split_flip_id, '_wp_page_template', true ),
	'content_sha256'    => $pns_split_flip_current_sha,
	'post_content'      => $pns_split_flip_page->post_content,
);
$pns_split_flip_backup_path = trailingslashit( $pns_split_flip_backup_dir ) . gmdate( 'Ymd-His' ) . '-split-section-text-demo-flip-5654.json';

if ( false === file_put_contents( $pns_split_flip_backup_path, wp_json_encode( $pns_split_flip_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_split_flip_backup_path ) );
}

$pns_split_flip_updated_id = wp_update_post(
	array(
		'ID'           => $pns_split_flip_id,
		'post_content' => wp_slash( $pns_split_flip_new_content ),
	),
	true
);

if ( is_wp_error( $pns_split_flip_updated_id ) ) {
	WP_CLI::error( $pns_split_flip_updated_id->get_error_message() );
}

clean_post_cache( $pns_split_flip_id );
WP_CLI::success( sprintf( 'Updated the shared variant UI attributes and flipped one demo. Rollback export: %s', $pns_split_flip_backup_path ) );
