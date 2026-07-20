<?php
/**
 * Extend the private Split Section style guide with an alignment matrix.
 *
 * Usage:
 *   wp eval-file scripts/extend-split-section-text-alignment-matrix.php 5654
 *   wp eval-file scripts/extend-split-section-text-alignment-matrix.php 5654 apply
 *
 * Dry-run is the default. Apply makes the four existing uneven Text | Text
 * examples genuinely top / top, then appends the other eight ordered pairs in
 * both wide and constrained layouts. Media sections are compared byte-for-byte
 * before and after the transformation.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PNS_SPLIT_MATRIX_STYLE_GUIDE_ID = 5654;
const PNS_SPLIT_MATRIX_SOURCE_SHA256 = '098ac53edeeada48f9a2dcfe755f8037a5522f7a4eba5848e91d856870fb3bad';
const PNS_SPLIT_MATRIX_RESULT_SHA256 = '4003944350da628475f72dd17b85e89dcafb343d0e6ed92ea7e80129a8d92215';
const PNS_SPLIT_MATRIX_ANCHOR_PREFIX = 'pns-text-text-alignment-demo-';

$pns_split_matrix_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_split_matrix_id    = isset( $pns_split_matrix_args[0] ) ? (int) $pns_split_matrix_args[0] : 0;
$pns_split_matrix_apply = in_array( 'apply', $pns_split_matrix_args, true ) || in_array( '--apply', $pns_split_matrix_args, true );
$pns_split_matrix_page  = get_post( $pns_split_matrix_id );

if ( PNS_SPLIT_MATRIX_STYLE_GUIDE_ID !== $pns_split_matrix_id ) {
	WP_CLI::error( 'Pass the explicit Split Section style-guide page ID: 5654 [apply].' );
}

if (
	! $pns_split_matrix_page instanceof WP_Post
	|| 'page' !== $pns_split_matrix_page->post_type
	|| 'private' !== $pns_split_matrix_page->post_status
	|| 'pns-style-guide-split-section-components' !== $pns_split_matrix_page->post_name
	|| 'PNS Style Guide: Split Section Components' !== $pns_split_matrix_page->post_title
) {
	WP_CLI::error( 'Page 5654 is no longer the expected private Split Section style-guide page.' );
}

$pns_split_matrix_live_hash = hash( 'sha256', $pns_split_matrix_page->post_content );

if ( '' !== PNS_SPLIT_MATRIX_RESULT_SHA256 && PNS_SPLIT_MATRIX_RESULT_SHA256 === $pns_split_matrix_live_hash ) {
	$pns_split_matrix_saved_blocks = parse_blocks( $pns_split_matrix_page->post_content );
	$pns_split_matrix_saved_stats  = pns_split_matrix_analyse( $pns_split_matrix_saved_blocks );
	pns_split_matrix_assert_result( $pns_split_matrix_saved_stats );
	WP_CLI::success( 'The private style guide already contains the complete Text | Text alignment matrix.' );
	return;
}

if ( PNS_SPLIT_MATRIX_SOURCE_SHA256 !== $pns_split_matrix_live_hash ) {
	WP_CLI::error( 'The live style guide no longer matches the reviewed copy-alignment page; refusing to overwrite newer content.' );
}

if ( serialize_blocks( parse_blocks( $pns_split_matrix_page->post_content ) ) !== $pns_split_matrix_page->post_content ) {
	WP_CLI::error( 'The current style-guide content does not round-trip byte-for-byte.' );
}

$pns_split_matrix_lock = (string) get_post_meta( $pns_split_matrix_id, '_edit_lock', true );

if ( $pns_split_matrix_apply && '' !== $pns_split_matrix_lock ) {
	$pns_split_matrix_lock_parts = explode( ':', $pns_split_matrix_lock, 2 );
	$pns_split_matrix_lock_time  = isset( $pns_split_matrix_lock_parts[0] ) ? (int) $pns_split_matrix_lock_parts[0] : 0;

	if ( $pns_split_matrix_lock_time > time() - 150 ) {
		WP_CLI::error( 'Page 5654 has an active editor lock. Close the editor and retry after the lock expires.' );
	}
}

$pns_split_matrix_blocks       = parse_blocks( $pns_split_matrix_page->post_content );
$pns_split_matrix_before_stats = pns_split_matrix_analyse( $pns_split_matrix_blocks );
$pns_split_matrix_before_media = pns_split_matrix_serialized_media_sections( $pns_split_matrix_blocks );

if (
	20 !== $pns_split_matrix_before_stats['split_sections']
	|| 8 !== $pns_split_matrix_before_stats['text_sections']
	|| 12 !== $pns_split_matrix_before_stats['media_sections']
	|| 28 !== $pns_split_matrix_before_stats['copy_groups']
	|| 0 !== $pns_split_matrix_before_stats['stack_groups']
	|| 0 !== $pns_split_matrix_before_stats['media_alignment_attributes']
	|| 0 !== $pns_split_matrix_before_stats['matrix_sections']
) {
	WP_CLI::error( 'The source page no longer matches the reviewed 20-section alignment guide.' );
}

$pns_split_matrix_templates = array();
$pns_split_matrix_old_hits  = pns_split_matrix_prepare_existing_sections( $pns_split_matrix_blocks, $pns_split_matrix_templates );

if ( 4 !== $pns_split_matrix_old_hits || 4 !== count( $pns_split_matrix_templates ) ) {
	WP_CLI::error( 'Could not find the four existing uneven examples and four matrix templates exactly once.' );
}

$pns_split_matrix_pairs = array(
	array( 'top', 'center' ),
	array( 'top', 'bottom' ),
	array( 'center', 'top' ),
	array( 'center', 'center' ),
	array( 'center', 'bottom' ),
	array( 'bottom', 'top' ),
	array( 'bottom', 'center' ),
	array( 'bottom', 'bottom' ),
);

$pns_split_matrix_blocks[] = pns_split_matrix_heading_block(
	'pns-split-section-text-alignment-matrix',
	'Text | Text vertical alignment matrix',
	'Top / Top is covered by the preceding uneven-content examples. The following sections cover every other ordered combination of Top, Middle, and Bottom.'
);
$pns_split_matrix_blocks[] = pns_split_matrix_heading_block(
	'',
	'Wide / full edge alignment combinations',
	'Panel B is deliberately shorter so its selected position is immediately visible. Panel A uses the same saved alignment controls.'
);

foreach ( $pns_split_matrix_pairs as $pns_split_matrix_pair ) {
	$pns_split_matrix_blocks[] = pns_split_matrix_demo_block(
		$pns_split_matrix_templates['wide-long-short'],
		'wide',
		$pns_split_matrix_pair[0],
		$pns_split_matrix_pair[1]
	);
}

$pns_split_matrix_blocks[] = pns_split_matrix_heading_block(
	'',
	'Constrained alignment combinations',
	'Panel A is deliberately shorter here, so the opposite panel setting is visually exercised without adding demo-only layout CSS.'
);

foreach ( $pns_split_matrix_pairs as $pns_split_matrix_pair ) {
	$pns_split_matrix_blocks[] = pns_split_matrix_demo_block(
		$pns_split_matrix_templates['constrained-short-long'],
		'constrained',
		$pns_split_matrix_pair[0],
		$pns_split_matrix_pair[1]
	);
}

$pns_split_matrix_after_stats = pns_split_matrix_analyse( $pns_split_matrix_blocks );
$pns_split_matrix_after_media = pns_split_matrix_serialized_media_sections( $pns_split_matrix_blocks );

pns_split_matrix_assert_result( $pns_split_matrix_after_stats );

if ( $pns_split_matrix_before_media !== $pns_split_matrix_after_media ) {
	WP_CLI::error( 'At least one media Split Section changed while generating the Text | Text matrix.' );
}

$pns_split_matrix_new_content = serialize_blocks( $pns_split_matrix_blocks );
$pns_split_matrix_new_hash    = hash( 'sha256', $pns_split_matrix_new_content );

WP_CLI::log( 'Style-guide #5654 is ready for the complete Text | Text vertical-alignment matrix.' );
WP_CLI::log( 'Result: 24 Text | Text sections, 12 byte-identical media sections, 60 normal-flow copy Groups, and zero Stack Groups.' );
WP_CLI::log( 'Generated content SHA-256: ' . $pns_split_matrix_new_hash );

if ( ! $pns_split_matrix_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with 5654 apply to save the pinned result.' );
	return;
}

if ( '' === PNS_SPLIT_MATRIX_RESULT_SHA256 || PNS_SPLIT_MATRIX_RESULT_SHA256 !== $pns_split_matrix_new_hash ) {
	WP_CLI::error( 'The generated content does not match the pinned result hash; refusing to save.' );
}

$pns_split_matrix_project_root = dirname( get_stylesheet_directory(), 5 );
$pns_split_matrix_backup_dir   = $pns_split_matrix_project_root . '/docs/jobs/split-section-alignment-matrix-db-backups';

if ( ! is_dir( $pns_split_matrix_backup_dir ) && ! wp_mkdir_p( $pns_split_matrix_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_split_matrix_backup_dir ) );
}

$pns_split_matrix_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'post_id'           => $pns_split_matrix_id,
	'post_title'        => $pns_split_matrix_page->post_title,
	'post_name'         => $pns_split_matrix_page->post_name,
	'post_status'       => $pns_split_matrix_page->post_status,
	'post_modified_gmt' => $pns_split_matrix_page->post_modified_gmt,
	'wp_page_template'  => get_post_meta( $pns_split_matrix_id, '_wp_page_template', true ),
	'content_sha256'    => $pns_split_matrix_live_hash,
	'post_content'      => $pns_split_matrix_page->post_content,
);
$pns_split_matrix_backup_path = trailingslashit( $pns_split_matrix_backup_dir ) . gmdate( 'Ymd-His' ) . '-pre-alignment-matrix-5654.json';
$pns_split_matrix_backup_json = wp_json_encode( $pns_split_matrix_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$pns_split_matrix_temp_path   = $pns_split_matrix_backup_path . '.tmp';

if (
	! is_string( $pns_split_matrix_backup_json )
	|| false === file_put_contents( $pns_split_matrix_temp_path, $pns_split_matrix_backup_json . "\n" )
	|| ! rename( $pns_split_matrix_temp_path, $pns_split_matrix_backup_path )
) {
	WP_CLI::error( sprintf( 'Could not write rollback data: %s', $pns_split_matrix_backup_path ) );
}

$pns_split_matrix_updated_id = wp_update_post(
	array(
		'ID'           => $pns_split_matrix_id,
		'post_content' => wp_slash( $pns_split_matrix_new_content ),
	),
	true
);

if ( is_wp_error( $pns_split_matrix_updated_id ) ) {
	WP_CLI::error( $pns_split_matrix_updated_id->get_error_message() );
}

clean_post_cache( $pns_split_matrix_id );
$pns_split_matrix_saved = get_post( $pns_split_matrix_id );

if ( ! $pns_split_matrix_saved instanceof WP_Post || $pns_split_matrix_new_hash !== hash( 'sha256', $pns_split_matrix_saved->post_content ) ) {
	WP_CLI::error( 'WordPress saved content does not match the generated alignment matrix.' );
}

WP_CLI::success( sprintf( 'Updated the private style guide. Rollback export: %s', $pns_split_matrix_backup_path ) );

/**
 * Make the existing uneven examples true top / top and capture clone sources.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree, passed by reference.
 * @param array<string,array<string,mixed>> $templates Captured template blocks.
 * @return int Number of existing uneven examples updated.
 */
