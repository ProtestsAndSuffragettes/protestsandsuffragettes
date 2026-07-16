<?php
/**
 * Validate retained render bridge contracts that are hard to see in screenshots.
 *
 * Run with:
 * wp eval-file app/public/wp-content/themes/protestsandsuffragettes/scripts/validate-retained-render-bridges.php
 *
 * @package protestsandsuffragettes
 */

$failures = array();

/**
 * Record a validation failure.
 *
 * @param string $message Failure message.
 * @return void
 */
function pns_retained_bridge_fail( $message ) {
	global $failures;

	$failures[] = $message;
}

/**
 * Assert a condition.
 *
 * @param bool   $condition Condition result.
 * @param string $message Failure message.
 * @return void
 */
function pns_retained_bridge_assert( $condition, $message ) {
	if ( ! $condition ) {
		pns_retained_bridge_fail( $message );
	}
}

$navigation_block = array(
	'blockName' => 'core/navigation',
	'attrs'     => array(
		'pnsRefSlug' => 'pns-primary-navigation',
	),
);

$resolved_navigation = pns_theme_resolve_template_ref_block_data( $navigation_block );

pns_retained_bridge_assert(
	0 < (int) ( $resolved_navigation['attrs']['ref'] ?? 0 ),
	'Primary Navigation pnsRefSlug did not resolve to a numeric ref.'
);

$synced_block = array(
	'blockName' => 'core/block',
	'attrs'     => array(
		'pnsRefSlug' => 'contact-form',
	),
);

$resolved_synced_block = pns_theme_resolve_template_ref_block_data( $synced_block );

pns_retained_bridge_assert(
	0 < (int) ( $resolved_synced_block['attrs']['ref'] ?? 0 ),
	'Synced block pnsRefSlug did not resolve to a numeric ref.'
);

$missing_navigation_ref_block = array(
	'blockName' => 'core/navigation',
	'attrs'     => array(
		'pnsRefSlug' => 'missing-navigation-fixture',
	),
);

$missing_navigation_ref_result = pns_theme_resolve_template_ref_block_data( $missing_navigation_ref_block );

pns_retained_bridge_assert(
	! isset( $missing_navigation_ref_result['attrs']['ref'] ),
	'Missing Navigation pnsRefSlug unexpectedly resolved to a ref.'
);

$missing_synced_ref_block = array(
	'blockName' => 'core/block',
	'attrs'     => array(
		'pnsRefSlug' => 'missing-synced-pattern-fixture',
	),
);

$missing_synced_ref_result = pns_theme_resolve_template_ref_block_data( $missing_synced_ref_block );

pns_retained_bridge_assert(
	! isset( $missing_synced_ref_result['attrs']['ref'] ),
	'Missing synced-pattern pnsRefSlug unexpectedly resolved to a ref.'
);

pns_retained_bridge_assert(
	! pns_theme_template_ref_fallback_is_valid(
		(object) array(
			'post_type' => 'wp_navigation',
			'post_name' => 'wrong-navigation-slug',
		),
		'wp_navigation',
		'pns-primary-navigation'
	),
	'Mismatched fallback post_name should not be accepted for a template ref.'
);

foreach ( pns_theme_get_template_ref_fallbacks() as $fallback_post_type => $fallback_refs ) {
	foreach ( $fallback_refs as $fallback_slug => $fallback_id ) {
		pns_retained_bridge_assert(
			pns_theme_template_ref_fallback_is_valid( get_post( $fallback_id ), $fallback_post_type, $fallback_slug ),
			sprintf( 'Fallback ID %d for %s:%s no longer matches its stable slug.', $fallback_id, $fallback_post_type, $fallback_slug )
		);
	}
}

$preserved_allowed_blocks = pns_theme_blacklist_blocks(
	array( 'core/paragraph', 'core/calendar', 'plugin/example' )
);

pns_retained_bridge_assert(
	array( 'core/paragraph', 'plugin/example' ) === $preserved_allowed_blocks,
	'Block blacklist should preserve an existing allowlist while subtracting unsupported blocks.'
);

pns_retained_bridge_assert(
	false === pns_theme_blacklist_blocks( false ),
	'Block blacklist should preserve a false existing block policy.'
);

$primary_overlay_block = array(
	'blockName' => 'core/navigation',
	'attrs'     => array(
		'pnsRefSlug' => 'pns-primary-navigation',
		'overlay'    => 'always',
		'icon'       => 'menu',
		'hasIcon'    => true,
	),
);

