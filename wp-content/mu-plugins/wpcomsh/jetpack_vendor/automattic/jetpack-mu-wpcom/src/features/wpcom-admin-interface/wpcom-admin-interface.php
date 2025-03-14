<?php
/**
 * Additional wpcom_admin_interface option on settings.
 *
 * @package automattic/jetpack-mu-wpcom
 */

use Automattic\Jetpack\Connection\Client;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection;
use Automattic\Jetpack\Jetpack_Mu_Wpcom;
use Automattic\Jetpack\Masterbar\Admin_Menu;
use Automattic\Jetpack\Status;
use Automattic\Jetpack\Status\Host;

/**
 * Add the Admin Interface Style setting on the General settings page.
 * This setting allows users to switch between the classic WP-Admin interface and the WordPress.com legacy dashboard.
 * The setting is stored in the wpcom_admin_interface option.
 * The setting is displayed only if the has the wp-admin interface selected.
 */
function wpcomsh_wpcom_admin_interface_settings_field() {
	add_settings_field( 'wpcom_admin_interface', __( 'Admin Interface Style', 'jetpack-mu-wpcom' ), 'wpcom_admin_interface_display', 'general', 'default' );

	register_setting( 'general', 'wpcom_admin_interface', array( 'sanitize_callback' => 'esc_attr' ) );
}

/**
 * Display the wpcom_admin_interface setting on the General settings page.
 */
function wpcom_admin_interface_display() {
	remove_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option', 10 );
	$value = get_option( 'wpcom_admin_interface' );
	add_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option', 10 );

	echo '<fieldset>';
	echo '<label><input type="radio" name="wpcom_admin_interface" value="wp-admin" ' . checked( 'wp-admin', $value, false ) . '/> <span>' . esc_html__( 'Classic style', 'jetpack-mu-wpcom' ) . '</span></label><p>' . esc_html__( 'Use WP-Admin to manage your site.', 'jetpack-mu-wpcom' ) . '</p><br>';
	echo '<label><input type="radio" name="wpcom_admin_interface" value="calypso" ' . checked( 'calypso', $value, false ) . '/> <span>' . esc_html__( 'Default style', 'jetpack-mu-wpcom' ) . '</span></label><p>' . esc_html__( 'Use WordPress.com’s native dashboard to manage your site.', 'jetpack-mu-wpcom' ) . '</p><br>';
	echo '</fieldset>';
}
add_action( 'admin_init', 'wpcomsh_wpcom_admin_interface_settings_field' );

/**
 * Track the wpcom_admin_interface_changed event.
 *
 * @param string $value The new value.
 * @return void
 */
function wpcom_admin_interface_track_changed_event( $value ) {
	$event_name = 'wpcom_admin_interface_changed';
	$properties = array( 'interface' => $value );
	if ( function_exists( 'wpcomsh_record_tracks_event' ) ) {
		wpcomsh_record_tracks_event( $event_name, $properties );
	} else {
		require_lib( 'tracks/client' );
		tracks_record_event( get_current_user_id(), $event_name, $properties );
	}
}

/**
 * Check if we should disable the calypso links.
 *
 * @param string $screen The given screen.
 *
 * @return bool
 */
function wpcom_should_disable_calypso_links( string $screen ): bool {
	if ( get_option( 'wpcom_admin_interface' ) === 'wp-admin' || ! ( new Host() )->is_wpcom_platform() ) {
		return true;
	}

	if ( ( new Host() )->is_wpcom_simple() && function_exists( '\Automattic\Jetpack\Dashboard_Customizations\show_unified_nav' ) && ! \Automattic\Jetpack\Dashboard_Customizations\show_unified_nav() ) {
		return true;
	}

	if ( ( new Host() )->is_woa_site() && ! apply_filters( 'jetpack_load_admin_menu_class', true ) ) {
		return true;
	}

	return Admin_Menu::get_instance()->get_preferred_view( $screen ) === Admin_Menu::CLASSIC_VIEW;
}

/**
 * Update the wpcom_admin_interface option on wpcom as it's the persistent data.
 * Also implements the redirect from WP Admin to Calypso when the interface option
 * is changed.
 *
 * @access private
 * @since 4.20.0
 *
 * @param string $new_value The new settings value.
 * @param string $old_value The old settings value.
 * @return string The value to update.
 */
