<?php
/**
 * Featured image focal point support.
 *
 * @package protestsandsuffragettes
 */

const PNS_STANDALONE_FEATURED_IMAGE_FOCUS_X_META = '_pns_featured_image_focus_x';
const PNS_STANDALONE_FEATURED_IMAGE_FOCUS_Y_META = '_pns_featured_image_focus_y';

/**
 * Get post types that support featured-image focal points.
 *
 * @return string[]
 */
function pns_theme_get_featured_image_focus_post_types() {
	return apply_filters(
		'pns_theme_featured_image_focus_post_types',
		array(
			'post',
			'herstory',
		)
	);
}

/**
 * Register REST-visible featured-image focal point metadata.
 *
 * @return void
 */
function pns_theme_register_featured_image_focus_meta() {
	$meta_args = array(
		'auth_callback'     => 'pns_theme_can_edit_featured_image_focus_meta',
		'sanitize_callback' => 'pns_theme_sanitize_featured_image_focus_coordinate',
		'show_in_rest'      => true,
		'single'            => true,
		'type'              => 'number',
	);

	foreach ( pns_theme_get_featured_image_focus_post_types() as $post_type ) {
		register_post_meta( $post_type, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_X_META, $meta_args );
		register_post_meta( $post_type, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_Y_META, $meta_args );
	}
}

add_action( 'init', 'pns_theme_register_featured_image_focus_meta', 20 );

/**
 * Check whether the current user can update focal point metadata.
 *
 * @param bool   $allowed Existing auth value.
 * @param string $meta_key Meta key.
 * @param int    $post_id Post ID.
 * @return bool
 */
function pns_theme_can_edit_featured_image_focus_meta( $allowed, $meta_key, $post_id ) {
	unset( $allowed, $meta_key );

	return current_user_can( 'edit_post', $post_id );
}

/**
 * Sanitize one focal point coordinate.
 *
 * @param mixed $value Raw coordinate.
 * @return float
 */
function pns_theme_sanitize_featured_image_focus_coordinate( $value ) {
	if ( ! is_numeric( $value ) ) {
		return 0.5;
	}

	return min( 1, max( 0, (float) $value ) );
}

/**
 * Get editor settings for the featured-image focal point UI.
 *
 * @return array<string,mixed>
 */
function pns_theme_get_featured_image_focus_editor_settings() {
	return array(
		'metaKeys'  => array(
			'x' => PNS_STANDALONE_FEATURED_IMAGE_FOCUS_X_META,
			'y' => PNS_STANDALONE_FEATURED_IMAGE_FOCUS_Y_META,
		),
		'postTypes' => array_values( pns_theme_get_featured_image_focus_post_types() ),
	);
}

/**
 * Get a saved featured-image focal point for a post.
 *
 * @param int $post_id Post ID.
 * @return array{x:float,y:float}|null
 */
function pns_theme_get_featured_image_focus_point( $post_id ) {
	$post_id = absint( $post_id );

	if (
		0 >= $post_id ||
		! metadata_exists( 'post', $post_id, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_X_META ) ||
		! metadata_exists( 'post', $post_id, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_Y_META )
	) {
		return null;
	}

	$x = get_post_meta( $post_id, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_X_META, true );
	$y = get_post_meta( $post_id, PNS_STANDALONE_FEATURED_IMAGE_FOCUS_Y_META, true );

	if ( ! is_numeric( $x ) || ! is_numeric( $y ) ) {
		return null;
	}

	return array(
		'x' => min( 1, max( 0, (float) $x ) ),
		'y' => min( 1, max( 0, (float) $y ) ),
	);
}

/**
 * Get a CSS object/background-position value for a post focal point.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function pns_theme_get_featured_image_focus_position( $post_id ) {
	$focus_point = pns_theme_get_featured_image_focus_point( $post_id );

	if ( null === $focus_point ) {
		return '';
	}

	return pns_theme_format_featured_image_focus_percent( $focus_point['x'] ) . '% ' . pns_theme_format_featured_image_focus_percent( $focus_point['y'] ) . '%';
}

/**
 * Format one focal coordinate as a compact percentage number.
 *
 * @param float $coordinate Normalized coordinate.
 * @return string
 */
function pns_theme_format_featured_image_focus_percent( $coordinate ) {
	return rtrim( rtrim( number_format( $coordinate * 100, 2, '.', '' ), '0' ), '.' );
}

/**
 * Apply saved focal point metadata to core Post Featured Image blocks.
 *
 * @param string        $block_content Rendered block content.
 * @param array         $block Parsed block data.
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return string
 */
function pns_theme_apply_featured_image_focus_to_post_featured_image_block( $block_content, $block, $block_instance = null ) {
	unset( $block );

	if ( '' === trim( $block_content ) ) {
		return $block_content;
	}

	$position = pns_theme_get_featured_image_focus_position(
		pns_theme_get_featured_image_focus_block_post_id( $block_instance )
	);

	if ( '' === $position ) {
		return $block_content;
	}

	return pns_theme_apply_featured_image_focus_to_image_markup( $block_content, $position );
}