$primary_overlay_result = pns_theme_strip_navigation_overlay_template_block_data( $primary_overlay_block );

pns_retained_bridge_assert(
	! isset( $primary_overlay_result['attrs']['overlay'], $primary_overlay_result['attrs']['icon'], $primary_overlay_result['attrs']['hasIcon'] ),
	'Primary Navigation overlay attrs were not stripped.'
);

$generic_overlay_block = array(
	'blockName' => 'core/navigation',
	'attrs'     => array(
		'overlay' => 'always',
		'icon'    => 'menu',
		'hasIcon' => true,
	),
);

$generic_overlay_result = pns_theme_strip_navigation_overlay_template_block_data( $generic_overlay_block );

pns_retained_bridge_assert(
	'always' === ( $generic_overlay_result['attrs']['overlay'] ?? '' ) && 'menu' === ( $generic_overlay_result['attrs']['icon'] ?? '' ) && true === ( $generic_overlay_result['attrs']['hasIcon'] ?? null ),
	'Generic Navigation overlay attrs should survive primary-only cleanup.'
);

$cta_overlay_block = array(
	'blockName' => 'core/navigation',
	'attrs'     => array(
		'className'              => 'pns-cross-site-banner-cta',
		'overlayMenu'            => 'always',
		'overlayBackgroundColor' => 'brand-purple',
		'overlayTextColor'       => 'neutral-0',
	),
);

$cta_overlay_result = pns_theme_normalize_cta_navigation_block_data( $cta_overlay_block );

pns_retained_bridge_assert(
	'never' === ( $cta_overlay_result['attrs']['overlayMenu'] ?? '' )
	&& ! isset( $cta_overlay_result['attrs']['overlayBackgroundColor'], $cta_overlay_result['attrs']['overlayTextColor'] ),
	'CTA Navigation overlay attrs were not normalized.'
);

$primary_navigation_post = get_page_by_path( 'pns-primary-navigation', OBJECT, 'wp_navigation' );

pns_retained_bridge_assert(
	$primary_navigation_post instanceof WP_Post,
	'Primary Navigation record pns-primary-navigation was not found.'
);

if ( $primary_navigation_post instanceof WP_Post ) {
	$primary_navigation_content = $primary_navigation_post->post_content;
	$primary_navigation_blocks  = parse_blocks( $primary_navigation_content );

	pns_retained_bridge_assert(
		3 === substr_count( $primary_navigation_content, 'pns-navigation-submenu-overview' ),
		'Primary Navigation should contain exactly three saved submenu overview links.'
	);

	foreach ( array( 'About', 'Herstories', 'Shenanigans' ) as $overview_label ) {
		$matching_submenus = array_filter(
			$primary_navigation_blocks,
			static function ( $block ) use ( $overview_label ) {
				return 'core/navigation-submenu' === ( $block['blockName'] ?? '' )
					&& $overview_label === ( $block['attrs']['label'] ?? '' );
			}
		);
		$submenu          = reset( $matching_submenus );
		$first_child      = is_array( $submenu ) ? ( $submenu['innerBlocks'][0] ?? null ) : null;
		$first_child_attr = is_array( $first_child ) ? ( $first_child['attrs'] ?? array() ) : array();

		pns_retained_bridge_assert(
			'core/navigation-link' === ( $first_child['blockName'] ?? '' )
			&& $overview_label === ( $first_child_attr['label'] ?? '' )
			&& str_contains( $first_child_attr['className'] ?? '', 'pns-navigation-submenu-overview' ),
			sprintf( 'Primary Navigation submenu "%s" should start with a saved overview link.', $overview_label )
		);
	}
}

add_filter( 'pns_theme_enable_template_reveal', '__return_false' );

pns_retained_bridge_assert(
	false === pns_theme_template_reveal_enabled(),
	'Template reveal rollback filter did not disable template reveal.'
);

remove_filter( 'pns_theme_enable_template_reveal', '__return_false' );

if ( ! empty( $failures ) ) {
	foreach ( $failures as $failure ) {
		WP_CLI::warning( $failure );
	}

	WP_CLI::error( sprintf( 'Retained render bridge validation failed with %d failure(s).', count( $failures ) ) );
}

WP_CLI::success( 'Retained render bridge validation passed.' );