function pns_split_matrix_prepare_existing_sections( &$blocks, &$templates ) {
	$top_top_anchors = array(
		'pns-text-text-demo-text-text-long-short',
		'pns-text-text-demo-text-text-short-long',
		'pns-text-text-demo-text-text-constrained-long-short',
		'pns-text-text-demo-text-text-constrained-short-long',
	);
	$template_anchors = array(
		'pns-text-text-demo-text-text-long-short'             => 'wide-long-short',
		'pns-text-text-demo-text-text-short-long'             => 'wide-short-long',
		'pns-text-text-demo-text-text-constrained-long-short' => 'constrained-long-short',
		'pns-text-text-demo-text-text-constrained-short-long' => 'constrained-short-long',
	);
	$hits = 0;

	foreach ( $blocks as &$block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) ) {
			$anchor = (string) ( $block['attrs']['anchor'] ?? '' );

			if ( isset( $template_anchors[ $anchor ] ) ) {
				$templates[ $template_anchors[ $anchor ] ] = $block;
			}

			if ( in_array( $anchor, $top_top_anchors, true ) ) {
				$block['attrs']['textVerticalAlignment']          = 'top';
				$block['attrs']['secondaryTextVerticalAlignment'] = 'top';
				++$hits;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$hits += pns_split_matrix_prepare_existing_sections( $block['innerBlocks'], $templates );
		}
	}
	unset( $block );

	return $hits;
}