function wpcom_admin_interface_pre_update_option( $new_value, $old_value ) {
	if ( $new_value === $old_value ) {
		return $new_value;
	}

	if ( ! class_exists( 'Jetpack_Options' ) || ! class_exists( 'Automattic\Jetpack\Connection\Client' ) || ! class_exists( 'Automattic\Jetpack\Status\Host' ) ) {
		return $new_value;
	}

	global $pagenow;
	$on_wp_admin_options_page = isset( $pagenow ) && 'options.php' === $pagenow;

	if ( $on_wp_admin_options_page ) {
		wpcom_admin_interface_track_changed_event( $new_value );
	}

	if ( ! ( new Automattic\Jetpack\Status\Host() )->is_wpcom_simple() ) {
		$blog_id = Jetpack_Options::get_option( 'id' );
		Automattic\Jetpack\Connection\Client::wpcom_json_api_request_as_user(
			"/sites/$blog_id/hosting/admin-interface",
			'v2',
			array( 'method' => 'POST' ),
			array( 'interface' => $new_value )
		);
	}

	// We want to redirect to Calypso if the user has switched interface options to 'calypso'
	// Unfortunately we need to run this side-effect in the option updating filter because
	// the general settings page doesn't give us a good point to hook into the form submission.
	if ( 'calypso' === $new_value && $on_wp_admin_options_page ) {
		add_filter(
			'wp_redirect',
			/**
			 * Filters the existing redirect in wp-admin/options.php so we go to Calypso instead
			 * of to a GET version of the WP Admin general options page.
			 */
			function ( $location ) {
				$updated_settings_page = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
				if ( $location === $updated_settings_page && ! wpcom_is_duplicate_views_experiment_enabled() ) {
					return 'https://wordpress.com/settings/general/' . wpcom_get_site_slug();
				} else {
					return $location;
				}
			}
		);
	}

	return $new_value;
}
add_filter( 'pre_update_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_update_option', 10, 2 );

const WPCOM_DUPLICATED_VIEW = array(
	'edit.php',
	'edit.php?post_type=page',
	'edit.php?post_type=post', // Alias for posts. It's used for the post filters (published, draft, sticky, etc).
	'edit.php?post_type=jetpack-portfolio',
	'edit.php?post_type=jetpack-testimonial',
	'edit-comments.php',
	'edit-tags.php?taxonomy=category',
	'edit-tags.php?taxonomy=post_tag',
	'options-general.php',
	'options-writing.php',
	'options-reading.php',
	'options-discussion.php',
	'upload.php',
);

/**
 * Get the current screen section.
 *
 * Temporary function copied from Base_Admin_Menu.
 *
 * return string
 */
function wpcom_admin_get_current_screen() {
	// phpcs:disable WordPress.Security.NonceVerification
	global $pagenow;
	$screen = isset( $_REQUEST['screen'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['screen'] ) ) : $pagenow;
	if ( isset( $_GET['post_type'] ) ) {
		$screen = add_query_arg( 'post_type', sanitize_text_field( wp_unslash( $_GET['post_type'] ) ), $screen );
	}
	if ( isset( $_GET['taxonomy'] ) ) {
		$screen = add_query_arg( 'taxonomy', sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ), $screen );
	}
	if ( isset( $_GET['page'] ) ) {
		$screen = add_query_arg( 'page', sanitize_text_field( wp_unslash( $_GET['page'] ) ), $screen );
	}
	return $screen;
	// phpcs:enable WordPress.Security.NonceVerification
}

/**
 * Override the wpcom_admin_interface option with experiment variation.
 *
 * @param mixed $default_value The value to return instead of the option value.
 *
 * @return string Filtered wpcom_admin_interface option.
 */
function wpcom_admin_interface_pre_get_option( $default_value ) {
	$current_screen = wpcom_admin_get_current_screen();

	if ( in_array( $current_screen, WPCOM_DUPLICATED_VIEW, true ) && wpcom_is_duplicate_views_experiment_enabled() ) {
		return 'wp-admin';
	}

	return $default_value;
}

/**
 * Change the Admin menu links to WP-Admin for specific sections.
 *
 * @param array $value Preferred views.
 *
 * @return array Filtered preferred views.
 */
function wpcom_admin_get_user_option_jetpack( $value ) {
	if ( ! wpcom_is_duplicate_views_experiment_enabled() ) {
		return $value;
	}

	if ( ! is_array( $value ) ) {
		$value = array();
	}

	foreach ( WPCOM_DUPLICATED_VIEW as $path ) {
		$value[ $path ] = Automattic\Jetpack\Masterbar\Base_Admin_Menu::CLASSIC_VIEW;
	}

	return $value;
}

