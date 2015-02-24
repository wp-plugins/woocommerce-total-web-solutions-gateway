<?php
/*
Plugin Name: WC Total Web Solutions Gateway
Plugin URI: http://payments.totalwebsolutions.com
Description: Intergrates to the Total Web Solutions Pay Page 
Version: 1.1.0
Author: <support@totalwebsolutions.com>
Author URI: http://www.totalwebsolutions.com

*/

add_action('plugins_loaded', 'woocommerce_totalweb_init', 0);

function woocommerce_totalweb_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	
	$plugin_dir = plugin_dir_path(__FILE__);
	
	/**
 	 * Localication
	 */
	//load_textdomain( 'woocommerce', $plugin_dir. 'lang/totalweb-'.get_locale().'.mo' );
	
	if(!defined('AUTHORIZE_NET_SDK')) {
		define('AUTHORIZE_NET_SDK', 1);
		require_once $plugin_dir . 'includes/authorize-net.php';
	}
	
	require_once $plugin_dir . 'gateway-totalweb.php';

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function add_totalweb_gateway($methods) {
		$methods[] = 'WC_Gateway_Totalweb';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'add_totalweb_gateway' );
	
	if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
		if(empty($_GET['wc-api']) && !empty($_GET['authorizeListenerSIM'])) {
			$asim = new WC_Gateway_Totalweb;
		}
	}
} 
