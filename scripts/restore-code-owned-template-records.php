<?php
/**
 * Restore code ownership for audited active template and template-part records.
 *
 * The Site Editor stores global template customisations as posts. This migration
 * exports every record before deletion, verifies the exact audited content
 * hashes, then removes the saved copies so WordPress resolves the theme files.
 *
 * Usage:
 *   wp eval-file scripts/restore-code-owned-template-records.php
 *   wp eval-file scripts/restore-code-owned-template-records.php apply
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$pns_restore_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pns_restore_apply = in_array( 'apply', $pns_restore_args, true ) || in_array( '--apply', $pns_restore_args, true );
$pns_restore_theme = get_stylesheet();
$pns_restore_dir   = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/template-code-authority-db-backups';

/*
 * These values intentionally make the operation fail closed if an editor has
 * changed a record since the July 2026 drift audit. Update them only after a
 * fresh review; do not use this as a general-purpose template deletion tool.
 */
$pns_restore_expected_records = array(
	array(
		'id'     => 6895,
		'type'   => 'wp_template',
		'slug'   => 'home',
		'sha256' => '165ccb40540312a574ba2801328e9c2204fefcee6b55076a3a02b474802e4f89',
	),
	array(
		'id'     => 6936,
		'type'   => 'wp_template',
		'slug'   => 'archive-herstory',
		'sha256' => '1068c81b45a29bd1315562e46ee359b53e664f9611bd81db1e84ed25e582aab8',
	),
	array(
		'id'     => 7117,
		'type'   => 'wp_template',
		'slug'   => 'search',
		'sha256' => 'a946d749d45bee47dcbae2c95d1b258920d05f90b1bb388413d01be50b80d1fb',
	),
	array(
		'id'     => 6987,
		'type'   => 'wp_template',
		'slug'   => 'page-search',
		'sha256' => '15df1fc479e4ca3927b54a3e5e81a5c9d0a922c886e468151058fcae6970f2e4',
	),
	array(
		'id'     => 6709,
		'type'   => 'wp_template_part',
		'slug'   => 'header',
		'sha256' => 'f4e5a41b77832768cf51f5287b61c4c926cc7d736392d8deab652371d6819612',
	),
	array(
		'id'     => 7120,
		'type'   => 'wp_template_part',
		'slug'   => 'footer',
		'sha256' => '7c4376d671cbbfd5639960795150b18d0e7e36d377493e11b324a112dca95fc0',
	),
);

$pns_restore_clone_records = array(
	array( 'id' => 7000, 'type' => 'wp_template', 'slug' => 'search-shadow-7000', 'sha256' => '7f828352c3cf879d8b139f8888dd46b49bc7d2576ed8eb97c9731b82d204c41b' ),
	array( 'id' => 7115, 'type' => 'wp_template', 'slug' => 'search-shadow-7115', 'sha256' => 'fe392fc002535aa5d602fc45e6094fd5272c79a3c7d1fdd0ad1dfb428262a9ce' ),
	array( 'id' => 7116, 'type' => 'wp_template', 'slug' => 'search-shadow-7116', 'sha256' => 'fe392fc002535aa5d602fc45e6094fd5272c79a3c7d1fdd0ad1dfb428262a9ce' ),
	array( 'id' => 7118, 'type' => 'wp_template', 'slug' => 'search-11', 'sha256' => '6df6ab6bb9558dbab6c0a4265b79d445238898f12241164a528f264545631b76' ),
	array( 'id' => 7119, 'type' => 'wp_template_part', 'slug' => 'header-2', 'sha256' => 'f4e5a41b77832768cf51f5287b61c4c926cc7d736392d8deab652371d6819612' ),
	array( 'id' => 7121, 'type' => 'wp_template', 'slug' => 'search-10', 'sha256' => '6df6ab6bb9558dbab6c0a4265b79d445238898f12241164a528f264545631b76' ),
	array( 'id' => 7122, 'type' => 'wp_template', 'slug' => 'search-9', 'sha256' => '6df6ab6bb9558dbab6c0a4265b79d445238898f12241164a528f264545631b76' ),
	array( 'id' => 7123, 'type' => 'wp_template', 'slug' => 'search-8', 'sha256' => '1a1f7c170889091bd091bec7d8a670e08adff535cba1841acd85729828f38220' ),
	array( 'id' => 7124, 'type' => 'wp_template', 'slug' => 'search-7', 'sha256' => '71c8716ea3203d200c3f6b71151fec81c9812e71233d27029c8cd262d03a3f00' ),
	array( 'id' => 7125, 'type' => 'wp_template', 'slug' => 'search-6', 'sha256' => '71c8716ea3203d200c3f6b71151fec81c9812e71233d27029c8cd262d03a3f00' ),
	array( 'id' => 7126, 'type' => 'wp_template', 'slug' => 'search-5', 'sha256' => 'b70716a5a22298c377402fdabccb56eb031bcd11da17876086029ba24cd4215f' ),
	array( 'id' => 7127, 'type' => 'wp_template', 'slug' => 'search-4', 'sha256' => '337730313aca0c20532b7a00193b2ada273190fe7748aec470127508833636d7' ),
	array( 'id' => 7128, 'type' => 'wp_template', 'slug' => 'search-3', 'sha256' => '7018a360ba3318b7fd417415974932cd8a269a7d4b3c97eea24a6987927d3b7a' ),
	array( 'id' => 7129, 'type' => 'wp_template', 'slug' => 'search-2', 'sha256' => '7018a360ba3318b7fd417415974932cd8a269a7d4b3c97eea24a6987927d3b7a' ),
	array( 'id' => 7114, 'type' => 'wp_template_part', 'slug' => 'header-3', 'sha256' => 'f4e5a41b77832768cf51f5287b61c4c926cc7d736392d8deab652371d6819612' ),
);

