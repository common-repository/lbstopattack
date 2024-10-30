<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $lbsa_db_version;
$lbsa_db_version = '1.0';


add_action( 'admin_menu', 'add_lbstopattack_admin_menu' );
 
// Add a new top level menu link to the ACP
function add_lbstopattack_admin_menu()
{
      add_menu_page(
        'LBStopAttack', // Title of the page
        'LBStopAttack', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'lbsa_home', // The 'slug' - file to display when clicking the link
        'show_lbstopattack_adm_page'
    );

   
}

function show_lbstopattack_adm_page() {

global $wpdb;
require_once LBSTOPHACK_PLUGIN_DIR . 'inc/lbsa_home.php';

}



function lbstopattack_InstallOptionsOnActivationPlugin() {

global $wpdb;
global $lbsa_db_version;

add_option( "lbsa_onlyfront", '0' );
add_option( "lbsa_namespaces", 'GET,POST,REQUEST' );
add_option( "lbsa_levelLFI", '2' );
add_option( "lbsa_sendnotification", '0' );
add_option( "lbsa_sendto", '' );
add_option( "lbsa_raiseerror", '1' );
add_option( "lbsa_redirurl", '' );
add_option( "lbsa_errorcode", '500' );
add_option( "lbsa_errormsg", 'Internal Server Error' );
add_option( "lbsa_ipblock", '0' );
add_option( "lbsa_ipblocktime", '0' );
add_option( "lbsa_ipblockcount", '0' );
add_option( "lbsa_checkwp", '0' );
add_option( "lbsa_unactive", '0' );

	$table_name = $wpdb->prefix . 'lbsa_iptable';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." ( ";
	$sql .= '`ip` BIGINT NOT NULL COMMENT \'ip to long\', ';
	$sql .= '`firsthacktime` DATETIME NOT NULL , ';
	$sql .= '`lasthacktime` DATETIME NOT NULL , ';
	$sql .= '`hackcount` INT NOT NULL DEFAULT \'1\', ';
	$sql .= '`autodelete` TINYINT NOT NULL DEFAULT \'1\', ';
	$sql .= 'PRIMARY KEY ( `ip` ) ';
	$sql .= ') ENGINE = InnoDB  DEFAULT CHARSET=utf8; ';
	
	$wpdb->query($sql);

	add_option( "lbsa_db_version", $lbsa_db_version );

}

function lbstopattack_DeleteOptionsOnUnActivationPlugin() {

global $wpdb;

delete_option( "lbsa_onlyfront");
delete_option( "lbsa_namespaces");
delete_option( "lbsa_levelLFI");
delete_option( "lbsa_sendnotification");
delete_option( "lbsa_sendto");
delete_option( "lbsa_raiseerror");
delete_option( "lbsa_redirurl");
delete_option( "lbsa_errorcode");
delete_option( "lbsa_errormsg");
delete_option( "lbsa_ipblock");
delete_option( "lbsa_ipblocktime");
delete_option( "lbsa_ipblockcount");
delete_option( "lbsa_checkwp");


$table_name = $wpdb->prefix . 'lbsa_iptable';

$sql = "DROP TABLE IF EXISTS ".$table_name;
$wpdb->query($sql);

delete_option( "lbsa_db_version");

}





function lbstopattack_saveLBSAdata() {

if ( ! wp_verify_nonce( $_POST['lbsa_wpnonce'], 'lbsa_save-nonce' ) ) {
	die( __( 'Erreur occured', 'lbstopattack' ) ); 
} else {

$onlyfront = intval($_POST['onlyfront']);
$namespaces = sanitize_text_field($_POST['namespaces']);
$levelLFI = intval($_POST['levelLFI']);
$sendnotification = intval($_POST['sendnotification']);
$emails = explode(",", sanitize_text_field($_POST['sendto']));
$sendto="";
foreach($emails as $semail) {
$saveEmail = sanitize_email(trim($semail));
	if($saveEmail){
		if($sendto!="")$sendto.=",";
		$sendto .=$saveEmail;
	}
}
$raiseerror = intval($_POST['raiseerror']);
$redirurl = esc_url_raw($_POST['redirurl']);
$errorcode = intval($_POST['errorcode']);
$errormsg = sanitize_text_field($_POST['errormsg']);
$ipblock = intval($_POST['ipblock']);
$ipblocktime = intval($_POST['ipblocktime']);
$ipblockcount = intval($_POST['ipblockcount']);
$checkwp = intval($_POST['checkwp']);
$unactive = isset($_POST['unactive']) && intval($_POST['unactive'])>0?1:0;

update_option( "lbsa_onlyfront", $onlyfront );
update_option( "lbsa_namespaces", $namespaces );
update_option( "lbsa_levelLFI", $levelLFI );
update_option( "lbsa_sendnotification", $sendnotification );
update_option( "lbsa_sendto", $sendto );
update_option( "lbsa_raiseerror", $raiseerror );
update_option( "lbsa_redirurl", $redirurl );
update_option( "lbsa_errorcode", $errorcode );
update_option( "lbsa_errormsg", $errormsg );
update_option( "lbsa_ipblock", $ipblock );
update_option( "lbsa_ipblocktime", $ipblocktime );
update_option( "lbsa_ipblockcount", $ipblockcount );
update_option( "lbsa_checkwp", $checkwp );
update_option( "lbsa_unactive", $unactive );
}

}//saveLBSAdata

function lbstopattack_getConfigLBSA() {

$configLBSA=array();

$configLBSA['lbsa_onlyfront'] = get_option( "lbsa_onlyfront");
$configLBSA['lbsa_namespaces'] = get_option( "lbsa_namespaces");
$configLBSA['lbsa_levelLFI'] = get_option( "lbsa_levelLFI");
$configLBSA['lbsa_sendnotification'] = get_option( "lbsa_sendnotification");
$configLBSA['lbsa_sendto'] = get_option( "lbsa_sendto");
$configLBSA['lbsa_raiseerror'] = get_option( "lbsa_raiseerror");
$configLBSA['lbsa_redirurl'] = get_option( "lbsa_redirurl");
$configLBSA['lbsa_errorcode'] = get_option( "lbsa_errorcode");
$configLBSA['lbsa_errormsg'] = get_option( "lbsa_errormsg");
$configLBSA['lbsa_ipblock'] = get_option( "lbsa_ipblock");
$configLBSA['lbsa_ipblocktime']= get_option( "lbsa_ipblocktime");
$configLBSA['lbsa_ipblockcount'] = get_option( "lbsa_ipblockcount");
$configLBSA['lbsa_checkwp'] = get_option( "lbsa_checkwp");
$configLBSA['lbsa_unactive'] = get_option( "lbsa_unactive");

return $configLBSA;


}


