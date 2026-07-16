<?php
/**
 * Render and editor block filters.
 *
 * This is the home for cross-domain filters only: vendor render cleanups,
 * portable template fixtures, Core block compatibility, and editor policy.
 * Feature-domain behavior belongs in its owning integration file (for example,
 * `inc/herstories.php`). Keep hook registration next to the callback unless a
 * documented ordering contract requires otherwise.
 *
 * @package protestsandsuffragettes
 */

/**
 * Remove an empty Ecwid shortcode marker from rendered store blocks.
 *
 * Ecwid can leave a literal `[]` marker beside the app output. This keeps that
 * vendor artifact out of the storefront without changing stored page content.
 *
 * Keep this bridge in the theme, not `pns-blocks`: it is a presentation cleanup
 * for the third-party `ecwid/store-block` used by the site templates, not
 * behavior for the project-owned product-grid block. Removal gate: delete this
 * filter when Ecwid stops emitting the marker or the stored template no longer
 * renders `ecwid/store-block`.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_clean_ecwid_store_block( $block_content, $block ) {
	if ( 'ecwid/store-block' === ( $block['blockName'] ?? '' ) ) {
		return str_replace( '[]', '', $block_content );
	}

	return $block_content;
}

add_filter( 'render_block', 'pns_theme_clean_ecwid_store_block', 10, 2 );

/**
 * Render EmailOctopus shortcodes saved in core Shortcode blocks.
 *
 * WordPress 7.0's core Shortcode block server renderer only applies wpautop().
 * The EmailOctopus plugin still exposes the form through a shortcode, so synced
 * sections need this narrow bridge to render the hosted form script.
 *
 * Keep this in the theme for now because the remaining shortcode lives in
 * theme-owned synced-pattern content. `ran-octopus-forms` owns the Jetpack contact form,
 * EmailOctopus API subscription path, newsletter opt-in, Turnstile, and health
 * checks; it does not own legacy hosted EmailOctopus embeds. Removal gate:
 * remove this filter after the legacy synced newsletter/contact fixture is
 * migrated to the `ran-octopus-forms/contact-form` pattern or retired.
 *
 * @param string $block_content Rendered shortcode block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_render_emailoctopus_shortcode_block( $block_content, $block ) {
	if ( 'core/shortcode' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$shortcode_content = '';

	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$shortcode_content = $block['innerHTML'];
	} elseif ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
		$shortcode_content = implode( '', array_filter( $block['innerContent'], 'is_string' ) );
	}

	if ( '' === trim( $shortcode_content ) ) {
		$shortcode_content = $block_content;
	}

	if ( ! has_shortcode( $shortcode_content, 'emailoctopus' ) && ! has_shortcode( $block_content, 'emailoctopus' ) ) {
		return $block_content;
	}

	return do_shortcode( trim( $shortcode_content ) );
}

add_filter( 'render_block_core/shortcode', 'pns_theme_render_emailoctopus_shortcode_block', 10, 2 );

/**
 * Suppress the News archive's lead story on pagination pages.
 *
 * The Home template is the posts page at `/news/`. Its featured-post block
 * deliberately selects the latest post while the grid starts at offset one.
 * On the Query block's `?query-1-page=n` pagination URLs, retaining that lead
 * repeats the latest story above an older grid, so only page one renders it.
 *
 * @param string $block_content Rendered block content.
 * @return string
 */