/**
 * Create one alignment demo from a reviewed Text | Text block.
 *
 * @param array<string,mixed> $template Source block.
 * @param string              $width Width family.
 * @param string              $primary First panel alignment.
 * @param string              $secondary Second panel alignment.
 * @return array<string,mixed>
 */
function pns_split_matrix_demo_block( $template, $width, $primary, $secondary ) {
	$block              = $template;
	$primary_slug       = sanitize_key( $primary );
	$secondary_slug     = sanitize_key( $secondary );
	$block['attrs']['anchor']        = PNS_SPLIT_MATRIX_ANCHOR_PREFIX . $width . '-' . $primary_slug . '-' . $secondary_slug;
	$block['attrs']['mediaType']     = 'text';
	$block['attrs']['layoutVariant'] = 'wide' === $width ? 'edge-media-right' : 'media-right';
	$block['attrs']['align']         = 'full';

	unset( $block['attrs']['textVerticalAlignment'], $block['attrs']['secondaryTextVerticalAlignment'] );

	if ( 'center' !== $primary ) {
		$block['attrs']['textVerticalAlignment'] = $primary;
	}

	if ( 'center' !== $secondary ) {
		$block['attrs']['secondaryTextVerticalAlignment'] = $secondary;
	}

	$label = ( 'wide' === $width ? 'Wide / full edge' : 'Constrained' )
		. ' alignment · '
		. pns_split_matrix_alignment_label( $primary )
		. ' / '
		. pns_split_matrix_alignment_label( $secondary );

	$columns = $block['innerBlocks'][0]['innerBlocks'] ?? array();

	if ( 2 !== count( $columns ) ) {
		WP_CLI::error( 'A Text | Text matrix template no longer contains exactly two columns.' );
	}

	if (
		! pns_split_matrix_replace_first_heading( $block['innerBlocks'][0]['innerBlocks'][0]['innerBlocks'], $label . ' · Panel A' )
		|| ! pns_split_matrix_replace_first_heading( $block['innerBlocks'][0]['innerBlocks'][1]['innerBlocks'], $label . ' · Panel B' )
	) {
		WP_CLI::error( 'Could not relabel both headings in an alignment matrix clone.' );
	}

	return $block;
}

