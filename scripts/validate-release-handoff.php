<?php
/**
 * Validate that a standalone-theme release preserves Administrator data.
 *
 * Run with WordPress loaded:
 * wp eval-file scripts/validate-release-handoff.php [probe|capture|verify] [snapshot-path]
 *
 * `probe` is the default. It requires the named navigation records to exist,
 * exercises the non-overwriting navigation seed path, and proves that the
 * header/footer references, navigation records, social-link setting, and
 * global-styles identity are unchanged. It never writes when its preconditions
 * are met. `capture` writes a hash-only local snapshot; `verify` compares a
 * later request against it. Neither mode exports navigation content or social
 * URLs.
 *
 * @package protestsandsuffragettes
 */

$pns_release_handoff_args = isset( $args ) && is_array( $args ) ? $args : array();
$pns_release_handoff_mode = $pns_release_handoff_args[0] ?? 'probe';
$pns_release_handoff_path = $pns_release_handoff_args[1] ?? get_theme_file_path( '.cache/release-handoff.json' );

if ( ! in_array( $pns_release_handoff_mode, array( 'probe', 'capture', 'verify' ), true ) ) {
	WP_CLI::error( 'Use probe, capture, or verify, optionally followed by a snapshot path.' );
}

if ( ! is_string( $pns_release_handoff_path ) || '' === trim( $pns_release_handoff_path ) ) {
	WP_CLI::error( 'The release-handoff snapshot path must be a non-empty string.' );
}

$pns_release_handoff_state = pns_theme_release_handoff_state();

if ( 'capture' === $pns_release_handoff_mode ) {
	pns_theme_release_handoff_write_snapshot( $pns_release_handoff_path, $pns_release_handoff_state );
	WP_CLI::success( sprintf( 'Release-handoff snapshot captured at %s.', $pns_release_handoff_path ) );
	return;
}

if ( 'verify' === $pns_release_handoff_mode ) {
	$expected_state = pns_theme_release_handoff_read_snapshot( $pns_release_handoff_path );
	pns_theme_release_handoff_assert_same_state( $expected_state, $pns_release_handoff_state );
	WP_CLI::success( 'Release-handoff snapshot matches current Administrator data.' );
	return;
}

pns_theme_release_handoff_assert_shell_contract( $pns_release_handoff_state );

$seed_result = pns_theme_seed_navigation_refs( false );

if ( is_wp_error( $seed_result ) ) {
	WP_CLI::error( sprintf( 'Non-overwriting navigation seed failed: %s', $seed_result->get_error_message() ) );
}

foreach ( $seed_result as $seeded_navigation ) {
	if ( 'kept' !== ( $seeded_navigation['action'] ?? '' ) ) {
		WP_CLI::error(
			sprintf(
				'Expected the release seed to keep existing navigation, but %s was %s.',
				(string) ( $seeded_navigation['slug'] ?? 'unknown' ),
				(string) ( $seeded_navigation['action'] ?? 'unknown' )
			)
		);
	}
}

pns_theme_release_handoff_assert_same_state(
	$pns_release_handoff_state,
	pns_theme_release_handoff_state()
);

WP_CLI::success( 'Release-handoff probe passed. Administrator data and code-owned shell references are unchanged.' );

/**
 * Collect only the identifiers, state, and hashes needed to detect release
 * mutations. The raw navigation and social-link values deliberately stay out
 * of the local snapshot.
 *
 * @return array<string,mixed>
 */
function pns_theme_release_handoff_state() {
	if ( 'protestsandsuffragettes' !== get_stylesheet() ) {
		WP_CLI::error( 'Release-handoff validation requires the PNS theme to be active.' );
	}

	$navigation = array();

	foreach ( pns_theme_release_handoff_navigation_slugs() as $slug ) {
		$post = pns_theme_release_handoff_find_post( $slug, 'wp_navigation' );

		$navigation[] = array(
			'slug'        => $slug,
			'state'       => $post instanceof WP_Post ? 'saved' : 'missing',
			'id'          => $post instanceof WP_Post ? (int) $post->ID : null,
			'post_status' => $post instanceof WP_Post ? $post->post_status : null,
			'db_sha256'   => $post instanceof WP_Post ? hash( 'sha256', rtrim( $post->post_content ) ) : null,
		);
	}

	$option_name = pns_theme_get_footer_social_links_option_name();
	$social_data = get_option( $option_name, null );

	return array(
		'schema_version' => 1,
		'active_theme'   => get_stylesheet(),
		'navigation'     => $navigation,
		'footer_social_links' => array(
			'state'        => null === $social_data ? 'defaults' : ( empty( $social_data ) ? 'empty' : 'saved' ),
			'value_sha256' => hash( 'sha256', serialize( $social_data ) ),
		),
		'global_styles' => pns_theme_release_handoff_global_styles_state(),
	);
}