add_filter( 'get_user_option_jetpack_admin_menu_preferred_views', 'wpcom_admin_get_user_option_jetpack' );
add_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option', 10 );

add_action(
	'admin_menu',
	function () {
		remove_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option' );
	},
	PHP_INT_MIN
);

add_action(
	'admin_menu',
	function () {
		add_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option', 10 );
	},
	PHP_INT_MAX
);
/**
 * Hides the "View" switcher on WP Admin screens enforced by the "Remove duplicate views" experiment.
 */
function wpcom_duplicate_views_hide_view_switcher() {
	$admin_menu_class = wpcom_get_custom_admin_menu_class();
	if ( $admin_menu_class ) {
		$admin_menu = $admin_menu_class::get_instance();

		$current_screen = wpcom_admin_get_current_screen();
		if ( in_array( $current_screen, WPCOM_DUPLICATED_VIEW, true ) && wpcom_is_duplicate_views_experiment_enabled() ) {
			remove_filter( 'in_admin_header', array( $admin_menu, 'add_dashboard_switcher' ) );
		}
	}
}
add_action( 'admin_init', 'wpcom_duplicate_views_hide_view_switcher' );

/**
 * Determines whether the admin interface has been recently changed by checking the presence of the `admin-interface-changed` query param.
 *
 * @return bool
 */
function wpcom_has_admin_interface_changed() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	return ( sanitize_key( wp_unslash( $_GET['admin-interface-changed'] ?? 'false' ) ) ) === 'true';
}

/**
 * Determine if the intro tour for the classic admin interface should be shown.
 *
 * @return bool
 */
function wpcom_should_show_classic_tour() {
	if ( get_option( 'wpcom_admin_interface' ) !== 'wp-admin' ) {
		return false;
	}

	$tour_completed_option = get_option( 'wpcom_classic_tour_completed' );
	$is_tour_in_progress   = $tour_completed_option === '0';
	$is_tour_completed     = $tour_completed_option === '1';

	if ( $is_tour_completed ) {
		return false;
	}

	if ( ! wpcom_has_admin_interface_changed() && ! $is_tour_in_progress ) {
		return false;
	}

	// Don't show the tour to non-administrators since it highlights features that are unavailable to them.
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	global $pagenow;
	return $pagenow === 'index.php';
}

/**
 * Render the HTML template needed by the classic tour script.
 */
function wpcom_render_classic_tour_template() {
	if ( ! wpcom_should_show_classic_tour() ) {
		return;
	}
	?>
	<template id="wpcom-classic-tour-step-template">
		<div class="wpcom-classic-tour-step">
			<button class="button button-secondary" data-action="dismiss" title="<?php esc_attr_e( 'Dismiss', 'jetpack-mu-wpcom' ); ?>"><span class="dashicons dashicons-no-alt"></span></button>
			<h3>{{title}}</h3>
			<p>{{description}}</p>
			<div class="wpcom-classic-tour-step-footer">
				<div class="wpcom-classic-tour-step-current"><?php esc_html_e( 'Step {{currentStep}} of {{totalSteps}}', 'jetpack-mu-wpcom' ); ?></div>
				<button data-action="prev" class="button button-secondary"><?php esc_html_e( 'Previous', 'jetpack-mu-wpcom' ); ?></button>
				<button data-action="next" class="button button-primary"><?php esc_html_e( 'Next', 'jetpack-mu-wpcom' ); ?></button>
				<button data-action="dismiss" class="button button-primary"><?php esc_html_e( 'Got it!', 'jetpack-mu-wpcom' ); ?></button>
			</div>
		</div>
	</template>
	<?php
}
add_action( 'admin_footer', 'wpcom_render_classic_tour_template' );

/**
 * Enqueue the scripts that show an intro tour with some educational tooltips for folks who turn the classic admin interface on.
 */
