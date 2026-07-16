<?php
/**
 * Add real-image duotone specimens to the private Style Guide.
 *
 * Usage:
 *   wp eval-file scripts/add-style-guide-duotone-thumbnails.php
 *   wp eval-file scripts/add-style-guide-duotone-thumbnails.php -- apply
 *
 * Every specimen pairs its colour bar with a native Image block using the same
 * theme preset. That demonstrates WordPress's generated duotone filter rather
 * than a hand-drawn approximation of its two source colours.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_duotone_args  = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_duotone_apply = in_array( 'apply', $pns_duotone_args, true ) || in_array( '--apply', $pns_duotone_args, true );
$pns_duotone_page  = get_post( 6340 );

if ( ! $pns_duotone_page || 'page' !== $pns_duotone_page->post_type ) {
	WP_CLI::error( 'Style Guide duotone specimens require page #6340.' );
}

if ( 'page-light-surface-wide-content' !== get_page_template_slug( 6340 ) ) {
	WP_CLI::error( 'Style Guide duotone specimens stopped because page #6340 no longer uses the light-surface wide-content template.' );
}

$pns_duotone_presets = array(
	array( 'deep-purple-and-neutral-0', 'Deep purple and neutral 0', '#170145', '#ffffff' ),
	array( 'brand-purple-and-neutral-0', 'Brand purple and neutral 0', '#3D207E', '#ffffff' ),
	array( 'heritage-green-and-neutral-0', 'Heritage green and neutral 0', '#006B5F', '#ffffff' ),
	array( 'brand-purple-and-accent-mint', 'Brand purple and accent mint', '#3D207E', '#7bdcb5' ),
	array( 'heritage-green-and-accent-mint', 'Heritage green and accent mint', '#006B5F', '#7bdcb5' ),
	array( 'neutral-800-and-neutral-50', 'Neutral 800 and neutral 50', '#2B2B2B', '#F0F0F0' ),
);

$pns_duotone_markup = <<<'BLOCKS'
<!-- wp:group {"className":"pns-style-guide-duotone-preset-card-grid-v2","layout":{"type":"grid","minimumColumnWidth":"10rem"}} -->
<div class="wp-block-group pns-style-guide-duotone-preset-card-grid-v2">
BLOCKS;

foreach ( $pns_duotone_presets as $pns_duotone_preset ) {
	list( $pns_duotone_slug, $pns_duotone_name, $pns_duotone_dark, $pns_duotone_light ) = $pns_duotone_preset;
	$pns_duotone_markup .= sprintf(
		<<<'BLOCKS'

<!-- wp:group {"className":"pns-style-guide-duotone-preset-card"} -->
<div class="wp-block-group pns-style-guide-duotone-preset-card"><!-- wp:html -->
<div class="pns-style-guide-duotone-preset-card__swatch"><span style="background:%3$s;"></span><span style="background:%4$s;"></span></div>
<!-- /wp:html -->

<!-- wp:image {"id":4534,"sizeSlug":"medium","linkDestination":"none","aspectRatio":"1","scale":"cover","style":{"color":{"duotone":"var:preset|duotone|%1$s"}}} -->
<figure class="wp-block-image size-medium"><img src="/wp-content/uploads/2025/02/11.jpg" alt="" class="wp-image-4534" style="aspect-ratio:1;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"pns-style-guide-duotone-preset-card__description"} -->
<p class="pns-style-guide-duotone-preset-card__description">%2$s</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
BLOCKS,
		esc_attr( $pns_duotone_slug ),
		esc_html( $pns_duotone_name ),
		esc_attr( $pns_duotone_dark ),
		esc_attr( $pns_duotone_light )
	);
}

$pns_duotone_markup .= "\n</div>\n<!-- /wp:group -->\n";

define( 'PNS_STYLE_GUIDE_DUOTONE_PRESET_MARKUP', $pns_duotone_markup );

/**
 * Replace the old separate swatches and samples with paired live specimens.
 *
 * @param string $content Current saved block content.
 * @return string|WP_Error Updated content or an error explaining why the page drifted.
 */
