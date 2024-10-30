
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(isset($_POST['savelbsa']) && intval($_POST['savelbsa'])==1) {

	
	add_action( "LBSA_sate_data", "lbstopattack_saveLBSAdata" );
	do_action( 'LBSA_sate_data' );

	echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"><p><strong>'.__('Settings saved.', "lbstopattack").'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Ignore this notification.', "lbstopattack").'</span></button></div>';
}

$configLBSA = lbstopattack_getConfigLBSA();

if($wpdb->prefix=="wp_") {

	echo "<div class='notice notice-warning'>".__("The prefix of your database tables is the default wordpress prefix wp_. This prefix is known to everyone and is commonly used in SQL injection attempts, which can cause problems. As this type of wp_ character string is often used by third-party plugins, like wp_mail_smtp, it is not possible for us to protect SQL attacks with this prefix at the risk of block your site by using plugins using this character string in their name or their file names or even commands. Better is to not use wp_  prefix for your tables and change it if it is possible ! By default we do not control this prefix but you can ask to do it then deactivate it in case of problem.","lbstopattack")."</div>";
}

$nonce = wp_create_nonce( 'lbsa_save-nonce' );

?>

<div class="wrap">
  <h1><?php echo __('LBStopAttack - SQL Injection & LFI Interceptor : Configuration', "lbstopattack"); ?></h1>
 
 <form method="post" action="" novalidate="novalidate" name="LBStopattackAdminForm" id="LBStopattackAdminForm">
<input type="hidden" name="savelbsa" value="1">
<table class="form-table">
	<tr>
	<th scope="row"><label for="unactive=" title="<?php echo __('Deactivate', "lbstopattack"); ?>"><?php echo __('Deactivate', "lbstopattack"); ?></label></th>
	<td><input type="checkbox" name="unactive" value="1" <?php echo ($configLBSA['lbsa_unactive']==1?" checked ":""); ?>> <small><?php echo __('If selected, the protect script is not activate, can be usefull if you work on your template with element for example', "lbstopattack"); ?></small><br>
</tr>
<tr>
	<th scope="row"><label for="onlyfront=" title="<?php echo __('Ignore request sent to /administrator', "lbstopattack"); ?>"><?php echo __('Works on Front End only', "lbstopattack"); ?></label></th>
	<td><input type="radio" name="onlyfront" value="0" <?php echo ($configLBSA['lbsa_onlyfront']==0?" checked ":""); ?>> <?php echo __('No', "lbstopattack"); ?><br>
		<input type="radio" name="onlyfront" value="1" <?php echo ($configLBSA['lbsa_onlyfront']==1?" checked ":"")?>> <?php echo __('Yes', "lbstopattack"); ?></td>
</tr>
<tr>
	<th scope="row"><label for="checkwp="><?php echo __('Check wp_ tables prefix', "lbstopattack"); ?></label></th>
	<td><input type="radio" name="checkwp" value="0" <?php echo ($configLBSA['lbsa_checkwp']==0?" checked ":"")?>> <?php echo __('No', "lbstopattack"); ?><br>
		<input type="radio" name="checkwp" value="1" <?php echo ($configLBSA['lbsa_checkwp']==1?" checked ":"")?>> <?php echo __('Yes', "lbstopattack"); ?></td>
</tr>
<tr>
	<th scope="row"><label for="namespaces="<?php echo __('NameSpaces inspected', "lbstopattack"); ?>></label></th>
	<td><select id="namespaces" name="namespaces">
	<option value="GET" <?php echo ($configLBSA['lbsa_namespaces']=="GET"?" selected ":"")?>>Get</option>
	<option value="GET,POST" <?php echo ($configLBSA['lbsa_namespaces']=="GET,POST"?" selected ":"")?>>Get, Post</option>
	<option value="REQUEST" <?php echo ($configLBSA['lbsa_namespaces']=="REQUEST"?" selected ":"")?>>Request</option>
	<option value="GET,POST,REQUEST" <?php echo ($configLBSA['lbsa_namespaces']=="GET,POST,REQUEST"?" selected ":"")?>>Get, Post, Request</option>