function pns_theme_hide_paged_news_featured_post( $block_content ) {
	$news_query_page = absint( $_GET['query-1-page'] ?? 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination state.

	if ( is_home() && 1 < $news_query_page ) {
		return '';
	}

	return $block_content;
}

add_filter(
	'render_block_pns/featured-post',
	'pns_theme_hide_paged_news_featured_post'
);

/**
 * Mark the News archive grid so its featured-post offset can inform pagination.
 *
 * Core applies a Query block offset to each page's SQL limit but counts every
 * matching post when calculating the number of pages. The News grid reserves
 * the newest post for its lead block, so its offset needs to reduce that count.
 *
 * @param array    $query Query arguments parsed from the Query block.
 * @param WP_Block $block Query block instance.
 * @return array
 */
function pns_theme_mark_news_grid_featured_offset( $query, $block ) {
	if ( ! is_home() || 1 !== absint( $block->context['queryId'] ?? 0 ) ) {
		return $query;
	}

	$offset = absint( $block->context['query']['offset'] ?? 0 );

	if ( 0 < $offset ) {
		$query['pns_featured_post_offset'] = $offset;
	}

	return $query;
}

add_filter(
	'query_loop_block_query_vars',
	'pns_theme_mark_news_grid_featured_offset',
	10,
	2
);

/**
 * Remove the featured archive item from the grid's page-count calculation.
 *
 * @param int      $found_posts Number of posts matched by the query.
 * @param WP_Query $query Query that supplied the count.
 * @return int
 */
function pns_theme_adjust_featured_archive_grid_found_posts( $found_posts, $query ) {
	$featured_post_offset = absint( $query->get( 'pns_featured_post_offset' ) );

	if ( 0 === $featured_post_offset ) {
		return $found_posts;
	}

	return max( 0, $found_posts - $featured_post_offset );
}

add_filter( 'found_posts', 'pns_theme_adjust_featured_archive_grid_found_posts', 10, 2 );

/**
 * Render the configured site logo when archive/search cards lack a featured image.
 *
 * Owner: the standalone archive/search card contract. External pressure: Core
 * Post Featured Image returns empty markup for records without an image, while
 * the code-owned listings require a stable media column. The fallback is
 * render-only: posts and pages keep their stored featured image state. Removal
 * gate: delete when the card template owns an explicit empty-media state.
 *
 * @param string        $block_content Rendered featured image block content.
 * @param array         $block Parsed block data.
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return string
 */
function pns_theme_render_archive_featured_image_fallback_block( $block_content, $block, $block_instance = null ) {
	if ( '' !== trim( $block_content ) ) {
		return $block_content;
	}

	if ( ! pns_theme_is_archive_featured_image_fallback_context() ) {
		return $block_content;
	}

	$post_id = pns_theme_get_block_context_post_id( $block_instance );

	if ( 0 >= $post_id || has_post_thumbnail( $post_id ) ) {
		return $block_content;
	}

	$logo_id = pns_theme_get_site_logo_id();

	if ( 0 >= $logo_id ) {
		return $block_content;
	}

	return pns_theme_render_archive_featured_image_fallback( $block, $post_id, $logo_id );
}

add_filter( 'render_block_core/post-featured-image', 'pns_theme_render_archive_featured_image_fallback_block', 10, 3 );

/**
 * Hide search result dates for non-post results.
 *
 * Owner: the standalone search-card contract. External pressure: Core Post
 * Date renders equally for posts and pages, but the search design exposes
 * publication dates only for posts. Removal gate: delete when the search
 * template has an explicit post-only date slot.
 *
 * @param string        $block_content Rendered post date block content.
 * @param array         $block Parsed block data.
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return string
 */
function pns_theme_render_search_result_post_date_block( $block_content, $block, $block_instance = null ) {
	if ( ! is_search() ) {
		return $block_content;
	}

	$post_id = pns_theme_get_block_context_post_id( $block_instance );

	if ( 0 >= $post_id || 'post' === get_post_type( $post_id ) ) {
		return $block_content;
	}

	return '';
}

add_filter( 'render_block_core/post-date', 'pns_theme_render_search_result_post_date_block', 10, 3 );

/**
 * Get the active site logo attachment ID.
 *
 * @return int
 */
function pns_theme_get_site_logo_id() {
	$logo_id = absint( get_option( 'site_logo' ) );

	if ( 0 >= $logo_id ) {
		$logo_id = absint( get_theme_mod( 'custom_logo' ) );
	}

	return $logo_id;
}

/**
 * Check whether the current request should use archive featured-image fallbacks.
 *
 * @return bool
 */
function pns_theme_is_archive_featured_image_fallback_context() {
	return is_home() || is_archive() || is_search();
}

/**
 * Get the post ID from a rendered block context.
 *
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return int
 */
function pns_theme_get_block_context_post_id( $block_instance ) {
	if ( $block_instance instanceof WP_Block && isset( $block_instance->context['postId'] ) ) {
		return absint( $block_instance->context['postId'] );
	}

	return absint( get_the_ID() );
}

/**
 * Render one logo fallback for a missing archive/search featured image.
 *
 * @param array<string,mixed> $block Parsed block data.
 * @param int                 $post_id Current post ID.
 * @param int                 $logo_id Custom logo attachment ID.
 * @return string
 */
function pns_theme_render_archive_featured_image_fallback( $block, $post_id, $logo_id ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$size  = isset( $attrs['sizeSlug'] ) && is_string( $attrs['sizeSlug'] ) && '' !== $attrs['sizeSlug'] ? $attrs['sizeSlug'] : 'post-thumbnail';

	$image = wp_get_attachment_image(
		$logo_id,
		$size,
		false,
		array(
			'alt'   => get_the_title( $post_id ),
			'class' => pns_theme_get_archive_featured_image_fallback_image_class( $size ),
			'style' => 'width:100%;height:100%;object-fit:' . pns_theme_get_archive_featured_image_fallback_object_fit() . ';',
		)
	);

	if ( '' === $image ) {
		return '';
	}

	if ( ! empty( $attrs['isLink'] ) ) {
		$link_style = pns_theme_get_archive_featured_image_fallback_link_style( $attrs );
		$image      = sprintf(
			'<a href="%1$s" target="%2$s"%3$s>%4$s</a>',
			esc_url( get_permalink( $post_id ) ),
			esc_attr( $attrs['linkTarget'] ?? '_self' ),
			'' !== $link_style ? ' style="' . esc_attr( $link_style ) . '"' : '',
			$image
		);
	}

	return sprintf(
		'<figure%1$s%2$s>%3$s</figure>',
		pns_theme_get_archive_featured_image_fallback_class_attribute( $attrs ),
		pns_theme_get_archive_featured_image_fallback_style_attribute( $attrs ),
		$image
	);
}

/**
 * Build the fallback image class attribute.
 *
 * @param string $size Image size slug.
 * @return string
 */
function pns_theme_get_archive_featured_image_fallback_image_class( $size ) {
	$size_class = sanitize_html_class( $size );

	return trim( 'attachment-' . $size_class . ' size-' . $size_class . ' wp-post-image pns-featured-image-fallback' );
}

/**
 * Get the fallback image fit for the current listing context.
 *
 * @return string
 */
function pns_theme_get_archive_featured_image_fallback_object_fit() {
	return is_search() ? 'contain' : 'cover';
}

/**
 * Build the fallback figure class attribute from Post Featured Image attrs.
 *
 * @param array<string,mixed> $attrs Block attributes.
 * @return string
 */
function pns_theme_get_archive_featured_image_fallback_class_attribute( $attrs ) {
	$classes = array();

	if ( ! empty( $attrs['className'] ) && is_string( $attrs['className'] ) ) {
		$classes = array_merge( $classes, preg_split( '/\s+/', trim( $attrs['className'] ) ) ?: array() );
	}

	$classes[] = 'wp-block-post-featured-image';
	$classes[] = 'pns-featured-image-fallback-figure';

	if ( ! empty( $attrs['align'] ) && is_string( $attrs['align'] ) ) {
		$classes[] = 'align' . $attrs['align'];
	}

	$classes = array_filter(
		array_map(
			static function ( $class_name ) {
				return sanitize_html_class( $class_name );
			},
			$classes
		)
	);

	return '' !== implode( ' ', $classes ) ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
}

/**
 * Build the fallback figure style attribute from Post Featured Image attrs.
 *
 * @param array<string,mixed> $attrs Block attributes.
 * @return string
 */
function pns_theme_get_archive_featured_image_fallback_style_attribute( $attrs ) {
	$styles = pns_theme_get_archive_featured_image_fallback_styles( $attrs );

	return ! empty( $styles ) ? ' style="' . esc_attr( implode( ';', $styles ) ) . '"' : '';
}

/**
 * Build fallback figure style declarations from Post Featured Image attrs.
 *
 * @param array<string,mixed> $attrs Block attributes.
 * @return string[]
 */
function pns_theme_get_archive_featured_image_fallback_styles( $attrs ) {
	$styles = array();

	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		$value = $attrs['style']['spacing']['margin'][ $side ] ?? '';

		if ( is_string( $value ) && '' !== $value ) {
			$styles[] = 'margin-' . $side . ':' . $value;
		}
	}

	if ( ! empty( $attrs['aspectRatio'] ) && is_string( $attrs['aspectRatio'] ) ) {
		$styles[] = 'aspect-ratio:' . $attrs['aspectRatio'];
	}

	if ( ! empty( $attrs['height'] ) && is_string( $attrs['height'] ) ) {
		$styles[] = 'height:' . $attrs['height'];
	}

	if ( ! empty( $attrs['width'] ) && is_string( $attrs['width'] ) ) {
		$styles[] = 'width:' . $attrs['width'];
	}

	return $styles;
}

