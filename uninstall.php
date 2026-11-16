<?php
/**
 * Remove plugin data on uninstall.
 *
 * @package Skyhop404
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'skyhop404_settings' );