</select></td>
</tr>
<tr>
	<th scope="row"><label for="levelLFI="><?php echo __('LFI Level : Max number of consecutive ', "lbstopattack"); ?>'../'</label></th>
	<td><input name="levelLFI" type="text" id="levelLFI" value="<?php echo esc_attr($configLBSA['lbsa_levelLFI']); ?>" class="regular-text" placeholder="2"></td>
</tr>
<tr>
	<th scope="row"><label for="sendnotification="><?php echo __('Send Email Alert on injection/inclusion', "lbstopattack"); ?></label></th>
	<td><input type="radio" name="sendnotification" class="sendnotificationNo" value="0" <?php echo ($configLBSA['lbsa_sendnotification']==0?" checked ":"")?>> <?php echo __('No', "lbstopattack"); ?><br>
		<input type="radio" name="sendnotification" class="sendnotificationYes" value="1" <?php echo ($configLBSA['lbsa_sendnotification']==1?" checked ":"")?>> <?php echo __('Yes', "lbstopattack"); ?></td>
</tr>
<tr>
	<th scope="row"><label for="sendto="><?php echo __('Mail to notify attack', "lbstopattack"); ?></label></th>
	<td><input name="sendto" type="text" id="sendto" value="<?php echo esc_attr($configLBSA['lbsa_sendto']); ?>" class="regular-text" placeholder="firstname@domaine.com, lastname@domaine.com"></td>
</tr>
<tr>
	<th scope="row"><label for="raiseerror=" title="<?php echo __('Choose action to do on an attack between Raise Error or redirect on an url, like Interior ministry website of your country', "lbstopattack"); ?>"><?php echo __('Raise Error on Fault or do a redirection', "lbstopattack"); ?></label></th>
	<td><input type="radio" name="raiseerror" class="raiseerrorredir" value="0" <?php echo ($configLBSA['lbsa_raiseerror']==0?" checked ":"")?> onclick="if(jQuery(this).prop('checked')==true) {jQuery('#trredirurl').show();jQuery('#trerrorcode').hide();jQuery('#trerrormsg').hide();} else { jQuery('#trredirurl').hide();jQuery('#trerrorcode').show();jQuery('#trerrormsg').show();} "> <?php echo __('Redirection', "lbstopattack"); ?><br>
		<input type="radio" name="raiseerror" class="raiseerrorcode" value="1" <?php echo ($configLBSA['lbsa_raiseerror']==1?" checked ":"")?> onclick="if(jQuery(this).prop('checked')==true) {jQuery('#trredirurl').hide();jQuery('#trerrorcode').show();jQuery('#trerrormsg').show();} else { jQuery('#trredirurl').show();jQuery('#trerrorcode').hide();jQuery('#trerrormsg').hide();} "> <?php echo __('Raise Error', "lbstopattack"); ?></td>
</tr>
<tr id="trredirurl" <?php echo ($configLBSA['lbsa_raiseerror']==1?" style='display:none;' ":"")?>>
	<th scope="row"><label for="redirurl="><?php echo __('Redirect URL', "lbstopattack"); ?></label></th>
	<td><input name="redirurl" type="text" id="redirurl" value="<?php echo esc_attr($configLBSA['lbsa_redirurl']); ?>" class="regular-text" placeholder="i.e : https://www.interiorminestry.com"></td>
</tr>
<tr id="trerrorcode" <?php echo ($configLBSA['lbsa_raiseerror']==0?" style='display:none;' ":"")?>>
	<th scope="row"><label for="errorcode="><?php echo __('Http Error Code', "lbstopattack"); ?></label></th>
	<td><input name="errorcode" type="text" id="errorcode" value="<?php echo esc_attr($configLBSA['lbsa_errorcode']); ?>" class="regular-text" placeholder="i.e : 500"></td>
