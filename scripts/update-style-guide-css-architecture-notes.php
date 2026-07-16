<?php
/**
 * Replace the DB-backed style guide CSS-only candidate section with developer notes.
 *
 * Usage:
 *   wp eval-file scripts/update-style-guide-css-architecture-notes.php
 *   wp eval-file scripts/update-style-guide-css-architecture-notes.php apply
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$arguments  = isset( $args ) && is_array( $args ) ? $args : array();
$apply      = in_array( 'apply', $arguments, true ) || in_array( '--apply', $arguments, true );
$post_id    = 6340;
$backup_dir = dirname( ABSPATH, 2 ) . '/docs/jobs/style-guide-db-backups';

$new_section = <<<'HTML'
<!-- wp:group {"className":"pns-content-frame","style":{"spacing":{"blockGap":"var:preset|spacing|spacious"}}} -->
<div class="wp-block-group pns-content-frame"><!-- wp:heading -->
<h2 class="wp-block-heading">CSS-Only Architecture Notes</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Developer notes for values that intentionally remain in CSS rather than <code>theme.json</code>. This is not a backlog. It records ownership decisions, compatibility boundaries, and the conditions that would justify revisiting a private CSS contract.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>As a compact inventory, authored CSS still defines a substantial set of private custom properties for navigation, surface adapters, section bridges, vendor overrides, component geometry, form fields, buttons, rhythm, layout, and motion. Counts are useful for orientation, but rendered behavior and ownership are the deciding factors.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Public Token Decisions</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Editor-facing design choices belong in <code>theme.json</code>:</strong> color palette, neutral ladder, gradients, duotones, spacing, font sizes, font families, and core line-height roles should stay visible through normal block controls when editors are expected to choose them.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Shared site UI belongs in reusable primitives:</strong> buttons, native form controls, disabled states, outline states, focus rings, and surface-aware button colors are implemented as shared contracts rather than one-off component CSS.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Surface roles bridge public tokens to real layouts:</strong> components consume local variables such as <code>--pns-surface-*</code> and <code>--pns-section-*</code> so generic CSS does not keep inventing brand-color aliases.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Private CSS Contracts</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Navigation metrics:</strong> the core Navigation drawer and desktop menu are split by domain, but still use many private spacing, offset, marker, and breakpoint values. Further simplification is welcome when rendered tests prove a value is redundant without weakening the <code>999px</code>/<code>1000px</code> drawer and desktop contract.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Content rhythm internals:</strong> baseline paragraph rhythm, list gaps, quote rhythm, compact display line-height, and WordPress generated-flow bridges are CSS contracts. Change them only with frontend and editor evidence because they affect composed block content from multiple sources.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Layout frame helpers:</strong> <code>contentSize</code> and <code>wideSize</code> are the WordPress-native public layout knobs. PNS wrapper helpers remain private where they express footer, shop, split-section, or section-frame geometry rather than an editor choice.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Vendor adapters:</strong> Ecwid, EmailOctopus, Jetpack, and plugin-generated markup often need scoped CSS values that should not become theme tokens unless the same value becomes a cross-site product decision.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Motion and component geometry:</strong> global motion aliases, header logo sizing, banner geometry, pagination surfaces, and single-post header spacing are private implementation details until a designer-facing control is genuinely useful.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Compatibility And Known Debt</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Navigation complexity:</strong> the drawer migration reduced custom markup and JavaScript, but the CSS still encodes a detailed responsive contract. Treat that as known complexity, not accidental dead weight.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Generated WordPress flow:</strong> some rules exist because block editor output, saved templates, and generated layout classes do not provide stable semantic hooks. Prefer explicit component hooks when they exist; keep generated-flow safeguards when they are the only reliable boundary.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Review Triggers</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Promote a CSS value to <code>theme.json</code></strong> when it represents an editor-facing design choice, a repeated cross-surface semantic role, or a product-level spacing/type/color decision.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Keep a value private</strong> when it is component geometry, plugin markup adaptation, generated-flow protection, or compatibility behavior that editors should not choose directly.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Revisit a private contract</strong> when rendered tests show unused values, plugin markup changes, WordPress core output changes, or a repeated private value starts acting like a public design role.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->
HTML;

$post = get_post( $post_id );

if ( ! $post ) {
	WP_CLI::error( sprintf( 'Style guide post %d not found.', $post_id ) );
}

$replacement_blocks = parse_blocks( $new_section );

if ( 1 !== count( $replacement_blocks ) ) {
	WP_CLI::error( 'Replacement section did not parse to exactly one block.' );
}

/**
 * Replace the pns-content-frame group that owns the CSS-only notes section.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<string,mixed>            $replacement Replacement block.
 * @param bool                           $replaced Whether a replacement occurred.
 * @return array<int,array<string,mixed>>
 */
function pns_replace_style_guide_css_notes( array $blocks, array $replacement, bool &$replaced ): array {
	foreach ( $blocks as &$block ) {
		$class_name = isset( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] )
			? $block['attrs']['className']
			: '';

		if (
			! $replaced
			&& 'core/group' === ( $block['blockName'] ?? '' )
			&& str_contains( $class_name, 'pns-content-frame' )
			&& (
				str_contains( serialize_block( $block ), 'CSS-Only Candidates' )
				|| str_contains( serialize_block( $block ), 'CSS-Only Architecture Notes' )
			)
		) {
			$block    = $replacement;
			$replaced = true;
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_replace_style_guide_css_notes(
				$block['innerBlocks'],
				$replacement,
				$replaced
			);
		}
	}

	return $blocks;
}

$replaced = false;
$blocks   = parse_blocks( $post->post_content );
$updated  = serialize_blocks(
	pns_replace_style_guide_css_notes(
		$blocks,
		$replacement_blocks[0],
		$replaced
	)
);

if ( ! $replaced ) {
	WP_CLI::error( 'Could not find CSS-only style guide section to replace.' );
}

if ( $updated === $post->post_content ) {
	WP_CLI::success( 'CSS-only architecture notes are already current.' );
	return;
}

WP_CLI::log( 'Will replace CSS-Only Candidates with CSS-Only Architecture Notes.' );

if ( ! $apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write rollback export and update the style guide.' );
	return;
}

if ( ! is_dir( $backup_dir ) && ! wp_mkdir_p( $backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $backup_dir ) );
}

$backup_path = trailingslashit( $backup_dir ) . gmdate( 'Ymd-His' ) . '-css-architecture-notes.json';
file_put_contents(
	$backup_path,
	wp_json_encode(
		array(
			'created_at_gmt' => gmdate( 'c' ),
			'ID'             => $post_id,
			'post_title'     => $post->post_title,
			'post_content'   => $post->post_content,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	)
);

wp_update_post(
	array(
		'ID'           => $post_id,
		'post_content' => $updated,
	)
);

WP_CLI::success( sprintf( 'Updated CSS-only architecture notes. Rollback export: %s', $backup_path ) );
