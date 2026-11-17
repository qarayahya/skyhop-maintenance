<?php
/**
 * Frontend: serves the maintenance page to visitors.
 *
 * @package SkyhopMaintenance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the game while the site is in maintenance mode.
 */
class Skyhop_Maintenance_Frontend {

	const HANDLE = 'skyhop-maintenance';

	/**
	 * Register hooks. template_redirect only fires for frontend requests, so
	 * wp-admin, wp-login.php, admin-ajax, cron and the REST API keep working.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ), 0 );
	}

	/**
	 * Whether an administrator is previewing via ?skyhop_preview=1.
	 * Read-only display switch, so no nonce is involved.
	 *
	 * @return bool
	 */
	public static function is_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['skyhop_preview'] ) && current_user_can( 'manage_options' );
	}

	/**
	 * Whether the current user sees the real site.
	 *
	 * @return bool
	 */
	public static function can_bypass() {
		$can = false;

		if ( is_user_logged_in() ) {
			$settings = Skyhop_Maintenance_Plugin::get_settings();
			$can      = 'logged_in' === $settings['bypass'] || current_user_can( 'manage_options' );
		}

		/**
		 * Filters whether the current visitor skips the maintenance page.
		 *
		 * @param bool $can Whether the visitor can bypass maintenance mode.
		 */
		return (bool) apply_filters( 'skyhop_maintenance_can_bypass', $can );
	}

	/**
	 * Decide whether this request gets the maintenance page.
	 *
	 * @return bool
	 */
	public static function should_render() {
		if ( self::is_preview() ) {
			return true;
		}

		if ( ! Skyhop_Maintenance_Plugin::is_enabled() || is_robots() || is_favicon() || self::can_bypass() ) {
			return false;
		}

		/**
		 * Filters whether the maintenance page replaces the current request.
		 *
		 * @param bool $render Whether to render the maintenance page.
		 */
		return (bool) apply_filters( 'skyhop_maintenance_should_render', true );
	}

	/**
	 * Settings for this render, including preview overrides from the settings screen.
	 *
	 * @return array
	 */
	public static function render_settings() {
		$settings = Skyhop_Maintenance_Plugin::get_settings();

		if ( self::is_preview() ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['sh_theme'] ) ) {
				$theme = sanitize_key( wp_unslash( $_GET['sh_theme'] ) );
				if ( 'random' === $theme || isset( Skyhop_Maintenance_Plugin::themes()[ $theme ] ) ) {
					$settings['theme'] = $theme;
				}
			}
			if ( isset( $_GET['sh_difficulty'] ) ) {
				$difficulty = sanitize_key( wp_unslash( $_GET['sh_difficulty'] ) );
				if ( isset( Skyhop_Maintenance_Plugin::difficulties()[ $difficulty ] ) ) {
					$settings['difficulty'] = $difficulty;
				}
			}
			// phpcs:enable
		}

		return $settings;
	}

	/**
	 * Countdown target (Unix timestamp) when one should be shown, else 0.
	 *
	 * @param array $settings Render settings.
	 * @return int
	 */
	public static function countdown_target( array $settings ) {
		$target = Skyhop_Maintenance_Plugin::return_timestamp( $settings['return_at'] );
		return ( $settings['show_countdown'] && $target > time() ) ? $target : 0;
	}

	/**
	 * Seconds for the Retry-After header: until the expected return time when
	 * it is set and in the future (capped at a day), otherwise one hour.
	 *
	 * @return int
	 */
	private static function retry_after() {
		$remaining = Skyhop_Maintenance_Plugin::return_timestamp() - time();
		return $remaining > 60 ? min( $remaining, DAY_IN_SECONDS ) : HOUR_IN_SECONDS;
	}

	/**
	 * Send the maintenance page and stop.
	 */
	public static function maybe_render() {
		if ( ! self::should_render() ) {
			return;
		}

		// 503 tells search engines the outage is temporary. Previews stay 200.
		if ( ! self::is_preview() ) {
			status_header( 503 );
			header( 'Retry-After: ' . self::retry_after() );
		}
		nocache_headers();

		$settings = self::render_settings();
		self::register_assets( $settings );

		include SKYHOP_MAINT_DIR . 'templates/maintenance.php';
		exit;
	}

	/**
	 * Register the game script and style with its config.
	 *
	 * @param array $settings Render settings.
	 */
	private static function register_assets( array $settings ) {
		wp_register_style( self::HANDLE, SKYHOP_MAINT_URL . 'assets/css/game.css', array(), SKYHOP_MAINT_VERSION );
		wp_register_script( self::HANDLE, SKYHOP_MAINT_URL . 'assets/js/game.js', array(), SKYHOP_MAINT_VERSION, true );

		$config = array(
			'theme'       => $settings['theme'],
			'difficulty'  => $settings['difficulty'],
			'sound'       => (bool) $settings['sound'],
			'allowSwitch' => (bool) $settings['allow_switch'],
			/* translators: Letters painted on the first game gates, one per gate. Keep it short. */
			'word'        => __( 'SOON', 'skyhop-maintenance' ),
			'strings'     => array(
				'start'     => __( 'Tap, click or press Space to fly', 'skyhop-maintenance' ),
				'ready'     => __( 'Ready?', 'skyhop-maintenance' ),
				'paused'    => __( 'Paused', 'skyhop-maintenance' ),
				'resume'    => __( 'Tap or press Space to continue', 'skyhop-maintenance' ),
				'gameOver'  => __( 'Game over', 'skyhop-maintenance' ),
				'newBest'   => __( 'New best', 'skyhop-maintenance' ),
				'retry'     => __( 'Tap to try again', 'skyhop-maintenance' ),
				/* translators: %d: best score. */
				'best'      => __( 'Best %d', 'skyhop-maintenance' ),
				/* translators: 1: score, 2: best score. */
				'scoreLine' => __( 'Score %1$d · Best %2$d', 'skyhop-maintenance' ),
				'soundOn'   => __( 'Mute sound', 'skyhop-maintenance' ),
				'soundOff'  => __( 'Turn sound on', 'skyhop-maintenance' ),
			),
		);

		wp_add_inline_script( self::HANDLE, 'window.SkyhopMaintenanceConfig = ' . wp_json_encode( $config ) . ';', 'before' );
	}
}
