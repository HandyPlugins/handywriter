<?php
/**
 * Plugin Name:       Handywriter
 * Plugin URI:        https://handyplugins.co/handywriter/
 * Description:       Handywriter is an AI-powered writing assistant that can help you write better, faster, and more easily within WordPress.
 * Version:           1.2.1
 * Requires at least: 5.4
 * Requires PHP:      5.6
 * Author:            HandyPlugins
 * Author URI:        https://handyplugins.co/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       handywriter
 * Domain Path:       /languages
 *
 * @package Handywriter
 */

namespace Handywriter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
define( 'HANDYWRITER_VERSION', '1.2.1' );
define( 'HANDYWRITER_PLUGIN_FILE', __FILE__ );
define( 'HANDYWRITER_URL', plugin_dir_url( __FILE__ ) );
define( 'HANDYWRITER_PATH', plugin_dir_path( __FILE__ ) );
define( 'HANDYWRITER_INC', HANDYWRITER_PATH . 'includes/' );

// Require Composer autoloader if it exists.
if ( file_exists( HANDYWRITER_PATH . 'vendor/autoload.php' ) ) {
	include_once HANDYWRITER_PATH . 'vendor/autoload.php';
}

// Include files.
require_once HANDYWRITER_INC . 'constants.php';
require_once HANDYWRITER_INC . 'utils.php';
require_once HANDYWRITER_INC . 'core.php';
require_once HANDYWRITER_INC . 'history.php';
require_once HANDYWRITER_INC . 'admin/dashboard.php';


$network_activated = Utils\is_network_wide( HANDYWRITER_PLUGIN_FILE );
if ( ! defined( 'HANDYWRITER_IS_NETWORK' ) ) {
	define( 'HANDYWRITER_IS_NETWORK', $network_activated );
}

register_deactivation_hook( __FILE__, '\Handywriter\Core\deactivate' );


/**
 * Bootstrap the plugin.
 *
 * @return void
 */
function bootstrap() {
	Core\setup();
	History\setup();
	Admin\Dashboard\setup();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
