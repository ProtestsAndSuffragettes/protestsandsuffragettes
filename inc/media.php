<?php
/**
 * Media configuration.
 *
 * @package protestsandsuffragettes
 */

add_image_size( 'featured', 1600, 600, array( 'center', 'center' ) );
add_image_size( 'card', 800, 600, array( 'center', 'center' ) );
add_image_size( 'square', 800, 800, array( 'center', 'center' ) );

/**
 * Add custom image sizes to the media size chooser.
 *
 * @param array<string,string> $sizes Registered image size labels.
 * @return array<string,string>
 */
function pns_theme_custom_sizes( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'featured' => __( 'Featured', 'protestsandsuffragettes' ),
			'card'     => __( 'Card', 'protestsandsuffragettes' ),
			'square'   => __( 'Square', 'protestsandsuffragettes' ),
		)
	);
}

add_filter( 'image_size_names_choose', 'pns_theme_custom_sizes' );