/**
 * Replace the first heading in a descendant tree.
 *
 * @param array<int,array<string,mixed>> $blocks Descendants, passed by reference.
 * @param string                         $text Heading text.
 * @return bool
 */
function pns_split_matrix_replace_first_heading( &$blocks, $text ) {
	foreach ( $blocks as &$block ) {
		if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
			$html                  = '<h3 class="wp-block-heading">' . esc_html( $text ) . '</h3>';
			$block['attrs']['level'] = 3;
			$block['innerHTML']      = $html;
			$block['innerContent']   = array( $html );
			unset( $block );
			return true;
		}

		if ( ! empty( $block['innerBlocks'] ) && pns_split_matrix_replace_first_heading( $block['innerBlocks'], $text ) ) {
			unset( $block );
			return true;
		}
	}
	unset( $block );

	return false;
}

/**
 * Create a constrained heading block for the demo matrix.
 *
 * @param string $anchor Optional anchor.
 * @param string $heading Heading text.
 * @param string $copy Supporting copy.
 * @return array<string,mixed>
 */
function pns_split_matrix_heading_block( $anchor, $heading, $copy ) {
	$attributes = array(
		'className' => 'pns-split-section-text-demo-width-heading',
		'layout'    => array( 'type' => 'constrained' ),
	);

	if ( '' !== $anchor ) {
		$attributes['anchor'] = $anchor;
	}

	$id_attribute = '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '';
	$content      = '<!-- wp:group ' . wp_json_encode( $attributes ) . ' -->'
		. '<div' . $id_attribute . ' class="wp-block-group pns-split-section-text-demo-width-heading">'
		. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>' . esc_html( $copy ) . '</p><!-- /wp:paragraph -->'
		. '</div><!-- /wp:group -->';
	$parsed       = parse_blocks( $content );

	if ( 1 !== count( $parsed ) || 'core/group' !== ( $parsed[0]['blockName'] ?? '' ) ) {
		WP_CLI::error( 'Could not create an alignment matrix heading block.' );
	}

	return $parsed[0];
}