function wpcom_classic_tour_enqueue_scripts() {
	if ( ! wpcom_should_show_classic_tour() ) {
		return;
	}

	update_option( 'wpcom_classic_tour_completed', '0' );

	wp_enqueue_style(
		'wpcom-classic-tour',
		plugins_url( 'classic-tour.css', __FILE__ ),
		array(),
		Jetpack_Mu_Wpcom::PACKAGE_VERSION
	);

	wp_enqueue_script(
		'wpcom-classic-tour',
		plugins_url( 'classic-tour.js', __FILE__ ),
		array(),
		Jetpack_Mu_Wpcom::PACKAGE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$data = array(
		'dismissNonce' => wp_create_nonce( 'wpcom_dismiss_classic_tour' ),
		'steps'        => array(
			array(
				'target'      => '.toplevel_page_wpcom-hosting-menu',
				'placement'   => 'right-bottom',
				'title'       => esc_html__( 'Upgrades is now Hosting', 'jetpack-mu-wpcom' ),
				'description' => esc_html__( 'The Hosting menu contains the My Home page and all items from the Upgrades menu, including Plans, Domains, Emails, Purchases, and more.', 'jetpack-mu-wpcom' ),
				'position'    => 'fixed',
			),
			array(
				'target'      => '.wpcom_site_management_widget__site-actions',
				'placement'   => 'bottom',
				'title'       => esc_html__( 'Hosting overview', 'jetpack-mu-wpcom' ),
				'description' => esc_html__( 'Access the new site management panel and all developer tools such as hosting configuration, GitHub deployments, metrics, PHP logs, and server logs.', 'jetpack-mu-wpcom' ),
				'position'    => 'absolute',
			),
			array(
				'target'      => '.wp-admin-bar-all-sites',
				'placement'   => 'bottom-right',
				'title'       => esc_html__( 'All your sites', 'jetpack-mu-wpcom' ),
				'description' => esc_html__( 'Click here to access your sites, domains, Reader, account settings, and more.', 'jetpack-mu-wpcom' ),
				'position'    => 'fixed',
			),
		),
	);

	wp_add_inline_script(
		'wpcom-site-menu',
		'window.wpcomClassicTour = ' . wp_json_encode( $data ) . ';'
	);
}
add_action( 'admin_enqueue_scripts', 'wpcom_classic_tour_enqueue_scripts' );

/**
 * Handles the AJAX requests to dismiss the classic tour.
 */
function wpcom_dismiss_classic_tour() {
	check_ajax_referer( 'wpcom_dismiss_classic_tour' );
	update_option( 'wpcom_classic_tour_completed', '1' );
	wp_die();
}
add_action( 'wp_ajax_wpcom_dismiss_classic_tour', 'wpcom_dismiss_classic_tour' );

/**
 * Displays a success notice in the dashboard after changing the admin interface.
 */
function wpcom_show_admin_interface_notice() {
	if ( ! wpcom_has_admin_interface_changed() ) {
		return;
	}

	global $pagenow;
	if ( $pagenow !== 'index.php' ) {
		return;
	}

	wp_admin_notice(
		__( 'Admin interface style changed.', 'jetpack-mu-wpcom' ),
		array(
			'type'        => 'success',
			'dismissible' => true,
		)
	);
}
add_action( 'admin_notices', 'wpcom_show_admin_interface_notice' );

/**
 * Force a cache purge.
 *
 * @return void
 */
function wpcom_rdv_reset_cache_if_needed() {
	if ( ! get_user_option( 'rdv_force_cache_is_deleted', get_current_user_id() ) ) {
		update_user_option( get_current_user_id(), 'rdv_force_cache_is_deleted', true, true );
		delete_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, true );
	}
}

/**
 * Check if the might be an a11n on Atomic sites.
 *
 * @return bool
 */
function wpcom_atomic_rdv_maybe_is_a11n() {
	$is_proxy_atomic    = defined( 'AT_PROXIED_REQUEST' ) && AT_PROXIED_REQUEST;
	$is_support_session = WPCOMSH_Support_Session_Detect::is_probably_support_session();
	$admin_menu_is_a11n = isset( $_GET['admin_menu_is_a11n'] ) && function_exists( 'wpcomsh_is_admin_menu_api_request' ) && wpcomsh_is_admin_menu_api_request();

	/**
	 * This handles two contexts: Calypso and WP-Admin.
	 *
	 * Calypso: WPCOM admin-menu API endpoint mapper sends a "admin_menu_is_a11n" param for a12s. If the param exists, then we'll switch to treatment.
	 * WP-Admin: We check if the user is proxied and if it's not in a support session.
	 */
	return $admin_menu_is_a11n || ( $is_proxy_atomic && ! $is_support_session );
}

