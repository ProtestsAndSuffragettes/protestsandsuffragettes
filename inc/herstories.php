<?php
/**
 * Herstories CPT theme integration.
 *
 * @package protestsandsuffragettes
 */

/**
 * Provide the visual scaffold for new Herstory CPT entries.
 *
 * The pns-herstories plugin owns the CPT and the existence of an editor
 * scaffold; this theme owns the markup used for the site-specific layout.
 *
 * @param array<int,array<int,mixed>> $template Plugin fallback template.
 * @return array<int,array<int,mixed>>
 */
function pns_theme_herstories_editor_template( $template ) {
	$pattern_slugs = pns_theme_get_herstory_scaffold_pattern_slugs();

	if ( empty( $pattern_slugs ) ) {
		return $template;
	}

	$template = array();

	foreach ( $pattern_slugs as $pattern_slug ) {
		$template[] = array(
			'core/pattern',
			array( 'slug' => $pattern_slug ),
		);
	}

	$template[] = array(
		'core/pattern',
		array( 'slug' => 'pns/entry-herstory-navigation' ),
	);

	return $template;
}

add_filter( 'pns_herstories_editor_template', 'pns_theme_herstories_editor_template' );

/**
 * Get named pattern slugs used by the Herstory blank-entry scaffold.
 *
 * @return string[]
 */
function pns_theme_get_herstory_scaffold_pattern_slugs() {
	return array(
		'pns/suffragette-hero',
		'pns/split-section-image',
		'pns/image-strip',
		'pns/suffragette-facts',
	);
}

/**
 * Hide the secondary Herstories archive grid until there are entries after the
 * featured first item.
 *
 * Owner: this theme's Herstories archive composition. External pressure: a
 * static Core Group cannot conditionally suppress its follow-on Query Loop.
 * Removal gate: delete when the Herstories template/block owns an explicit
 * conditional secondary-grid state.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_hide_sparse_herstories_more_section( $block_content, $block ) {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class_name = $block['attrs']['className'] ?? '';

	if ( ! is_string( $class_name ) || ! preg_match( '/\bpns-herstories-more-section\b/', $class_name ) ) {
		return $block_content;
	}

	if ( ! is_post_type_archive( 'herstory' ) ) {
		return $block_content;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'herstory',
			'post_status'            => 'publish',
			'posts_per_page'         => 2,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return 2 > count( $query->posts ) ? '' : $block_content;
}

add_filter( 'render_block', 'pns_theme_hide_sparse_herstories_more_section', 10, 2 );

/**
 * Suppress the Herstories archive's lead entry on pagination pages.
 *
 * The archive grid skips its featured entry, so page two and beyond must not
 * render that lead again above the remaining Herstories.
 *
 * @param string $block_content Rendered block content.
 * @return string
 */
