<?php
/**
 * Replace the failed style-guide Stack trial with Split Section alignment.
 *
 * Usage:
 *   wp eval-file scripts/migrate-split-section-text-alignment.php 5654
 *   wp eval-file scripts/migrate-split-section-text-alignment.php 5654 apply
 *
 * Dry-run is the default. Apply restores the verified pre-Stack content and
 * adds alignment attributes only to four deliberately uneven Text | Text
 * examples. Media variations remain completely untouched.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PNS_SPLIT_ALIGNMENT_STYLE_GUIDE_ID = 5654;
const PNS_SPLIT_ALIGNMENT_STACK_SHA256 = '22469ef57a1f8b6a4fabdae2d37dfff870527e6a003d019ce05ff3671b002705';
const PNS_SPLIT_ALIGNMENT_BASE_SHA256 = '41486dcdc92a5b83b15d50a89b613d3af14a85a9f3e39413d1a241daf0b71eeb';
const PNS_SPLIT_ALIGNMENT_RESULT_SHA256 = '098ac53edeeada48f9a2dcfe755f8037a5522f7a4eba5848e91d856870fb3bad';
const PNS_SPLIT_ALIGNMENT_EXPECTED_SECTIONS = 20;
const PNS_SPLIT_ALIGNMENT_EXPECTED_COPY_GROUPS = 28;

$pns_split_alignment_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_split_alignment_id    = isset( $pns_split_alignment_args[0] ) ? (int) $pns_split_alignment_args[0] : 0;
$pns_split_alignment_apply = in_array( 'apply', $pns_split_alignment_args, true ) || in_array( '--apply', $pns_split_alignment_args, true );
$pns_split_alignment_page  = get_post( $pns_split_alignment_id );

if ( PNS_SPLIT_ALIGNMENT_STYLE_GUIDE_ID !== $pns_split_alignment_id ) {
	WP_CLI::error( 'Pass the explicit Split Section style-guide page ID: 5654 [apply].' );
}

if (
	! $pns_split_alignment_page instanceof WP_Post
	|| 'page' !== $pns_split_alignment_page->post_type
	|| 'private' !== $pns_split_alignment_page->post_status
	|| 'pns-style-guide-split-section-components' !== $pns_split_alignment_page->post_name
	|| 'PNS Style Guide: Split Section Components' !== $pns_split_alignment_page->post_title
) {
	WP_CLI::error( 'Page 5654 is no longer the expected private Split Section style-guide page.' );
}

$pns_split_alignment_live_hash = hash( 'sha256', $pns_split_alignment_page->post_content );

if ( PNS_SPLIT_ALIGNMENT_RESULT_SHA256 === $pns_split_alignment_live_hash ) {
	$pns_split_alignment_saved_blocks = parse_blocks( $pns_split_alignment_page->post_content );
	$pns_split_alignment_saved_stats  = pns_split_alignment_process_blocks( $pns_split_alignment_saved_blocks, false );

	if (
		PNS_SPLIT_ALIGNMENT_EXPECTED_SECTIONS !== $pns_split_alignment_saved_stats['split_sections']
		|| PNS_SPLIT_ALIGNMENT_EXPECTED_COPY_GROUPS !== $pns_split_alignment_saved_stats['copy_groups']
		|| 0 !== $pns_split_alignment_saved_stats['stack_groups']
		|| 2 !== $pns_split_alignment_saved_stats['primary_top']
		|| 2 !== $pns_split_alignment_saved_stats['secondary_top']
		|| 0 !== $pns_split_alignment_saved_stats['media_alignment_attributes']
	) {
		WP_CLI::error( 'The saved alignment migration hash matches, but its block contract does not.' );
	}

	WP_CLI::success( 'The private style guide already has copy-only alignment and no media alignment attributes.' );
	return;
}

if ( PNS_SPLIT_ALIGNMENT_STACK_SHA256 !== $pns_split_alignment_live_hash ) {
	WP_CLI::error( 'The live style guide no longer matches the reviewed Stack trial; refusing to overwrite newer content.' );
}

$pns_split_alignment_lock = (string) get_post_meta( $pns_split_alignment_id, '_edit_lock', true );

if ( $pns_split_alignment_apply && '' !== $pns_split_alignment_lock ) {
	$pns_split_alignment_lock_parts = explode( ':', $pns_split_alignment_lock, 2 );
	$pns_split_alignment_lock_time  = isset( $pns_split_alignment_lock_parts[0] ) ? (int) $pns_split_alignment_lock_parts[0] : 0;

	if ( $pns_split_alignment_lock_time > time() - 150 ) {
		WP_CLI::error( 'Page 5654 has an active editor lock. Close the editor and retry after the lock expires.' );
	}
}

$pns_split_alignment_project_root = dirname( get_stylesheet_directory(), 5 );
$pns_split_alignment_source_path  = $pns_split_alignment_project_root . '/docs/jobs/split-section-stack-db-backups/20260717-151144-split-section-stacks-5654.json';
$pns_split_alignment_source_json  = file_get_contents( $pns_split_alignment_source_path );
$pns_split_alignment_source       = is_string( $pns_split_alignment_source_json ) ? json_decode( $pns_split_alignment_source_json, true ) : null;

if (
	! is_array( $pns_split_alignment_source )
	|| PNS_SPLIT_ALIGNMENT_STYLE_GUIDE_ID !== (int) ( $pns_split_alignment_source['post_id'] ?? 0 )
	|| 'private' !== ( $pns_split_alignment_source['post_status'] ?? '' )
	|| 'pns-style-guide-split-section-components' !== ( $pns_split_alignment_source['post_name'] ?? '' )
	|| 'PNS Style Guide: Split Section Components' !== ( $pns_split_alignment_source['post_title'] ?? '' )
	|| PNS_SPLIT_ALIGNMENT_BASE_SHA256 !== ( $pns_split_alignment_source['content_sha256'] ?? '' )
	|| ! is_string( $pns_split_alignment_source['post_content'] ?? null )
	|| PNS_SPLIT_ALIGNMENT_BASE_SHA256 !== hash( 'sha256', $pns_split_alignment_source['post_content'] )
) {
	WP_CLI::error( 'The pre-Stack rollback export failed its identity or content checks.' );
}

$pns_split_alignment_blocks = parse_blocks( $pns_split_alignment_source['post_content'] );

if ( serialize_blocks( $pns_split_alignment_blocks ) !== $pns_split_alignment_source['post_content'] ) {
	WP_CLI::error( 'The rollback content does not round-trip byte-for-byte.' );
}

$pns_split_alignment_before = pns_split_alignment_process_blocks( $pns_split_alignment_blocks, false );

if (
	PNS_SPLIT_ALIGNMENT_EXPECTED_SECTIONS !== $pns_split_alignment_before['split_sections']
	|| 8 !== $pns_split_alignment_before['text_sections']
	|| 12 !== $pns_split_alignment_before['media_sections']
	|| PNS_SPLIT_ALIGNMENT_EXPECTED_COPY_GROUPS !== $pns_split_alignment_before['copy_groups']
	|| 0 !== $pns_split_alignment_before['stack_groups']
	|| 0 !== $pns_split_alignment_before['alignment_attributes']
) {
	WP_CLI::error( 'The rollback export no longer matches the expected 20-section pre-Stack matrix.' );
}

$pns_split_alignment_after = pns_split_alignment_process_blocks( $pns_split_alignment_blocks, true );

if (
	PNS_SPLIT_ALIGNMENT_EXPECTED_SECTIONS !== $pns_split_alignment_after['split_sections']
	|| 8 !== $pns_split_alignment_after['text_sections']
	|| 12 !== $pns_split_alignment_after['media_sections']
	|| PNS_SPLIT_ALIGNMENT_EXPECTED_COPY_GROUPS !== $pns_split_alignment_after['copy_groups']
	|| 0 !== $pns_split_alignment_after['stack_groups']
	|| 2 !== $pns_split_alignment_after['primary_top']
	|| 2 !== $pns_split_alignment_after['secondary_top']
	|| 0 !== $pns_split_alignment_after['media_alignment_attributes']
	|| 4 !== $pns_split_alignment_after['target_hits']
) {
	WP_CLI::error( 'Generated alignment migration failed its expected Text | Text and media no-op contract.' );
}

$pns_split_alignment_new_content = serialize_blocks( $pns_split_alignment_blocks );
$pns_split_alignment_new_hash    = hash( 'sha256', $pns_split_alignment_new_content );

WP_CLI::log( 'Style-guide #5654 is ready to replace the Stack trial with copy-only alignment controls.' );
WP_CLI::log( 'Result: 28 normal-flow copy Groups, four top-aligned Text | Text panels, and zero media alignment attributes.' );
WP_CLI::log( 'Generated content SHA-256: ' . $pns_split_alignment_new_hash );

if ( ! $pns_split_alignment_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with 5654 apply to update the private style guide.' );
	return;
}

$pns_split_alignment_backup_dir = $pns_split_alignment_project_root . '/docs/jobs/split-section-alignment-db-backups';

if ( ! is_dir( $pns_split_alignment_backup_dir ) && ! wp_mkdir_p( $pns_split_alignment_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_split_alignment_backup_dir ) );
}

$pns_split_alignment_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'post_id'           => $pns_split_alignment_id,
	'post_title'        => $pns_split_alignment_page->post_title,
	'post_name'         => $pns_split_alignment_page->post_name,
	'post_status'       => $pns_split_alignment_page->post_status,
	'post_modified_gmt' => $pns_split_alignment_page->post_modified_gmt,
	'wp_page_template'  => get_post_meta( $pns_split_alignment_id, '_wp_page_template', true ),
	'content_sha256'    => hash( 'sha256', $pns_split_alignment_page->post_content ),
	'post_content'      => $pns_split_alignment_page->post_content,
);
$pns_split_alignment_backup_path = trailingslashit( $pns_split_alignment_backup_dir ) . gmdate( 'Ymd-His' ) . '-pre-alignment-5654.json';
$pns_split_alignment_backup_json = wp_json_encode( $pns_split_alignment_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$pns_split_alignment_temp_path   = $pns_split_alignment_backup_path . '.tmp';

if (
	! is_string( $pns_split_alignment_backup_json )
	|| false === file_put_contents( $pns_split_alignment_temp_path, $pns_split_alignment_backup_json . "\n" )
	|| ! rename( $pns_split_alignment_temp_path, $pns_split_alignment_backup_path )
) {
	WP_CLI::error( sprintf( 'Could not write rollback data: %s', $pns_split_alignment_backup_path ) );
}

$pns_split_alignment_updated_id = wp_update_post(
	array(
		'ID'           => $pns_split_alignment_id,
		'post_content' => wp_slash( $pns_split_alignment_new_content ),
	),
	true
);

if ( is_wp_error( $pns_split_alignment_updated_id ) ) {
	WP_CLI::error( $pns_split_alignment_updated_id->get_error_message() );
}

clean_post_cache( $pns_split_alignment_id );
$pns_split_alignment_saved = get_post( $pns_split_alignment_id );

if ( ! $pns_split_alignment_saved instanceof WP_Post || $pns_split_alignment_new_hash !== hash( 'sha256', $pns_split_alignment_saved->post_content ) ) {
	WP_CLI::error( 'WordPress saved content does not match the generated alignment migration.' );
}

WP_CLI::success( sprintf( 'Updated the private style guide. Rollback export: %s', $pns_split_alignment_backup_path ) );

/**
 * Analyse or mutate the style-guide Split Section tree.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree, passed by reference.
 * @param bool                           $mutate Whether to add outer alignment attributes.
 * @return array<string,int>
 */
