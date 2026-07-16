<?php
/**
 * Remove byte-identical saved templates and template parts that shadow files.
 *
 * Usage:
 *   wp eval-file scripts/remove-matching-template-shadows.php
 *   wp eval-file scripts/remove-matching-template-shadows.php apply
 *
 * Saved records are exported before permanent deletion because WordPress does
 * not support trash for template and template-part posts.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_shadow_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pns_shadow_apply = in_array( 'apply', $pns_shadow_args, true ) || in_array( '--apply', $pns_shadow_args, true );
$pns_shadow_theme = get_stylesheet();
$pns_shadow_dir   = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/template-shadow-db-backups';
$pns_shadow_posts = get_posts(
	array(
		'post_type'      => array( 'wp_template', 'wp_template_part' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'tax_query'      => array(
			array(
				'taxonomy' => 'wp_theme',
				'field'    => 'slug',
				'terms'    => array( $pns_shadow_theme ),
			),
		),
	)
);
$pns_shadow_matches = array();

foreach ( $pns_shadow_posts as $pns_shadow_post ) {
	$pns_shadow_directory = 'wp_template_part' === $pns_shadow_post->post_type ? 'parts' : 'templates';
	$pns_shadow_source    = get_stylesheet_directory() . '/' . $pns_shadow_directory . '/' . $pns_shadow_post->post_name . '.html';

	if ( ! is_readable( $pns_shadow_source ) ) {
		WP_CLI::warning( sprintf( 'Skipping #%d: no readable source file at %s.', $pns_shadow_post->ID, $pns_shadow_source ) );
		continue;
	}

	$pns_shadow_content = file_get_contents( $pns_shadow_source );

	if ( $pns_shadow_post->post_content !== $pns_shadow_content ) {
		WP_CLI::warning( sprintf( 'Skipping #%d (%s): saved content diverges from %s.', $pns_shadow_post->ID, $pns_shadow_post->post_name, $pns_shadow_source ) );
		continue;
	}

	$pns_shadow_matches[] = array(
		'post'        => $pns_shadow_post,
		'source_path' => $pns_shadow_source,
	);
	WP_CLI::log( sprintf( '#%d (%s) is a byte-identical shadow of %s.', $pns_shadow_post->ID, $pns_shadow_post->post_name, $pns_shadow_source ) );
}

if ( empty( $pns_shadow_matches ) ) {
	WP_CLI::success( 'No byte-identical active template shadows were found.' );
	return;
}

if ( ! $pns_shadow_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to export and delete the matching shadows.' );
	return;
}

if ( ! is_dir( $pns_shadow_dir ) && ! wp_mkdir_p( $pns_shadow_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_shadow_dir ) );
}

$pns_shadow_backup = array(
	'created_at_gmt' => gmdate( 'c' ),
	'records'        => array(),
);

foreach ( $pns_shadow_matches as $pns_shadow_match ) {
	$pns_shadow_post = $pns_shadow_match['post'];
	$pns_shadow_backup['records'][] = array(
		'ID'           => $pns_shadow_post->ID,
		'post_type'    => $pns_shadow_post->post_type,
		'post_status'  => $pns_shadow_post->post_status,
		'post_name'    => $pns_shadow_post->post_name,
		'post_title'   => $pns_shadow_post->post_title,
		'post_content' => $pns_shadow_post->post_content,
		'wp_theme'     => wp_get_object_terms( $pns_shadow_post->ID, 'wp_theme', array( 'fields' => 'slugs' ) ),
	);
}

$pns_shadow_backup_path = trailingslashit( $pns_shadow_dir ) . gmdate( 'Ymd-His' ) . '-matching-shadows.json';

if ( false === file_put_contents( $pns_shadow_backup_path, wp_json_encode( $pns_shadow_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_shadow_backup_path ) );
}

foreach ( $pns_shadow_matches as $pns_shadow_match ) {
	$pns_shadow_post = $pns_shadow_match['post'];
	$pns_shadow_deleted = wp_delete_post( $pns_shadow_post->ID, true );

	if ( ! $pns_shadow_deleted ) {
		WP_CLI::error( sprintf( 'Could not delete template shadow #%d.', $pns_shadow_post->ID ) );
	}
}

WP_CLI::success( sprintf( 'Deleted %d byte-identical template shadow(s). Rollback export: %s', count( $pns_shadow_matches ), $pns_shadow_backup_path ) );
