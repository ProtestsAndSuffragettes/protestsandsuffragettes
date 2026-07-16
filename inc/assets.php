<?php
/**
 * Asset loading and theme setup.
 *
 * @package protestsandsuffragettes
 */

/**
 * Return a theme asset URI, preferring a compiled file when present.
 *
 * @param string      $source_filename Source asset path relative to the theme.
 * @param string|null $compiled_filename Optional compiled asset path.
 * @return string
 */
function pns_theme_theme_asset_uri( $source_filename, $compiled_filename = null ) {
	$compiled_filename = $compiled_filename ? $compiled_filename : preg_replace( '/\.css$/', '.min.css', $source_filename );
	$compiled_path     = get_stylesheet_directory() . '/' . $compiled_filename;

	if ( file_exists( $compiled_path ) ) {
		return get_stylesheet_directory_uri() . '/' . $compiled_filename;
	}

	return get_stylesheet_directory_uri() . '/' . $source_filename;
}

/**
 * Return a theme asset version, preferring file modification time in development.
 *
 * @param string      $source_filename Source asset path relative to the theme.
 * @param string|null $compiled_filename Optional compiled asset path.
 * @return string
 */
function pns_theme_theme_asset_version( $source_filename, $compiled_filename = null ) {
	$compiled_filename = $compiled_filename ? $compiled_filename : preg_replace( '/\.css$/', '.min.css', $source_filename );
	$compiled_path     = get_stylesheet_directory() . '/' . $compiled_filename;
	$source_path       = get_stylesheet_directory() . '/' . $source_filename;
	$asset_path        = file_exists( $compiled_path ) ? $compiled_path : $source_path;

	if ( file_exists( $asset_path ) ) {
		return (string) filemtime( $asset_path );
	}

	return wp_get_theme()->get( 'Version' );
}

/**
 * Return a theme asset path relative to the stylesheet directory.
 *
 * @param string      $source_filename Source asset path relative to the theme.
 * @param string|null $compiled_filename Optional compiled asset path.
 * @return string
 */
function pns_theme_theme_asset_relative_path( $source_filename, $compiled_filename = null ) {
	return str_replace(
		get_stylesheet_directory_uri() . '/',
		'',
		pns_theme_theme_asset_uri( $source_filename, $compiled_filename )
	);
}

/**
 * Determine whether development cache-busting should be aggressive.
 *
 * @return bool
 */
function pns_theme_dev_cache_busting_enabled() {
	return in_array( wp_get_environment_type(), array( 'local', 'development' ), true )
		|| ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		|| ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
}

/**
 * Set up PNS theme supports and editor styles.
 *
 * @return void
 */
function pns_theme_setup() {
	global $editor_styles;

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'align-wide' );

	if ( pns_theme_dev_cache_busting_enabled() && function_exists( 'wp_clean_theme_json_cache' ) ) {
		wp_clean_theme_json_cache();
	}

	$editor_styles = array_values(
		array_filter(
			(array) $editor_styles,
			function( $editor_style ) {
				return ! in_array(
					strtok( (string) $editor_style, '?' ),
					array(
						'editor-style.css',
						'./assets/css/editor-style.css',
						'styles/editor.css',
						'styles/dist/editor.min.css',
					),
					true
				);
			}
		)
	);

	add_editor_style(
		pns_theme_theme_asset_relative_path( 'styles/editor.css', 'styles/dist/editor.min.css' )
	);
}

add_action( 'after_setup_theme', 'pns_theme_setup' );

/**
 * Determine whether the current request is an Ecwid storefront request.
 *
 * The plugin's store-page predicate handles product, cart, category, and other
 * virtual Shop routes. The path fallback keeps the theme helper safe if Ecwid
 * is temporarily unavailable during local development.
 *
 * @return bool
 */
function pns_theme_is_ecwid_storefront_request() {
	$is_storefront = class_exists( 'Ecwid_Store_Page' ) && Ecwid_Store_Page::is_store_page();

	if ( ! $is_storefront && is_page( 'shop' ) ) {
		$is_storefront = true;
	}

	$request_path = wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );

	if ( ! $is_storefront && is_string( $request_path ) && 0 === strpos( trailingslashit( $request_path ), '/shop/' ) ) {
		$is_storefront = true;
	}

	return (bool) apply_filters( 'pns_theme_is_ecwid_storefront_request', $is_storefront );
}

