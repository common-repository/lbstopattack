<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/***** interceptor ******/

$p_raiseError = intval($configLBSA['lbsa_raiseerror']);
$p_errorCode = intval($configLBSA['lbsa_errorcode'])?esc_attr($configLBSA['lbsa_errorcode']):500;
$p_errorMsg = $configLBSA['lbsa_errormsg']?esc_attr($configLBSA['lbsa_errormsg']):"Internal Server Error";
$p_nameSpaces = $configLBSA['lbsa_namespaces']?esc_attr($configLBSA['lbsa_namespaces']):"GET,POST'";
$p_dbprefix=$wpdb->prefix;
$p_levelLFI=$configLBSA['lbsa_levelLFI']?intval($configLBSA['lbsa_levelLFI']):1;
$p_sendNotification=intval($configLBSA['lbsa_sendnotification']);
$p_MailNotification=esc_attr($configLBSA['lbsa_sendto']);
$redirect_to=esc_attr($configLBSA['lbsa_redirurl']);
$p_ipBlock  = $configLBSA['lbsa_ipblock']?intval($configLBSA['lbsa_ipblock']):0;
$p_ipBlockTime  = intval($configLBSA['lbsa_ipblocktime'])?intval($configLBSA['lbsa_ipblocktime']):300;
$p_ipBlockCount  = intval($configLBSA['lbsa_ipblockcount'])?intval($configLBSA['lbsa_ipblockcount']):2;
$p_ipBlockCount = ($p_ipBlockCount < 1 ? 1 : $p_ipBlockCount);
$lbsa_checkwp = intval($configLBSA['lbsa_checkwp']);

$locallist = array('127.0.0.1','::1'); 
if(in_array($_SERVER['REMOTE_ADDR'], $locallist)){

	$iptestForLocalhost = gethostbyname('www.google.com');

	$remoteIP = ip2long($iptestForLocalhost);
	
   
}
else {
	
	$remoteIP = ip2long($_SERVER['REMOTE_ADDR']);
	
}


$sql = "DELETE FROM `".$table_name."` WHERE DATE_ADD(`lasthacktime`, INTERVAL {$p_ipBlockTime} SECOND) < NOW() AND `autodelete`=1;";
$wpdb->query($sql);

if($p_ipBlock){

	 $rowcount = $wpdb->get_var("SELECT COUNT(*) from `".$table_name."` WHERE ip = '{$remoteIP}' AND `hackcount` >= {$p_ipBlockCount}"); ;

			if($rowcount){
				// unceremoniously shut down connection
				ob_end_clean();
				header('HTTP/1.0 403 Forbidden');
				header('Status: 403 Forbidden');
				header('Content-Length: 0',true);
				header('Connection: Close');
				exit;
			}


}//p_ipBlock