add_filter( 'render_block_core/post-featured-image', 'pns_theme_apply_featured_image_focus_to_post_featured_image_block', 9, 3 );

/**
 * Apply saved focal point metadata to featured-image Cover blocks.
 *
 * @param string        $block_content Rendered block content.
 * @param array         $block Parsed block data.
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return string
 */
function pns_theme_apply_featured_image_focus_to_cover_block( $block_content, $block, $block_instance = null ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

	if ( empty( $attrs['useFeaturedImage'] ) ) {
		return $block_content;
	}

	$position = pns_theme_get_featured_image_focus_position(
		pns_theme_get_featured_image_focus_block_post_id( $block_instance )
	);

	if ( '' === $position ) {
		return $block_content;
	}

	$updated_content = pns_theme_apply_featured_image_focus_to_cover_image_markup( $block_content, $position );

	return '' !== $updated_content ? $updated_content : $block_content;
}

add_filter( 'render_block_core/cover', 'pns_theme_apply_featured_image_focus_to_cover_block', 10, 3 );

/**
 * Get the rendered block post ID.
 *
 * @param WP_Block|null $block_instance Runtime block instance.
 * @return int
 */
function pns_theme_get_featured_image_focus_block_post_id( $block_instance ) {
	if ( function_exists( 'pns_theme_get_block_context_post_id' ) ) {
		return pns_theme_get_block_context_post_id( $block_instance );
	}

	if ( $block_instance instanceof WP_Block && isset( $block_instance->context['postId'] ) ) {
		return absint( $block_instance->context['postId'] );
	}

	return absint( get_the_ID() );
}

/**
 * Apply object-position to the first image in a Post Featured Image block.
 *
 * @param string $markup Rendered markup.
 * @param string $position CSS position value.
 * @return string
 */
function pns_theme_apply_featured_image_focus_to_image_markup( $markup, $position ) {
	$processor = new WP_HTML_Tag_Processor( $markup );

	if ( ! $processor->next_tag( 'img' ) ) {
		return $markup;
	}

	pns_theme_set_featured_image_focus_tag_position( $processor, 'object-position', $position );
	$processor->set_attribute( 'data-object-position', $position );

	return $processor->get_updated_html();
}

/**
 * Apply focus styles to the media element in a Cover block.
 *
 * @param string $markup Rendered markup.
 * @param string $position CSS position value.
 * @return string
 */
function pns_theme_apply_featured_image_focus_to_cover_image_markup( $markup, $position ) {
	$processor = new WP_HTML_Tag_Processor( $markup );

	while ( $processor->next_tag( 'img' ) ) {
		if ( pns_theme_featured_image_focus_tag_has_class( $processor, 'wp-block-cover__image-background' ) ) {
			pns_theme_set_featured_image_focus_tag_position( $processor, 'object-position', $position );
			$processor->set_attribute( 'data-object-position', $position );

			return $processor->get_updated_html();
		}
	}

	$processor = new WP_HTML_Tag_Processor( $markup );

	while ( $processor->next_tag( 'div' ) ) {
		if ( pns_theme_featured_image_focus_tag_has_class( $processor, 'wp-block-cover__image-background' ) ) {
			pns_theme_set_featured_image_focus_tag_position( $processor, 'background-position', $position );

			return $processor->get_updated_html();
		}
	}

	return '';
}

/**
 * Check whether the current HTML tag has a class.
 *
 * @param WP_HTML_Tag_Processor $processor HTML processor.
 * @param string                $class_name Class name.
 * @return bool
 */
function pns_theme_featured_image_focus_tag_has_class( $processor, $class_name ) {
	$classes = $processor->get_attribute( 'class' );

	return is_string( $classes ) && in_array( $class_name, preg_split( '/\s+/', $classes ) ?: array(), true );
}

/**
 * Set one inline CSS position declaration on the current HTML tag.
 *
 * @param WP_HTML_Tag_Processor $processor HTML processor.
 * @param string                $property CSS property.
 * @param string                $position CSS position value.
 * @return void
 */
function pns_theme_set_featured_image_focus_tag_position( $processor, $property, $position ) {
	$style = $processor->get_attribute( 'style' );
	$style = is_string( $style ) ? $style : '';

	$processor->set_attribute(
		'style',
		pns_theme_set_featured_image_focus_style_declaration( $style, $property, $position )
	);
}

/**
 * Add or replace a CSS declaration in an inline style attribute.
 *
 * @param string $style Existing inline style.
 * @param string $property CSS property.
 * @param string $value CSS value.
 * @return string
 */
function pns_theme_set_featured_image_focus_style_declaration( $style, $property, $value ) {
	$property    = strtolower( $property );
	$declaration = $property . ':' . $value;
	$style       = trim( $style );
	$pattern     = '/(^|;)\s*' . preg_quote( $property, '/' ) . '\s*:[^;]*/i';

	if ( preg_match( $pattern, $style ) ) {
		$updated_style = preg_replace( $pattern, '$1' . $declaration, $style );

		return is_string( $updated_style ) ? $updated_style : $style;
	}

	return rtrim( $style, ';' ) . ( '' !== $style ? ';' : '' ) . $declaration . ';';
}
