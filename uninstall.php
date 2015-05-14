<?php

/*
	PHP file containing uninstall procedure.
	WP First Letter Avatar prefix - 'wpfla'
*/

// delete plugin options:

if (!defined('WP_UNINSTALL_PLUGIN')){
	exit;
}

$option_name = 'wpfla_settings';
if (is_multisite()){
	delete_site_option($option_name);
	delete_option($option_name);
} else {
	delete_option($option_name);
}