/**
 * Option to force and cache the Remove duplicate Views experiment assigned variation.
 */
const RDV_EXPERIMENT_FORCE_ASSIGN_OPTION = 'remove_duplicate_views_experiment_assignment_160125';

/**
 * Check if the duplicate views experiment is enabled.
 *
 * @return boolean
 */
function wpcom_is_duplicate_views_experiment_enabled() {
	$experiment_platform = 'calypso';
	$experiment_name     = "{$experiment_platform}_post_onboarding_holdout_160125";
	$aa_test_name        = "{$experiment_platform}_post_onboarding_aa_150125";

	static $is_enabled = null;
	if ( $is_enabled !== null ) {
		return $is_enabled;
	}

	$host = new Host();

	if ( $host->is_wpcom_simple() && is_automattician() || $host->is_atomic_platform() && wpcom_atomic_rdv_maybe_is_a11n() ) {
		wpcom_rdv_reset_cache_if_needed();
	}

	$variation = get_user_option( RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, get_current_user_id() );

	/**
	 * We cache it for both AT and Simple because we want to give a12s to be able to switch between variations for their accounts - this can be useful during support.
	 * Note that switching the variations can only be achieved through the escape hatch, not via ExPlat.
	 *
	 * If we don't cache it, the is_automattician conditions will force treatment every time.
	 */
	if ( false !== $variation ) {
		$is_enabled = 'treatment' === $variation;
		return $is_enabled;
	}

	if ( $host->is_wpcom_simple() ) {
		\ExPlat\assign_current_user( $aa_test_name );
		$is_enabled = 'treatment' === \ExPlat\assign_current_user( $experiment_name );

		if ( is_automattician() ) {
			$is_enabled = true;
			update_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, 'treatment', true );
			wpcom_set_rdv_calypso_preference( 'treatment' );
		}

		return $is_enabled;
	}

	if ( wpcom_atomic_rdv_maybe_is_a11n() ) {
		update_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, 'treatment', true );
		wpcom_set_rdv_calypso_preference( 'treatment' );
		$is_enabled = true;

		return true;
	}

	if ( ! ( new Jetpack_Connection() )->is_user_connected() ) {
		$is_enabled = false;
		return $is_enabled;
	}

	$aa_test_request_path = add_query_arg(
		array( 'experiment_name' => $aa_test_name ),
		"/experiments/0.1.0/assignments/{$experiment_platform}"
	);
	Client::wpcom_json_api_request_as_user( $aa_test_request_path, 'v2' );

	$request_path = add_query_arg(
		array( 'experiment_name' => $experiment_name ),
		"/experiments/0.1.0/assignments/{$experiment_platform}"
	);
	$response     = Client::wpcom_json_api_request_as_user( $request_path, 'v2' );

	if ( is_wp_error( $response ) ) {
		$is_enabled = false;
		return $is_enabled;
	}

	$response_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $response_code ) {
		$is_enabled = false;
		return $is_enabled;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $data['variations'] ) && array_key_exists( $experiment_name, $data['variations'] ) ) {
		$variation = $data['variations'][ $experiment_name ];
		update_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, $variation, true );

		$is_enabled = 'treatment' === $variation;
		return $is_enabled;
	} else {
		$is_enabled = false;
		return $is_enabled;
	}
}

/**
 * Set the Calypso preference for rdv.
 *
 * This is needed to override the ExPlat variation assignment in order to be able to revert the variation for some users.
 *
 * @param string|null $assignment The experiment variation.
 * @return void
 */
function wpcom_set_rdv_calypso_preference( $assignment ) {
	if ( ( new Host() )->is_wpcom_simple() ) {
		$preferences                                       = get_user_attribute( get_current_user_id(), 'calypso_preferences' );
		$preferences[ RDV_EXPERIMENT_FORCE_ASSIGN_OPTION ] = $assignment;
		update_user_attribute( get_current_user_id(), 'calypso_preferences', $preferences );
	} else {
		Client::wpcom_json_api_request_as_user(
			'/me/preferences',
			'2',
			array(
				'method' => 'POST',
			),
			array( 'calypso_preferences' => array( RDV_EXPERIMENT_FORCE_ASSIGN_OPTION => $assignment ) )
		);
	}
}

/**
 * Force a variation (control/treatment) for the Remove Duplicate Views experiment.
 *
 * @return void
 */
