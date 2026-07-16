<?php
/**
 * Primary and mobile navigation behavior.
 *
 * @package protestsandsuffragettes
 */

define( 'PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG', 'pns-primary-navigation' );

/**
 * Apply core Navigation style controls to theme-owned navigation CSS variables.
 *
 * Core serializes block gap values on the Navigation block. The standalone
 * header CSS consumes custom properties instead, so this bridges editor-authored
 * spacing into the frontend without page-specific CSS.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_apply_navigation_block_support_variables( $block_content, $block ) {
	if ( 'core/navigation' !== ( $block['blockName'] ?? '' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$attrs  = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$is_cta = isset( $attrs['className'] ) && is_string( $attrs['className'] ) && str_contains( $attrs['className'], 'pns-cross-site-banner-cta' );
	$gap    = pns_theme_get_navigation_spacing_value( $attrs['style']['spacing']['blockGap'] ?? '', ! $is_cta );

	if ( '' === $gap ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( 'nav' ) ) {
		return $block_content;
	}

	$style = trim( (string) $processor->get_attribute( 'style' ) );
	$style = '' !== $style ? rtrim( $style, ';' ) . '; ' : '';
	$style .= '--pns--navigation--gap: ' . $gap;

	if ( $is_cta ) {
		$style .= '; --pns--cross-site-banner-cta-gap: ' . $gap;
	}

	$processor->set_attribute( 'style', $style );

	return $processor->get_updated_html();
}

add_filter( 'render_block_core/navigation', 'pns_theme_apply_navigation_block_support_variables', 9, 2 );

/**
 * Get the current primary navigation record ID.
 *
 * @return int Navigation ID, or 0 when unavailable.
 */
function pns_theme_get_primary_navigation_id() {
	return pns_theme_resolve_template_ref_id( 'wp_navigation', PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG );
}

/**
 * Add a stable class to the primary navigation block.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_add_primary_navigation_class( $block_content, $block ) {
	if ( 'core/navigation' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$navigation_id = (int) ( $block['attrs']['ref'] ?? 0 );
	$slug          = isset( $block['attrs']['pnsRefSlug'] ) && is_string( $block['attrs']['pnsRefSlug'] ) ? $block['attrs']['pnsRefSlug'] : '';
	$primary_id    = pns_theme_get_primary_navigation_id();

	if ( PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG !== $slug && $primary_id !== $navigation_id ) {
		return $block_content;
	}

	if ( 0 >= $navigation_id ) {
		$navigation_id = $primary_id;
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( 'nav' ) ) {
		return $block_content;
	}

	$processor->add_class( 'pns-primary-navigation' );

	return $processor->get_updated_html();
}

add_filter( 'render_block_core/navigation', 'pns_theme_add_primary_navigation_class', 10, 2 );

/**
 * Render the desktop-only header search disclosure beneath the primary navigation.
 *
 * The visible mobile search entry point remains a normal saved Navigation link.
 * This native GET form deliberately sends visitors to the existing WordPress
 * search results route without introducing a parallel search implementation.
 *
 * @return string
 */
function pns_theme_render_header_search_drawer() {
	$panel_id = 'pns-site-search-panel';
	$input_id = 'pns-site-search-input';

	return sprintf(
		'<div class="pns-header-search__drawer" id="%1$s" hidden><form class="pns-header-search__form wp-block-search__button-inside wp-block-search__text-button wp-block-search" role="search" method="get" action="%3$s"><label class="wp-block-search__label screen-reader-text" for="%4$s">%5$s</label><div class="pns-header-search__controls wp-block-search__inside-wrapper"><input id="%4$s" class="pns-header-search__input wp-block-search__input" placeholder="%6$s" type="search" name="s" required><button aria-label="%2$s" class="wp-block-search__button wp-element-button" type="submit">%2$s</button></div></form></div>',
		esc_attr( $panel_id ),
		esc_html__( 'Search', 'protestsandsuffragettes' ),
		esc_url( home_url( '/' ) ),
		esc_attr( $input_id ),
		esc_html__( 'Search the site', 'protestsandsuffragettes' ),
		esc_attr__( 'know your herstory...', 'protestsandsuffragettes' )
	);
}

