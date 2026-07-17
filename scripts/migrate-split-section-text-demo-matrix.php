<?php
/**
 * Append the Text | Text Split Section demo matrix to the private style guide.
 *
 * Usage:
 *   wp eval-file scripts/migrate-split-section-text-demo-matrix.php 5654
 *   wp eval-file scripts/migrate-split-section-text-demo-matrix.php 5654 apply
 *
 * Dry-run is the default. Apply writes a complete JSON rollback export before
 * appending the matrix. Existing page content is preserved byte-for-byte.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_split_demo_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_split_demo_id    = isset( $pns_split_demo_args[0] ) ? (int) $pns_split_demo_args[0] : 0;
$pns_split_demo_apply = in_array( 'apply', $pns_split_demo_args, true ) || in_array( '--apply', $pns_split_demo_args, true );
$pns_split_demo_page  = get_post( $pns_split_demo_id );

if ( 5654 !== $pns_split_demo_id ) {
	WP_CLI::error( 'Pass the explicit Split Section style-guide page ID: 5654 [apply].' );
}

if (
	! $pns_split_demo_page instanceof WP_Post
	|| 'page' !== $pns_split_demo_page->post_type
	|| 'private' !== $pns_split_demo_page->post_status
	|| 'pns-style-guide-split-section-components' !== $pns_split_demo_page->post_name
	|| 'PNS Style Guide: Split Section Components' !== $pns_split_demo_page->post_title
) {
	WP_CLI::error( 'Page 5654 is no longer the expected private Split Section style-guide page.' );
}

$pns_split_demo_marker       = '<!-- pns-split-section-text-demo-matrix -->';
$pns_split_demo_item_marker  = '"anchor":"pns-text-text-demo-';
$pns_split_demo_marker_count = substr_count( $pns_split_demo_page->post_content, $pns_split_demo_marker );
$pns_split_demo_item_count   = substr_count( $pns_split_demo_page->post_content, $pns_split_demo_item_marker );

if ( 1 === $pns_split_demo_marker_count && 8 === $pns_split_demo_item_count ) {
	WP_CLI::success( 'The complete Text | Text demo matrix is already present; nothing to append.' );
	return;
}

if ( 0 !== $pns_split_demo_marker_count || 0 !== $pns_split_demo_item_count ) {
	WP_CLI::error(
		sprintf(
			'Found an incomplete demo matrix (%d intro marker, %d demo markers); refusing to append.',
			$pns_split_demo_marker_count,
			$pns_split_demo_item_count
		)
	);
}

$pns_split_demo_expected_sha = 'd3d926f65c44501bf3c0bec3960d293275b1658fdbb88ca1c880f2144c7628fd';
$pns_split_demo_current_sha  = hash( 'sha256', $pns_split_demo_page->post_content );

if ( $pns_split_demo_expected_sha !== $pns_split_demo_current_sha ) {
	WP_CLI::error(
		sprintf(
			'Style-guide content changed: expected SHA-256 %s, found %s.',
			$pns_split_demo_expected_sha,
			$pns_split_demo_current_sha
		)
	);
}

$pns_split_demo_matrix = pns_split_text_demo_matrix_markup();
$pns_split_demo_blocks = parse_blocks( $pns_split_demo_matrix );
$pns_split_demo_splits = array_values(
	array_filter(
		$pns_split_demo_blocks,
		static function ( $block ) {
			return 'pns/split-section' === ( $block['blockName'] ?? '' );
		}
	)
);

if ( 8 !== count( $pns_split_demo_splits ) ) {
	WP_CLI::error( sprintf( 'Generated demo matrix is invalid: expected 8 Split Sections, found %d.', count( $pns_split_demo_splits ) ) );
}

$pns_split_demo_layout_counts = array(
	'edge-media-left'  => 0,
	'edge-media-right' => 0,
	'media-right'      => 0,
);

foreach ( $pns_split_demo_splits as $pns_split_demo_split ) {
	$pns_split_demo_layout = $pns_split_demo_split['attrs']['layoutVariant'] ?? '';
	$pns_split_demo_type   = $pns_split_demo_split['attrs']['mediaType'] ?? '';

	if ( 'text' !== $pns_split_demo_type || ! array_key_exists( $pns_split_demo_layout, $pns_split_demo_layout_counts ) ) {
		WP_CLI::error( sprintf( 'Generated demo matrix contains unexpected type/layout "%s" / "%s".', $pns_split_demo_type, $pns_split_demo_layout ) );
	}

	++$pns_split_demo_layout_counts[ $pns_split_demo_layout ];
}

if (
	1 !== $pns_split_demo_layout_counts['edge-media-left']
	|| 3 !== $pns_split_demo_layout_counts['edge-media-right']
	|| 4 !== $pns_split_demo_layout_counts['media-right']
) {
	WP_CLI::error( 'Generated demo matrix does not contain the expected wide, constrained, and flipped examples.' );
}

WP_CLI::log( 'Style-guide #5654: 8 Text | Text Split Section demos are ready to append.' );
WP_CLI::log( 'Matrix: full edge and constrained, each with short/short, long/long, long/short, and short/long panels.' );

if ( ! $pns_split_demo_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with 5654 apply to append the matrix.' );
	return;
}

$pns_split_demo_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/split-section-text-demo-backups';

if ( ! is_dir( $pns_split_demo_backup_dir ) && ! wp_mkdir_p( $pns_split_demo_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_split_demo_backup_dir ) );
}

$pns_split_demo_backup = array(
	'schema_version'    => 1,
	'created_at_gmt'    => gmdate( 'c' ),
	'post_id'           => $pns_split_demo_id,
	'post_title'        => $pns_split_demo_page->post_title,
	'post_name'         => $pns_split_demo_page->post_name,
	'post_status'       => $pns_split_demo_page->post_status,
	'post_modified_gmt' => $pns_split_demo_page->post_modified_gmt,
	'wp_page_template'  => get_post_meta( $pns_split_demo_id, '_wp_page_template', true ),
	'content_sha256'    => $pns_split_demo_current_sha,
	'post_content'      => $pns_split_demo_page->post_content,
);
$pns_split_demo_backup_path = trailingslashit( $pns_split_demo_backup_dir ) . gmdate( 'Ymd-His' ) . '-split-section-text-demo-5654.json';

if ( false === file_put_contents( $pns_split_demo_backup_path, wp_json_encode( $pns_split_demo_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_split_demo_backup_path ) );
}

$pns_split_demo_new_content = $pns_split_demo_page->post_content . "\n\n" . $pns_split_demo_matrix;

if ( 0 !== strpos( $pns_split_demo_new_content, $pns_split_demo_page->post_content ) ) {
	WP_CLI::error( 'Generated content does not preserve the existing page content as an exact prefix.' );
}

$pns_split_demo_updated_id = wp_update_post(
	array(
		'ID'           => $pns_split_demo_id,
		'post_content' => wp_slash( $pns_split_demo_new_content ),
	),
	true
);

if ( is_wp_error( $pns_split_demo_updated_id ) ) {
	WP_CLI::error( $pns_split_demo_updated_id->get_error_message() );
}

clean_post_cache( $pns_split_demo_id );
WP_CLI::success( sprintf( 'Appended the 8-example Text | Text demo matrix. Rollback export: %s', $pns_split_demo_backup_path ) );

/**
 * Build the complete demo matrix as serialized Gutenberg block markup.
 *
 * @return string
 */