function wpcom_force_assign_variation_for_remove_duplicate_views_experiment() {
	if ( ! isset( $_GET['force-assign-rdv-variation'] ) ) {
		return;
	}

	$assignment = in_array( $_GET['force-assign-rdv-variation'], array( 'control', 'treatment' ), true ) ? sanitize_text_field( wp_unslash( $_GET['force-assign-rdv-variation'] ) ) : false;

	if ( ! $assignment ) {
		return;
	}

	wpcom_set_rdv_calypso_preference( $assignment );

	/**
	 * Setting the option globally (third parameter) will have the following behavior:
	 * - On Simple Sites, the option will be shared between them.
	 * - On Atomic Sites, the option will NOT be shared.
	 *
	 * This also means that if a user has a Simple Sites and Atomic sites, the option will not be shared between them.
	 *
	 * For example, if the option is set on a Simple Site, every other site will get it, except for the Atomic ones.
	 * If the option is set on an Atomic Site, this will apply only on this site - it won't be shared between the other Atomic Sites OR Simple Sites.
	 *
	 *  This option is also not moved from Simple to Atomic on AT transfer.
	 */
	update_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, $assignment, true );
}

/**
 * Reset the assignment cache for the Remove duplicate views experiment.
 *
 * @return void
 */
function wpcom_reset_assignment_for_remove_duplicate_views_experiment() {
	if ( ! isset( $_GET['force-reset-rdv-variation'] ) ) {
		return;
	}

	wpcom_set_rdv_calypso_preference( null );

	/**
	 * Setting the option globally (third parameter) will have the following behavior:
	 * - On Simple Sites, the option will be shared between them.
	 * - On Atomic Sites, the option will NOT be shared.
	 *
	 * This also means that if a user has a Simple Sites and Atomic sites, the option will not be shared between them.
	 *
	 * For example, if the option is set on a Simple Site, every other site will get it, except for the Atomic ones.
	 * If the option is set on an Atomic Site, this will apply only on this site - it won't be shared between the other Atomic Sites OR Simple Sites.
	 *
	 * This option is also not moved from Simple to Atomic on AT transfer.
	 *
	 * Since this should be used only in exceptional cases, there's no need to implement something better.
	 */
	delete_user_option( get_current_user_id(), RDV_EXPERIMENT_FORCE_ASSIGN_OPTION, true );
}

if ( defined( 'A8C_PROXIED_REQUEST' ) && A8C_PROXIED_REQUEST || defined( 'AT_PROXIED_REQUEST' ) && AT_PROXIED_REQUEST ) {
	add_action( 'admin_init', 'wpcom_force_assign_variation_for_remove_duplicate_views_experiment' );
	add_action( 'admin_init', 'wpcom_reset_assignment_for_remove_duplicate_views_experiment' );
}

/**
 * Retrieves the current blog ID in a WordPress.com or Jetpack environment.
 *
 * This function determines the blog ID based on the hosting environment:
 * - On WordPress.com (`IS_WPCOM` defined), it returns the current blog ID.
 * - In a Jetpack environment, it checks the Jetpack options for the associated blog ID.
 * - If no valid ID is found in the Jetpack options, it falls back to the default current blog ID.
 *
 * @return int The current blog ID.
 */
function wpcom_get_current_blog_id() {
	// Check if we’re in a WordPress.com environment
	if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
		return get_current_blog_id();
	}

	// Attempt to retrieve the blog ID from Jetpack options
	$jetpack_options = get_option( 'jetpack_options' );
	if ( is_array( $jetpack_options ) && isset( $jetpack_options['id'] ) ) {
		return (int) $jetpack_options['id'];
	}

	// Default fallback to the standard blog ID
	return get_current_blog_id();
}

/**
 * Displays a notice when a user visits the enforced WP Admin view of a removed Calypso screen for
 * the first time.
 */