/**
 * Determine whether the current post embeds a native Ecwid block or shortcode.
 *
 * Project-owned `ran/ecwid-shop-teaser` cards are intentionally absent here:
 * they are rendered on the server and link to Shop without needing Ecwid's
 * browser storefront runtime.
 *
 * @return bool
 */
function pns_theme_current_content_has_native_ecwid_embed() {
	$post = get_post();

	if ( ! $post instanceof WP_Post || '' === trim( $post->post_content ) ) {
		return false;
	}

	$content = $post->post_content;

	if ( class_exists( 'Ecwid_Gutenberg' ) && function_exists( 'has_block' ) ) {
		foreach ( Ecwid_Gutenberg::get_block_names() as $block_name ) {
			if ( has_block( $block_name, $post ) ) {
				return true;
			}
		}
	}

	$shortcode_names = array(
		'ecwid',
		'ec_store',
		'ecwid_productbrowser',
		'ecwid_minicart',
		'ecwid_search',
		'ecwid_categories',
		'ecwid_product',
		'ecwid_searchbox',
		'ec_product',
		'ecwid_script',
	);

	foreach ( $shortcode_names as $shortcode_name ) {
		if ( shortcode_exists( $shortcode_name ) && has_shortcode( $content, $shortcode_name ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Determine whether any active sidebar contains an Ecwid widget.
 *
 * A sidebar widget can render on pages beyond the current post, so this remains
 * intentionally conservative until that widget is removed or scoped itself.
 *
 * @return bool
 */
function pns_theme_has_active_ecwid_widget() {
	$sidebars = wp_get_sidebars_widgets();

	if ( ! is_array( $sidebars ) ) {
		return false;
	}

	foreach ( $sidebars as $sidebar_id => $widget_ids ) {
		if ( 'wp_inactive_widgets' === $sidebar_id || ! is_array( $widget_ids ) ) {
			continue;
		}

		foreach ( $widget_ids as $widget_id ) {
			if ( is_string( $widget_id ) && ( 0 === strpos( $widget_id, 'ecwid' ) || 0 === strpos( $widget_id, 'ec_' ) ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Decide whether this request needs Ecwid's native browser storefront runtime.
 *
 * The Ecwid plugin otherwise enqueues CSS, JavaScript, preconnects, a bootstrap
 * prefetch, and a Shop prerender site-wide. Keep that runtime on native Ecwid
 * surfaces only; use the filter for a future third-party integration that needs
 * it on another route.
 *
 * @return bool
 */
function pns_theme_should_load_native_ecwid_assets() {
	$should_load = pns_theme_is_ecwid_storefront_request()
		|| pns_theme_current_content_has_native_ecwid_embed()
		|| pns_theme_has_active_ecwid_widget()
		|| 'show_on_all_pages' === get_option( 'ec_show_floating_cart_widget' );

	return (bool) apply_filters( 'pns_theme_load_native_ecwid_assets', $should_load );
}

/**
 * Suppress Ecwid's global head hints and configuration on non-storefront routes.
 *
 * @return void
 */
function pns_theme_scope_native_ecwid_head_assets() {
	if ( pns_theme_should_load_native_ecwid_assets() ) {
		return;
	}

	if ( class_exists( 'Ecwid_HTML_Meta' ) ) {
		remove_action( 'wp', array( 'Ecwid_HTML_Meta', 'maybe_create' ) );
	}

	remove_action( 'wp_head', 'ecwid_print_inline_js_config' );
	remove_action( 'wp_head', 'ecwid_product_browser_url_in_head' );
}

add_action( 'wp', 'pns_theme_scope_native_ecwid_head_assets', 1 );

/**
 * Prevent the Ecwid plugin from registering its global frontend assets on
 * editorial routes. This runs before Ecwid's default-priority enqueue callback.
 *
 * @return void
 */
function pns_theme_scope_native_ecwid_enqueued_assets() {
	if ( ! pns_theme_should_load_native_ecwid_assets() ) {
		remove_action( 'wp_enqueue_scripts', 'ecwid_enqueue_frontend' );
	}
}

add_action( 'wp_enqueue_scripts', 'pns_theme_scope_native_ecwid_enqueued_assets', 1 );

/**
 * Enqueue PNS theme frontend assets.
 *
 * @return void
 */
function pns_theme_enqueue_styles() {
	$dependencies = array();

	if ( pns_theme_should_load_native_ecwid_assets() && ( wp_style_is( 'ecwid-css', 'registered' ) || wp_style_is( 'ecwid-css', 'enqueued' ) ) ) {
		$dependencies[] = 'ecwid-css';
	}

	wp_enqueue_style(
		'pns-theme-style',
		pns_theme_theme_asset_uri( 'styles/frontend.css', 'styles/dist/frontend.min.css' ),
		$dependencies,
		pns_theme_theme_asset_version( 'styles/frontend.css', 'styles/dist/frontend.min.css' )
	);

	wp_enqueue_script(
		'pns-theme-core-navigation-drawer',
		get_stylesheet_directory_uri() . '/scripts/core-navigation-drawer.js',
		array(),
		pns_theme_theme_asset_version( 'scripts/core-navigation-drawer.js' ),
		true
	);

	wp_enqueue_script(
		'pns-theme-header-search-drawer',
		get_stylesheet_directory_uri() . '/scripts/header-search-drawer.js',
		array(),
		pns_theme_theme_asset_version( 'scripts/header-search-drawer.js' ),
		true
	);

	if ( pns_theme_should_enqueue_ecwid_loading_feedback() ) {
		wp_enqueue_script(
			'pns-theme-ecwid-loading-feedback',
			get_stylesheet_directory_uri() . '/scripts/ecwid-loading-feedback.js',
			array(),
			pns_theme_theme_asset_version( 'scripts/ecwid-loading-feedback.js' ),
			false
		);
	}
}

add_action( 'wp_enqueue_scripts', 'pns_theme_enqueue_styles' );

/**
 * Keep the Ecwid pending-state helper ahead of the parser-blocking storefront.
 *
 * Static product cards can be interactive before Ecwid's remote runtime has
 * loaded. Jetpack Boost must not move this route-scoped head script behind
 * those cards or the first click has no observable pending state.
 *
 * @param string[] $handles Script handles excluded from Boost's move-to-footer pass.
 * @return string[]
 */
function pns_theme_exclude_early_ecwid_helper_from_boost( $handles ) {
	$handles[] = 'pns-theme-ecwid-loading-feedback';

	return array_values( array_unique( $handles ) );
}

add_filter(
	'jetpack_boost_render_blocking_js_exclude_handles',
	'pns_theme_exclude_early_ecwid_helper_from_boost'
);

/**
 * Keep Jetpack Boost's HTML-level JS deferral away from native Ecwid routes.
 *
 * Boost's render-blocking-JS output buffer entity-encodes operators inside
 * Ecwid's inline scripts and can move jQuery below an inline Ecwid consumer.
 * Other Boost modules remain enabled.
 *
 * @param mixed $should_defer Existing Boost decision.
 * @return mixed False on Ecwid routes; the existing decision elsewhere.
 */
function pns_theme_disable_boost_js_defer_for_ecwid( $should_defer ) {
	if (
		pns_theme_is_ecwid_storefront_request()
		|| pns_theme_current_content_has_native_ecwid_embed()
	) {
		return false;
	}

	return $should_defer;
}

add_filter(
	'jetpack_boost_should_defer_js',
	'pns_theme_disable_boost_js_defer_for_ecwid',
	100
);

/**
 * Remove Boost's setup-time shortcode script rewriter on native Ecwid routes.
 *
 * Boost registers this callback before its defer decision. Returning false from
 * `jetpack_boost_should_defer_js` stops output buffering but leaves the callback
 * active, so remove only that known callback before page content renders.
 */
function pns_theme_remove_boost_shortcode_rewriter_for_ecwid() {
	if (
		! pns_theme_is_ecwid_storefront_request()
		&& ! pns_theme_current_content_has_native_ecwid_embed()
	) {
		return;
	}

	$hook = $GLOBALS['wp_filter']['do_shortcode_tag'] ?? null;

	if ( ! $hook instanceof WP_Hook ) {
		return;
	}

	$boost_class = 'Automattic\\Jetpack_Boost\\Modules\\Optimizations\\Render_Blocking_JS\\Render_Blocking_JS';

	foreach ( $hook->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$function = $callback['function'] ?? null;

			if (
				is_array( $function )
				&& isset( $function[0], $function[1] )
				&& is_object( $function[0] )
				&& $function[0] instanceof $boost_class
				&& 'add_ignore_attribute' === $function[1]
			) {
				remove_filter( 'do_shortcode_tag', $function, $priority );
			}
		}
	}
}

add_action(
	'template_redirect',
	'pns_theme_remove_boost_shortcode_rewriter_for_ecwid'
);

/**
 * Decide whether the Ecwid loading-feedback helper belongs on this route.
 *
 * @return bool
 */
function pns_theme_should_enqueue_ecwid_loading_feedback() {
	return (bool) apply_filters(
		'pns_theme_enqueue_ecwid_loading_feedback',
		pns_theme_is_ecwid_storefront_request()
			|| pns_theme_current_content_has_native_ecwid_embed()
	);
}

/**
 * Register PNS theme editor block assets.
 *
 * @return void
 */
function pns_theme_register_editor_block_assets() {
	if ( wp_script_is( 'pns-theme-editor-blocks', 'registered' ) ) {
		return;
	}

	wp_register_script(
		'pns-theme-editor-blocks',
		get_stylesheet_directory_uri() . '/scripts/editor-blocks.js',
		array(
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-compose',
			'wp-core-data',
			'wp-data',
			'wp-dom-ready',
			'wp-edit-post',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
			'wp-plugins',
			'wp-rich-text',
		),
		pns_theme_theme_asset_version( 'scripts/editor-blocks.js' ),
		true
	);
}

/**
 * Enqueue PNS theme editor assets.
 *
 * @return void
 */
function pns_theme_enqueue_editor_assets() {
	pns_theme_register_editor_block_assets();

	wp_enqueue_script( 'pns-theme-editor-blocks' );
	wp_add_inline_script(
		'pns-theme-editor-blocks',
		'window.pnsThemeFeaturedImageFocus=' . wp_json_encode( pns_theme_get_featured_image_focus_editor_settings() ) . ';',
		'before'
	);
}

add_action( 'enqueue_block_editor_assets', 'pns_theme_enqueue_editor_assets' );

/**
 * Register standalone editor block categories.
 *
 * @param array<int,array<string,string>> $categories Existing block categories.
 * @return array<int,array<string,string>>
 */
function pns_theme_register_block_categories( $categories ) {
	$pns_category = array(
		'slug'  => 'pns-layout',
		'title' => __( 'PNS Blocks', 'protestsandsuffragettes' ),
	);
	$categories   = array_values(
		array_filter(
			$categories,
			function( $category ) {
				return 'pns-layout' !== ( $category['slug'] ?? '' );
			}
		)
	);

	if ( ! pns_theme_has_registered_pns_blocks() ) {
		return $categories;
	}

	array_unshift( $categories, $pns_category );

	return $categories;
}

add_filter( 'block_categories_all', 'pns_theme_register_block_categories' );

/**
 * Determine whether any registered block belongs in the PNS inserter group.
 *
 * @return bool
 */
function pns_theme_has_registered_pns_blocks() {
	if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
		return false;
	}

	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $block_name => $block_type ) {
		if ( pns_theme_is_pns_block_type( $block_name, $block_type->title ?? '' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether a block type should be grouped as project-owned PNS UI.
 *
 * @param string $block_type Block type name.
 * @param mixed  $title Block title.
 * @return bool
 */
function pns_theme_is_pns_block_type( $block_type, $title ) {
	return 0 === strpos( (string) $block_type, 'pns/' )
		|| 0 === strpos( (string) $block_type, 'ran/' )
		|| ( is_string( $title ) && preg_match( '/^PNS(?:\b|\s*-)/i', $title ) );
}

/**
 * Group project-owned PNS blocks together in the inserter.
 *
 * @param array<string,mixed> $args Block registration arguments.
 * @param string             $block_type Block type name.
 * @return array<string,mixed>
 */
function pns_theme_group_pns_blocks( $args, $block_type ) {
	$title = $args['title'] ?? '';

	if ( ! pns_theme_is_pns_block_type( $block_type, $title ) ) {
		return $args;
	}

	$args['category'] = 'pns-layout';

	return $args;
}

add_filter( 'register_block_type_args', 'pns_theme_group_pns_blocks', 10, 2 );

/**
 * Register standalone editor wrapper blocks.
 *
 * @return void
 */
function pns_theme_register_editor_blocks() {
	pns_theme_register_editor_block_assets();
}

add_action( 'init', 'pns_theme_register_editor_blocks' );