</tr>
<tr id="trerrormsg" <?php echo ($configLBSA['lbsa_raiseerror']==0?" style='display:none;' ":"")?>>
	<th scope="row"><label for="errormsg="><?php echo __('Http Error Message', "lbstopattack"); ?></label></th>
	<td><input name="errormsg" type="text" id="errormsg" value="<?php echo esc_attr($configLBSA['lbsa_errormsg']); ?>" class="regular-text" placeholder="i.e : Internal Server Error"></td>
</tr>

<tr>
	<th scope="row"><label for="ipblock="><?php echo __('Enable temporary IP block', "lbstopattack"); ?></label></th>
	<td><input type="radio" name="ipblock" class="ipblockNo" value="0" <?php echo ($configLBSA['lbsa_ipblock']==0?" checked ":"")?>> <?php echo __('No', "lbstopattack"); ?><br>
		<input type="radio" name="ipblock" class="ipblockYes" value="1" <?php echo ($configLBSA['lbsa_ipblock']==1?" checked ":"")?>> <?php echo __('Yes', "lbstopattack"); ?></td>
</tr>

<tr>
	<th scope="row"><label for="ipblocktime=" title="How many seconds hold ip block enabled"><?php echo __('Seconds to hold ip banned', "lbstopattack"); ?></label></th>
	<td><input name="ipblocktime" type="text" id="ipblocktime" value="<?php echo esc_attr($configLBSA['lbsa_ipblocktime']); ?>" class="regular-text" placeholder="i.e : 3600 fot 1 hour"></td>
</tr>
<tr>
	<th scope="row"><label for="ipblockcount=" title="Max hacks attempt before ip block starts"><?php echo __('Max hacks attempt', "lbstopattack"); ?></label></th>
	<td><input name="ipblockcount" type="text" id="ipblockcount" value="<?php echo esc_attr($configLBSA['lbsa_ipblockcount']); ?>" class="regular-text" placeholder="i.e : 2 "></td>
</tr>

<?php

if (current_user_can( 'manage_options' ) )  {
		
?>
<tr>
	<td colspan="2"><input type="button" value="<?php echo __('Save', "lbstopattack"); ?>" class="button button-primary" onclick="sendLBStopattackAdminForm()"></td>
</tr>
<?php


}

?>

</table>
<input type="hidden" name="lbsa_wpnonce" value="<?php echo $nonce; ?>">


 </form>
</div>

<script>

	function sendLBStopattackAdminForm() {

		var form = document.LBStopattackAdminForm;



			if(form.levelLFI.value=="" || form.levelLFI.value<2) {

				alert("<?php echo __('For LFI Level, 2 is minimim accepted', "lbstopattack"); ?>");
				
			}
			else if(jQuery(".sendnotificationYes").prop("checked")==true && form.sendto.value=="") {

				alert("<?php echo __('Please give an email to send notify attack or check No to send mail to notify attack', "lbstopattack"); ?>");
				
			}
			else if(jQuery(".raiseerrorredir").prop("checked")==true && form.redirurl.value=="") {

				alert("<?php echo __('Please complete the url input to redirect hack attempt', "lbstopattack"); ?>");
				
			}
			else if(jQuery(".raiseerrorcode").prop("checked")==true && (form.errorcode.value=="" || form.errormsg.value=="") ) {

				alert("<?php echo __('Please complete the Http Raise error data to manage it', "lbstopattack"); ?>");
				
			}
			else if(jQuery(".ipblockYes").prop("checked")==true && (form.ipblocktime.value=="" || form.ipblockcount.value=="") ) {

				alert("<?php echo __('Please complete the IP block inputs data to manage it', "lbstopattack"); ?>");
				
			}
			else {
				form.submit();
			}



	}

	jQuery(".notice-dismiss").on("click", function(){

		jQuery(this).parent().remove();

	});

</script>



