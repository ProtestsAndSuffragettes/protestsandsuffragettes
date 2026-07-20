<?php
/**
 * PNS site dependency contract and health checks.
 *
 * The entries below describe the plugin stack required by this PNS site. They
 * are not generic dependencies of every installation of the individual
 * plugins, which remain independently usable where their own documentation
 * says so.
 *
 * @package protestsandsuffragettes
 */

/**
 * Get the plugin dependency contract for this PNS site.
 *
 * @return array<string, array<string, string|bool>>
 */
function pns_theme_get_dependency_contract() {
	return array(
		'pns-blocks'            => array(
			'name'     => __( 'PNS Blocks', 'protestsandsuffragettes' ),
			'file'     => 'pns-blocks/pns-blocks.php',
			'purpose'  => __( 'PNS site block layouts, including split sections.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'pns-search-routing'    => array(
			'name'     => __( 'PNS Search Routing', 'protestsandsuffragettes' ),
			'file'     => 'pns-search-routing/pns-search-routing.php',
			'purpose'  => __( 'Canonical editorial search routes and search query scope.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'pns-herstories'        => array(
			'name'     => __( 'PNS Herstories', 'protestsandsuffragettes' ),
			'file'     => 'pns-herstories/pns-herstories.php',
			'purpose'  => __( 'Herstories content and presentation.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'ran-enhanced-cover'    => array(
			'name'     => __( 'RAN Enhanced Cover', 'protestsandsuffragettes' ),
			'file'     => 'ran-enhanced-cover/ran-enhanced-cover.php',
			'purpose'  => __( 'Enhanced cover blocks used by PNS content.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'ran-ecwid-shop-teaser' => array(
			'name'     => __( 'RAN Ecwid Shop Teaser', 'protestsandsuffragettes' ),
			'file'     => 'ran-ecwid-shop-teaser/ran-ecwid-shop-teaser.php',
			'purpose'  => __( 'The PNS shop teaser block.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'ran-emailoctopus-jetpack-forms' => array(
			'name'     => __( 'RAN EmailOctopus for Jetpack Forms', 'protestsandsuffragettes' ),
			'file'     => 'ran-emailoctopus-jetpack-forms/ran-emailoctopus-jetpack-forms.php',
			'purpose'  => __( 'EmailOctopus subscriptions from selected Jetpack forms.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'ran-turnstile-for-jetpack-forms' => array(
			'name'     => __( 'RAN Turnstile for Jetpack Forms', 'protestsandsuffragettes' ),
			'file'     => 'ran-turnstile-for-jetpack-forms/ran-turnstile-for-jetpack-forms.php',
			'purpose'  => __( 'Cloudflare Turnstile protection for selected Jetpack forms.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'jetpack'               => array(
			'name'     => __( 'Jetpack', 'protestsandsuffragettes' ),
			'file'     => 'jetpack/jetpack.php',
			'purpose'  => __( 'Consent controls and slideshow content used by the site.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'ecwid'                 => array(
			'name'     => __( 'Ecwid by Lightspeed Ecommerce Shopping Cart', 'protestsandsuffragettes' ),
			'file'     => 'ecwid-shopping-cart/ecwid-shopping-cart.php',
			'purpose'  => __( 'The native PNS shop integration.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'emailoctopus'          => array(
			'name'     => __( 'EmailOctopus', 'protestsandsuffragettes' ),
			'file'     => 'emailoctopus/emailoctopus.php',
			'purpose'  => __( 'A legacy hosted newsletter embed that remains in site content.', 'protestsandsuffragettes' ),
			'required' => true,
		),
		'jetpack-boost'         => array(
			'name'     => __( 'Jetpack Boost', 'protestsandsuffragettes' ),
			'file'     => 'jetpack-boost/jetpack-boost.php',
			'purpose'  => __( 'Optional performance tooling.', 'protestsandsuffragettes' ),
			'required' => false,
		),
	);
}

/**
 * Get the installed and active state of the PNS site dependency contract.
 *
 * @return array<string, array<string, string|bool>>
 */
function pns_theme_get_dependency_statuses() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins  = get_plugins();
	$statuses = array();

	foreach ( pns_theme_get_dependency_contract() as $id => $dependency ) {
		$is_installed = isset( $plugins[ $dependency['file'] ] );
		$is_active    = $is_installed && is_plugin_active( $dependency['file'] );

		$statuses[ $id ] = array_merge(
			$dependency,
			array(
				'id'        => $id,
				'installed' => $is_installed,
				'active'    => $is_active,
				'status'    => $is_active ? 'active' : ( $is_installed ? 'inactive' : 'missing' ),
			)
		);
	}

	return $statuses;
}

/**
 * Get required PNS site dependencies that are not active.
 *
 * @return array<string, array<string, string|bool>>
 */
function pns_theme_get_unavailable_required_dependencies() {
	return array_filter(
		pns_theme_get_dependency_statuses(),
		static function ( $dependency ) {
			return $dependency['required'] && ! $dependency['active'];
		}
	);
}

/**
 * Get unavailable dependency names grouped by their state.
 *
 * @param array<string, array<string, string|bool>> $dependencies Dependency status entries.
 * @return array<string, array<int, string>>
 */
function pns_theme_get_unavailable_dependency_names( $dependencies ) {
	$names = array(
		'missing'  => array(),
		'inactive' => array(),
	);

	foreach ( $dependencies as $dependency ) {
		if ( isset( $names[ $dependency['status'] ] ) ) {
			$names[ $dependency['status'] ][] = $dependency['name'];
		}
	}

	return $names;
}

/**
 * Get plain-language dependency state messages.
 *
 * @param array<string, array<int, string>> $names Dependency names grouped by state.
 * @return array<int, string>
 */
function pns_theme_get_dependency_state_messages( $names ) {
	$messages = array();

	if ( ! empty( $names['missing'] ) ) {
		$messages[] = sprintf(
			/* translators: %s: comma-separated plugin names. */
			_n( '%s is not installed.', '%s are not installed.', count( $names['missing'] ), 'protestsandsuffragettes' ),
			implode( ', ', $names['missing'] )
		);
	}

	if ( ! empty( $names['inactive'] ) ) {
		$messages[] = sprintf(
			/* translators: %s: comma-separated plugin names. */
			_n( '%s is installed but inactive.', '%s are installed but inactive.', count( $names['inactive'] ), 'protestsandsuffragettes' ),
			implode( ', ', $names['inactive'] )
		);
	}

	return $messages;
}

/**
 * Register the PNS dependency Site Health test.
 *
 * @param array<string, array<string, array<string, mixed>>> $tests Site Health tests.
 * @return array<string, array<string, array<string, mixed>>>
 */
function pns_theme_register_dependency_site_health_test( $tests ) {
	$tests['direct']['pns_theme_dependencies'] = array(
		'label' => __( 'PNS site dependencies', 'protestsandsuffragettes' ),
		'test'  => 'pns_theme_run_dependency_site_health_test',
	);

	return $tests;
}

add_filter( 'site_status_tests', 'pns_theme_register_dependency_site_health_test' );

/**
 * Run the PNS dependency Site Health test.
 *
 * @return array<string, mixed>
 */
function pns_theme_run_dependency_site_health_test() {
	$unavailable = pns_theme_get_unavailable_required_dependencies();
	$site_health = admin_url( 'site-health.php?tab=site-health' );

	if ( empty( $unavailable ) ) {
		return array(
			'label'       => __( 'PNS site dependencies are available', 'protestsandsuffragettes' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'PNS Site', 'protestsandsuffragettes' ),
				'color' => 'blue',
			),
			'description' => '<p>' . esc_html__( 'All plugins required by the current PNS site bundle are installed and active.', 'protestsandsuffragettes' ) . '</p>',
			'actions'     => '<p><a href="' . esc_url( $site_health ) . '">' . esc_html__( 'View Site Health', 'protestsandsuffragettes' ) . '</a></p>',
			'test'        => 'pns_theme_dependencies',
		);
	}

	$messages    = pns_theme_get_dependency_state_messages( pns_theme_get_unavailable_dependency_names( $unavailable ) );
	$description = '<p>' . esc_html__( 'The current PNS site bundle cannot provide all of its expected functionality until these dependencies are available.', 'protestsandsuffragettes' ) . '</p><ul>';

	foreach ( $messages as $message ) {
		$description .= '<li>' . esc_html( $message ) . '</li>';
	}

	$description .= '</ul>';

	return array(
		'label'       => __( 'PNS site dependencies are unavailable', 'protestsandsuffragettes' ),
		'status'      => 'critical',
		'badge'       => array(
			'label' => __( 'PNS Site', 'protestsandsuffragettes' ),
			'color' => 'blue',
		),
		'description' => $description,
		'actions'     => '<p><a href="' . esc_url( $site_health ) . '">' . esc_html__( 'Review PNS site dependencies', 'protestsandsuffragettes' ) . '</a></p>',
		'test'        => 'pns_theme_dependencies',
	);
}

/**
 * Show administrators a persistent warning when a required PNS dependency is unavailable.
 *
 * @return void
 */
function pns_theme_render_dependency_admin_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$unavailable = pns_theme_get_unavailable_required_dependencies();

	if ( empty( $unavailable ) ) {
		return;
	}

	$messages = pns_theme_get_dependency_state_messages( pns_theme_get_unavailable_dependency_names( $unavailable ) );
	?>
	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'PNS site setup needs attention.', 'protestsandsuffragettes' ); ?></strong>
			<?php echo esc_html( implode( ' ', $messages ) ); ?>
			<a href="<?php echo esc_url( admin_url( 'site-health.php?tab=site-health' ) ); ?>"><?php esc_html_e( 'Review PNS site dependencies.', 'protestsandsuffragettes' ); ?></a>
		</p>
	</div>
	<?php
}

add_action( 'admin_notices', 'pns_theme_render_dependency_admin_notice' );