/**
 * Build the fallback link style attribute from Post Featured Image attrs.
 *
 * @param array<string,mixed> $attrs Block attributes.
 * @return string
 */
function pns_theme_get_archive_featured_image_fallback_link_style( $attrs ) {
	return ! empty( $attrs['height'] ) && is_string( $attrs['height'] ) ? 'height:' . $attrs['height'] : '';
}

/**
 * Get stable template reference fallbacks.
 *
 * Owner: portable code-template fixtures. External pressure: Core Navigation
 * and reusable blocks persist environment-local numeric IDs. Templates refer
 * to DB-backed records by slug; the fallback IDs only keep the current live
 * site rendering if a slug lookup is temporarily missing. Removal gate: delete
 * once Core supports stable reference slugs for these block attributes.
 *
 * @return array<string,array<string,int>>
 */
function pns_theme_get_template_ref_fallbacks() {
	return array(
		'wp_navigation' => array(
			'pns-primary-navigation'    => 1035,
			'pns-banner-cta-navigation' => 5259,
			'pns-footer-navigation'     => 1032,
		),
		'wp_block'      => array(
			'connect-social' => 1494,
			'contact-form'   => 1493,
		),
	);
}

/**
 * Check whether a fallback post still matches the requested stable reference.
 *
 * Numeric IDs drift between Local databases. The ID fallback is only safe
 * when the saved record still has the expected type and slug.
 *
 * @param object|null $fallback  Fallback post-like object.
 * @param string      $post_type Expected post type.
 * @param string      $slug      Expected post_name slug.
 * @return bool
 */
