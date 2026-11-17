<?php
/**
 * Remove plugin data on uninstall.
 *
 * @package SkyhopMaintenance
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'skyhop_maintenance_settings' );
