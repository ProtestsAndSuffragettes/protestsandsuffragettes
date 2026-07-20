<?php
/**
 * Block pattern registration.
 *
 * @package protestsandsuffragettes
 */

/**
 * Register standalone block pattern categories.
 *
 * @return void
 */
function pns_theme_register_pattern_categories() {
	register_block_pattern_category(
		'pns-layout',
		array( 'label' => __( 'PNS Layouts', 'protestsandsuffragettes' ) )
	);

	register_block_pattern_category(
		'pns-quotes',
		array( 'label' => __( 'PNS Quotes', 'protestsandsuffragettes' ) )
	);

	register_block_pattern_category(
		'pns-herstories',
		array( 'label' => __( 'PNS Herstories', 'protestsandsuffragettes' ) )
	);
}

add_action( 'init', 'pns_theme_register_pattern_categories' );

/**
 * Get PNS theme code-backed pattern definitions.
 *
 * @return array<string,array<string,mixed>>
 */
function pns_theme_get_code_patterns() {
	return array(
		'pns/page-hero'                => array(
			'title'       => __( 'PNS - Page Hero', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width page hero with a replaceable background image and introductory copy.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'page', 'header', 'hero' ),
			'file'        => 'patterns/page-hero.php',
			'viewport'    => 1000,
		),
		'pns/basic-centred-content'    => array(
			'title'       => __( 'PNS - Basic Centred Content', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width centred content section with neutral starter headings and paragraphs.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'basic', 'centred', 'centered', 'content', 'text', 'layout' ),
			'file'        => 'patterns/basic-centred-content.php',
			'viewport'    => 1000,
		),
		'pns/split-section-image'      => array(
			'title'       => __( 'PNS - Split Section Image', 'protestsandsuffragettes' ),
			'description' => __( 'Two-column section with editable copy and a replaceable edge-aligned image.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'split', 'columns', 'image', 'text', 'media' ),
			'file'        => 'patterns/split-section-image.php',
			'inserter'    => false,
			'viewport'    => 1000,
		),
		'pns/split-section-slideshow'  => array(
			'title'       => __( 'PNS - Split Section Slideshow', 'protestsandsuffragettes' ),
			'description' => __( 'Two-column section with editable copy and an edge-aligned Jetpack slideshow.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'split', 'columns', 'slideshow', 'text', 'media' ),
			'file'        => 'patterns/split-section-slideshow.php',
			'inserter'    => false,
			'viewport'    => 1000,
		),
		'pns/text-only-section'        => array(
			'title'       => __( 'PNS - Text Only Section', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width section with a constrained copy column and editable starter text.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'text', 'copy', 'section', 'content' ),
			'file'        => 'patterns/text-only-section.php',
			'viewport'    => 1000,
		),
		'pns/entry-post-navigation' => array(
			'title'       => __( 'PNS - Entry Post Navigation', 'protestsandsuffragettes' ),
			'description' => __( 'Native previous/back/next navigation for standard posts.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'previous', 'next', 'post', 'navigation' ),
			'file'        => 'patterns/entry-post-navigation.php',
			'inserter'    => false,
			'viewport'    => 1000,
		),
		'pns/news-hero'             => array(
			'title'       => __( 'PNS - News Hero', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width Enhanced Cover hero for a news post. Set the same image as the post featured image and the cover poster.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'news', 'post', 'hero', 'cover' ),
			'file'        => 'patterns/news-hero.php',
			'viewport'    => 1000,
		),
		'pns/post-card'             => array(
			'title'       => __( 'PNS - Post Card', 'protestsandsuffragettes' ),
			'description' => __( 'Shared vertical card for post and Herstory query grids.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'post', 'card', 'archive', 'herstory', 'news' ),
			'file'        => 'patterns/post-card.php',
			'inserter'    => false,
			'viewport'    => 420,
		),
		'pns/post-card-horizontal'  => array(
			'title'       => __( 'PNS - Post Card Horizontal', 'protestsandsuffragettes' ),
			'description' => __( 'Shared horizontal card for native search result loops.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'post', 'card', 'search', 'horizontal' ),
			'file'        => 'patterns/post-card-horizontal.php',
			'inserter'    => false,
			'viewport'    => 900,
		),
		'pns/image-strip'           => array(
			'title'       => __( 'PNS - Image Strip', 'protestsandsuffragettes' ),
			'description' => __( 'Canonical full-width image strip for visual breaks between content sections.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'suffragette', 'herstory', 'images', 'gallery', 'full width' ),
			'file'        => 'patterns/image-strip.php',
			'viewport'    => 1000,
		),
		'pns/blockquote-cover'         => array(
			'title'       => __( 'PNS - Blockquote Cover', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width quote cover with image background and citation.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'quote', 'blockquote', 'cover' ),
			'file'        => 'patterns/blockquote-cover.php',
			'viewport'    => 1000,
		),
		'pns/blockquote-with-red-line' => array(
			'title'       => __( 'PNS - Blockquote With Red Line', 'protestsandsuffragettes' ),
			'description' => __( 'Full-width quote cover with red keyline image and citation.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-layout' ),
			'keywords'    => array( 'pns', 'quote', 'blockquote', 'red line', 'keyline' ),
			'file'        => 'patterns/blockquote-with-red-line.php',
			'viewport'    => 1000,
		),
		'pns/suffragette-facts'        => array(
			'title'       => __( 'PNS - Suffragette Facts', 'protestsandsuffragettes' ),
			'description' => __( 'Herstory facts section with editable bullet list and a replaceable image.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-herstories' ),
			'keywords'    => array( 'pns', 'suffragette', 'herstory', 'facts', 'list' ),
			'file'        => 'patterns/suffragette-facts.php',
			'viewport'    => 1000,
		),
		'pns/suffragette-hero'         => array(
			'title'       => __( 'PNS - Suffragette Hero', 'protestsandsuffragettes' ),
			'description' => __( 'Herstory page hero with a replaceable background image, title, intro copy, and editable active dates.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-herstories' ),
			'keywords'    => array( 'pns', 'suffragette', 'herstory', 'hero', 'profile' ),
			'file'        => 'patterns/suffragette-hero.php',
			'viewport'    => 1000,
		),
		'pns/entry-herstory-navigation' => array(
			'title'       => __( 'PNS - Entry Herstory Navigation', 'protestsandsuffragettes' ),
			'description' => __( 'Herstory previous/back/next navigation using the Herstories editorial order.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-herstories' ),
			'keywords'    => array( 'pns', 'previous', 'next', 'herstory', 'navigation' ),
			'file'        => 'patterns/entry-herstory-navigation.php',
			'viewport'    => 1000,
		),
		'pns/suffragette-stats'        => array(
			'title'       => __( 'PNS - Suffragette Stats', 'protestsandsuffragettes' ),
			'description' => __( 'Stats layout with editable labels, values, and portrait/image column.', 'protestsandsuffragettes' ),
			'categories'  => array( 'pns-herstories' ),
			'keywords'    => array( 'pns', 'suffragette', 'herstory', 'stats', 'statistics', 'numbers' ),
			'file'        => 'patterns/suffragette-stats.php',
			'viewport'    => 1000,
		),

	);
}

/**
 * Get the synced-pattern fixture directory.
 *
 * @return string
 */
function pns_theme_get_synced_pattern_fixture_dir() {
	return get_theme_file_path( 'synced-patterns' );
}

/**
 * Get synced-pattern fixture manifest entries.
 *
 * These remain native wp_block synced patterns so insertion and frontend
 * rendering use WordPress' supported referenced-pattern path.
 *
 * @return array<int,array<string,mixed>>|WP_Error
 */
function pns_theme_get_synced_pattern_manifest() {
	$manifest_path = pns_theme_get_synced_pattern_fixture_dir() . '/manifest.json';

	if ( ! file_exists( $manifest_path ) ) {
		return new WP_Error( 'pns_synced_pattern_manifest_missing', sprintf( 'Synced pattern manifest not found: %s', $manifest_path ) );
	}

	$manifest_json = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture file.

	if ( false === $manifest_json ) {
		return new WP_Error( 'pns_synced_pattern_manifest_unreadable', sprintf( 'Synced pattern manifest could not be read: %s', $manifest_path ) );
	}

	$patterns = json_decode( $manifest_json, true );

	if ( ! is_array( $patterns ) ) {
		return new WP_Error( 'pns_synced_pattern_manifest_invalid', sprintf( 'Synced pattern manifest JSON is invalid: %s', json_last_error_msg() ) );
	}

	return $patterns;
}

/**
 * Ensure a synced-pattern category term exists.
 *
 * @param array<string,mixed> $category Category data from the fixture manifest.
 * @return string|WP_Error Category slug on success.
 */
function pns_theme_ensure_synced_pattern_category_term( $category ) {
	$term_slug = isset( $category['slug'] ) && is_string( $category['slug'] ) ? $category['slug'] : '';
	$term_name = isset( $category['name'] ) && is_string( $category['name'] ) ? $category['name'] : '';

	if ( '' === $term_slug || '' === $term_name ) {
		return new WP_Error( 'pns_synced_pattern_category_invalid', 'Synced pattern category is missing a slug or name.' );
	}

	$term = term_exists( $term_slug, 'wp_pattern_category' );

	if ( 0 === $term || null === $term ) {
		$term = wp_insert_term(
			$term_name,
			'wp_pattern_category',
			array( 'slug' => $term_slug )
		);
	}

	if ( is_wp_error( $term ) ) {
		return $term;
	}

	return $term_slug;
}

/**
 * Seed native synced patterns from theme-owned fixtures.
 *
 * Existing synced patterns are kept unless $update_existing is true.
 *
 * @param bool $update_existing Whether existing wp_block content should be overwritten.
 * @return array<int,array<string,mixed>>|WP_Error
 */
function pns_theme_seed_synced_patterns( $update_existing = false ) {
	$patterns = pns_theme_get_synced_pattern_manifest();

	if ( is_wp_error( $patterns ) ) {
		return $patterns;
	}

	$results     = array();
	$fixture_dir = pns_theme_get_synced_pattern_fixture_dir();

	foreach ( $patterns as $pattern ) {
		if ( ! is_array( $pattern ) ) {
			return new WP_Error( 'pns_synced_pattern_manifest_entry_invalid', 'Synced pattern manifest entries must be objects.' );
		}

		$title    = isset( $pattern['title'] ) && is_string( $pattern['title'] ) ? $pattern['title'] : '';
		$slug     = isset( $pattern['slug'] ) && is_string( $pattern['slug'] ) ? $pattern['slug'] : '';
		$status   = isset( $pattern['status'] ) && is_string( $pattern['status'] ) ? $pattern['status'] : 'publish';
		$file     = isset( $pattern['file'] ) && is_string( $pattern['file'] ) ? $pattern['file'] : '';
		$category = isset( $pattern['category'] ) && is_array( $pattern['category'] ) ? $pattern['category'] : array();

		if ( '' === $title || '' === $slug || '' === $file ) {
			return new WP_Error( 'pns_synced_pattern_manifest_entry_incomplete', 'Synced pattern manifest entry is missing title, slug, or file.' );
		}

		$content_path = $fixture_dir . '/' . basename( $file );
		$content      = file_get_contents( $content_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture file.

		if ( false === $content ) {
			return new WP_Error( 'pns_synced_pattern_fixture_unreadable', sprintf( 'Synced pattern fixture could not be read: %s', $content_path ) );
		}

		$term_slug = pns_theme_ensure_synced_pattern_category_term( $category );

		if ( is_wp_error( $term_slug ) ) {
			return $term_slug;
		}

		$existing = get_page_by_path( $slug, OBJECT, 'wp_block' );
		$post     = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => $status,
			'post_type'    => 'wp_block',
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
			} elseif ( 'draft' === $status && 'draft' !== $existing->post_status && rtrim( $existing->post_content ) === rtrim( $content ) ) {
				$result = wp_update_post(
					array(
						'ID'          => $post_id,
						'post_status' => 'draft',
					),
					true
				);

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$action = 'status-updated';
			}
		} else {
			$result = wp_insert_post( $post, true );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$post_id = (int) $result;
			$action  = 'created';
		}

		wp_set_object_terms( $post_id, $term_slug, 'wp_pattern_category', false );

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
 * Seed missing synced patterns when the theme is activated.
 *
 * @return void
 */
function pns_theme_seed_synced_patterns_on_activation() {
	$result = pns_theme_seed_synced_patterns( false );

	if ( is_wp_error( $result ) ) {
		error_log( sprintf( 'PNS synced pattern activation seed failed: %s', $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Activation failures should be visible in local logs.
	}
}

add_action( 'after_switch_theme', 'pns_theme_seed_synced_patterns_on_activation' );

/**
 * Keep native synced patterns assigned to a PNS category and title.
 *
 * @return void
 */
function pns_theme_ensure_managed_synced_pattern_metadata() {
	$patterns = pns_theme_get_synced_pattern_manifest();

	if ( is_wp_error( $patterns ) ) {
		return;
	}

	foreach ( $patterns as $pattern ) {
		if ( ! is_array( $pattern ) ) {
			continue;
		}

		$slug     = isset( $pattern['slug'] ) && is_string( $pattern['slug'] ) ? $pattern['slug'] : '';
		$title    = isset( $pattern['title'] ) && is_string( $pattern['title'] ) ? $pattern['title'] : '';
		$category = isset( $pattern['category'] ) && is_array( $pattern['category'] ) ? $pattern['category'] : array();

		if ( '' === $slug ) {
			continue;
		}

		$post = get_page_by_path( $slug, OBJECT, 'wp_block' );

		if ( ! $post ) {
			continue;
		}

		if ( '' !== $title && $title !== $post->post_title ) {
			wp_update_post(
				array(
					'ID'         => (int) $post->ID,
					'post_title' => $title,
				)
			);
		}

		$term_slug = pns_theme_ensure_synced_pattern_category_term( $category );

		if ( is_wp_error( $term_slug ) ) {
			continue;
		}

		$current_terms = wp_get_object_terms( (int) $post->ID, 'wp_pattern_category', array( 'fields' => 'slugs' ) );

		if ( is_wp_error( $current_terms ) || in_array( $term_slug, $current_terms, true ) ) {
			continue;
		}

		wp_set_object_terms( (int) $post->ID, $term_slug, 'wp_pattern_category', false );
	}
}

add_action( 'init', 'pns_theme_ensure_managed_synced_pattern_metadata', 30 );

/**
 * Register PNS theme code patterns.
 *
 * @return void
 */
function pns_theme_register_code_patterns() {
	$patterns = pns_theme_get_code_patterns();

	foreach ( $patterns as $slug => $pattern ) {
		if ( WP_Block_Patterns_Registry::get_instance()->is_registered( $slug ) ) {
			unregister_block_pattern( $slug );
		}

		$path = get_theme_file_path( $pattern['file'] );

		if ( ! file_exists( $path ) ) {
			continue;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Pattern content is read from a local theme file.
		$content = file_get_contents( $path );

		if ( false === $content ) {
			continue;
		}

		$content = preg_replace( '/^<\?php.*?\?>\s*/s', '', $content );

		register_block_pattern(
			$slug,
			array(
				'title'         => $pattern['title'],
				'description'   => $pattern['description'],
				'categories'    => $pattern['categories'],
				'content'       => is_string( $content ) ? trim( $content ) : '',
				'inserter'      => $pattern['inserter'] ?? true,
				'keywords'      => $pattern['keywords'],
				'viewportWidth' => $pattern['viewport'],
			)
		);
	}
}

add_action( 'init', 'pns_theme_register_code_patterns', 20 );

/**
 * Disable the WordPress.org remote pattern directory for this editorial theme.
 *
 * The PNS theme provides an explicit PNS pattern library. Remote examples
 * can surface as uncategorised demo patterns in the editor and are not suitable
 * starter content for this site.
 *
 * @return false
 */
function pns_theme_disable_remote_block_patterns() {
	return false;
}

add_filter( 'should_load_remote_block_patterns', 'pns_theme_disable_remote_block_patterns' );

/**
 * Keep the editor inserter focused on approved starter patterns.
 *
 * Native synced patterns/reusable blocks are stored as wp_block posts and appear
 * under "My patterns"; those are content records, so this registry cleanup does
 * not hide or mutate them.
 *
 * @return void
 */
function pns_theme_enforce_blessed_pattern_library() {
	$allowed_patterns   = array_keys( pns_theme_get_code_patterns() );
	$allowed_patterns[] = 'ran-octopus-forms/contact-form';
	$allowed_patterns = apply_filters( 'pns_theme_allowed_block_patterns', $allowed_patterns );
	$allowed_patterns = is_array( $allowed_patterns ) ? array_values( array_filter( $allowed_patterns, 'is_string' ) ) : array();
	$registry         = WP_Block_Patterns_Registry::get_instance();

	foreach ( $registry->get_all_registered() as $pattern ) {
		$name = isset( $pattern['name'] ) && is_string( $pattern['name'] ) ? $pattern['name'] : '';

		if ( '' !== $name && ! in_array( $name, $allowed_patterns, true ) ) {
			unregister_block_pattern( $name );
		}
	}

	$allowed_categories = array( 'pns-layout', 'pns-quotes', 'pns-herstories', 'ran-octopus-forms', 'default' );
	$allowed_categories = apply_filters( 'pns_theme_allowed_block_pattern_categories', $allowed_categories );
	$allowed_categories = is_array( $allowed_categories ) ? array_values( array_filter( $allowed_categories, 'is_string' ) ) : array();
	$category_registry  = WP_Block_Pattern_Categories_Registry::get_instance();

	foreach ( $category_registry->get_all_registered() as $category ) {
		$name = isset( $category['name'] ) && is_string( $category['name'] ) ? $category['name'] : '';

		if ( '' !== $name && ! in_array( $name, $allowed_categories, true ) ) {
			unregister_block_pattern_category( $name );
		}
	}
}

add_action( 'init', 'pns_theme_enforce_blessed_pattern_library', 1030 );
