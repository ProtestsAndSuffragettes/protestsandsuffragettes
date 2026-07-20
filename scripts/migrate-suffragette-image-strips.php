<?php
/**
 * Canonicalise saved Suffragette Image Strips to the generic Image Strip pattern.
 *
 * Usage:
 *   wp eval-file scripts/migrate-suffragette-image-strips.php
 *   wp eval-file scripts/migrate-suffragette-image-strips.php apply
 *
 * The dry run is read-only. Apply requires the explicit argument, writes an
 * atomic rollback export, verifies every target has not changed since it was
 * read, and performs the database updates as one transaction.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_image_strip_args = isset( $args ) && is_array( $args ) ? $args : array();

foreach ( $pns_image_strip_args as $pns_image_strip_arg ) {
	if ( ! in_array( $pns_image_strip_arg, array( 'apply', '--apply' ), true ) ) {
		WP_CLI::error( sprintf( 'Unexpected argument: %s. Use only apply.', $pns_image_strip_arg ) );
	}
}

$pns_image_strip_apply      = in_array( 'apply', $pns_image_strip_args, true ) || in_array( '--apply', $pns_image_strip_args, true );
$pns_image_strip_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/suffragette-image-strip-db-backups';

/**
 * Return whether a class string contains one exact class token.
 *
 * @param string $class_name Class string.
 * @param string $token Class token.
 * @return bool
 */
function pns_image_strip_has_class( string $class_name, string $token ): bool {
	$tokens = preg_split( '/\s+/', trim( $class_name ) );

	return is_array( $tokens ) && in_array( $token, $tokens, true );
}

/**
 * Replace one exact class token, preserving the original token order.
 *
 * @param string $class_name Class string.
 * @param string $old_token Legacy token.
 * @param string $new_token Canonical token.
 * @return string
 */
function pns_image_strip_replace_class( string $class_name, string $old_token, string $new_token ): string {
	$tokens = preg_split( '/\s+/', trim( $class_name ) );

	if ( ! is_array( $tokens ) ) {
		return $class_name;
	}

	foreach ( $tokens as &$token ) {
		if ( $old_token === $token ) {
			$token = $new_token;
		}
	}
	unset( $token );

	return implode( ' ', $tokens );
}

/**
 * Update the serialized wrapper markup that Core retains with parsed blocks.
 *
 * @param array<string,mixed> $block Parsed block.
 * @param string              $old_class_name Original class string.
 * @param string              $new_class_name Canonical class string.
 * @return array<string,mixed>
 */
function pns_image_strip_replace_saved_markup( array $block, string $old_class_name, string $new_class_name ): array {
	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$block['innerHTML'] = str_replace( $old_class_name, $new_class_name, $block['innerHTML'] );
	}

	if ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
		foreach ( $block['innerContent'] as &$inner_content ) {
			if ( is_string( $inner_content ) ) {
				$inner_content = str_replace( $old_class_name, $new_class_name, $inner_content );
			}
		}
		unset( $inner_content );
	}

	return $block;
}

/**
 * Convert legacy Image Strip blocks recursively and record each conversion.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<int,string>              $changes Human-readable changes.
 * @return array<int,array<string,mixed>>
 */
function pns_image_strip_migrate_blocks( array $blocks, array &$changes ): array {
	foreach ( $blocks as &$block ) {
		$block_name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';

		if ( 'core/columns' === $block_name && pns_image_strip_has_class( $class_name, 'pns-suffragette-image-strip' ) ) {
			$new_class_name      = pns_image_strip_replace_class( $class_name, 'pns-suffragette-image-strip', 'pns-image-strip' );
			$metadata            = isset( $attrs['metadata'] ) && is_array( $attrs['metadata'] ) ? $attrs['metadata'] : array();
			$metadata['categories']  = array( 'pns-layout' );
			$metadata['patternName'] = 'pns/image-strip';
			$metadata['name']        = 'PNS - Image Strip';
			$attrs['className']  = $new_class_name;
			$attrs['metadata']   = $metadata;
			$block['attrs']      = $attrs;
			$block               = pns_image_strip_replace_saved_markup( $block, $class_name, $new_class_name );
			$changes[]           = 'PNS - Suffragette Image Strip -> PNS - Image Strip';
		}

		if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_image_strip_migrate_blocks( $block['innerBlocks'], $changes );
		}
	}
	unset( $block );

	return $blocks;
}

/**
 * Return the number of legacy Image Strip roots remaining in parsed blocks.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @return int
 */
function pns_image_strip_count_legacy_blocks( array $blocks ): int {
	$count = 0;

	foreach ( $blocks as $block ) {
		$block_name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';

		if ( 'core/columns' === $block_name && pns_image_strip_has_class( $class_name, 'pns-suffragette-image-strip' ) ) {
			++$count;
		}

		if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$count += pns_image_strip_count_legacy_blocks( $block['innerBlocks'] );
		}
	}

	return $count;
}

global $wpdb;

$pns_image_strip_rows = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title, post_name, post_modified_gmt, post_content
	FROM {$wpdb->posts}
	WHERE post_type IN ('page', 'post', 'herstory', 'wp_block', 'wp_template', 'wp_template_part')
		AND post_status IN ('publish', 'draft', 'private', 'pending', 'future')
		AND post_content LIKE '%pns-suffragette-image-strip%'
	ORDER BY post_type, ID"
);

$pns_image_strip_targets = array();

