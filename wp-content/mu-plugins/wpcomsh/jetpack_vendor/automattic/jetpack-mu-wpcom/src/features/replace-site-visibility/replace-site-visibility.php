<?php
/**
 * Replaces the 'Site Visibility' privacy options selector with a Calypso link.
 *
 * @package automattic/jetpack-mu-wpcom
 */

/**
 * Whether the current site is connected to Jetpack.
 *
 * @return bool
 */
function is_jetpack_connected() {
	// @phan-suppress-next-line PhanUndeclaredClassMethod
	return class_exists( 'Jetpack' ) && Jetpack::is_connection_ready();
}

/**
 * Replaces the 'Site Visibility' privacy options selector with a Calypso link.
 */
function replace_site_visibility() {
	// We are not either in Simple or Atomic.
	if ( ! class_exists( 'Automattic\Jetpack\Status' ) ) {
		return;
	}

	$jetpack_status = new Automattic\Jetpack\Status();
	$site_slug      = $jetpack_status->get_site_suffix();
	$current_screen = wpcom_admin_get_current_screen();

	if ( ! is_jetpack_connected() && $jetpack_status->is_private_site() ) {
		$settings_url = esc_url_raw( sprintf( '/wp-admin/admin.php?page=jetpack' ) );
		$manage_label = __( 'Jetpack is disconnected & site is private. Reconnect Jetpack to manage site visibility settings.', 'jetpack-mu-wpcom' );
	} elseif ( ! is_jetpack_connected() ) {
		return;
	} else {
		$settings_url = esc_url_raw( sprintf( 'https://wordpress.com/settings/general/%s#site-privacy-settings', $site_slug ) );

		// To prevent "Default + hold-out" users from redirecting to /wp-admin/options-general.php.
		// p1738634823404529/1738634703.754159-slack-CRWCHQGUB
		if ( in_array( $current_screen, WPCOM_DUPLICATED_VIEW, true ) && wpcom_is_duplicate_views_experiment_enabled() ) {
			$settings_url = esc_url_raw( sprintf( 'https://wordpress.com/sites/settings/site/%s', $site_slug ) );
		}

		$manage_label = __( 'Manage your privacy settings', 'jetpack-mu-wpcom' );
	}

	$escaped_content = '<a href="' . esc_url( $settings_url ) . '">' . esc_html( $manage_label ) . '</a>';

	?>
<noscript>
<p><?php echo wp_kses_post( $escaped_content ); ?></p>
</noscript>
<script>
( function() {
	var widgetArea = document.querySelector( '.option-site-visibility td' );
	if ( ! widgetArea ) {
		return;
	}
	widgetArea.innerHTML = '<?php echo wp_kses_post( $escaped_content ); ?>';
} )()
</script>
		<?php
}
add_action( 'blog_privacy_selector', 'replace_site_visibility' );
