<?php
/**
 * Theme lifecycle settings and cleanup.
 *
 * @package protestsandsuffragettes
 */

/**
 * Get the option name used for the uninstall policy.
 *
 * @return string
 */
function pns_theme_get_uninstall_policy_option_name() {
	return 'pns_theme_uninstall_policy';
}

/**
 * Sanitize the uninstall policy setting.
 *
 * @param string $value Raw option value.
 * @return string
 */
function pns_theme_sanitize_uninstall_policy( $value ) {
	return 'clean' === $value ? 'clean' : 'keep';
}

/**
 * Get the current uninstall policy.
 *
 * @return string
 */
function pns_theme_get_uninstall_policy() {
	return pns_theme_sanitize_uninstall_policy(
		(string) get_option( pns_theme_get_uninstall_policy_option_name(), 'keep' )
	);
}

/**
 * Register lifecycle settings.
 *
 * @return void
 */
function pns_theme_register_lifecycle_settings() {
	register_setting(
		'pns_theme_lifecycle',
		pns_theme_get_uninstall_policy_option_name(),
		array(
			'default'           => 'keep',
			'sanitize_callback' => 'pns_theme_sanitize_uninstall_policy',
			'type'              => 'string',
		)
	);
}

add_action( 'admin_init', 'pns_theme_register_lifecycle_settings' );

/**
 * Add the theme lifecycle settings page.
 *
 * @return void
 */
function pns_theme_add_lifecycle_settings_page() {
	add_theme_page(
		__( 'PNS Theme Setup', 'protestsandsuffragettes' ),
		__( 'PNS Theme Setup', 'protestsandsuffragettes' ),
		'manage_options',
		'pns-theme-theme-setup',
		'pns_theme_render_lifecycle_settings_page'
	);
}

add_action( 'admin_menu', 'pns_theme_add_lifecycle_settings_page' );

/**
 * Get the source logo stored in the theme.
 *
 * @return string
 */
function pns_theme_get_default_logo_asset_path() {
	return get_theme_file_path( 'assets/images/logo.png' );
}

/**
 * Check whether the current Site Logo option points at a usable attachment.
 *
 * @return bool
 */
function pns_theme_has_valid_site_logo() {
	$site_logo_id = (int) get_option( 'site_logo' );

	if ( 0 >= $site_logo_id ) {
		return false;
	}

	$attachment = get_post( $site_logo_id );

	return $attachment && 'attachment' === $attachment->post_type && wp_get_attachment_url( $site_logo_id );
}

/**
 * Find a Media Library attachment previously seeded from the theme logo.
 *
 * @return int
 */
function pns_theme_get_seeded_site_logo_attachment_id() {
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_pns_theme_seeded_site_logo',
			'meta_value'     => 'assets/images/logo.png',
		)
	);

	return empty( $attachments ) ? 0 : (int) $attachments[0];
}

/**
 * Seed the theme default logo into the Media Library and set it as Site Logo.
 *
 * Existing valid Site Logo selections are kept so admin UI changes persist.
 *
 * @return int|WP_Error Site Logo attachment ID, or WP_Error on failure.
 */