/**
 * Append the desktop search disclosure to the rendered primary Navigation.
 *
 * @param string $block_content Rendered Navigation block markup.
 * @param array  $block Parsed block data.
 * @return string
 */
function pns_theme_append_header_search_drawer( $block_content, $block ) {
	if ( 'core/navigation' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$navigation_id = (int) ( $block['attrs']['ref'] ?? 0 );
	$slug          = isset( $block['attrs']['pnsRefSlug'] ) && is_string( $block['attrs']['pnsRefSlug'] ) ? $block['attrs']['pnsRefSlug'] : '';
	$primary_id    = pns_theme_get_primary_navigation_id();

	if ( PNS_STANDALONE_PRIMARY_NAVIGATION_SLUG !== $slug && $primary_id !== $navigation_id ) {
		return $block_content;
	}

	return sprintf(
		'<div class="pns-header__navigation-search pns-header-search">%1$s%2$s</div>',
		$block_content,
		pns_theme_render_header_search_drawer()
	);
}

add_filter( 'render_block_core/navigation', 'pns_theme_append_header_search_drawer', 11, 2 );

/**
 * Get the navigation fixture directory.
 *
 * @return string
 */
function pns_theme_get_navigation_fixture_dir() {
	return get_theme_file_path( 'navigation' );
}

/**
 * Get navigation fixture manifest entries.
 *
 * @return array<int,array<string,mixed>>|WP_Error
 */
function pns_theme_get_navigation_manifest() {
	$manifest_path = pns_theme_get_navigation_fixture_dir() . '/manifest.json';

	if ( ! file_exists( $manifest_path ) ) {
		return new WP_Error( 'pns_navigation_manifest_missing', sprintf( 'Navigation manifest not found: %s', $manifest_path ) );
	}

	$manifest_json = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture file.

	if ( false === $manifest_json ) {
		return new WP_Error( 'pns_navigation_manifest_unreadable', sprintf( 'Navigation manifest could not be read: %s', $manifest_path ) );
	}

	$navigation_records = json_decode( $manifest_json, true );

	if ( ! is_array( $navigation_records ) ) {
		return new WP_Error( 'pns_navigation_manifest_invalid', sprintf( 'Navigation manifest JSON is invalid: %s', json_last_error_msg() ) );
	}

	return $navigation_records;
}

/**
 * Seed native navigation records from theme-owned defaults.
 *
 * Existing records are kept unless $update_existing is true so editor-owned
 * navigation remains editable.
 *
 * @param bool $update_existing Whether existing wp_navigation content should be overwritten.
 * @return array<int,array<string,mixed>>|WP_Error
 */
function pns_theme_seed_navigation_refs( $update_existing = false ) {
	$navigation_records = pns_theme_get_navigation_manifest();

	if ( is_wp_error( $navigation_records ) ) {
		return $navigation_records;
	}

	$results     = array();
	$fixture_dir = pns_theme_get_navigation_fixture_dir();

	foreach ( $navigation_records as $record ) {
		if ( ! is_array( $record ) ) {
			return new WP_Error( 'pns_navigation_manifest_entry_invalid', 'Navigation manifest entries must be objects.' );
		}

		$title  = isset( $record['title'] ) && is_string( $record['title'] ) ? $record['title'] : '';
		$slug   = isset( $record['slug'] ) && is_string( $record['slug'] ) ? $record['slug'] : '';
		$status = isset( $record['status'] ) && is_string( $record['status'] ) ? $record['status'] : 'publish';
		$file   = isset( $record['file'] ) && is_string( $record['file'] ) ? $record['file'] : '';

		if ( '' === $title || '' === $slug || '' === $file ) {
			return new WP_Error( 'pns_navigation_manifest_entry_incomplete', 'Navigation manifest entry is missing title, slug, or file.' );
		}

		$content_path = $fixture_dir . '/' . basename( $file );
		$content      = file_get_contents( $content_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture file.

		if ( false === $content ) {
			return new WP_Error( 'pns_navigation_fixture_unreadable', sprintf( 'Navigation fixture could not be read: %s', $content_path ) );
		}

		$existing = get_page_by_path( $slug, OBJECT, 'wp_navigation' );
		$post     = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => $status,
			'post_type'    => 'wp_navigation',
			'post_content' => rtrim( $content ) . "\n",
		);

		if ( $existing ) {
			$post_id = (int) $existing->ID;
			$action  = 'kept';

			if ( $update_existing ) {
				$post['ID'] = $post_id;
				$result     = wp_update_post( $post, true );

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$action = 'updated';
			}
		} else {
			$result = wp_insert_post( $post, true );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$post_id = (int) $result;
			$action  = 'created';
		}

		$results[] = array(
			'action'     => $action,
			'current_id' => $post_id,
			'title'      => $title,
			'slug'       => $slug,
		);
	}

	return $results;
}

/**
 * Seed missing navigation records when the theme is activated.
 *
 * @return void
 */
function pns_theme_seed_navigation_refs_on_activation() {
	$result = pns_theme_seed_navigation_refs( false );

	if ( is_wp_error( $result ) ) {
		error_log( sprintf( 'PNS navigation activation seed failed: %s', $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Activation failures should be visible in local logs.
	}
}

add_action( 'after_switch_theme', 'pns_theme_seed_navigation_refs_on_activation' );

/**
 * Resolve a core Navigation spacing value to a safe CSS value.
 *
 * @param mixed $spacing Spacing value.
 * @param bool  $preserve_zero Whether a zero value is an intentional override.
 * @return string CSS spacing value.
 */
function pns_theme_get_navigation_spacing_value( $spacing, $preserve_zero = false ) {
	if ( is_array( $spacing ) ) {
		$row_gap    = pns_theme_sanitize_navigation_css_value( 'row-gap', $spacing['top'] ?? '' );
		$column_gap = pns_theme_sanitize_navigation_css_value( 'column-gap', $spacing['left'] ?? '' );

		if ( ! $preserve_zero && pns_theme_is_zero_navigation_spacing( $row_gap ) ) {
			$row_gap = '';
		}

		if ( ! $preserve_zero && pns_theme_is_zero_navigation_spacing( $column_gap ) ) {
			$column_gap = '';
		}

		if ( '' !== $row_gap && '' !== $column_gap ) {
			return $row_gap . ' ' . $column_gap;
		}

		return '' !== $column_gap ? $column_gap : $row_gap;
	}

	$gap = pns_theme_sanitize_navigation_css_value( 'gap', $spacing );

	return ! $preserve_zero && pns_theme_is_zero_navigation_spacing( $gap ) ? '' : $gap;
}

/**
 * Determine whether a spacing value is only zero values.
 *
 * @param string $value CSS spacing value.
 * @return bool
 */
function pns_theme_is_zero_navigation_spacing( $value ) {
	if ( '' === $value ) {
		return false;
	}

	return (bool) preg_match( '/^0(?:px|rem|em|%)?(?:\s+0(?:px|rem|em|%)?)*$/', trim( $value ) );
}

/**
 * Build a WordPress preset CSS custom property reference.
 *
 * @param string $preset_type Preset type.
 * @param string $preset_slug Preset slug.
 * @return string CSS variable reference.
 */
function pns_theme_get_preset_css_variable( $preset_type, $preset_slug ) {
	return 'var(--wp--preset--' . sanitize_key( $preset_type ) . '--' . sanitize_title( $preset_slug ) . ')';
}

/**
 * Sanitize a CSS declaration value for a known property.
 *
 * @param string $property CSS property used for WordPress safe CSS filtering.
 * @param mixed  $value Raw CSS value.
 * @return string Safe CSS value.
 */
function pns_theme_sanitize_navigation_css_value( $property, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return '';
	}

	$value = trim( $value );

	if ( str_starts_with( $value, 'var:preset|' ) ) {
		$parts = explode( '|', $value );

		if ( 3 === count( $parts ) ) {
			return pns_theme_get_preset_css_variable( $parts[1], $parts[2] );
		}
	}

	$filtered = safecss_filter_attr( sanitize_key( $property ) . ': ' . $value );

	if ( preg_match( '/^[a-z-]+\\s*:\\s*(.+)$/', $filtered, $matches ) ) {
		return rtrim( trim( $matches[1] ), ';' );
	}

	return '';
}
