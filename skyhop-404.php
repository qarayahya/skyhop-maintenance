<?php
/**
 * Plugin Name:       Skyhop 404
 * Description:       Turns your 404 page into a one-tap arcade flying game, with six themes and a settings page under Settings → Skyhop 404.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       skyhop-404
 *
 * @package Skyhop404
 */

defined( 'ABSPATH' ) || exit;

define( 'SKYHOP404_VERSION', '1.0.0' );
define( 'SKYHOP404_FILE', __FILE__ );
define( 'SKYHOP404_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKYHOP404_URL', plugin_dir_url( __FILE__ ) );
define( 'SKYHOP404_OPTION', 'skyhop404_settings' );

require_once SKYHOP404_DIR . 'includes/class-skyhop404-plugin.php';
require_once SKYHOP404_DIR . 'includes/class-skyhop404-frontend.php';

if ( is_admin() ) {
	require_once SKYHOP404_DIR . 'includes/class-skyhop404-admin.php';
}

register_activation_hook( __FILE__, array( 'Skyhop404_Plugin', 'activate' ) );
add_action( 'plugins_loaded', array( 'Skyhop404_Plugin', 'init' ) );