$wr=array();

    foreach(explode(',', $p_nameSpaces) as $nsp){
                switch ($nsp){
                    case 'GET':
                        $nameSpace = rest_sanitize_array($_GET);
                        break;
                    case 'POST':
                        $nameSpace = rest_sanitize_array($_POST);
                        break;
                    case 'COOKIE':
                        $nameSpace = rest_sanitize_array($_COOKIE);
                        break;
                    case 'REQUEST':
                        $nameSpace = rest_sanitize_array($_REQUEST);
                        break;
                }
            foreach($nameSpace as $k => $v){ 
            
                if(is_numeric($v)) continue;
                if(is_array($v)) continue;
                $v = sanitize_text_field($v);
                /* SQL injection */
                // strip /* comments */
                $a = preg_replace('!/\*.*?\*/!s', ' ', $v); 
                /* union select ... jos_users */
                if (preg_match('/UNION(?:\s+ALL)?\s+SELECT/i', $a)){ 
                    $wr[] = "** Union Select [$nsp:$k] => $v"; 
                    if(!$p_raiseError){
                        $v = preg_replace('/UNION(?:\s+ALL)?\s+SELECT/i', '--', $a);
                    }
                }

                /* table name */
               //$ta = array ('/\s`?+(#__)/', '/\s+`?(wp_)/i', "/\s+`?({$p_dbprefix}_)/i");
               // $ta = array ('/(\s+|\.|,)`?(#__)/i', '/(\s+|\.|,)`?(wp_)/i', "/(\s+|\.|,)`?({$p_dbprefix})/i");
                if($lbsa_checkwp) {
                	 $ta = array ('/#__/i', '/wp_/i', "/{$p_dbprefix}/i");
                }
                else {
                	 $ta = array ('/#__/i', "/{$p_dbprefix}/i");
                }
               
                foreach ($ta as $t){  
                    if (preg_match($t, $v)){
                        $wr[] = "** Table name in url [$nsp:$k] => $v"; 
                        if(!$p_raiseError){
                            $v = preg_replace($t, ' --$1', $v);
                        }
                    }
                }
                
                /* LFI */
                
                $recurse = str_repeat('\.\.\/', $p_levelLFI+1);
                $i=0;
                while (preg_match("/$recurse/", $v)){
                    if(!$i) $wr[] = "** Local File Inclusion [$nsp:$k] => $v"; 
                    if(!$p_raiseError){
                        $v = preg_replace('/\.\.\//', '', $v);
                    }else{
                        break;
                    }
                    $i++;
                }
                unset($v);
            } // namespace
        } //namespaces


        
        if(($p_ipBlock) AND !empty($wr)){ 
		    $sql = "INSERT INTO `".$table_name."` (`ip`, `firsthacktime`, `lasthacktime` ) VALUES ({$remoteIP}, NOW(), NOW()) ON DUPLICATE KEY UPDATE `lasthacktime` = NOW(), `hackcount` = `hackcount` + 1;";
			$wpdb->query($sql);
		}
    
        
        if(($p_sendNotification) AND !empty($wr))  {
            
            lbstopattack_sendNotification($wr, $p_MailNotification); 
            
        }
       
       		if(!$p_raiseError AND !empty($wr) AND $redirect_to!=""){

        		 header('Location: '.$redirect_to);
	            exit();


				
			}
			else if(!empty($wr)) {
	           
	          	if(version_compare(PHP_VERSION, '5.4.0') >= 0 && function_exists('http_response_code')) {
				    
				    http_response_code($p_errorCode);
				    ob_end_clean();
					header("HTTP/1.0 $p_errorCode $p_errorMsg");
					header("Status: $p_errorCode $p_errorMsg");
					header('Content-Length: 0',true);
					header('Connection: Close');
				exit;

				}
				else {

					header(sanitize_textarea_field($_SERVER['SERVER_PROTOCOL']) . " ".intval($p_errorCode)." ".sanitize_textarea_field($p_errorMsg)." ", true, intval($p_errorCode));
					 ob_end_clean();
					header("HTTP/1.0 $p_errorCode $p_errorMsg");
					header("Status: $p_errorCode $p_errorMsg");
					header('Content-Length: 0',true);
					header('Connection: Close');
					exit;

				}
	            
        	}
       
        
     
        
            function lbstopattack_sendNotification($warnings=array(), $p_sendTo=""){ 
        
                  
            $warning = implode("\r\n", $warnings);
            $warning .= "\r\n\r\n";

            $warning .= "**PAGE / SERVER INFO\r\n";
            $warning .= "\r\n\r\n";
            foreach(explode(',', 'REMOTE_ADDR,HTTP_USER_AGENT,REQUEST_METHOD,QUERY_STRING,HTTP_REFERER,REQUEST_URI,HTTP_HOST') as $sg){
                if(!isset($_SERVER[$sg])) continue;
                $sg = sanitize_textarea_field($sg);
                $warning .= "*{$sg} :\r\n{$_SERVER[$sg]}\r\n\r\n";
            }
            $warning .= "\r\n\r\n";
            
            $warning .= "** SUPERGLOBALS DUMP (sanitized)\r\n";
            
            $warning .= "\r\n\r\n";
            $warning .= '*$_GET DUMP';
            $warning .= "\r\n";
            foreach($_GET as $k => $v){
                $warning .= " -[".sanitize_textarea_field($k)."] => ".sanitize_textarea_field($v)."\r\n";
            }

            $warning .= "\r\n\r\n";
            $warning .= '*$_POST DUMP';
            $warning .= "\r\n";
            foreach($_POST as $k => $v){
                $warning .= " -[".sanitize_textarea_field($k)."] => ".sanitize_textarea_field($v)."\\r\n";
            }

            $warning .= "\r\n\r\n";
            $warning .= '*$_COOKIE DUMP';
            $warning .= "\r\n";
            foreach($_COOKIE as $k => $v){
                $warning .= " -[".sanitize_textarea_field($k)."] =>".sanitize_textarea_field($v)."\v\r\n";
            }

            $warning .= "\r\n\r\n";
            $warning .= '*$_REQUEST DUMP';
            $warning .= "\r\n";
            foreach($_REQUEST as $k => $v){
                $warning .= " -[".sanitize_textarea_field($k)."] => ".sanitize_textarea_field($v)."\\r\n";
            }
            $sitename = get_option( 'blogname' );
            $site_mail = get_option( 'admin_email' );
            $headers[] = "From: ".$sitename."<".$site_mail.">";
            wp_mail( $p_sendTo, $sitename.' : LBSA alert Fraudulent interception', $warning, $headers);


        }
   



/****** fin intercepto *********/