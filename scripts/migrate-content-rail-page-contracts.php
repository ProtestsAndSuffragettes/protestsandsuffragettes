<?php
/**
 * Migrate saved Contact and Shop blocks to the shared layout contracts.
 *
 * Usage:
 *   wp eval-file scripts/migrate-content-rail-page-contracts.php
 *   wp eval-file scripts/migrate-content-rail-page-contracts.php -- apply
 *
 * The dry run is read-only. Apply exports the original page content and
 * template assignment before updating the saved page blocks.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_content_rail_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pns_content_rail_apply = in_array( 'apply', $pns_content_rail_args, true ) || in_array( '--apply', $pns_content_rail_args, true );
$pns_content_rail_dir   = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/content-rail-page-db-backups';
$pns_content_rail_pages = array(
	'contact-us' => pns_content_rail_page_by_slug( 'contact-us' ),
	'shop'       => pns_content_rail_page_by_slug( 'shop' ),
);

foreach ( $pns_content_rail_pages as $pns_content_rail_slug => $pns_content_rail_page ) {
	if ( ! $pns_content_rail_page instanceof WP_Post ) {
		WP_CLI::error( sprintf( 'Expected published page "%s" was not found.', $pns_content_rail_slug ) );
	}
}

$pns_content_rail_contact_content = pns_content_rail_contact_content( $pns_content_rail_pages['contact-us']->post_content );
$pns_content_rail_shop_content    = pns_content_rail_shop_content( $pns_content_rail_pages['shop']->post_content );
$pns_content_rail_updates         = array(
	array(
		'page'              => $pns_content_rail_pages['contact-us'],
		'content'           => $pns_content_rail_contact_content,
		'template'          => 'page-light-surface',
		'expected_template' => 'page-light-surface',
	),
	array(
		'page'              => $pns_content_rail_pages['shop'],
		'content'           => $pns_content_rail_shop_content,
		'template'          => 'page-light-surface-wide-content',
		'expected_template' => 'page-light-surface-wide-content',
	),
);

foreach ( $pns_content_rail_updates as $pns_content_rail_update ) {
	$pns_content_rail_page     = $pns_content_rail_update['page'];
	$pns_content_rail_template = get_post_meta( $pns_content_rail_page->ID, '_wp_page_template', true );

	if ( $pns_content_rail_template !== $pns_content_rail_update['expected_template'] ) {
		WP_CLI::error(
			sprintf(
				'Page #%d (%s) uses "%s", not the expected "%s" template. Stop rather than overwrite a later editor change.',
				$pns_content_rail_page->ID,
				$pns_content_rail_page->post_name,
				$pns_content_rail_template,
				$pns_content_rail_update['expected_template']
			)
		);
	}

	WP_CLI::log(
		sprintf(
			'%s: %s content; template %s -> %s.',
			$pns_content_rail_page->post_name,
			$pns_content_rail_page->post_content === $pns_content_rail_update['content'] ? 'already matches' : 'will update',
			$pns_content_rail_template,
			$pns_content_rail_update['template']
		)
	);
}

if ( ! $pns_content_rail_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to migrate the saved page contracts.' );
	return;
}

if ( ! is_dir( $pns_content_rail_dir ) && ! wp_mkdir_p( $pns_content_rail_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create backup directory: %s', $pns_content_rail_dir ) );
}

$pns_content_rail_backup = array(
	'created_at_gmt' => gmdate( 'c' ),
	'pages'          => array(),
);

foreach ( $pns_content_rail_updates as $pns_content_rail_update ) {
	$pns_content_rail_page = $pns_content_rail_update['page'];
	$pns_content_rail_backup['pages'][] = array(
		'ID'                => $pns_content_rail_page->ID,
		'post_name'         => $pns_content_rail_page->post_name,
		'post_content'      => $pns_content_rail_page->post_content,
		'wp_page_template'  => get_post_meta( $pns_content_rail_page->ID, '_wp_page_template', true ),
	);
}

$pns_content_rail_backup_path = trailingslashit( $pns_content_rail_dir ) . gmdate( 'Ymd-His' ) . '-contact-shop.json';

if ( false === file_put_contents( $pns_content_rail_backup_path, wp_json_encode( $pns_content_rail_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) ) {
	WP_CLI::error( sprintf( 'Could not write backup: %s', $pns_content_rail_backup_path ) );
}

foreach ( $pns_content_rail_updates as $pns_content_rail_update ) {
	$pns_content_rail_page = $pns_content_rail_update['page'];
	$pns_content_rail_id   = wp_update_post(
		array(
			'ID'           => $pns_content_rail_page->ID,
			'post_content' => wp_slash( $pns_content_rail_update['content'] ),
		),
		true
	);

	if ( is_wp_error( $pns_content_rail_id ) ) {
		WP_CLI::error( $pns_content_rail_id->get_error_message() );
	}

	update_post_meta( $pns_content_rail_page->ID, '_wp_page_template', $pns_content_rail_update['template'] );
	clean_post_cache( $pns_content_rail_page->ID );
}

WP_CLI::success( sprintf( 'Migrated Contact and Shop. Rollback export: %s', $pns_content_rail_backup_path ) );

/**
 * @param string $slug Page slug.
 * @return WP_Post|null
 */
