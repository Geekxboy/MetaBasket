<?php
/*
Plugin Name: Meta Basket
Description: Adds a woocommerce product field that is used for sending items to the enjin address provided.
Author: MyMetaverse
Author URI: https://mymetaverse.io/
Version: 1.1.1
*/

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Define variables for plugin
 */
define('ENJIN_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ENJIN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include Files
require_once ENJIN_PLUGIN_PATH . 'includes/Functions.php';
require_once ENJIN_PLUGIN_PATH . 'includes/Options.php';
require_once ENJIN_PLUGIN_PATH . 'includes/EnjinAPI.php';
require_once ENJIN_PLUGIN_PATH . 'includes/WooCommerce-Hook.php';
require_once ENJIN_PLUGIN_PATH . 'includes/User-Fields.php';



if (!function_exists('nifty_plugin_activate_hooks')) {
	add_action( 'init', 'nifty_plugin_activate_hooks' );

	function nifty_plugin_activate_hooks() {
		do_action( 'nifty_plugin_enjin_user_fields' );
		do_action( 'nifty_plugin_extra_functions' );
	}
}