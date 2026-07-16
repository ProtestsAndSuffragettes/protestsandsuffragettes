<?php
/**
 * Blog post permalink helpers.
 *
 * @package protestsandsuffragettes
 */

/**
 * Register single-post routes below the public News archive.
 */
function pns_theme_register_news_post_rewrites() {
	add_rewrite_rule( '^news/([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
}
add_action( 'init', 'pns_theme_register_news_post_rewrites' );

/**
 * Keep public post links under /news/ without changing taxonomy archive bases.
 *
 * @param string  $permalink The generated permalink.
 * @param WP_Post $post      The post object.
 * @param bool    $leavename Whether to keep the post name placeholder.
 * @return string
 */
function pns_theme_filter_news_post_permalink( $permalink, $post, $leavename ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return $permalink;
	}

	$slug = $leavename ? '%postname%' : $post->post_name;

	return home_url( user_trailingslashit( 'news/' . $slug ) );
}
add_filter( 'post_link', 'pns_theme_filter_news_post_permalink', 10, 3 );