function pns_theme_template_ref_fallback_is_valid( $fallback, $post_type, $slug ) {
	return $fallback
		&& isset( $fallback->post_type, $fallback->post_name )
		&& $post_type === $fallback->post_type
		&& $slug === $fallback->post_name;
}

/**
 * Resolve a DB-backed template reference by post type and slug.
 *
 * @param string $post_type Post type to search.
 * @param string $slug Stable post_name slug.
 * @return int Matching post ID, or 0 when unavailable.
 */
function pns_theme_resolve_template_ref_id( $post_type, $slug ) {
	static $cache = array();

	$cache_key = $post_type . ':' . $slug;

	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$post = get_page_by_path( $slug, OBJECT, $post_type );

	if ( $post && $post_type === $post->post_type ) {
		$cache[ $cache_key ] = (int) $post->ID;
		return $cache[ $cache_key ];
	}

	$fallbacks   = pns_theme_get_template_ref_fallbacks();
	$fallback_id = (int) ( $fallbacks[ $post_type ][ $slug ] ?? 0 );

	if ( 0 < $fallback_id ) {
		$fallback = get_post( $fallback_id );

		if ( pns_theme_template_ref_fallback_is_valid( $fallback, $post_type, $slug ) ) {
			$cache[ $cache_key ] = $fallback_id;
			return $cache[ $cache_key ];
		}
	}

	$cache[ $cache_key ] = 0;
	return 0;
}

/**
 * Inject current numeric refs for slug-backed template placeholders.
 *
 * WordPress core/navigation and core/block still render from numeric `ref`
 * attributes, but code-owned templates should not hard-code environment-local
 * IDs.
 *
 * @param array<string,mixed> $parsed_block Parsed block data.
 * @return array<string,mixed> Updated block data.
 */
