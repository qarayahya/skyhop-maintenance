<?php
/**
 * Core: defaults, theme registry, settings access and the toolbar indicator.
 *
 * @package SkyhopMaintenance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared plugin helpers.
 */
class Skyhop_Maintenance_Plugin {

	const PAGE = 'skyhop-maintenance';

	/**
	 * Boot the plugin.
	 */
	public static function init() {
		load_plugin_textdomain( 'skyhop-maintenance', false, dirname( plugin_basename( SKYHOP_MAINT_FILE ) ) . '/languages' );

		Skyhop_Maintenance_Frontend::init();

		if ( is_admin() ) {
			Skyhop_Maintenance_Admin::init();
		}

		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_node' ), 100 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'admin_bar_style' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_bar_style' ) );
	}

	/**
	 * Store defaults on activation. Maintenance mode starts switched off so
	 * activating the plugin never takes a site offline by surprise.
	 */
	public static function activate() {
		add_option( SKYHOP_MAINT_OPTION, self::defaults() );
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'        => 0,
			'bypass'         => 'admins',
			'return_at'      => '',
			'show_countdown' => 1,
			'theme'          => 'meadow',
			'allow_switch'   => 1,
			'difficulty'     => 'normal',
			'sound'          => 1,
			'headline'       => __( "We'll be right back.", 'skyhop-maintenance' ),
			'message'        => __( "We're giving the site a quick tune-up. Pass the time with a game; we'll be back shortly.", 'skyhop-maintenance' ),
			'contact_email'  => '',
			'show_login'     => 1,
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
				'label'       => __( 'Sunny Meadow', 'skyhop-maintenance' ),
				'description' => __( 'A busy bee dodging brick towers on a summer afternoon.', 'skyhop-maintenance' ),
			),
			'night'  => array(
				'label'       => __( 'Midnight City', 'skyhop-maintenance' ),
				'description' => __( 'A night owl weaving between lit-up towers.', 'skyhop-maintenance' ),
			),
			'neon'   => array(
				'label'       => __( 'Neon Drive', 'skyhop-maintenance' ),
				'description' => __( 'A synthwave ship slicing through glowing gates.', 'skyhop-maintenance' ),
			),
			'ocean'  => array(
				'label'       => __( 'Deep Reef', 'skyhop-maintenance' ),
				'description' => __( 'A small fish swimming past coral columns.', 'skyhop-maintenance' ),
			),
			'space'  => array(
				'label'       => __( 'Cosmic Drift', 'skyhop-maintenance' ),
				'description' => __( 'A rocket threading space-station pylons.', 'skyhop-maintenance' ),
			),
			'candy'  => array(
				'label'       => __( 'Candy Clouds', 'skyhop-maintenance' ),
				'description' => __( 'A jelly drop bouncing between candy canes.', 'skyhop-maintenance' ),
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
			'easy'   => __( 'Relaxed', 'skyhop-maintenance' ),
			'normal' => __( 'Normal', 'skyhop-maintenance' ),
			'hard'   => __( 'Hard', 'skyhop-maintenance' ),
		);
	}

	/**
	 * Saved settings merged over defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( SKYHOP_MAINT_OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	/**
	 * Whether maintenance mode is switched on.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return ! empty( self::get_settings()['enabled'] );
	}

	/**
	 * Expected return time as a Unix timestamp, or 0 when unset or invalid.
	 * Stored as "Y-m-d\TH:i" (the datetime-local format) in the site timezone.
	 *
	 * @param string|null $value Stored value. Defaults to the saved setting.
	 * @return int
	 */
	public static function return_timestamp( $value = null ) {
		if ( null === $value ) {
			$value = self::get_settings()['return_at'];
		}
		if ( ! is_string( $value ) || '' === $value ) {
			return 0;
		}
		$date = date_create_immutable_from_format( 'Y-m-d\TH:i', $value, wp_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}

	/**
	 * Toolbar badge, so logged-in admins can't forget the site is closed.
	 *
	 * @param WP_Admin_Bar $bar Toolbar instance.
	 */
	public static function admin_bar_node( $bar ) {
		if ( ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$bar->add_node(
			array(
				'id'    => 'skyhop-maintenance',
				'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">' . esc_html__( 'Maintenance mode', 'skyhop-maintenance' ) . '</span>',
				'href'  => admin_url( 'options-general.php?page=' . self::PAGE ),
				'meta'  => array( 'title' => __( 'Visitors currently see the maintenance page', 'skyhop-maintenance' ) ),
			)
		);
	}

	/**
	 * Styles for the toolbar badge.
	 */
	public static function admin_bar_style() {
		if ( ! is_admin_bar_showing() || ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$css = '#wpadminbar #wp-admin-bar-skyhop-maintenance>.ab-item{background:#996800;color:#fff}'
			. '#wpadminbar #wp-admin-bar-skyhop-maintenance:hover>.ab-item{background:#7a5300;color:#fff}'
			. '#wpadminbar #wp-admin-bar-skyhop-maintenance .ab-icon:before{content:"\f107";top:2px;color:#fff}';

		wp_add_inline_style( 'admin-bar', $css );
	}
}