/**
 * Analyse the Split Section contract and alignment pair coverage.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree.
 * @return array<string,mixed>
 */
function pns_split_matrix_analyse( $blocks ) {
	$stats = array(
		'split_sections'             => 0,
		'text_sections'              => 0,
		'media_sections'             => 0,
		'copy_groups'                => 0,
		'stack_groups'               => 0,
		'alignment_attributes'       => 0,
		'media_alignment_attributes' => 0,
		'matrix_sections'            => 0,
		'matrix_pairs'               => array( 'wide' => array(), 'constrained' => array() ),
		'old_top_top'                => 0,
		'text_layout_variants'       => array(),
	);

	pns_split_matrix_walk_for_stats( $blocks, $stats );
	sort( $stats['matrix_pairs']['wide'] );
	sort( $stats['matrix_pairs']['constrained'] );

	return $stats;
}

/**
 * Walk Split Section roots and collect statistics.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree.
 * @param array<string,mixed>            $stats Mutable statistics.
 */
function pns_split_matrix_walk_for_stats( $blocks, &$stats ) {
	$old_top_top_anchors = array(
		'pns-text-text-demo-text-text-long-short',
		'pns-text-text-demo-text-text-short-long',
		'pns-text-text-demo-text-text-constrained-long-short',
		'pns-text-text-demo-text-text-constrained-short-long',
	);

	foreach ( $blocks as $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) ) {
			++$stats['split_sections'];
			$media_type     = (string) ( $block['attrs']['mediaType'] ?? '' );
			$layout_variant = (string) ( $block['attrs']['layoutVariant'] ?? '' );
			$is_text_text    = 'text' === $media_type;
			$anchor          = (string) ( $block['attrs']['anchor'] ?? '' );
			$has_primary     = array_key_exists( 'textVerticalAlignment', $block['attrs'] );
			$has_secondary   = array_key_exists( 'secondaryTextVerticalAlignment', $block['attrs'] );

			$stats['alignment_attributes'] += $has_primary ? 1 : 0;
			$stats['alignment_attributes'] += $has_secondary ? 1 : 0;

			if ( $is_text_text ) {
				++$stats['text_sections'];
				$stats['text_layout_variants'][ $layout_variant ] = ( $stats['text_layout_variants'][ $layout_variant ] ?? 0 ) + 1;
				$primary   = (string) ( $block['attrs']['textVerticalAlignment'] ?? 'center' );
				$secondary = (string) ( $block['attrs']['secondaryTextVerticalAlignment'] ?? 'center' );

				if ( in_array( $anchor, $old_top_top_anchors, true ) && 'top' === $primary && 'top' === $secondary ) {
					++$stats['old_top_top'];
				}

				if ( str_starts_with( $anchor, PNS_SPLIT_MATRIX_ANCHOR_PREFIX ) ) {
					++$stats['matrix_sections'];
					$width = str_starts_with( $anchor, PNS_SPLIT_MATRIX_ANCHOR_PREFIX . 'wide-' ) ? 'wide' : 'constrained';
					$stats['matrix_pairs'][ $width ][] = $primary . '/' . $secondary;
				}
			} else {
				++$stats['media_sections'];
				$stats['media_alignment_attributes'] += $has_primary ? 1 : 0;
				$stats['media_alignment_attributes'] += $has_secondary ? 1 : 0;
			}

			pns_split_matrix_count_copy_groups( $block['innerBlocks'], $stats );
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			pns_split_matrix_walk_for_stats( $block['innerBlocks'], $stats );
		}
	}
}

