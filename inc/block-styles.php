<?php
/**
 * Block style registration.
 *
 * @package protestsandsuffragettes
 */

/**
 * Enqueue block-scoped PNS theme styles.
 *
 * @return void
 */
	function pns_theme_enqueue_block_styles() {
		$block_styles = array(
			'core/columns'      => 'styles/blocks/core-columns.css',
			'core/group'        => 'styles/blocks/core-group.css',
			'core/image'        => 'styles/blocks/core-image.css',
			'core/separator'    => 'styles/blocks/core-separator.css',
			'core/social-links' => 'styles/blocks/core-social-links.css',
			'jetpack/slideshow' => 'styles/blocks/jetpack-slideshow.css',
	);

	foreach ( $block_styles as $block_name => $style ) {
		$style_path = is_array( $style ) ? $style['path'] : $style;
		$style_deps = is_array( $style ) && isset( $style['deps'] ) ? $style['deps'] : array();

		wp_enqueue_block_style(
			$block_name,
			array(
				'handle' => 'pns-theme-' . str_replace( '/', '-', $block_name ),
				'src'    => get_stylesheet_directory_uri() . '/' . $style_path,
				'path'   => get_stylesheet_directory() . '/' . $style_path,
				'deps'   => $style_deps,
				'ver'    => pns_theme_theme_asset_version( $style_path ),
			)
		);
	}
}

add_action( 'init', 'pns_theme_enqueue_block_styles' );