function pns_theme_resolve_template_ref_block_data( $parsed_block ) {
	$block_name = isset( $parsed_block['blockName'] ) && is_string( $parsed_block['blockName'] ) ? $parsed_block['blockName'] : '';
	$attrs      = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$slug       = isset( $attrs['pnsRefSlug'] ) && is_string( $attrs['pnsRefSlug'] ) ? $attrs['pnsRefSlug'] : '';

	if ( '' === $slug ) {
		return $parsed_block;
	}

	$post_type = '';

	if ( 'core/navigation' === $block_name ) {
		$post_type = 'wp_navigation';
	} elseif ( 'core/block' === $block_name ) {
		$post_type = 'wp_block';
	}

	if ( '' === $post_type ) {
		return $parsed_block;
	}

	$resolved_id = pns_theme_resolve_template_ref_id( $post_type, $slug );

	if ( 0 >= $resolved_id ) {
		return $parsed_block;
	}

	$parsed_block['attrs']['ref'] = $resolved_id;

	return $parsed_block;
}

add_filter( 'render_block_data', 'pns_theme_resolve_template_ref_block_data' );

/**
 * Resolve slug-backed taxonomy filters for code-owned Query block fixtures.
 *
 * Owner: portable code-template fixtures. External pressure: Core Query stores
 * taxonomy filters as environment-local term IDs. This lets theme fixtures keep
 * stable term slugs while rendering native taxQuery attrs for WordPress.
 * Removal gate: delete once Core supports taxonomy slugs in saved Query attrs.
 *
 * @param array<string,mixed> $parsed_block Parsed block data.
 * @return array<string,mixed> Updated block data.
 */