function pns_content_rail_page_by_slug( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	return $page instanceof WP_Post && 'publish' === $page->post_status ? $page : null;
}

/**
 * @param string $content Current Contact page block markup.
 * @return string
 */
function pns_content_rail_contact_content( $content ) {
	$blocks = parse_blocks( $content );
	$cover  = $blocks[0] ?? null;
	$form   = pns_content_rail_find_block( $blocks, 'jetpack/contact-form' );

	if ( ! is_array( $cover ) || 'core/cover' !== $cover['blockName'] || ! is_array( $form ) ) {
		WP_CLI::error( 'Contact page no longer has the expected Cover and Jetpack contact form blocks.' );
	}

	$is_page_hero = str_contains( $cover['attrs']['className'] ?? '', 'pns-page-hero' );
	$is_legacy_colour_contract = 'neutral-800' === ( $cover['attrs']['backgroundColor'] ?? '' )
		|| str_contains( $cover['innerHTML'], 'has-neutral-800-background-color' );

	/*
	 * Once the layout wrapper is in place, leave all Core Cover settings alone.
	 * In particular, its native overlay and text controls belong to editors.
	 */
	if ( $is_page_hero && ! $is_legacy_colour_contract ) {
		return $content;
	}

	/*
	 * Core blocks retain their saved HTML alongside their attributes. Build this
	 * small, deliberately owned page fixture as valid static block markup rather
	 * than changing only parsed attributes (which would leave stale HTML on the
	 * frontend until the editor resaves the page).
	 */
	return '<!-- wp:cover {"url":"http://localhost:10008/wp-content/uploads/2022/12/About_Header_v2-2-1024x495.png","id":1884,"dimRatio":0,"overlayColor":"brand-purple","isUserOverlayColor":true,"focalPoint":{"x":0.81,"y":0.37},"isDark":true,"sizeSlug":"large","layout":{"type":"default"},"align":"full","className":"pns-section pns-layout pns-page-hero pns-site-frame-panel","textColor":"neutral-0","contentPosition":"center left"} -->'
		. '<div class="wp-block-cover alignfull is-dark has-custom-content-position is-position-center-left pns-section pns-layout pns-page-hero pns-site-frame-panel has-neutral-0-color has-text-color"><img class="wp-block-cover__image-background wp-image-1884 size-large" alt="" src="http://localhost:10008/wp-content/uploads/2022/12/About_Header_v2-2-1024x495.png" style="object-position:81% 37%" data-object-fit="cover" data-object-position="81% 37%"/><span aria-hidden="true" class="wp-block-cover__background has-brand-purple-background-color has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"pns-hero__inner pns-section-inner","layout":{"type":"default"}} -->'
		. '<div class="wp-block-group pns-hero__inner pns-section-inner"><!-- wp:group {"className":"pns-copy-column pns-hero-copy","layout":{"type":"default"}} -->'
		. '<div class="wp-block-group pns-copy-column pns-hero-copy"><!-- wp:heading {"level":1,"textColor":"neutral-0"} -->'
		. '<h1 class="wp-block-heading has-neutral-0-color has-text-color">Contact Us</h1><!-- /wp:heading -->'
		. '<!-- wp:paragraph {"textColor":"neutral-0"} --><p class="has-neutral-0-color has-text-color">We are a friendly bunch!</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:group --></div></div><!-- /wp:cover -->'
		. '<!-- wp:group {"className":"pns-content-frame","layout":{"type":"default"}} -->'
		. '<div class="wp-block-group pns-content-frame">' . serialize_block( $form ) . '</div><!-- /wp:group -->';
}

