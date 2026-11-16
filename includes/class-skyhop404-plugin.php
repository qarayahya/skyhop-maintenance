<?php
/**
 * Core: defaults, theme registry and settings access.
 *
 * @package Skyhop404
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared plugin helpers.
 */
class Skyhop404_Plugin {

	/**
	 * Boot the plugin.
	 */
	public static function init() {
		load_plugin_textdomain( 'skyhop-404', false, dirname( plugin_basename( SKYHOP404_FILE ) ) . '/languages' );

		Skyhop404_Frontend::init();

		if ( is_admin() ) {
			Skyhop404_Admin::init();
		}
	}

	/**
	 * Store defaults on activation without overwriting existing settings.
	 */
	public static function activate() {
		add_option( SKYHOP404_OPTION, self::defaults() );
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'      => 1,
			'visibility'   => 'everyone',
			'theme'        => 'meadow',
			'allow_switch' => 1,
			'difficulty'   => 'normal',
			'sound'        => 1,
			'headline'     => __( 'This page flew away.', 'skyhop-404' ),
			'message'      => __( "We couldn't find what you were looking for. While you're here, see how far you can fly.", 'skyhop-404' ),
			'show_home'    => 1,
			'show_search'  => 1,
		);
	}

	/**
	 * Game themes. Keys must match the THEMES map in assets/js/game.js.
	 *
	 * @return array<string, array{label: string, description: string}>
	 */
	public static function themes() {
		return array(
			'meadow' => array(
				'label'       => __( 'Sunny Meadow', 'skyhop-404' ),
				'description' => __( 'A busy bee dodging brick towers on a summer afternoon.', 'skyhop-404' ),
			),
			'night'  => array(
				'label'       => __( 'Midnight City', 'skyhop-404' ),
				'description' => __( 'A night owl weaving between lit-up towers.', 'skyhop-404' ),
			),
			'neon'   => array(
				'label'       => __( 'Neon Drive', 'skyhop-404' ),
				'description' => __( 'A synthwave ship slicing through glowing gates.', 'skyhop-404' ),
			),
			'ocean'  => array(
				'label'       => __( 'Deep Reef', 'skyhop-404' ),
				'description' => __( 'A small fish swimming past coral columns.', 'skyhop-404' ),
			),
			'space'  => array(
				'label'       => __( 'Cosmic Drift', 'skyhop-404' ),
				'description' => __( 'A rocket threading space-station pylons.', 'skyhop-404' ),
			),
			'candy'  => array(
				'label'       => __( 'Candy Clouds', 'skyhop-404' ),
				'description' => __( 'A jelly drop bouncing between candy canes.', 'skyhop-404' ),
			),
		);
	}

	/**
	 * Difficulty levels.
	 *
	 * @return array<string, string>
	 */
	public static function difficulties() {
		return array(
			'easy'   => __( 'Relaxed', 'skyhop-404' ),
			'normal' => __( 'Normal', 'skyhop-404' ),
			'hard'   => __( 'Hard', 'skyhop-404' ),
		);
	}

	/**
	 * Saved settings merged over defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( SKYHOP404_OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}
}