foreach ( $pns_image_strip_rows as $pns_image_strip_row ) {
	$changes = array();
	$blocks  = pns_image_strip_migrate_blocks( parse_blocks( $pns_image_strip_row->post_content ), $changes );
	$updated = serialize_blocks( $blocks );

	if ( empty( $changes ) || $updated === $pns_image_strip_row->post_content ) {
		continue;
	}

	if ( 0 !== pns_image_strip_count_legacy_blocks( parse_blocks( $updated ) ) ) {
		WP_CLI::error( sprintf( 'Refusing to migrate #%d: legacy blocks remain after serialization.', $pns_image_strip_row->ID ) );
	}

	$pns_image_strip_targets[] = array(
		'ID'                 => (int) $pns_image_strip_row->ID,
		'post_type'          => $pns_image_strip_row->post_type,
		'post_status'        => $pns_image_strip_row->post_status,
		'post_title'         => $pns_image_strip_row->post_title,
		'post_name'          => $pns_image_strip_row->post_name,
		'post_modified_gmt'  => $pns_image_strip_row->post_modified_gmt,
		'original_content'   => $pns_image_strip_row->post_content,
		'original_sha256'    => hash( 'sha256', $pns_image_strip_row->post_content ),
		'updated_content'    => $updated,
		'changes'            => $changes,
	);
}

WP_CLI::log( sprintf( 'Current rows requiring Image Strip migration: %d', count( $pns_image_strip_targets ) ) );

foreach ( $pns_image_strip_targets as $pns_image_strip_target ) {
	WP_CLI::log(
		sprintf(
			'#%d [%s/%s] %s (%d strips)',
			$pns_image_strip_target['ID'],
			$pns_image_strip_target['post_type'],
			$pns_image_strip_target['post_status'],
			'' !== $pns_image_strip_target['post_title'] ? $pns_image_strip_target['post_title'] : '(no title)',
			count( $pns_image_strip_target['changes'] )
		)
	);
}

if ( ! $pns_image_strip_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and migrate the current rows.' );
	return;
}

if ( empty( $pns_image_strip_targets ) ) {
	WP_CLI::success( 'No rows require migration.' );
	return;
}

foreach ( $pns_image_strip_targets as $pns_image_strip_target ) {
	$current_content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $pns_image_strip_target['ID'] ) );

	if ( ! is_string( $current_content ) || ! hash_equals( $pns_image_strip_target['original_sha256'], hash( 'sha256', $current_content ) ) ) {
		WP_CLI::error( sprintf( 'Refusing to migrate #%d: content changed after the dry-run read.', $pns_image_strip_target['ID'] ) );
	}
}

if ( ! is_dir( $pns_image_strip_backup_dir ) && ! wp_mkdir_p( $pns_image_strip_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_image_strip_backup_dir ) );
}

$pns_image_strip_backup_rows = array();

foreach ( $pns_image_strip_targets as $pns_image_strip_target ) {
	$pns_image_strip_backup_rows[] = array(
		'ID'                => $pns_image_strip_target['ID'],
		'post_type'         => $pns_image_strip_target['post_type'],
		'post_status'       => $pns_image_strip_target['post_status'],
		'post_title'        => $pns_image_strip_target['post_title'],
		'post_name'         => $pns_image_strip_target['post_name'],
		'post_modified_gmt' => $pns_image_strip_target['post_modified_gmt'],
		'post_content'      => $pns_image_strip_target['original_content'],
	);
}

$pns_image_strip_backup_json = wp_json_encode(
	array(
		'created_at_gmt' => gmdate( 'c' ),
		'purpose'        => 'Canonicalise PNS - Suffragette Image Strip blocks to PNS - Image Strip.',
		'rows'           => $pns_image_strip_backup_rows,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ( ! is_string( $pns_image_strip_backup_json ) ) {
	WP_CLI::error( 'Could not encode the rollback export.' );
}

$pns_image_strip_backup_path = trailingslashit( $pns_image_strip_backup_dir ) . gmdate( 'Ymd-His' ) . '-suffragette-image-strips.json';
$pns_image_strip_backup_temp = $pns_image_strip_backup_path . '.tmp';

if ( false === file_put_contents( $pns_image_strip_backup_temp, $pns_image_strip_backup_json . "\n" ) || ! rename( $pns_image_strip_backup_temp, $pns_image_strip_backup_path ) ) {
	WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_image_strip_backup_path ) );
}

if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
	WP_CLI::error( 'Could not start the Image Strip migration transaction.' );
}

foreach ( $pns_image_strip_targets as $pns_image_strip_target ) {
	$updated = $wpdb->update(
		$wpdb->posts,
		array( 'post_content' => $pns_image_strip_target['updated_content'] ),
		array( 'ID' => $pns_image_strip_target['ID'] ),
		array( '%s' ),
		array( '%d' )
	);

	if ( false === $updated ) {
		$wpdb->query( 'ROLLBACK' );
		WP_CLI::error( sprintf( 'Migration failed for #%d. Transaction rolled back; rollback export retained at %s.', $pns_image_strip_target['ID'], $pns_image_strip_backup_path ) );
	}
}

if ( false === $wpdb->query( 'COMMIT' ) ) {
	$wpdb->query( 'ROLLBACK' );
	WP_CLI::error( sprintf( 'Could not commit the migration. Rollback export retained at %s.', $pns_image_strip_backup_path ) );
}

foreach ( $pns_image_strip_targets as $pns_image_strip_target ) {
	clean_post_cache( $pns_image_strip_target['ID'] );
}

WP_CLI::success( sprintf( 'Migrated %d rows. Rollback export: %s', count( $pns_image_strip_targets ), $pns_image_strip_backup_path ) );