/**
 * @param string $content Current Shop page block markup.
 * @return string
 */
function pns_content_rail_shop_content( $content ) {
	$blocks = parse_blocks( $content );
	$cover  = $blocks[0] ?? null;

	if ( ! is_array( $cover ) || 'core/cover' !== $cover['blockName'] ) {
		WP_CLI::error( 'Shop page no longer starts with the expected Cover block.' );
	}

	if ( str_contains( $cover['attrs']['className'] ?? '', 'pns-page-hero' ) ) {
		return $content;
	}

	$hero_copy = '';

	foreach ( $cover['innerBlocks'] as $inner_block ) {
		$hero_copy .= serialize_block( $inner_block );
	}

	/*
	 * As with Contact, emit both the block attributes and the static HTML. Core
	 * Cover stores them independently, so changing attributes alone would leave
	 * its old constrained inner markup in place on the frontend.
	 */
	$hero = '<!-- wp:cover {"url":"http://localhost:10008/wp-content/uploads/2022/08/Scotlands_Suffragette_Trumps@2x.png","id":1119,"dimRatio":50,"overlayColor":"deep-purple","isUserOverlayColor":true,"minHeight":529,"minHeightUnit":"px","sizeSlug":"full","style":{"elements":{"heading":{"color":{"text":"var:preset|color|neutral-0"}},"link":{"color":{"text":"var:preset|color|neutral-0"}}}},"textColor":"neutral-0","contentPosition":"center left","isDark":false,"align":"full","className":"pns-section pns-layout pns-page-hero pns-site-frame-panel","layout":{"type":"default"}} -->'
		. '<div class="wp-block-cover alignfull is-light has-custom-content-position is-position-center-left pns-section pns-layout pns-page-hero pns-site-frame-panel has-neutral-0-color has-text-color has-link-color" style="min-height:529px"><img class="wp-block-cover__image-background wp-image-1119 size-full" alt="" src="http://localhost:10008/wp-content/uploads/2022/08/Scotlands_Suffragette_Trumps@2x.png" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-deep-purple-background-color has-background-dim-50 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"pns-hero__inner pns-section-inner","layout":{"type":"default"}} -->'
		. '<div class="wp-block-group pns-hero__inner pns-section-inner"><!-- wp:group {"className":"pns-copy-column pns-hero-copy","layout":{"type":"default"}} -->'
		. '<div class="wp-block-group pns-copy-column pns-hero-copy">' . $hero_copy . '</div><!-- /wp:group --></div><!-- /wp:group --></div></div><!-- /wp:cover -->';

	return $hero . serialize_blocks( array_slice( $blocks, 1 ) );
}

/**
 * @param array<int,array<string,mixed>> $blocks Block tree to search.
 * @param string                         $block_name Required block name.
 * @return array<string,mixed>|null
 */
function pns_content_rail_find_block( $blocks, $block_name ) {
	foreach ( $blocks as $block ) {
		if ( $block_name === $block['blockName'] ) {
			return $block;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = pns_content_rail_find_block( $block['innerBlocks'], $block_name );

			if ( is_array( $found ) ) {
				return $found;
			}
		}
	}

	return null;
}
