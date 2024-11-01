<?php
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) exit();

// delete transient
if (function_exists('wpprs_getpluginoptions')) {
	$options = wpprs_getpluginoptions();
	$uid = $options[WPPRS_DEFAULT_UID_NAME];
	delete_transient('wpprs_count_' . $uid);
}
	
delete_option('wpprs'); 


?>