function pns_split_text_demo_matrix_markup() {
	$markup = '<!-- pns-split-section-text-demo-matrix -->'
		. '<!-- wp:group {"anchor":"pns-split-section-text-demo-matrix","className":"pns-split-section-text-demo-matrix","layout":{"type":"constrained"}} -->'
		. '<div id="pns-split-section-text-demo-matrix" class="wp-block-group pns-split-section-text-demo-matrix">'
		. '<!-- wp:heading --><h2 class="wp-block-heading">Text | Text Split Section test matrix</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>These examples exercise both width settings with balanced and deliberately uneven amounts of editorial content. Each panel includes an H3 heading and a button.</p><!-- /wp:paragraph -->'
		. '</div><!-- /wp:group -->';

	$variants = array(
		'text-text'             => 'Wide / full edge',
		'text-text-constrained' => 'Constrained',
	);
	$scenarios = array(
		'short-short' => array( 'short', 'short' ),
		'long-long'   => array( 'long', 'long' ),
		'long-short'  => array( 'long', 'short' ),
		'short-long'  => array( 'short', 'long' ),
	);

	foreach ( $variants as $variant => $variant_label ) {
		$markup .= "\n\n" . '<!-- wp:group {"className":"pns-split-section-text-demo-width-heading","layout":{"type":"constrained"}} -->'
			. '<div class="wp-block-group pns-split-section-text-demo-width-heading">'
			. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $variant_label ) . ' Text | Text</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>Four content-balance checks: short / short, long / long, long / short, and short / long.</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:group -->';

		foreach ( $scenarios as $scenario => $panel_lengths ) {
			$scenario_label = ucwords( str_replace( '-', ' / ', $scenario ) );
			$markup        .= "\n\n" . pns_split_text_demo_section_markup( $variant, $variant_label, $scenario, $scenario_label, $panel_lengths );
		}
	}

	return $markup;
}