function wpcom_show_removed_calypso_screen_notice() {
	$blog_id = wpcom_get_current_blog_id();

	// Do not show notice on sites created after the experiment started (2025-01-16).
	if ( $blog_id > 240790000 ) { // 240790000 is the ID of a site created on 2025-01-16.
		return;
	}

	$admin_menu_class = wpcom_get_custom_admin_menu_class();
	if ( ! $admin_menu_class ) {
		return;
	}

	$current_screen = wpcom_admin_get_current_screen();

	if ( ! in_array( $current_screen, WPCOM_DUPLICATED_VIEW, true ) ) {
		return;
	}

	if ( ( new Host() )->is_wpcom_simple() ) {
		$preferences  = get_user_attribute( get_current_user_id(), 'calypso_preferences' );
		$is_dismissed = $preferences[ 'removed-calypso-screen-dismissed-notice-' . $current_screen ] ?? false;
		if ( $is_dismissed ) {
			return;
		}
	} else {
		$notices_dismissed_locally = get_user_option( 'wpcom_removed_calypso_screen_dismissed_notices' );
		if ( ! is_array( $notices_dismissed_locally ) ) {
			$notices_dismissed_locally = array();
		}

		if ( in_array( $current_screen, $notices_dismissed_locally, true ) ) {
			return;
		}

		if ( ! ( new Jetpack_Connection() )->is_user_connected() ) {
			return;
		}

		$response = Client::wpcom_json_api_request_as_user( '/me/preferences', 'v2' );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return;
		}

		$notices_dismissed_globally = array();
		$preferences                = json_decode( wp_remote_retrieve_body( $response ), true );
		foreach ( $preferences as $key => $value ) {
			if ( $value && preg_match( '/^removed-calypso-screen-dismissed-notice-(.+)$/', $key, $matches ) ) {
				$notices_dismissed_globally[] = $matches[1];
			}
		}

		if ( array_diff( $notices_dismissed_globally, $notices_dismissed_locally ) ) {
			update_user_option( get_current_user_id(), 'wpcom_removed_calypso_screen_dismissed_notices', $notices_dismissed_globally, true );
		}

		if ( in_array( $current_screen, $notices_dismissed_globally, true ) ) {
			return;
		}
	}

	if ( ! wpcom_is_duplicate_views_experiment_enabled() ) {
		return;
	}

	remove_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option' );
	$uses_wp_admin_interface = get_option( 'wpcom_admin_interface' ) === 'wp-admin';
	add_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option', 10 );
	if ( $uses_wp_admin_interface ) {
		return;
	}

	remove_filter( 'get_user_option_jetpack_admin_menu_preferred_views', 'wpcom_admin_get_user_option_jetpack' );
	$preferred_views = get_user_option( 'jetpack_admin_menu_preferred_views' );
	add_filter( 'get_user_option_jetpack_admin_menu_preferred_views', 'wpcom_admin_get_user_option_jetpack' );
	if ( ! empty( $preferred_views ) && isset( $preferred_views[ $current_screen ] ) && $preferred_views[ $current_screen ] === 'classic' ) {
		return;
	}

	$handle = jetpack_mu_wpcom_enqueue_assets( 'removed-calypso-screen-notice', array( 'js', 'css' ) );
	wp_set_script_translations( $handle, 'jetpack-mu-wpcom', Jetpack_Mu_Wpcom::PKG_DIR . 'languages' );

	global $title;
	$clean_title = preg_replace( '/\(\d+\)/', '', $title );
	$clean_title = trim( $clean_title );
	$config      = wp_json_encode(
		array(
			'title'        => $clean_title,
			'screen'       => $current_screen,
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'dismissNonce' => wp_create_nonce( 'wpcom_dismiss_removed_calypso_screen_notice' ),
		)
	);

	wp_add_inline_script(
		$handle,
		"window.removedCalypsoScreenNoticeConfig = $config;",
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'wpcom_show_removed_calypso_screen_notice' );

/**
 * Gets the name of the class used to customize the admin menu when Nav Unification is enabled.
 *
 * @return false|string The class name of the customized admin menu if any, false otherwise.
 */
function wpcom_get_custom_admin_menu_class() {
	if ( ! function_exists( '\Automattic\Jetpack\Masterbar\get_admin_menu_class' ) || ! function_exists( '\Automattic\Jetpack\Masterbar\should_customize_nav' ) ) {
		return false;
	}

	$admin_menu_class = apply_filters( 'jetpack_admin_menu_class', \Automattic\Jetpack\Masterbar\get_admin_menu_class() );
	if ( ! \Automattic\Jetpack\Masterbar\should_customize_nav( $admin_menu_class ) ) {
		return false;
	}

	return $admin_menu_class;
}

/**
 * Handles the AJAX request to dismiss a notice of a removed Calypso screen.
 */
