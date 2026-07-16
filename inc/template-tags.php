<?php
/**
 * Template helpers.
 *
 * @package protestsandsuffragettes
 */

/**
 * Determine whether the queried page is the given page family.
 *
 * @param string $slug Page or ancestor slug.
 * @return bool
 */
function pns_theme_is_page_family( $slug ) {
	if ( ! is_page() ) {
		return false;
	}

	$page_id = get_queried_object_id();
	$slugs   = array( get_post_field( 'post_name', $page_id ) );

	foreach ( get_post_ancestors( $page_id ) as $ancestor_id ) {
		$slugs[] = get_post_field( 'post_name', $ancestor_id );
	}

	return in_array( $slug, $slugs, true );
}

/**
 * Return whether the experimental page-level template reveal is enabled.
 *
 * Rollback options:
 * - Set PNS_STANDALONE_TEMPLATE_REVEAL to false before the theme loads.
 * - Or add: add_filter( 'pns_theme_enable_template_reveal', '__return_false' );
 *
 * @return bool
 */
function pns_theme_template_reveal_enabled() {
	$enabled = defined( 'PNS_STANDALONE_TEMPLATE_REVEAL' )
		? (bool) PNS_STANDALONE_TEMPLATE_REVEAL
		: true;

	return (bool) apply_filters( 'pns_theme_enable_template_reveal', $enabled );
}

/**
 * Add PNS theme body classes.
 *
 * Herstories styling spans a landing page, child pages, and single Herstory
 * entries, so the body class gives CSS one stable page-family hook.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function pns_theme_body_classes( $classes ) {
	if ( pns_theme_is_page_family( 'herstories' ) || is_singular( 'herstory' ) ) {
		$classes[] = 'pns-page-family-herstories';
	}

	if ( pns_theme_template_reveal_enabled() ) {
		$classes[] = 'pns-template-reveal-enabled';
	}

	return $classes;
}

add_filter( 'body_class', 'pns_theme_body_classes' );
