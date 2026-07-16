<?php
/**
 * Add live card and taxonomy-pill component examples to the private Style Guide.
 *
 * Usage:
 *   wp eval-file scripts/add-style-guide-component-examples.php
 *   wp eval-file scripts/add-style-guide-component-examples.php -- apply
 *
 * The Style Guide previews the real hidden card patterns inside Query Loops.
 * This deliberately uses live editorial data so its markup cannot diverge from
 * the production archive and search card contracts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_style_guide_component_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_style_guide_component_apply = in_array( 'apply', $pns_style_guide_component_args, true ) || in_array( '--apply', $pns_style_guide_component_args, true );
$pns_style_guide_component_page  = get_post( 6340 );

if ( ! $pns_style_guide_component_page || 'page' !== $pns_style_guide_component_page->post_type ) {
	WP_CLI::error( 'Style Guide component examples require page #6340.' );
}

if ( 'page-light-surface-wide-content' !== get_page_template_slug( 6340 ) ) {
	WP_CLI::error( 'Style Guide component examples stopped because page #6340 no longer uses the light-surface wide-content template.' );
}

$pns_style_guide_component_markup = <<<'BLOCKS'
<!-- wp:group {"align":"full","className":"pns-section pns-text-only-section pns-style-guide-component-examples has-neutral-0-background-color has-background","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","right":"var:preset|spacing|regular","bottom":"var:preset|spacing|section","left":"var:preset|spacing|regular"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-group alignfull pns-section pns-text-only-section pns-style-guide-component-examples has-neutral-0-background-color has-background" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--section);padding-right:var(--wp--preset--spacing--regular);padding-bottom:var(--wp--preset--spacing--section);padding-left:var(--wp--preset--spacing--regular)"><!-- wp:group {"className":"pns-section-frame","style":{"spacing":{"blockGap":"var:preset|spacing|spacious"}}} -->
<div class="wp-block-group pns-section-frame"><!-- wp:heading -->
<h2 class="wp-block-heading">Component Patterns</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Live previews of the reusable post-card variants. These Query Loops render the same hidden patterns used by production archive and search templates, so structural card changes are visible here automatically.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vertical Post Card</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The <code>pns/post-card</code> pattern is the shared archive-card contract: media, title, metadata, excerpt, and taxonomy-pill footer.</p>
<!-- /wp:paragraph -->

<!-- wp:query {"queryId":63401,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"pns-style-guide-card-preview pns-style-guide-card-preview--vertical","layout":{"type":"default"}} -->
<div class="wp-block-query pns-style-guide-card-preview pns-style-guide-card-preview--vertical"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|section","padding":{"right":"0px","left":"0px"}}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"12rem"}} -->
<!-- wp:pattern {"slug":"pns/post-card"} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Published posts will appear here when available.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Horizontal Post Card</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The <code>pns/post-card-horizontal</code> pattern is the shared result-row variant used by archive and search templates.</p>
<!-- /wp:paragraph -->

<!-- wp:query {"queryId":63402,"query":{"perPage":2,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"pns-style-guide-card-preview pns-style-guide-card-preview--horizontal pns-search-results","layout":{"type":"default"}} -->
<div class="wp-block-query pns-style-guide-card-preview pns-style-guide-card-preview--horizontal pns-search-results"><!-- wp:post-template {"className":"pns-search-results__list","layout":{"type":"default"}} -->
<!-- wp:pattern {"slug":"pns/post-card-horizontal"} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Published posts will appear here when available.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

BLOCKS;

define( 'PNS_STYLE_GUIDE_COMPONENT_EXAMPLES_MARKUP', $pns_style_guide_component_markup );

/**
 * Insert or replace the component sample before the Style Guide's architecture notes.
 *
 * @param string $content Current saved block content.
 * @return string|WP_Error Updated content or an error explaining why the page drifted.
 */
function pns_add_style_guide_component_examples( $content ) {
	$marker               = 'pns-style-guide-component-examples';
	$heading              = '<h2 class="wp-block-heading">CSS-Only Architecture Notes</h2>';
	$section_group_marker = '<!-- wp:group {"align":"full","className":"pns-section';
	$marker_position      = strpos( $content, $marker );

	if ( false !== $marker_position ) {
		$section_start = strrpos( substr( $content, 0, $marker_position ), '<!-- wp:group ' );
		$section_end   = strpos( $content, $section_group_marker, $marker_position );

		if ( false === $section_start || false === $section_end ) {
			return new WP_Error( 'component_section_boundary_not_found', 'Style Guide component-example section boundaries were not found.' );
		}

		$existing_section = substr( $content, $section_start, $section_end - $section_start );

		if ( PNS_STYLE_GUIDE_COMPONENT_EXAMPLES_MARKUP === $existing_section ) {
			return $content;
		}

		return substr( $content, 0, $section_start ) . PNS_STYLE_GUIDE_COMPONENT_EXAMPLES_MARKUP . substr( $content, $section_end );
	}

	$heading_position = strpos( $content, $heading );

	if ( false === $heading_position ) {
		return new WP_Error( 'architecture_notes_not_found', 'Style Guide architecture notes anchor was not found.' );
	}

	$prefix         = substr( $content, 0, $heading_position );
	$section_offset = strrpos( $prefix, $section_group_marker );

	if ( false === $section_offset ) {
		return new WP_Error( 'architecture_section_not_found', 'Style Guide architecture notes section boundary was not found.' );
	}

	return substr( $content, 0, $section_offset ) . PNS_STYLE_GUIDE_COMPONENT_EXAMPLES_MARKUP . substr( $content, $section_offset );
}