function pns_split_alignment_process_blocks( &$blocks, $mutate ) {
	$stats = array(
		'split_sections'             => 0,
		'text_sections'              => 0,
		'media_sections'             => 0,
		'copy_groups'                => 0,
		'stack_groups'               => 0,
		'alignment_attributes'       => 0,
		'primary_top'                => 0,
		'secondary_top'              => 0,
		'media_alignment_attributes' => 0,
		'target_hits'                => 0,
	);

	pns_split_alignment_walk_roots( $blocks, $mutate, $stats );

	return $stats;
}

/**
 * Find and process Split Section roots.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree, passed by reference.
 * @param bool                           $mutate Whether to write alignment attributes.
 * @param array<string,int>              $stats Mutable counters.
 */
function pns_split_alignment_walk_roots( &$blocks, $mutate, &$stats ) {
	$target_alignments = array(
		'pns-text-text-demo-text-text-long-short'             => 'secondaryTextVerticalAlignment',
		'pns-text-text-demo-text-text-short-long'             => 'textVerticalAlignment',
		'pns-text-text-demo-text-text-constrained-long-short' => 'secondaryTextVerticalAlignment',
		'pns-text-text-demo-text-text-constrained-short-long' => 'textVerticalAlignment',
	);
	$legacy_text_layouts = array( 'text-text', 'text-text-reversed', 'text-text-constrained', 'text-text-constrained-reversed' );

	foreach ( $blocks as &$block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) ) {
			++$stats['split_sections'];
			$media_type     = (string) ( $block['attrs']['mediaType'] ?? '' );
			$layout_variant = (string) ( $block['attrs']['layoutVariant'] ?? '' );
			$is_text_text    = 'text' === $media_type || in_array( $layout_variant, $legacy_text_layouts, true );
			$anchor          = (string) ( $block['attrs']['anchor'] ?? '' );

			if ( $mutate ) {
				unset( $block['attrs']['textVerticalAlignment'], $block['attrs']['secondaryTextVerticalAlignment'] );

				if ( $is_text_text && isset( $target_alignments[ $anchor ] ) ) {
					$block['attrs'][ $target_alignments[ $anchor ] ] = 'top';
					++$stats['target_hits'];
				}
			}

			$has_primary   = array_key_exists( 'textVerticalAlignment', $block['attrs'] );
			$has_secondary = array_key_exists( 'secondaryTextVerticalAlignment', $block['attrs'] );

			if ( $has_primary ) {
				++$stats['alignment_attributes'];
			}

			if ( $has_secondary ) {
				++$stats['alignment_attributes'];
			}

			if ( $is_text_text ) {
				++$stats['text_sections'];
				$stats['primary_top'] += 'top' === ( $block['attrs']['textVerticalAlignment'] ?? '' ) ? 1 : 0;
				$stats['secondary_top'] += 'top' === ( $block['attrs']['secondaryTextVerticalAlignment'] ?? '' ) ? 1 : 0;
			} else {
				++$stats['media_sections'];
				$stats['media_alignment_attributes'] += $has_primary ? 1 : 0;
				$stats['media_alignment_attributes'] += $has_secondary ? 1 : 0;
			}

			pns_split_alignment_count_copy_groups( $block['innerBlocks'], $stats );
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			pns_split_alignment_walk_roots( $block['innerBlocks'], $mutate, $stats );
		}
	}
	unset( $block );
}

/**
 * Count normal-flow and Stack copy Groups inside one Split Section.
 *
 * @param array<int,array<string,mixed>> $blocks Descendant block tree.
 * @param array<string,int>              $stats Mutable counters.
 */
function pns_split_alignment_count_copy_groups( $blocks, &$stats ) {
	foreach ( $blocks as $block ) {
		$class_name = (string) ( $block['attrs']['className'] ?? '' );
		$is_copy    = 'core/group' === ( $block['blockName'] ?? '' )
			&& str_contains( ' ' . $class_name . ' ', ' pns-split-section__copy ' );

		if ( $is_copy ) {
			++$stats['copy_groups'];
			$layout = $block['attrs']['layout'] ?? array();

			if ( 'flex' === ( $layout['type'] ?? '' ) && 'vertical' === ( $layout['orientation'] ?? '' ) ) {
				++$stats['stack_groups'];
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			pns_split_alignment_count_copy_groups( $block['innerBlocks'], $stats );
		}
	}
}