function wpcom_dismiss_removed_calypso_screen_notice() {
	check_ajax_referer( 'wpcom_dismiss_removed_calypso_screen_notice' );
	if ( isset( $_REQUEST['screen'] ) ) {
		$screen = sanitize_text_field( wp_unslash( $_REQUEST['screen'] ) );
		if ( ( new Host() )->is_wpcom_simple() ) {
			$preferences = get_user_attribute( get_current_user_id(), 'calypso_preferences' );

			// If $preferences is not array we log the contents so that we can further debug.
			if ( ! is_array( $preferences ) && function_exists( 'log2logstash' ) ) {
				// The expected value when preferences aren't set is an empty string.
				if ( $preferences !== '' ) {
					$blog_id = wpcom_get_current_blog_id();
					log2logstash(
						array(
							'feature' => 'wpcom-dismiss-wp-admin-notice',
							'message' => 'Retrieved non-empty, non-array Calypso preferences.',
							'extra'   => wp_json_encode(
								array(
									'preferences' => $preferences,
									'user_id'     => get_current_user_id(),
									'blog_id'     => $blog_id,
								)
							),
						)
					);
					wp_die();
				}

				// If the preferences are empty, we set them to an empty array.
				$preferences = array();
			}

			$preferences[ 'removed-calypso-screen-dismissed-notice-' . $screen ] = true;
			update_user_attribute( get_current_user_id(), 'calypso_preferences', $preferences );
		} else {
			Client::wpcom_json_api_request_as_user(
				'/me/preferences',
				'2',
				array(
					'method' => 'POST',
				),
				array( 'calypso_preferences' => (object) array( 'removed-calypso-screen-dismissed-notice-' . $screen => true ) )
			);
			$notices_dismissed_locally = get_user_option( 'wpcom_removed_calypso_screen_dismissed_notices' );
			if ( ! is_array( $notices_dismissed_locally ) ) {
				$notices_dismissed_locally = array();
			}
			$notices_dismissed_locally[] = $screen;
			update_user_option( get_current_user_id(), 'wpcom_removed_calypso_screen_dismissed_notices', $notices_dismissed_locally, true );
		}
	}
	wp_die();
}
add_action( 'wp_ajax_wpcom_dismiss_removed_calypso_screen_notice', 'wpcom_dismiss_removed_calypso_screen_notice' );

/**
 * Enable the Blaze dashboard (WP-Admin) for users that have the RDV experiment enabled.
 *
 * @param bool $activation_status The activation status - use WP-Admin or Calypso.
 * @return mixed|true
 */
function wpcom_enable_blaze_dashboard_for_experiment( $activation_status ) {
	if ( ! wpcom_is_duplicate_views_experiment_enabled() ) {
		return $activation_status;
	}

	return true;
}

add_filter( 'jetpack_blaze_dashboard_enable', 'wpcom_enable_blaze_dashboard_for_experiment' );

/**
 * Make the Jetpack Stats page to point to the Calypso Stats Admin menu - temporary. This is needed because WP-Admin pages are rolled-out individually.
 *
 * This should be removed when the sites are fully untangled (or with the Jetpack Stats).
 *
 * This is enabled only for the stats page for users that are part of the remove duplicate views experiment.
 *
 * @param string $file The parent_file of the page.
 *
 * @return mixed
 */
function wpcom_select_calypso_admin_menu_stats_for_jetpack_post_stats( $file ) {
	global $_wp_real_parent_file, $pagenow;

	$is_on_stats_page = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'stats' === $_GET['page'];

	if ( ! $is_on_stats_page || ! wpcom_is_duplicate_views_experiment_enabled() ) {
		return $file;
	}

	remove_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option' );
	$is_using_wp_admin = get_option( 'wpcom_admin_interface' ) === 'wp-admin';
	if ( function_exists( 'wpcom_admin_interface_pre_get_option' ) ) {
		add_filter( 'pre_option_wpcom_admin_interface', 'wpcom_admin_interface_pre_get_option' );
	}

	if ( $is_using_wp_admin ) {
		return $file;
	}

	if ( ! wpcom_get_custom_admin_menu_class() ) {
		return $file;
	}

	/**
	 * Not ideal... We shouldn't be doing this.
	 */
	$_wp_real_parent_file['jetpack'] = 'https://wordpress.com/stats/day/' . ( new Status() )->get_site_suffix(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	return $file;
}

add_filter( 'parent_file', 'wpcom_select_calypso_admin_menu_stats_for_jetpack_post_stats' );