function pns_theme_seed_default_site_logo() {
	if ( pns_theme_has_valid_site_logo() ) {
		return (int) get_option( 'site_logo' );
	}

	$seeded_attachment_id = pns_theme_get_seeded_site_logo_attachment_id();

	if ( 0 < $seeded_attachment_id && wp_get_attachment_url( $seeded_attachment_id ) ) {
		update_option( 'site_logo', $seeded_attachment_id );
		return $seeded_attachment_id;
	}

	$source_path = pns_theme_get_default_logo_asset_path();

	if ( ! file_exists( $source_path ) ) {
		return new WP_Error( 'pns_default_logo_missing', sprintf( 'Default logo asset not found: %s', $source_path ) );
	}

	$logo_data = file_get_contents( $source_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.

	if ( false === $logo_data ) {
		return new WP_Error( 'pns_default_logo_unreadable', sprintf( 'Default logo asset could not be read: %s', $source_path ) );
	}

	$upload = wp_upload_bits( 'pns-logo.png', null, $logo_data );

	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'pns_default_logo_upload_failed', $upload['error'] );
	}

	$filetype = wp_check_filetype( $upload['file'] );

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => __( 'Protests and Suffragettes Logo', 'protestsandsuffragettes' ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );

	if ( ! is_wp_error( $metadata ) && is_array( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', __( 'Protests and Suffragettes', 'protestsandsuffragettes' ) );
	update_post_meta( $attachment_id, '_pns_theme_seeded_site_logo', 'assets/images/logo.png' );
	update_option( 'site_logo', $attachment_id );

	return $attachment_id;
}

/**
 * Seed the default Site Logo on theme activation.
 *
 * @return void
 */
function pns_theme_seed_default_site_logo_on_activation() {
	$result = pns_theme_seed_default_site_logo();

	if ( is_wp_error( $result ) ) {
		error_log( sprintf( 'PNS default logo activation seed failed: %s', $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Activation failures should be visible in local logs.
	}
}

add_action( 'after_switch_theme', 'pns_theme_seed_default_site_logo_on_activation' );

/**
 * Render the lifecycle settings page.
 *
 * @return void
 */
function pns_theme_render_lifecycle_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$option_name = pns_theme_get_uninstall_policy_option_name();
	$policy      = pns_theme_get_uninstall_policy();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'PNS Theme Setup', 'protestsandsuffragettes' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'pns_theme_lifecycle' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'When switching away from this theme', 'protestsandsuffragettes' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="<?php echo esc_attr( $option_name ); ?>" value="keep" <?php checked( 'keep', $policy ); ?>>
								<?php esc_html_e( 'Keep synced patterns/templates and seeded logo', 'protestsandsuffragettes' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Recommended. The synced patterns stay available and existing referenced blocks keep rendering.', 'protestsandsuffragettes' ); ?>
							</p>
							<br>
							<label>
								<input type="radio" name="<?php echo esc_attr( $option_name ); ?>" value="clean" <?php checked( 'clean', $policy ); ?>>
								<?php esc_html_e( 'Clean uninstall synced patterns/templates and seeded logo', 'protestsandsuffragettes' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Deletes the synced patterns listed in the theme fixture manifest and the seeded logo attachment when the theme is switched away. Existing content that still references those patterns may stop rendering.', 'protestsandsuffragettes' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Delete synced pattern records seeded by this theme.
 *
 * @return array<int,array<string,mixed>>|WP_Error
 */
function pns_theme_delete_seeded_synced_patterns() {
	$patterns = pns_theme_get_synced_pattern_manifest();

	if ( is_wp_error( $patterns ) ) {
		return $patterns;
	}

	$results = array();

	foreach ( $patterns as $pattern ) {
		if ( ! is_array( $pattern ) ) {
			continue;
		}

		$slug = isset( $pattern['slug'] ) && is_string( $pattern['slug'] ) ? $pattern['slug'] : '';

		if ( '' === $slug ) {
			continue;
		}

		$post = get_page_by_path( $slug, OBJECT, 'wp_block' );

		if ( ! $post ) {
			$results[] = array(
				'action' => 'missing',
				'slug'   => $slug,
			);
			continue;
		}

		$deleted = wp_delete_post( (int) $post->ID, true );

		$results[] = array(
			'action' => $deleted ? 'deleted' : 'failed',
			'id'     => (int) $post->ID,
			'slug'   => $slug,
		);
	}

	return $results;
}

/**
 * Delete the seeded Site Logo attachment, if it is still the active Site Logo.
 *
 * @return void
 */
function pns_theme_delete_seeded_site_logo() {
	$site_logo_id = (int) get_option( 'site_logo' );

	if ( 0 >= $site_logo_id ) {
		return;
	}

	if ( 'assets/images/logo.png' !== get_post_meta( $site_logo_id, '_pns_theme_seeded_site_logo', true ) ) {
		return;
	}

	delete_option( 'site_logo' );
	wp_delete_attachment( $site_logo_id, true );
}

/**
 * Clean up synced patterns when the theme is switched away, if requested.
 *
 * @return void
 */
function pns_theme_cleanup_synced_patterns_on_switch_theme() {
	if ( 'clean' !== pns_theme_get_uninstall_policy() ) {
		return;
	}

	$result = pns_theme_delete_seeded_synced_patterns();

	if ( is_wp_error( $result ) ) {
		error_log( sprintf( 'PNS synced pattern cleanup failed: %s', $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cleanup failures should be visible in local logs.
		return;
	}

	delete_option( pns_theme_get_uninstall_policy_option_name() );
	pns_theme_delete_seeded_site_logo();
}

add_action( 'switch_theme', 'pns_theme_cleanup_synced_patterns_on_switch_theme' );
