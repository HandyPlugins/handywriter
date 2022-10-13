<?php
/**
 * Uninstall Handywriter
 *
 * Deletes all plugin related data and configurations
 *
 * @package Handywriter
 */

// Exit if accessed directly.
use const Handywriter\Constants\SETTING_OPTION;
use const Handywriter\Constants\LICENSE_KEY_OPTION;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'plugin.php';

if ( HANDYWRITER_IS_NETWORK ) {
	delete_site_option( SETTING_OPTION );
	delete_site_option( LICENSE_KEY_OPTION );
} else {
	delete_option( SETTING_OPTION );
	delete_option( LICENSE_KEY_OPTION );
}
