<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-org-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package     woo_one_click_upsell_funnel
 * @subpackage woo_one_click_upsell_funnel/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ONBOARD_PLUGIN_NAME', 'One Click Upsell Funnel for Woocommerce' );

if ( class_exists( 'WPSwings_Onboarding_Helper' ) ) {
	$onboard = new WPSwings_Onboarding_Helper();
}

$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'woo-one-click-upsell-funnel' ) );
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'funnels-list';

if ( 'overview' === get_transient( 'wps_upsell_default_settings_tab' ) ) {

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';
}


$wps_wocuf_pro_funnels_list = get_option( 'wps_wocuf_funnels_list', array() );
$wps_wocuf_pro_funnel_number = '';
if ( ! empty( $wps_wocuf_pro_funnels_list ) ) {

	// Temp funnel variable.
	$wps_wocuf_pro_funnel_duplicate = $wps_wocuf_pro_funnels_list;

	// Make key pointer point to the end funnel.
	end( $wps_wocuf_pro_funnel_duplicate );

	// Now key function will return last funnel key.
	$wps_wocuf_pro_funnel_number = key( $wps_wocuf_pro_funnel_duplicate );
} else {
	// When no funnel is there then new funnel id will be 1 (0+1).
	$wps_wocuf_pro_funnel_number = 0;
}

echo '<div class="notice notice-info is-dismissible">';
		echo '<p style="font-size: large;font-weight: bold;color:red">';
		esc_html_e('One Click Upsell Funnel for WooCommerce Plugin is Migrated to Upsell Funnel Builder for WooCommerce.', 'woo-one-click-upsell-funnel');
		echo '</p>';
		echo '</div>';

?>


<div class="wps-notice-wrapper">
<?php  
		do_action( 'wps_wocuf_pro_setting_tab_active', '', '', '' );
		
?>
</div>


<?php if ( ! wps_upsell_lite_elementor_plugin_active() && false === get_transient( 'wps_upsell_elementor_inactive_notice' ) ) : ?>

<div id="wps_upsell_elementor_notice" class="notice notice-info is-dismissible">
	<p><span class="wps_upsell_heading_span"><?php esc_html_e( 'We have integrated with Elementor', 'woo-one-click-upsell-funnel' ); ?></span><?php esc_html_e( ' – now the most advanced WordPress page builder can be used to completely customize Upsell Offer pages. Moreover, we provide three stunning and beautiful offer templates.', 'woo-one-click-upsell-funnel' ); ?></p>

	<p><?php esc_html_e( 'To completely utilize all features of this plugin please activate Elementor.', 'woo-one-click-upsell-funnel' ); ?></p>

	<p><?php esc_html_e( 'Elementor is FREE and available on ORG ', 'woo-one-click-upsell-funnel' ); ?><a href="https://wordpress.org/plugins/elementor/" target="_blank"><?php esc_html_e( 'here', 'woo-one-click-upsell-funnel' ); ?></a></p>

	<p><?php esc_html_e( 'You don\'t need to worry about Elementor as it works independently and won\'t conflict with other page builders or WordPress new editor.', 'woo-one-click-upsell-funnel' ); ?></p>

	<p class="submit">

		<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>" id="wps_upsell_activate_elementor" class="button" target="_blank"><?php esc_html_e( 'Install and activate Elementor now &rarr;', 'woo-one-click-upsell-funnel' ); ?></a>
		<br>
		<a id="wps_upsell_dismiss_elementor_inactive_notice" href="javascript:void(0)" class="button"><?php esc_html_e( 'Dismiss this notice', 'woo-one-click-upsell-funnel' ); ?></a>

	</p>
</div>

<?php endif; ?>

<div class="wrap woocommerce" id="wps_wocuf_pro_setting_wrapper">

	<!-- To make WordPress notice appear at this place. As it searchs from top and appears at the 1st heading tag-->
	<h1></h1>

	<div class="hide"  id="wps_wocuf_pro_loader">	
		<img id="wps-wocuf-loading-image" src="<?php echo 'images/spinner-2x.gif'; ?>" >
	</div>
	<div class="wps_wocuf_pro_header">
		<div class="wps_wocuf_pro_setting_title"><?php esc_html_e( 'One Click Upsell Funnel for WooCommerce', 'woo-one-click-upsell-funnel' ); ?></div>

	</div>


	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">

	<?php

		if (  $wps_wocuf_pro_funnel_number < 1 ){

			?>
			<a class="nav-tab <?php echo 'creation-setting' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=one-click-section&sub_tab=post-save-offer-section"><?php esc_html_e( 'Create Funnel', 'woo-one-click-upsell-funnel' ); ?></a>
			<?php
		} else{
			?>
			<a class="nav-tab ubo_offer_input <?php echo 'creation-setting' === $active_tab ? 'nav-tab-active' : ''; ?>" href="#"><?php esc_html_e( 'Create Funnel', 'woo-one-click-upsell-funnel' ); ?></a>
			<span class="wps_wocuf_pro_enable_plugin_span"></span>
			<?php

		}
	?>




		<a class="nav-tab <?php echo 'funnels-list' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=one-click-section&sub_tab=post-list-offer-section"><?php esc_html_e( 'Funnels List', 'woo-one-click-upsell-funnel' ); ?></a>
		<a class="nav-tab <?php echo 'shortcodes' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=shortcode-section"><?php esc_html_e( 'Shortcodes', 'woo-one-click-upsell-funnel' ); ?></a>
		<a class="nav-tab <?php echo 'store_checkout' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=store-checkout-section"><?php esc_html_e( 'Store Checkout', 'woo-one-click-upsell-funnel' ); ?></a>
		<a class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=global-setting&sub_tab=post-global-sect"><?php esc_html_e( 'Global Settings', 'woo-one-click-upsell-funnel' ); ?></a>
		<a class="nav-tab <?php echo 'overview' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=upsell-order-bump-offer-for-woocommerce-setting&tab=overview"><?php esc_html_e( 'Overview', 'woo-one-click-upsell-funnel' ); ?></a>

		<?php do_action( 'wps_wocuf_pro_setting_tab' ); ?>	
	</nav>
	<?php

	if ( 'creation-setting' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-creation.php';
	} elseif ( 'funnels-list' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-funnels-list.php';
	} elseif ( 'shortcodes' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-shortcodes.php';
	} elseif ( 'settings' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-settings.php';
	} elseif ( 'overview' === $active_tab ) {
		include_once 'templates/wps-wocuf-overview.php';
	} elseif ( 'store_checkout' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-store-checkout.php';
	}

		do_action( 'wps_wocuf_pro_setting_tab_html' );
	?>
</div>