function pns_add_style_guide_duotone_thumbnails( $content ) {
	$marker              = 'pns-style-guide-duotone-preset-card-grid-v2';
	$old_description     = '<p>Duotone presets are editor image controls. The samples below show the paired palette colors; apply them to image blocks when a two-color treatment is intentional.</p>';
	$new_description     = '<p>Duotone presets are editor image controls. Each specimen pairs its source colours with a square Image block using the live filter, so it shows the treatment rather than an approximation.</p>';
	$updated_description = str_replace( $old_description, $new_description, $content );
	$updated_description = str_replace( '<p>Duotone presets are editor image controls. The colour bars show each pair; the square samples below are real Image blocks using the live filter, so they show the treatment rather than an approximation.</p>', $new_description, $updated_description );
	$malformed_tail      = "\n</div>\n<!-- /wp:group -->\n</div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"className\":\"pns-section";
	$section_boundary    = "\n\n<!-- wp:group {\"align\":\"full\",\"className\":\"pns-section";
	$legacy_markers       = array( 'pns-style-guide-duotone-thumbnail-grid', 'pns-style-guide-duotone-preset-grid', 'pns-style-guide-duotone-preset-card-grid' );

	/*
	 * Repair the malformed tail emitted by the original v2 migration. The two
	 * preceding closers belong to the Duotone section and its frame; this tail
	 * is three surplus Group closers that cause Gutenberg to treat every later
	 * Style Guide block as raw HTML.
	 */
	if ( false !== strpos( $updated_description, $marker ) && false !== strpos( $updated_description, $malformed_tail ) ) {
		$updated_description = str_replace( $malformed_tail, $section_boundary, $updated_description, $pns_duotone_tail_repairs );

		if ( 1 !== $pns_duotone_tail_repairs ) {
			return new WP_Error( 'duotone_malformed_tail_not_unique', 'Style Guide duotone malformed tail did not match exactly once.' );
		}
	}

	if ( 2 < substr_count( $updated_description, $marker ) ) {
		return new WP_Error( 'duplicate_duotone_specimens', 'Style Guide contains duplicate duotone-thumbnail sections.' );
	}

	if ( 2 === substr_count( $updated_description, $marker ) ) {
		return $updated_description;
	}

	$heading = '<h3 class="wp-block-heading">Duotone Presets</h3>';
	$start   = strpos( $updated_description, $heading );

	if ( false === $start ) {
		return new WP_Error( 'duotone_heading_not_found', 'Style Guide Duotone Presets heading was not found.' );
	}

	$legacy_grid_start = false;
	$legacy_marker     = null;

	foreach ( $legacy_markers as $candidate_marker ) {
		$legacy_grid_start = strpos( $updated_description, '<!-- wp:group {"className":"' . $candidate_marker . '"', $start );

		if ( false !== $legacy_grid_start ) {
			$legacy_marker = $candidate_marker;
			break;
		}
	}

	if ( false === $legacy_grid_start ) {
		return new WP_Error( 'duotone_legacy_specimens_not_found', 'Style Guide duotone specimens did not match the expected saved markup.' );
	}

	$legacy_grid_end = strpos( $updated_description, "\n<!-- /wp:group -->\n</div>\n<!-- /wp:group -->", $legacy_grid_start );

	if ( false === $legacy_grid_end ) {
		return new WP_Error( 'duotone_legacy_specimens_not_found', 'Style Guide duotone specimen boundary was not found.' );
	}

	$legacy_grid_end += strlen( "\n<!-- /wp:group -->" );
	$replace_start = $legacy_grid_start;

	if ( 'pns-style-guide-duotone-thumbnail-grid' === $legacy_marker ) {
		$html_start = strpos( $updated_description, '<!-- wp:html -->', $start );
		$html_end   = false === $html_start ? false : strpos( $updated_description, '<!-- /wp:html -->', $html_start );

		if ( false === $html_start || false === $html_end || $html_end > $legacy_grid_start ) {
			return new WP_Error( 'duotone_swatches_not_found', 'Style Guide duotone colour-bar block did not match the expected saved markup.' );
		}

		$replace_start = $html_start;
	}

	return substr( $updated_description, 0, $replace_start ) . PNS_STYLE_GUIDE_DUOTONE_PRESET_MARKUP . substr( $updated_description, $legacy_grid_end );
}

