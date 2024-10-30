<?php
/**
 * @package lbstopattack
 * @version 1.1.6
 */
/*
Plugin Name: LB stop attack
Plugin URI: https://www.laubrotel.com
Description: This plugin attempts to block attacks and intrusions on your Wordpress website by blocking SQL query attempts from urls and file inclusion attempts.
Author: Laubro
Version: 1.1.6
text domain: lbstopattack
domain path: languages/
Author URI: https://www.laubrotel.com
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( !function_exists( 'add_action' ) ) {
	
	echo __("Hi there!  I'm just a plugin, not much I can do when called directly.", "lbstopattack");
	exit;
}

define( 'LBSTOPHACK_VERSION', '1.1.3' );
define( 'LBSTOPHACK_MINIMUM_WP_VERSION', '4.0' );
define( 'LBSTOPHACK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


require_once LBSTOPHACK_PLUGIN_DIR . 'inc/stopattack-functions.php';

register_activation_hook( __FILE__, 'lbstopattack_InstallOptionsOnActivationPlugin' );
register_deactivation_hook( __FILE__, 'lbstopattack_DeleteOptionsOnUnActivationPlugin' );

if(!function_exists("lbstopattack_placeProtect")){
function lbstopattack_placeProtect() {

global $wpdb;
$configLBSA = lbstopattack_getConfigLBSA();

$table_name = $wpdb->prefix . 'lbsa_iptable';

	if(!empty($configLBSA) && $configLBSA['lbsa_unactive']!=1 && is_admin() && $configLBSA['lbsa_onlyfront']!=1 || !empty($configLBSA) && $configLBSA['lbsa_unactive']!=1 && !is_admin()) {

		require_once LBSTOPHACK_PLUGIN_DIR . 'inc/stopattack.php';
	}

}
}


add_action( 'init', 'lbstopattack_placeProtect' );
add_action( 'init', 'LBSA_load_textdomain' );


  
if(!function_exists("LBSA_load_textdomain")){
function LBSA_load_textdomain() { 

 load_plugin_textdomain( 'lbstopattack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
}