/**
 * Count copy Groups and guard against the failed Stack experiment.
 *
 * @param array<int,array<string,mixed>> $blocks Descendants.
 * @param array<string,mixed>            $stats Mutable statistics.
 */
function pns_split_matrix_count_copy_groups( $blocks, &$stats ) {
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
			pns_split_matrix_count_copy_groups( $block['innerBlocks'], $stats );
		}
	}
}

/**
 * Return serialized media Split Sections in document order.
 *
 * @param array<int,array<string,mixed>> $blocks Block tree.
 * @return array<int,string>
 */
function pns_split_matrix_serialized_media_sections( $blocks ) {
	$media = array();

	foreach ( $blocks as $block ) {
		if ( 'pns/split-section' === ( $block['blockName'] ?? '' ) && 'text' !== ( $block['attrs']['mediaType'] ?? '' ) ) {
			$media[] = serialize_block( $block );
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$media = array_merge( $media, pns_split_matrix_serialized_media_sections( $block['innerBlocks'] ) );
		}
	}

	return $media;
}

/**
 * Assert the complete saved result contract.
 *
 * @param array<string,mixed> $stats Style-guide statistics.
 */
function pns_split_matrix_assert_result( $stats ) {
	$expected_pairs = array(
		'bottom/bottom',
		'bottom/center',
		'bottom/top',
		'center/bottom',
		'center/center',
		'center/top',
		'top/bottom',
		'top/center',
	);

	if (
		36 !== $stats['split_sections']
		|| 24 !== $stats['text_sections']
		|| 12 !== $stats['media_sections']
		|| 60 !== $stats['copy_groups']
		|| 0 !== $stats['stack_groups']
		|| 28 !== $stats['alignment_attributes']
		|| 0 !== $stats['media_alignment_attributes']
		|| 16 !== $stats['matrix_sections']
		|| 4 !== $stats['old_top_top']
		|| $expected_pairs !== $stats['matrix_pairs']['wide']
		|| $expected_pairs !== $stats['matrix_pairs']['constrained']
		|| 1 !== ( $stats['text_layout_variants']['edge-media-left'] ?? 0 )
		|| 11 !== ( $stats['text_layout_variants']['edge-media-right'] ?? 0 )
		|| 12 !== ( $stats['text_layout_variants']['media-right'] ?? 0 )
	) {
		WP_CLI::log( wp_json_encode( $stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		WP_CLI::error( 'The generated style guide failed its complete Text | Text alignment-matrix contract.' );
	}
}

/**
 * Convert the stored center value to the editorial label Middle.
 *
 * @param string $alignment Stored alignment value.
 * @return string
 */
function pns_split_matrix_alignment_label( $alignment ) {
	return 'center' === $alignment ? 'Middle' : ucfirst( $alignment );
}
