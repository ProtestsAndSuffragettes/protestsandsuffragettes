<?php
/**
 * Synchronise the active standalone Home template from its file-backed source.
 *
 * Usage:
 *   wp eval-file scripts/sync-home-template-to-db.php
 *   wp eval-file scripts/sync-home-template-to-db.php apply
 *
 * The dry run is read-only. Apply writes a rollback export when an active
 * standalone database template already exists, then creates or updates it.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_template_sync_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pns_template_sync_apply = in_array( 'apply', $pns_template_sync_args, true ) || in_array( '--apply', $pns_template_sync_args, true );
$pns_template_sync_theme = get_stylesheet();
$pns_template_sync_path  = get_stylesheet_directory() . '/templates/home.html';
$pns_template_sync_dir   = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/site-frame-panel-db-backups';

if ( ! file_exists( $pns_template_sync_path ) ) {
	WP_CLI::error( sprintf( 'Home template source was not found: %s', $pns_template_sync_path ) );
}

$pns_template_sync_content = file_get_contents( $pns_template_sync_path );

if ( false === $pns_template_sync_content ) {
	WP_CLI::error( sprintf( 'Home template source could not be read: %s', $pns_template_sync_path ) );
}

$pns_template_sync_posts = get_posts(
	array(
		'post_type'      => 'wp_template',
		'post_status'    => 'any',
		'name'           => 'home',
		'posts_per_page' => 1,
		'tax_query'      => array(
			array(
				'taxonomy' => 'wp_theme',
				'field'    => 'slug',
				'terms'    => array( $pns_template_sync_theme ),
			),
		),
	)
);
$pns_template_sync_post  = ! empty( $pns_template_sync_posts ) ? $pns_template_sync_posts[0] : null;

if ( $pns_template_sync_post instanceof WP_Post && $pns_template_sync_post->post_content === $pns_template_sync_content ) {
	WP_CLI::success( sprintf( 'Active standalone Home template #%d is already byte-equal to templates/home.html.', $pns_template_sync_post->ID ) );
	return;
}

if ( $pns_template_sync_post instanceof WP_Post ) {
	WP_CLI::log( sprintf( 'Active standalone Home template #%d will be updated from templates/home.html.', $pns_template_sync_post->ID ) );
} else {
	WP_CLI::log( 'No active standalone Home database template exists; one will be created from templates/home.html.' );
}

if ( ! $pns_template_sync_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to perform the sync.' );
	return;
}

if ( $pns_template_sync_post instanceof WP_Post ) {
	if ( ! is_dir( $pns_template_sync_dir ) && ! wp_mkdir_p( $pns_template_sync_dir ) ) {
		WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_template_sync_dir ) );
	}

	$pns_template_sync_backup = trailingslashit( $pns_template_sync_dir ) . gmdate( 'Ymd-His' ) . '-home-template.json';
	file_put_contents(
		$pns_template_sync_backup,
		wp_json_encode(
			array(
				'created_at_gmt' => gmdate( 'c' ),
				'ID'             => $pns_template_sync_post->ID,
				'post_type'      => $pns_template_sync_post->post_type,
				'post_status'    => $pns_template_sync_post->post_status,
				'post_name'      => $pns_template_sync_post->post_name,
				'post_content'   => $pns_template_sync_post->post_content,
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		)
	);

	wp_update_post(
		array(
			'ID'           => $pns_template_sync_post->ID,
			'post_content' => wp_slash( $pns_template_sync_content ),
		)
	);
	clean_post_cache( $pns_template_sync_post->ID );
	WP_CLI::success( sprintf( 'Updated standalone Home template #%d. Rollback export: %s', $pns_template_sync_post->ID, $pns_template_sync_backup ) );
	return;
}

$pns_template_sync_id = wp_insert_post(
	array(
		'post_type'    => 'wp_template',
		'post_status'  => 'publish',
		'post_name'    => 'home',
		'post_title'   => 'Home',
		'post_content' => wp_slash( $pns_template_sync_content ),
	),
	true
);

if ( is_wp_error( $pns_template_sync_id ) ) {
	WP_CLI::error( $pns_template_sync_id->get_error_message() );
}

wp_set_object_terms( $pns_template_sync_id, $pns_template_sync_theme, 'wp_theme', false );
clean_post_cache( $pns_template_sync_id );

WP_CLI::success( sprintf( 'Created standalone Home template #%d from templates/home.html.', $pns_template_sync_id ) );
