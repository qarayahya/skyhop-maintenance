<?php
/**
 * Plugin Name:       Skyhop Maintenance
 * Description:       A maintenance mode page your visitors can play: a one-tap flying game with six themes, a live countdown and a proper 503 status. Settings → Skyhop Maintenance.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       skyhop-maintenance
 *
 * @package SkyhopMaintenance
 */

defined( 'ABSPATH' ) || exit;

define( 'SKYHOP_MAINT_VERSION', '1.0.0' );
define( 'SKYHOP_MAINT_FILE', __FILE__ );
define( 'SKYHOP_MAINT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKYHOP_MAINT_URL', plugin_dir_url( __FILE__ ) );
define( 'SKYHOP_MAINT_OPTION', 'skyhop_maintenance_settings' );

require_once SKYHOP_MAINT_DIR . 'includes/class-skyhop-maintenance-plugin.php';
require_once SKYHOP_MAINT_DIR . 'includes/class-skyhop-maintenance-frontend.php';

if ( is_admin() ) {
	require_once SKYHOP_MAINT_DIR . 'includes/class-skyhop-maintenance-admin.php';
}

register_activation_hook( __FILE__, array( 'Skyhop_Maintenance_Plugin', 'activate' ) );
add_action( 'plugins_loaded', array( 'Skyhop_Maintenance_Plugin', 'init' ) );