$pns_style_guide_component_posts = array( $pns_style_guide_component_page );
$pns_style_guide_component_revs  = get_children(
	array(
		'post_parent' => 6340,
		'post_type'   => 'revision',
		'numberposts' => -1,
		'orderby'     => 'modified',
		'order'       => 'DESC',
	)
);

foreach ( $pns_style_guide_component_revs as $pns_style_guide_component_revision ) {
	if ( wp_is_post_autosave( $pns_style_guide_component_revision ) ) {
		$pns_style_guide_component_posts[] = $pns_style_guide_component_revision;
	}
}

$pns_style_guide_component_updates = array();

foreach ( $pns_style_guide_component_posts as $pns_style_guide_component_post ) {
	$pns_style_guide_component_content = pns_add_style_guide_component_examples( $pns_style_guide_component_post->post_content );

	if ( is_wp_error( $pns_style_guide_component_content ) ) {
		WP_CLI::error( sprintf( 'Post #%d: %s', $pns_style_guide_component_post->ID, $pns_style_guide_component_content->get_error_message() ) );
	}

	if ( $pns_style_guide_component_content === $pns_style_guide_component_post->post_content ) {
		WP_CLI::log( sprintf( 'Post #%d already contains the component examples.', $pns_style_guide_component_post->ID ) );
		continue;
	}

	$pns_style_guide_component_updates[] = array(
		'post'    => $pns_style_guide_component_post,
		'content' => $pns_style_guide_component_content,
	);
	WP_CLI::log( sprintf( 'Post #%d will receive the Style Guide card and pill examples.', $pns_style_guide_component_post->ID ) );
}

if ( ! $pns_style_guide_component_updates ) {
	WP_CLI::success( 'Style Guide component examples are already synchronised.' );
	return;
}

if ( ! $pns_style_guide_component_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write rollback exports and update the Style Guide.' );
	return;
}

$pns_style_guide_component_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/style-guide-component-examples-db-backups';

if ( ! is_dir( $pns_style_guide_component_backup_dir ) && ! wp_mkdir_p( $pns_style_guide_component_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create rollback directory: %s', $pns_style_guide_component_backup_dir ) );
}

foreach ( $pns_style_guide_component_updates as $pns_style_guide_component_update ) {
	$pns_style_guide_component_post = $pns_style_guide_component_update['post'];
	$pns_style_guide_component_path = sprintf(
		'%s/%s-post-%d-before-component-examples.json',
		$pns_style_guide_component_backup_dir,
		gmdate( 'Ymd\\THis\\Z' ),
		$pns_style_guide_component_post->ID
	);
	$pns_style_guide_component_backup = wp_json_encode(
		array(
			'schema_version' => 1,
			'created_at_gmt' => gmdate( 'c' ),
			'post_id'        => $pns_style_guide_component_post->ID,
			'parent_post_id' => (int) $pns_style_guide_component_post->post_parent,
			'post_name'      => $pns_style_guide_component_post->post_name,
			'content_sha256' => hash( 'sha256', $pns_style_guide_component_post->post_content ),
			'post_content'   => $pns_style_guide_component_post->post_content,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);

	if ( false === file_put_contents( $pns_style_guide_component_path, $pns_style_guide_component_backup . "\n" ) ) {
		WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_style_guide_component_path ) );
	}

	$pns_style_guide_component_result = wp_update_post(
		wp_slash(
			array(
				'ID'           => $pns_style_guide_component_post->ID,
				'post_content' => $pns_style_guide_component_update['content'],
			)
		),
		true
	);

	if ( is_wp_error( $pns_style_guide_component_result ) ) {
		WP_CLI::error( $pns_style_guide_component_result->get_error_message() );
	}

	clean_post_cache( $pns_style_guide_component_post->ID );
	WP_CLI::success( sprintf( 'Added Style Guide component examples to post #%d. Rollback export: %s', $pns_style_guide_component_post->ID, $pns_style_guide_component_path ) );
}