$pns_restore_records = array_merge( $pns_restore_expected_records, $pns_restore_clone_records );
$pns_restore_backup  = array(
	'created_at_gmt' => gmdate( 'c' ),
	'stylesheet'     => $pns_restore_theme,
	'records'        => array(),
);

foreach ( $pns_restore_records as $pns_restore_expected ) {
	$pns_restore_post = get_post( $pns_restore_expected['id'] );

	if ( ! $pns_restore_post || $pns_restore_post->post_type !== $pns_restore_expected['type'] || $pns_restore_post->post_name !== $pns_restore_expected['slug'] || hash( 'sha256', $pns_restore_post->post_content ) !== $pns_restore_expected['sha256'] ) {
		WP_CLI::error( sprintf( 'Record #%d no longer matches the audited migration target. Aborting without changes.', $pns_restore_expected['id'] ) );
	}

	$pns_restore_terms = wp_get_object_terms( $pns_restore_post->ID, 'wp_theme', array( 'fields' => 'slugs' ) );
	if ( ! in_array( $pns_restore_theme, $pns_restore_terms, true ) ) {
		WP_CLI::error( sprintf( 'Record #%d is not owned by the active theme. Aborting without changes.', $pns_restore_post->ID ) );
	}

	$pns_restore_backup['records'][] = array(
		'post'         => $pns_restore_post->to_array(),
		'meta'         => get_post_meta( $pns_restore_post->ID ),
		'wp_theme'     => $pns_restore_terms,
		'migration_set' => in_array( $pns_restore_expected, $pns_restore_expected_records, true ) ? 'code-authority' : 'unreferenced-clone',
	);
}

foreach ( $pns_restore_clone_records as $pns_restore_clone ) {
	$pns_restore_template_values = array(
		$pns_restore_clone['slug'],
		$pns_restore_theme . '//' . $pns_restore_clone['slug'],
	);
	$pns_restore_references = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_page_template' AND meta_value IN (%s, %s)",
			$pns_restore_template_values[0],
			$pns_restore_template_values[1]
		)
	);

	if ( ! empty( $pns_restore_references ) ) {
		WP_CLI::error( sprintf( 'Clone #%d is assigned to content. Aborting without changes.', $pns_restore_clone['id'] ) );
	}
}

foreach ( $pns_restore_expected_records as $pns_restore_expected ) {
	$pns_restore_directory = 'wp_template_part' === $pns_restore_expected['type'] ? 'parts' : 'templates';
	$pns_restore_source    = get_stylesheet_directory() . '/' . $pns_restore_directory . '/' . $pns_restore_expected['slug'] . '.html';

	if ( ! is_readable( $pns_restore_source ) ) {
		WP_CLI::error( sprintf( 'Missing source file for #%d: %s. Aborting without changes.', $pns_restore_expected['id'], $pns_restore_source ) );
	}
}

WP_CLI::log( sprintf( 'Verified %d audited records for code-authority restoration and clone cleanup.', count( $pns_restore_records ) ) );

if ( ! $pns_restore_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to export and permanently delete these saved records.' );
	return;
}

if ( ! is_dir( $pns_restore_dir ) && ! wp_mkdir_p( $pns_restore_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_restore_dir ) );
}

$pns_restore_backup_path = trailingslashit( $pns_restore_dir ) . gmdate( 'Ymd-His' ) . '-code-authority-template-records.json';

if ( false === file_put_contents( $pns_restore_backup_path, wp_json_encode( $pns_restore_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_restore_backup_path ) );
}

foreach ( $pns_restore_records as $pns_restore_expected ) {
	if ( ! wp_delete_post( $pns_restore_expected['id'], true ) ) {
		WP_CLI::error( sprintf( 'Could not delete record #%d.', $pns_restore_expected['id'] ) );
	}
}

WP_CLI::success( sprintf( 'Deleted %d saved template records. Rollback export: %s', count( $pns_restore_records ), $pns_restore_backup_path ) );
