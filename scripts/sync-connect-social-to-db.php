<?php
/**
 * Synchronise the Connect Social synced block from its version-controlled fixture.
 *
 * Usage:
 *   wp eval-file scripts/sync-connect-social-to-db.php
 *   wp eval-file scripts/sync-connect-social-to-db.php apply
 *
 * The dry run is read-only. Apply writes a rollback export before updating the
 * live wp_block record.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_connect_social_sync_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pns_connect_social_sync_apply = in_array( 'apply', $pns_connect_social_sync_args, true ) || in_array( '--apply', $pns_connect_social_sync_args, true );
$pns_connect_social_sync_path  = get_stylesheet_directory() . '/synced-patterns/connect-social.html';
$pns_connect_social_sync_dir   = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/dark-surface-db-backups';

if ( ! file_exists( $pns_connect_social_sync_path ) ) {
	WP_CLI::error( sprintf( 'Connect Social source was not found: %s', $pns_connect_social_sync_path ) );
}

$pns_connect_social_sync_content = file_get_contents( $pns_connect_social_sync_path );

if ( false === $pns_connect_social_sync_content ) {
	WP_CLI::error( sprintf( 'Connect Social source could not be read: %s', $pns_connect_social_sync_path ) );
}

$pns_connect_social_sync_posts = get_posts(
	array(
		'post_type'      => 'wp_block',
		'post_status'    => 'publish',
		'name'           => 'connect-social',
		'posts_per_page' => 1,
	)
);
$pns_connect_social_sync_post  = ! empty( $pns_connect_social_sync_posts ) ? $pns_connect_social_sync_posts[0] : null;

if ( ! $pns_connect_social_sync_post instanceof WP_Post ) {
	WP_CLI::error( 'Published Connect Social synced block was not found.' );
}

if ( $pns_connect_social_sync_post->post_content === $pns_connect_social_sync_content ) {
	WP_CLI::success( sprintf( 'Connect Social synced block #%d is already byte-equal to its fixture.', $pns_connect_social_sync_post->ID ) );
	return;
}

WP_CLI::log( sprintf( 'Connect Social synced block #%d will be updated from synced-patterns/connect-social.html.', $pns_connect_social_sync_post->ID ) );

if ( ! $pns_connect_social_sync_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to perform the sync.' );
	return;
}

if ( ! is_dir( $pns_connect_social_sync_dir ) && ! wp_mkdir_p( $pns_connect_social_sync_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_connect_social_sync_dir ) );
}

$pns_connect_social_sync_backup = trailingslashit( $pns_connect_social_sync_dir ) . gmdate( 'Ymd-His' ) . '-connect-social.json';

if ( false === file_put_contents(
	$pns_connect_social_sync_backup,
	wp_json_encode(
		array(
			'created_at_gmt' => gmdate( 'c' ),
			'ID'             => $pns_connect_social_sync_post->ID,
			'post_type'      => $pns_connect_social_sync_post->post_type,
			'post_status'    => $pns_connect_social_sync_post->post_status,
			'post_name'      => $pns_connect_social_sync_post->post_name,
			'post_content'   => $pns_connect_social_sync_post->post_content,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	)
) ) {
	WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_connect_social_sync_backup ) );
}

$pns_connect_social_sync_result = wp_update_post(
	array(
		'ID'           => $pns_connect_social_sync_post->ID,
		'post_content' => wp_slash( $pns_connect_social_sync_content ),
	),
	true
);

if ( is_wp_error( $pns_connect_social_sync_result ) ) {
	WP_CLI::error( $pns_connect_social_sync_result->get_error_message() );
}

clean_post_cache( $pns_connect_social_sync_post->ID );
WP_CLI::success( sprintf( 'Updated Connect Social synced block #%d. Rollback export: %s', $pns_connect_social_sync_post->ID, $pns_connect_social_sync_backup ) );
