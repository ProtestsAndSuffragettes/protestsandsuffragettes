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

/**
 * Post types where comments should default to closed.
 */
function pns_get_comment_locked_post_types() {
    return array( 'post', 'page', 'herstory');
}

/**
 * 1. Set the default editor UI toggle to "closed".
 */
function pns_default_comment_status( $status, $post_type, $comment_type ) {
    if ( 'comment' === $comment_type && in_array( $post_type, pns_get_comment_locked_post_types(), true ) ) {
        return 'closed';
    }
    return $status;
}

/**
 * 2. Enforce closed comments only when creating NEW posts.
 *    Editors can still enable comments on existing posts.
 */
function pns_force_closed_comments_on_insert( $data, $postarr ) {
    if ( in_array( $data['post_type'], pns_get_comment_locked_post_types(), true ) ) {

        // Only enforce on NEW posts (ID = 0)
        if ( empty( $postarr['ID'] ) ) {
            $data['comment_status'] = 'closed';
            $data['ping_status']    = 'closed';
        }
    }
    return $data;
}

/**
 * Apply the filters to enforce closed comments on new posts by default.
 * This is overridable in the editor UI if the user chooses to open comments.
 */
add_filter( 'get_default_comment_status', 'pns_default_comment_status', 10, 3 );
add_filter( 'wp_insert_post_data', 'pns_force_closed_comments_on_insert', 10, 2 );