/**
 * Build one Text | Text Split Section example.
 *
 * @param string            $variant       Layout variant slug.
 * @param string            $variant_label Human-readable layout label.
 * @param string            $scenario      Content-length scenario slug.
 * @param string            $scenario_label Human-readable scenario label.
 * @param array<int,string> $panel_lengths Content length for each panel.
 * @return string
 */
function pns_split_text_demo_section_markup( $variant, $variant_label, $scenario, $scenario_label, $panel_lengths ) {
	$anchor      = 'pns-text-text-demo-' . $variant . '-' . $scenario;
	$is_reversed = 'text-text' === $variant && 'long-short' === $scenario;
	$attributes  = array(
		'mediaType'     => 'text',
		'layoutVariant' => 'text-text' === $variant ? 'edge-media-right' : 'media-right',
		'align'         => 'full',
		'anchor'        => $anchor,
	);

	if ( $is_reversed ) {
		$attributes['layoutVariant'] = 'edge-media-left';
		$scenario_label             .= ' · Edge media left';
	}
	$columns    = pns_split_text_demo_panel_markup( 'brand-purple', $variant_label, $scenario_label, 'Panel A', $panel_lengths[0] )
		. pns_split_text_demo_panel_markup( 'heritage-green', $variant_label, $scenario_label, 'Panel B', $panel_lengths[1] );

	return '<!-- wp:pns/split-section ' . wp_json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' -->'
		. '<!-- wp:columns {"align":"full","className":"pns-split-section__columns"} -->'
		. '<div class="wp-block-columns alignfull pns-split-section__columns">'
		. $columns
		. '</div><!-- /wp:columns -->'
		. '<!-- /wp:pns/split-section -->';
}

/**
 * Build one coloured text panel.
 *
 * @param string $background    Theme colour slug.
 * @param string $variant_label Human-readable layout label.
 * @param string $scenario_label Human-readable scenario label.
 * @param string $panel_label   Panel label.
 * @param string $length        Either short or long.
 * @return string
 */
function pns_split_text_demo_panel_markup( $background, $variant_label, $scenario_label, $panel_label, $length ) {
	$heading = esc_html( $variant_label . ' · ' . $scenario_label . ' · ' . $panel_label );
	$content = '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . $heading . '</h3><!-- /wp:heading -->';

	if ( 'long' === $length ) {
		$content .= '<!-- wp:paragraph {"fontSize":"text-lead"} --><p class="has-text-lead-font-size">Long-form lead copy tests how the panel establishes hierarchy when both its heading and opening statement wrap across several lines.</p><!-- /wp:paragraph -->'
			. '<!-- wp:paragraph --><p>This deliberately generous example continues with multiple paragraphs. It gives editors a realistic view of rhythm, line length, internal spacing, and how the neighbouring panel behaves when the two sides contain different amounts of material.</p><!-- /wp:paragraph -->'
			. '<!-- wp:paragraph --><p>Links remain <a href="#pns-split-section-text-demo-matrix">visible within the colour panel</a>, while ordinary prose should stay readable from the widest desktop frame down to the single-column mobile layout.</p><!-- /wp:paragraph -->'
			. '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>First editorial point with enough words to wrap naturally.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Second point checks list indentation and vertical rhythm.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Third point confirms the list inherits the panel text colour.</li><!-- /wp:list-item --></ul><!-- /wp:list -->'
			. '<!-- wp:paragraph --><p>A final paragraph creates a substantial closing passage before the call to action, making any unwanted equal-height or bottom-alignment behaviour easy to spot.</p><!-- /wp:paragraph -->';
	} else {
		$content .= '<!-- wp:paragraph --><p>Brief supporting copy tests the panel at its smallest useful editorial size.</p><!-- /wp:paragraph -->';
	}

	$content     .= '<!-- wp:buttons {"className":"pns-split-section__cta"} --><div class="wp-block-buttons pns-split-section__cta"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#pns-split-section-text-demo-matrix">Review this panel</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';
	$column_attrs = array(
		'backgroundColor' => $background,
		'textColor'       => 'neutral-0',
		'className'       => 'pns-split-section__copy-column pns-split-section__text-column',
	);

	return '<!-- wp:column ' . wp_json_encode( $column_attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' -->'
		. '<div class="wp-block-column pns-split-section__copy-column pns-split-section__text-column has-neutral-0-color has-' . esc_attr( $background ) . '-background-color has-text-color has-background">'
		. '<!-- wp:group {"className":"pns-split-section__copy"} --><div class="wp-block-group pns-split-section__copy">'
		. $content
		. '</div><!-- /wp:group -->'
		. '</div><!-- /wp:column -->';
}