$pns_duotone_posts = array( $pns_duotone_page );
$pns_duotone_revs  = get_children(
	array(
		'post_parent' => 6340,
		'post_type'   => 'revision',
		'numberposts' => -1,
	)
);

foreach ( $pns_duotone_revs as $pns_duotone_revision ) {
	if ( wp_is_post_autosave( $pns_duotone_revision ) ) {
		$pns_duotone_posts[] = $pns_duotone_revision;
	}
}

$pns_duotone_updates = array();

foreach ( $pns_duotone_posts as $pns_duotone_post ) {
	$pns_duotone_content = pns_add_style_guide_duotone_thumbnails( $pns_duotone_post->post_content );

	if ( is_wp_error( $pns_duotone_content ) ) {
		WP_CLI::error( sprintf( 'Post #%d: %s', $pns_duotone_post->ID, $pns_duotone_content->get_error_message() ) );
	}

	if ( $pns_duotone_content === $pns_duotone_post->post_content ) {
		WP_CLI::log( sprintf( 'Post #%d already contains duotone-thumbnail specimens.', $pns_duotone_post->ID ) );
		continue;
	}

	$pns_duotone_updates[] = array(
		'post'    => $pns_duotone_post,
		'content' => $pns_duotone_content,
	);
	WP_CLI::log( sprintf( 'Post #%d will receive duotone-thumbnail specimens.', $pns_duotone_post->ID ) );
}

if ( ! $pns_duotone_updates ) {
	WP_CLI::success( 'Style Guide duotone-thumbnail specimens are already synchronised.' );
	return;
}

if ( ! $pns_duotone_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write rollback exports and update the Style Guide.' );
	return;
}

$pns_duotone_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/style-guide-duotone-db-backups';

if ( ! is_dir( $pns_duotone_backup_dir ) && ! wp_mkdir_p( $pns_duotone_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create rollback directory: %s', $pns_duotone_backup_dir ) );
}

foreach ( $pns_duotone_updates as $pns_duotone_update ) {
	$pns_duotone_post = $pns_duotone_update['post'];
	$pns_duotone_path = sprintf(
		'%s/%s-post-%d-before-duotone-specimens.json',
		$pns_duotone_backup_dir,
		gmdate( 'Ymd\\THis\\Z' ),
		$pns_duotone_post->ID
	);
	$pns_duotone_backup = wp_json_encode(
		array(
			'schema_version' => 1,
			'created_at_gmt' => gmdate( 'c' ),
			'post_id'        => $pns_duotone_post->ID,
			'parent_post_id' => (int) $pns_duotone_post->post_parent,
			'post_name'      => $pns_duotone_post->post_name,
			'content_sha256' => hash( 'sha256', $pns_duotone_post->post_content ),
			'post_content'   => $pns_duotone_post->post_content,
		),
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);

	if ( false === file_put_contents( $pns_duotone_path, $pns_duotone_backup . "\n" ) ) {
		WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_duotone_path ) );
	}

	$pns_duotone_result = wp_update_post(
		wp_slash(
			array(
				'ID'           => $pns_duotone_post->ID,
				'post_content' => $pns_duotone_update['content'],
			)
		),
		true
	);

	if ( is_wp_error( $pns_duotone_result ) ) {
		WP_CLI::error( $pns_duotone_result->get_error_message() );
	}

	clean_post_cache( $pns_duotone_post->ID );
	WP_CLI::success( sprintf( 'Added Style Guide duotone-thumbnail specimens to post #%d. Rollback export: %s', $pns_duotone_post->ID, $pns_duotone_path ) );
}