/**
 * @return string[]
 */
function pns_theme_release_handoff_navigation_slugs() {
	return array(
		'pns-primary-navigation',
		'pns-banner-cta-navigation',
		'pns-footer-navigation',
	);
}

/**
 * @param string $slug Post slug.
 * @param string $post_type Post type.
 * @return WP_Post|null
 */
function pns_theme_release_handoff_find_post( $slug, $post_type ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => get_post_stati( array(), 'names' ),
			'name'           => $slug,
			'posts_per_page' => 1,
		)
	);

	return $posts[0] ?? null;
}

/**
 * @return array<string,mixed>
 */
function pns_theme_release_handoff_global_styles_state() {
	$posts = get_posts(
		array(
			'post_type'      => 'wp_global_styles',
			'post_status'    => get_post_stati( array(), 'names' ),
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'slug',
					'terms'    => array( get_stylesheet() ),
				),
			),
		)
	);

	$state = array();

	foreach ( $posts as $post ) {
		$state[] = array(
			'id'          => (int) $post->ID,
			'slug'        => $post->post_name,
			'post_status' => $post->post_status,
			'db_sha256'   => hash( 'sha256', rtrim( $post->post_content ) ),
		);
	}

	return $state;
}

/**
 * Confirm the file-backed shell still points at its Administrator data slots.
 *
 * @param array<string,mixed> $state Release-handoff state.
 * @return void
 */
function pns_theme_release_handoff_assert_shell_contract( $state ) {
	foreach ( $state['navigation'] as $navigation ) {
		if ( 'saved' !== $navigation['state'] ) {
			WP_CLI::error( sprintf( 'Required Administrator navigation %s is missing.', $navigation['slug'] ) );
		}
	}

	$header_path = get_theme_file_path( 'parts/header.html' );
	$footer_path = get_theme_file_path( 'parts/footer.html' );
	$header      = file_get_contents( $header_path );
	$footer      = file_get_contents( $footer_path );

	if ( false === $header || false === $footer ) {
		WP_CLI::error( 'Could not read the standalone header or footer shell.' );
	}

	foreach ( array( 'pns-primary-navigation', 'pns-banner-cta-navigation' ) as $slug ) {
		if ( ! str_contains( $header, '"pnsRefSlug":"' . $slug . '"' ) ) {
			WP_CLI::error( sprintf( 'Header shell does not reference %s by stable slug.', $slug ) );
		}
	}

	if ( ! str_contains( $footer, '"pnsRefSlug":"pns-footer-navigation"' ) ) {
		WP_CLI::error( 'Footer shell does not reference pns-footer-navigation by stable slug.' );
	}

	if ( 1 !== substr_count( $footer, 'wp:pns/footer-social-links' ) ) {
		WP_CLI::error( 'Footer shell must contain exactly one hidden pns/footer-social-links declaration.' );
	}

	if ( str_contains( $footer, 'wp:social-links' ) ) {
		WP_CLI::error( 'Footer shell must not contain direct Core Social Links data.' );
	}
}

/**
 * @param string               $path Snapshot file path.
 * @param array<string,mixed> $state Release-handoff state.
 * @return void
 */
function pns_theme_release_handoff_write_snapshot( $path, $state ) {
	$directory = dirname( $path );

	if ( ! is_dir( $directory ) && ! wp_mkdir_p( $directory ) ) {
		WP_CLI::error( sprintf( 'Could not create snapshot directory %s.', $directory ) );
	}

	$encoded = wp_json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

	if ( ! is_string( $encoded ) || false === file_put_contents( $path, $encoded . "\n" ) ) {
		WP_CLI::error( sprintf( 'Could not write release-handoff snapshot %s.', $path ) );
	}
}

/**
 * @param string $path Snapshot file path.
 * @return array<string,mixed>
 */
function pns_theme_release_handoff_read_snapshot( $path ) {
	$contents = file_get_contents( $path );

	if ( false === $contents ) {
		WP_CLI::error( sprintf( 'Could not read release-handoff snapshot %s.', $path ) );
	}

	$state = json_decode( $contents, true );

	if ( ! is_array( $state ) || 1 !== (int) ( $state['schema_version'] ?? 0 ) ) {
		WP_CLI::error( 'Release-handoff snapshot has an unsupported schema.' );
	}

	return $state;
}

/**
 * @param array<string,mixed> $expected Expected release-handoff state.
 * @param array<string,mixed> $actual Actual release-handoff state.
 * @return void
 */
function pns_theme_release_handoff_assert_same_state( $expected, $actual ) {
	if ( wp_json_encode( $expected ) !== wp_json_encode( $actual ) ) {
		WP_CLI::error( 'Administrator data changed during the release-handoff check.' );
	}
}
