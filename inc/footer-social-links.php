<?php
/**
 * Administrator-managed footer social links.
 *
 * The footer shell remains a file-backed theme part. This deliberately small
 * settings surface owns only the enabled service URLs, so an Administrator can
 * maintain them without opening Site Editor template parts to Editors.
 *
 * @package protestsandsuffragettes
 */

/**
 * Return the option name used for footer social-link data.
 *
 * @return string
 */
function pns_theme_get_footer_social_links_option_name() {
	return 'pns_footer_social_links';
}

/**
 * Return the fixed service catalog and first-install defaults.
 *
 * Service identifiers are code-owned because Core Social Links uses them to
 * select its icon and CSS class. The setting controls URLs and enabled links,
 * not arbitrary services, markup, or display order.
 *
 * @return array<string,array{label:string,url:string}>
 */
function pns_theme_get_footer_social_link_definitions() {
	return array(
		'patreon'  => array(
			'label' => __( 'Patreon', 'protestsandsuffragettes' ),
			'url'   => 'https://www.patreon.com/cw/protestsandsuffragettes',
		),
		'linkedin' => array(
			'label' => __( 'LinkedIn', 'protestsandsuffragettes' ),
			'url'   => 'https://www.linkedin.com/company/protestsandsuffragettes',
		),
		'facebook' => array(
			'label' => __( 'Facebook', 'protestsandsuffragettes' ),
			'url'   => 'https://www.facebook.com/ProtestsandSuffragettes/',
		),
		'instagram' => array(
			'label' => __( 'Instagram', 'protestsandsuffragettes' ),
			'url'   => 'https://www.instagram.com/protestsandsuffragettes/',
		),
		'youtube'  => array(
			'label' => __( 'YouTube', 'protestsandsuffragettes' ),
			'url'   => 'https://www.youtube.com/channel/UCjewk8RF7cgIY0fCK_A5y4w',
		),
		'bluesky'  => array(
			'label' => __( 'Bluesky', 'protestsandsuffragettes' ),
			'url'   => 'https://bsky.app/profile/scotsuffragette.bsky.social',
		),
		'threads'  => array(
			'label' => __( 'Threads', 'protestsandsuffragettes' ),
			'url'   => 'https://www.threads.net/@protestsandsuffragettes',
		),
	);
}

/**
 * Return the URLs used until an Administrator saves the setting.
 *
 * @return array<string,string>
 */
function pns_theme_get_default_footer_social_links() {
	$defaults = array();

	foreach ( pns_theme_get_footer_social_link_definitions() as $service => $definition ) {
		$defaults[ $service ] = $definition['url'];
	}

	return $defaults;
}

/**
 * Sanitize one footer social URL.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function pns_theme_sanitize_footer_social_link_url( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$url = esc_url_raw( trim( (string) $value ), array( 'https' ) );

	if ( '' === $url ) {
		return '';
	}

	$parts = wp_parse_url( $url );

	if (
		! is_array( $parts )
		|| empty( $parts['host'] )
		|| empty( $parts['scheme'] )
		|| 'https' !== strtolower( (string) $parts['scheme'] )
	) {
		return '';
	}

	return $url;
}

/**
 * Normalize stored social-link data against the fixed service catalog.
 *
 * @param mixed $links Stored or submitted links.
 * @return array<string,string>
 */
function pns_theme_normalize_footer_social_links( $links ) {
	if ( ! is_array( $links ) ) {
		return array();
	}

	$normalized = array();

	foreach ( pns_theme_get_footer_social_link_definitions() as $service => $definition ) {
		if ( ! array_key_exists( $service, $links ) ) {
			continue;
		}

		$url = pns_theme_sanitize_footer_social_link_url( $links[ $service ] );

		if ( '' !== $url ) {
			$normalized[ $service ] = $url;
		}
	}

	return $normalized;
}

/**
 * Return the currently configured footer social links.
 *
 * An absent option gets the versioned defaults; an intentionally empty saved
 * option means that no footer links should render.
 *
 * @return array<string,string>
 */
function pns_theme_get_footer_social_links() {
	$stored_links = get_option( pns_theme_get_footer_social_links_option_name(), null );

	if ( null === $stored_links ) {
		return pns_theme_get_default_footer_social_links();
	}

	return pns_theme_normalize_footer_social_links( $stored_links );
}

/**
 * Sanitize submitted footer social links.
 *
 * Invalid URLs retain their previous valid value and create an admin error,
 * preventing a typo from silently removing a live footer link. An empty field
 * is the explicit way to disable one service.
 *
 * @param mixed $links Submitted links.
 * @return array<string,string>
 */
