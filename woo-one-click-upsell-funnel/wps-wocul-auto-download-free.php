<?php
/**
 * The update-specific functionality of the plugin.
 *
 * @package Bookings_For_Woocommerce_Pro
 */

/**
 * Replace plugin main function.
 *
 * @return boolean
 */
function wps_wocuf_org_replace_plugin() {
	$plugin_slug        = 'upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php';
	$plugin_name        = 'Upsell Funnel Builder for WooCommerce';
	$plugin_zip         = 'https://downloads.wordpress.org/plugin/upsell-order-bump-offer-for-woocommerce.zip';
	$current_pro_plugin = 'woo-one-click-upsell-funnel/woo-one-click-upsell-funnel.php';

	ob_start();

	if ( wps_wocuf_org_is_plugin_installed( $plugin_slug ) ) {
		wps_wocuf_org_upgrade_plugin( $plugin_slug );
		$installed = true;
	} else {
		$installed = wps_wocuf_org_install_plugin( $plugin_zip );
	}
	if ( ! is_wp_error( $installed ) && $installed ) {
		$status_free = activate_plugin( $plugin_slug );
		if ( ! is_wp_error( $status_free ) ) {
			return true;
		}
	}

	ob_end_clean();
	return false;
}

/**
 * Checking if plugin is already installed.
 *
 * @param string $slug string containing the plugin slug.
 * @return boolean
 */
function wps_wocuf_org_is_plugin_installed( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	if ( ! empty( $all_plugins[ $slug ] ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Install plugin.
 *
 * @param string $plugin_zip url for the plugin zip file at WordPress.
 * @return boolean
 */
function wps_wocuf_org_install_plugin( $plugin_zip ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	wp_cache_flush();
	$upgrader  = new Plugin_Upgrader();
	$installed = $upgrader->install( $plugin_zip );
	return $installed;
}

/**
 * Upgrade plugin.
 *
 * @param string $plugin_slug string contining the plugin slug.
 */
function wps_wocuf_org_upgrade_plugin( $plugin_slug ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	wp_cache_flush();
	$upgrader = new Plugin_Upgrader();
	$upgraded = $upgrader->upgrade( $plugin_slug );
}