function pns_theme_resolve_query_taxonomy_slug_filters( $parsed_block ) {
	$block_name = isset( $parsed_block['blockName'] ) && is_string( $parsed_block['blockName'] ) ? $parsed_block['blockName'] : '';

	if ( 'core/query' !== $block_name ) {
		return $parsed_block;
	}

	$attrs             = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$slug_tax_queries  = isset( $attrs['pnsTaxQuerySlugs'] ) && is_array( $attrs['pnsTaxQuerySlugs'] ) ? $attrs['pnsTaxQuerySlugs'] : array();
	$resolved_tax_query = array();

	foreach ( array( 'include', 'exclude' ) as $operator_group ) {
		$group = isset( $slug_tax_queries[ $operator_group ] ) && is_array( $slug_tax_queries[ $operator_group ] ) ? $slug_tax_queries[ $operator_group ] : array();

		foreach ( $group as $taxonomy => $slugs ) {
			if ( ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$slugs = is_array( $slugs ) ? $slugs : array( $slugs );
			$ids   = array();

			foreach ( $slugs as $slug ) {
				if ( ! is_string( $slug ) || '' === $slug ) {
					continue;
				}

				$term = get_term_by( 'slug', $slug, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$ids[] = (int) $term->term_id;
				}
			}

			if ( ! empty( $ids ) ) {
				$resolved_tax_query[ $operator_group ][ $taxonomy ] = array_values( array_unique( $ids ) );
			}
		}
	}

	if ( empty( $resolved_tax_query ) ) {
		return $parsed_block;
	}

	if ( ! isset( $parsed_block['attrs']['query'] ) || ! is_array( $parsed_block['attrs']['query'] ) ) {
		$parsed_block['attrs']['query'] = array();
	}

	$parsed_block['attrs']['query']['taxQuery'] = $resolved_tax_query;

	return $parsed_block;
}

add_filter( 'render_block_data', 'pns_theme_resolve_query_taxonomy_slug_filters' );

/**
 * Resolve slug-backed taxonomy filters for Query Loop child block context.
 *
 * Query Loop children read the inherited `query` context, so synced-pattern
 * children need this bridge in addition to parsed block data normalization.
 *
 * @param array<string,mixed> $context Default block context.
 * @param array<string,mixed> $parsed_block Parsed block data.
 * @param WP_Block|null      $parent_block Parent block instance.
 * @return array<string,mixed> Updated block context.
 */
function pns_theme_resolve_query_taxonomy_slug_context( $context, $parsed_block, $parent_block ) {
	if ( ! $parent_block instanceof WP_Block || 'core/query' !== $parent_block->name ) {
		return $context;
	}

	$resolved_parent = pns_theme_resolve_query_taxonomy_slug_filters( $parent_block->parsed_block );
	$tax_query       = $resolved_parent['attrs']['query']['taxQuery'] ?? null;

	if ( empty( $tax_query ) || ! is_array( $tax_query ) ) {
		return $context;
	}

	if ( ! isset( $context['query'] ) || ! is_array( $context['query'] ) ) {
		$context['query'] = array();
	}

	$context['query']['taxQuery'] = $tax_query;

	return $context;
}

add_filter( 'render_block_context', 'pns_theme_resolve_query_taxonomy_slug_context', 10, 3 );

/**
 * Check whether parsed Navigation attrs target the primary header navigation.
 *
 * @param array<string,mixed> $attrs Block attributes.
 * @return bool
 */
function pns_theme_is_primary_navigation_block_attrs( $attrs ) {
	if ( ! is_array( $attrs ) ) {
		return false;
	}

	$primary_slug  = defined( 'PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG' ) ? PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG : 'pns-primary-navigation';
	$slug          = isset( $attrs['pnsRefSlug'] ) && is_string( $attrs['pnsRefSlug'] ) ? $attrs['pnsRefSlug'] : '';
	$navigation_id = (int) ( $attrs['ref'] ?? 0 );

	if ( $primary_slug === $slug ) {
		return true;
	}

	return 0 < $navigation_id && function_exists( 'pns_theme_get_primary_navigation_id' ) && $navigation_id === pns_theme_get_primary_navigation_id();
}

/**
 * Remove core Navigation overlay template controls that this theme does not support.
 *
 * Owner: code-owned primary-navigation shell. External pressure: Core exposes
 * overlay-template and icon attributes that the theme-controlled drawer cannot
 * render. Removal gate: delete when primary Navigation can either render or no
 * longer save those unsupported attributes.
 *
 * @param array<string,mixed> $parsed_block Parsed block data.
 * @return array<string,mixed> Updated block data.
 */
function pns_theme_strip_navigation_overlay_template_block_data( $parsed_block ) {
	if ( 'core/navigation' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return $parsed_block;
	}

	if ( ! isset( $parsed_block['attrs'] ) || ! is_array( $parsed_block['attrs'] ) ) {
		return $parsed_block;
	}

	if ( ! pns_theme_is_primary_navigation_block_attrs( $parsed_block['attrs'] ) ) {
		return $parsed_block;
	}

	foreach ( array( 'overlay', 'icon', 'hasIcon' ) as $attribute_name ) {
		unset( $parsed_block['attrs'][ $attribute_name ] );
	}

	return $parsed_block;
}

add_filter( 'render_block_data', 'pns_theme_strip_navigation_overlay_template_block_data', 10 );

/**
 * Keep CTA navigation inline-only.
 *
 * Owner: code-owned cross-site-banner shell. External pressure: Core overlay
 * settings would save mobile-drawer behavior that the short inline action strip
 * cannot use. Removal gate: delete when CTA Navigation can render or no longer
 * save unsupported overlay attributes.
 *
 * @param array<string,mixed> $parsed_block Parsed block data.
 * @return array<string,mixed> Updated block data.
 */
function pns_theme_normalize_cta_navigation_block_data( $parsed_block ) {
	if ( 'core/navigation' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return $parsed_block;
	}

	$attrs      = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';
	$is_cta     = preg_match( '/\bpns-cross-site-banner-cta\b/', $class_name );

	if ( ! $is_cta ) {
		return $parsed_block;
	}

	$parsed_block['attrs']['overlayMenu'] = 'never';

	foreach ( array( 'overlay', 'overlayBackgroundColor', 'customOverlayBackgroundColor', 'overlayTextColor', 'customOverlayTextColor' ) as $attribute_name ) {
		unset( $parsed_block['attrs'][ $attribute_name ] );
	}

	return $parsed_block;
}

add_filter( 'render_block_data', 'pns_theme_normalize_cta_navigation_block_data', 11 );

/**
 * Register theme-owned Navigation block metadata attributes.
 *
 * Owner: portable code-template fixtures. External pressure: Core Navigation
 * and reusable blocks reference DB records by environment-local numeric ID.
 * These attributes provide stable slugs. Removal gate: delete once Core offers
 * a supported portable reference attribute.
 *
 * @param array<string,mixed> $args Block type registration args.
 * @param string             $block_type Block type name.
 * @return array<string,mixed>
 */
function pns_theme_register_navigation_metadata_attributes( $args, $block_type ) {
	if ( ! in_array( $block_type, array( 'core/navigation', 'core/block' ), true ) ) {
		return $args;
	}

	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$attribute_names = array( 'pnsRefSlug' );

	foreach ( $attribute_names as $attribute_name ) {
		$args['attributes'][ $attribute_name ] = array(
			'type' => 'string',
		);
	}

	return $args;
}

add_filter( 'register_block_type_args', 'pns_theme_register_navigation_metadata_attributes', 10, 2 );

/**
 * Keep PNS query pagination landmarks stable at archive boundaries.
 *
 * Owner: the standalone archive-pagination contract. External pressure: Core
 * omits Previous on the first page and Next on the last page. The PNS
 * treatment keeps those labels visible as disabled text so the page numbers
 * stay anchored in the middle slot between page loads. Removal gate: delete
 * when static query pagination can render disabled boundary controls.
 *
 * @param string $block_content Rendered pagination block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_render_stable_query_pagination_block( $block_content, $block ) {
	if ( ! pns_theme_block_has_class( $block, 'pns-query-pagination' ) ) {
		return $block_content;
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( 'nav' ) ) {
		return $block_content;
	}

	if ( ! $processor->has_class( 'pns-query-pagination' ) ) {
		return $block_content;
	}

	$processor->remove_class( 'has-background' );
	$processor->remove_class( 'has-neutral-50-background-color' );
	$block_content = $processor->get_updated_html();

	$has_previous = false !== strpos( $block_content, 'wp-block-query-pagination-previous' );
	$has_next     = false !== strpos( $block_content, 'wp-block-query-pagination-next' );

	if ( $has_previous && $has_next ) {
		return $block_content;
	}

	$previous_placeholder = $has_previous ? '' : pns_theme_render_disabled_query_pagination_boundary( 'previous', __( 'Previous', 'protestsandsuffragettes' ) );
	$next_placeholder     = $has_next ? '' : pns_theme_render_disabled_query_pagination_boundary( 'next', __( 'Next', 'protestsandsuffragettes' ) );

	return preg_replace(
		'/^(\s*<nav\b[^>]*>)(.*)(<\/nav>\s*)$/s',
		'$1' . $previous_placeholder . '$2' . $next_placeholder . '$3',
		$block_content
	) ?: $block_content;
}

add_filter( 'render_block_core/query-pagination', 'pns_theme_render_stable_query_pagination_block', 10, 2 );

/**
 * Render a disabled archive pagination boundary label.
 *
 * @param string $direction Boundary direction.
 * @param string $label Label text.
 * @return string
 */
function pns_theme_render_disabled_query_pagination_boundary( $direction, $label ) {
	return sprintf(
		'<span class="wp-block-query-pagination-%1$s pns-query-pagination__boundary is-disabled" aria-disabled="true">%2$s</span>',
		esc_attr( $direction ),
		esc_html( $label )
	);
}

/**
 * Check whether a parsed block has a class name.
 *
 * @param array<string,mixed> $block Parsed block data.
 * @param string              $class_name Class name to find.
 * @return bool
 */
function pns_theme_block_has_class( $block, $class_name ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

	if ( empty( $attrs['className'] ) || ! is_string( $attrs['className'] ) ) {
		return false;
	}

	return in_array( $class_name, preg_split( '/\s+/', $attrs['className'] ), true );
}

/**
 * Restrict unavailable core blocks in the editor.
 *
 * Owner: standalone editor-governance policy. External pressure: Core exposes
 * these blocks even though the supported PNS authoring contract has no safe
 * template or styling path for them. Removal gate: delete an entry only after
 * its authoring and frontend contract is deliberately supported.
 *
 * @param bool|string[]           $allowed_block_types Existing policy.
 * @param WP_Block_Editor_Context $block_editor_context Editor context.
 * @return bool|string[]
 */
function pns_theme_blacklist_blocks( $allowed_block_types = true, $block_editor_context = null ) {
	unset( $block_editor_context );

	$unsupported_blocks = array( 'core/archives', 'core/calendar' );

	if ( false === $allowed_block_types ) {
		return false;
	}

	if ( is_array( $allowed_block_types ) ) {
		return array_values( array_diff( $allowed_block_types, $unsupported_blocks ) );
	}

	$blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

	foreach ( $unsupported_blocks as $unsupported_block ) {
		unset( $blocks[ $unsupported_block ] );
	}

	return array_keys( $blocks );
}

add_filter( 'allowed_block_types_all', 'pns_theme_blacklist_blocks', 10, 2 );