function pns_theme_sanitize_footer_social_links( $links ) {
	$option_name = pns_theme_get_footer_social_links_option_name();
	$existing    = pns_theme_normalize_footer_social_links( get_option( $option_name, array() ) );
	$sanitized   = array();

	if ( ! is_array( $links ) ) {
		add_settings_error(
			$option_name,
			'pns_footer_social_links_invalid_value',
			__( 'Footer social links could not be saved because the submitted value was invalid.', 'protestsandsuffragettes' )
		);

		return $existing;
	}

	foreach ( pns_theme_get_footer_social_link_definitions() as $service => $definition ) {
		$value = array_key_exists( $service, $links ) && is_scalar( $links[ $service ] ) ? trim( (string) $links[ $service ] ) : '';

		if ( '' === $value ) {
			continue;
		}

		$url = pns_theme_sanitize_footer_social_link_url( $value );

		if ( '' === $url ) {
			if ( isset( $existing[ $service ] ) ) {
				$sanitized[ $service ] = $existing[ $service ];
			}

			add_settings_error(
				$option_name,
				'pns_footer_social_links_invalid_' . $service,
				sprintf(
					/* translators: %s: social network name. */
					__( '%s must use a complete HTTPS URL. Its previous value was kept.', 'protestsandsuffragettes' ),
					$definition['label']
				)
			);
			continue;
		}

		$sanitized[ $service ] = $url;
	}

	return $sanitized;
}

/**
 * Register the footer social-links setting.
 *
 * @return void
 */
function pns_theme_register_footer_social_links_settings() {
	register_setting(
		'pns_theme_footer_social_links',
		pns_theme_get_footer_social_links_option_name(),
		array(
			'default'           => pns_theme_get_default_footer_social_links(),
			'sanitize_callback' => 'pns_theme_sanitize_footer_social_links',
			'type'              => 'array',
		)
	);
}

add_action( 'admin_init', 'pns_theme_register_footer_social_links_settings' );

/**
 * Require Administrators for the Settings API submission boundary.
 *
 * @return string
 */
function pns_theme_footer_social_links_option_capability() {
	return 'manage_options';
}

add_filter( 'option_page_capability_pns_theme_footer_social_links', 'pns_theme_footer_social_links_option_capability' );

/**
 * Add the Administrator-only footer social-links screen.
 *
 * @return void
 */
function pns_theme_add_footer_social_links_settings_page() {
	add_theme_page(
		__( 'Footer Social Links', 'protestsandsuffragettes' ),
		__( 'Footer Social Links', 'protestsandsuffragettes' ),
		'manage_options',
		'pns-theme-footer-social-links',
		'pns_theme_render_footer_social_links_settings_page'
	);
}

add_action( 'admin_menu', 'pns_theme_add_footer_social_links_settings_page' );

/**
 * Render the footer social-links settings page.
 *
 * @return void
 */
function pns_theme_render_footer_social_links_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to manage footer social links.', 'protestsandsuffragettes' ) );
	}

	$option_name = pns_theme_get_footer_social_links_option_name();
	$links       = pns_theme_get_footer_social_links();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Footer Social Links', 'protestsandsuffragettes' ); ?></h1>
		<p><?php esc_html_e( 'These links appear in the code-owned footer. Leave a URL empty to hide that service; the display order and service list are fixed by the theme.', 'protestsandsuffragettes' ); ?></p>
		<?php settings_errors( $option_name ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'pns_theme_footer_social_links' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( pns_theme_get_footer_social_link_definitions() as $service => $definition ) : ?>
					<tr>
						<th scope="row">
							<label for="pns-footer-social-<?php echo esc_attr( $service ); ?>"><?php echo esc_html( $definition['label'] ); ?></label>
						</th>
						<td>
							<input
								class="regular-text code"
								id="pns-footer-social-<?php echo esc_attr( $service ); ?>"
								name="<?php echo esc_attr( $option_name . '[' . $service . ']' ); ?>"
								placeholder="<?php echo esc_attr( $definition['url'] ); ?>"
								type="url"
								value="<?php echo esc_attr( $links[ $service ] ?? '' ); ?>"
							>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Register the template-only footer social-links dynamic block.
 *
 * @return void
 */
function pns_theme_register_footer_social_links_block() {
	register_block_type( get_theme_file_path( 'blocks/footer-social-links' ) );
}

add_action( 'init', 'pns_theme_register_footer_social_links_block' );
