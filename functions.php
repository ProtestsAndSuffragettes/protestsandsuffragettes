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
	'inc/footer-social-links.php',
	'inc/media.php',
	'inc/block-filters.php',
	'inc/navigation.php',
);

foreach ( $pns_theme_includes as $pns_theme_include ) {
	require_once get_theme_file_path( $pns_theme_include );
}