function pns_theme_hide_paged_herstory_featured_post( $block_content ) {
	$herstory_query_page = absint( $_GET['query-15-page'] ?? 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination state.

	if ( is_post_type_archive( 'herstory' ) && 1 < $herstory_query_page ) {
		return '';
	}

	return $block_content;
}

add_filter(
	'render_block_pns/featured-post',
	'pns_theme_hide_paged_herstory_featured_post'
);

/**
 * Mark the Herstories archive grid so its featured-post offset can inform pagination.
 *
 * Core applies a Query block offset to each page's SQL limit but counts every
 * matching Herstory when calculating the number of pages. The archive grid
 * reserves the first entry for its lead block, so its offset needs to reduce
 * that count.
 *
 * @param array    $query Query arguments parsed from the Query block.
 * @param WP_Block $block Query block instance.
 * @return array
 */
function pns_theme_mark_herstory_grid_featured_offset( $query, $block ) {
	if (
		! is_post_type_archive( 'herstory' ) ||
		15 !== absint( $block->context['queryId'] ?? 0 )
	) {
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
	'pns_theme_mark_herstory_grid_featured_offset',
	10,
	2
);

/**
 * Render Herstory entry navigation from the content-model ordering API.
 *
 * Owner: this theme's Herstories entry composition. External pressure: Core
 * navigation cannot express the plugin's CPT archive, page hierarchy, and
 * sibling menu-order model. Removal gate: delete when a static block/template
 * path can express those navigation relationships.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_render_herstory_entry_navigation_block( $block_content, $block ) {
	if ( ! pns_theme_block_has_class( $block, 'pns-herstory-entry-navigation' ) ) {
		return $block_content;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post ) {
		return $block_content;
	}

	if ( ! pns_theme_is_herstory_navigation_post( $post ) ) {
		return $block_content;
	}

	$adjacent = pns_theme_get_herstory_adjacent_posts( $post );
	$back_url = 'herstory' === $post->post_type ? get_post_type_archive_link( 'herstory' ) : home_url( '/herstories/' );

	return pns_theme_render_entry_navigation_controls( $adjacent['previous'], $adjacent['next'], $back_url );
}

add_filter( 'render_block_core/group', 'pns_theme_render_herstory_entry_navigation_block', 10, 2 );

/**
 * Check whether the current post should use Herstories navigation.
 *
 * @param WP_Post $post Post object.
 * @return bool
 */
function pns_theme_is_herstory_navigation_post( $post ) {
	if ( 'herstory' === $post->post_type ) {
		return true;
	}

	if ( 'page' !== $post->post_type || 0 >= (int) $post->post_parent ) {
		return false;
	}

	foreach ( get_post_ancestors( $post ) as $ancestor_id ) {
		$ancestor = get_post( $ancestor_id );

		if ( $ancestor instanceof WP_Post && 'herstories' === $ancestor->post_name ) {
			return true;
		}
	}

	return false;
}

/**
 * Get adjacent posts for a Herstories page or CPT entry.
 *
 * @param WP_Post $post Post object.
 * @return array{previous:WP_Post|null,next:WP_Post|null}
 */
function pns_theme_get_herstory_adjacent_posts( $post ) {
	if ( 'herstory' === $post->post_type && class_exists( '\\PNS\\Herstories\\Queries' ) ) {
		return \PNS\Herstories\Queries::adjacent( $post );
	}

	$siblings = get_posts(
		array(
			'post_type'      => $post->post_type,
			'post_parent'    => (int) $post->post_parent,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'order'          => 'ASC',
		)
	);

	$ids   = wp_list_pluck( $siblings, 'ID' );
	$index = array_search( (int) $post->ID, array_map( 'intval', $ids ), true );

	if ( false === $index ) {
		return array(
			'previous' => null,
			'next'     => null,
		);
	}

	return array(
		'previous' => isset( $siblings[ $index - 1 ] ) ? $siblings[ $index - 1 ] : null,
		'next'     => isset( $siblings[ $index + 1 ] ) ? $siblings[ $index + 1 ] : null,
	);
}

/**
 * Render the Herstory previous/back/next button row.
 *
 * @param WP_Post|null $previous Previous post.
 * @param WP_Post|null $next Next post.
 * @param string       $back_url Back-to-archive URL.
 * @return string
 */
function pns_theme_render_entry_navigation_controls( $previous, $next, $back_url ) {
	$previous_button = '';
	$next_button     = '';

	if ( $previous instanceof WP_Post ) {
		$previous_button = pns_theme_render_entry_navigation_link( 'previous', __( 'Previous', 'protestsandsuffragettes' ), get_permalink( $previous ) );
	}

	$back_button = pns_theme_render_entry_navigation_link( '', __( 'Back to Herstories', 'protestsandsuffragettes' ), $back_url );

	if ( $next instanceof WP_Post ) {
		$next_button = pns_theme_render_entry_navigation_link( 'next', __( 'Next', 'protestsandsuffragettes' ), get_permalink( $next ) );
	}

	return sprintf(
		'<div class="wp-block-group alignfull pns-section pns-layout pns-entry-navigation pns-herstory-entry-navigation pns-site-frame-panel has-neutral-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--generous);padding-bottom:var(--wp--preset--spacing--generous)"><div class="wp-block-group pns-content-frame pns-entry-navigation__controls"><div class="post-navigation-link-previous wp-block-post-navigation-link pns-entry-navigation__action previous">%1$s</div><div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex"><div class="wp-block-button">%2$s</div></div><div class="post-navigation-link-next wp-block-post-navigation-link pns-entry-navigation__action next">%3$s</div></div></div>',
		$previous_button,
		$back_button,
		$next_button
	);
}

/**
 * Render one entry-navigation link.
 *
 * @param string $direction Direction class.
 * @param string $label Button label.
 * @param string $url Button URL.
 * @return string
 */
function pns_theme_render_entry_navigation_link( $direction, $label, $url ) {
	if ( '' === $direction ) {
		return sprintf(
			'<a class="wp-block-button__link wp-element-button" href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	$link    = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( $url ),
		esc_html( $label )
	);
	$arrow   = pns_theme_render_entry_navigation_arrow( $direction );

	if ( 'previous' === $direction ) {
		return sprintf(
			'<a href="%1$s" rel="prev">%2$s%3$s</a>',
			esc_url( $url ),
			$arrow,
			esc_html( $label )
		);
	}

	if ( 'next' === $direction ) {
		return sprintf(
			'<a href="%1$s" rel="next">%2$s%3$s</a>',
			esc_url( $url ),
			esc_html( $label ),
			$arrow
		);
	}

	return $link;
}

/**
 * Render one entry-navigation arrow.
 *
 * @param string $direction Direction class.
 * @return string
 */
function pns_theme_render_entry_navigation_arrow( $direction ) {
	return sprintf(
		'<span class="wp-block-post-navigation-link__arrow-%1$s is-arrow-arrow" aria-hidden="true">%2$s</span>',
		esc_attr( $direction ),
		'previous' === $direction ? '←' : '→'
	);
}
