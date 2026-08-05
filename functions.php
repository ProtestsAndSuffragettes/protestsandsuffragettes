<?php
/**
 * PNS theme bootstrap.
 *
 * @package protestsandsuffragettes
 */

$pns_theme_includes = array(
	'inc/featured-image-focus.php',
	'inc/assets.php',
	'inc/template-tags.php',
	'inc/patterns.php',
	'inc/herstories.php',
	'inc/blog-permalinks.php',
	'inc/block-styles.php',
	'inc/theme-lifecycle.php',
	'inc/dependencies.php',
	'inc/footer-social-links.php',
	'inc/media.php',
	'inc/block-filters.php',
	'inc/navigation.php',
);

foreach ( $pns_theme_includes as $pns_theme_include ) {
	require_once get_theme_file_path( $pns_theme_include );
}

/**
 * Include public Herstory entries in the native editorial search.
 *
 * @param string[] $post_types Post types allowed in editorial search.
 * @return string[]
 */
function pns_theme_add_herstories_to_search( $post_types ) {
	$post_types[] = 'herstory';

	return array_values( array_unique( $post_types ) );
}
add_filter( 'pns_search_routing_editorial_post_types', 'pns_theme_add_herstories_to_search' );
