<?php
/**
 * Frontend: swaps the 404 template for the game page.
 *
 * @package Skyhop404
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the game on 404 requests.
 */
class Skyhop404_Frontend {

	const HANDLE = 'skyhop-404';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
	}

	/**
	 * Whether an administrator is previewing via ?skyhop404_preview=1.
	 * Read-only display switch, so no nonce is involved.
	 *
	 * @return bool
	 */
	public static function is_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['skyhop404_preview'] ) && current_user_can( 'manage_options' );
	}

	/**
	 * Decide whether this request gets the game.
	 *
	 * @return bool
	 */
	public static function should_render() {
		if ( ! is_404() ) {
			return false;
		}

		if ( self::is_preview() ) {
			return true;
		}

		$settings = Skyhop404_Plugin::get_settings();

		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		if ( 'admins' === $settings['visibility'] && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		/**
		 * Filters whether the Skyhop 404 game replaces the current 404 page.
		 *
		 * @param bool $render Whether to render the game.
		 */
		return (bool) apply_filters( 'skyhop404_should_render', true );
	}

	/**
	 * Settings for this render, including preview overrides from the settings screen.
	 *
	 * @return array
	 */
	public static function render_settings() {
		$settings = Skyhop404_Plugin::get_settings();

		if ( self::is_preview() ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['sh_theme'] ) ) {
				$theme = sanitize_key( wp_unslash( $_GET['sh_theme'] ) );
				if ( 'random' === $theme || isset( Skyhop404_Plugin::themes()[ $theme ] ) ) {
					$settings['theme'] = $theme;
				}
			}
			if ( isset( $_GET['sh_difficulty'] ) ) {
				$difficulty = sanitize_key( wp_unslash( $_GET['sh_difficulty'] ) );
				if ( isset( Skyhop404_Plugin::difficulties()[ $difficulty ] ) ) {
					$settings['difficulty'] = $difficulty;
				}
			}
			// phpcs:enable
		}

		return $settings;
	}

	/**
	 * Swap in the game template.
	 *
	 * @param string $template Template path chosen by WordPress.
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( ! self::should_render() ) {
			return $template;
		}

		$settings = self::render_settings();

		// Admin-only or preview output must never land in a page cache.
		if ( self::is_preview() || 'admins' === $settings['visibility'] ) {
			nocache_headers();
		}

		self::register_assets( $settings );

		return SKYHOP404_DIR . 'templates/game.php';
	}

	/**
	 * Register the game script and style with its config.
	 *
	 * @param array $settings Render settings.
	 */
	private static function register_assets( array $settings ) {
		wp_register_style( self::HANDLE, SKYHOP404_URL . 'assets/css/game.css', array(), SKYHOP404_VERSION );
		wp_register_script( self::HANDLE, SKYHOP404_URL . 'assets/js/game.js', array(), SKYHOP404_VERSION, true );

		$config = array(
			'theme'       => $settings['theme'],
			'difficulty'  => $settings['difficulty'],
			'sound'       => (bool) $settings['sound'],
			'allowSwitch' => (bool) $settings['allow_switch'],
			'strings'     => array(
				'start'     => __( 'Tap, click or press Space to fly', 'skyhop-404' ),
				'ready'     => __( 'Ready?', 'skyhop-404' ),
				'paused'    => __( 'Paused', 'skyhop-404' ),
				'resume'    => __( 'Tap or press Space to continue', 'skyhop-404' ),
				'gameOver'  => __( 'Game over', 'skyhop-404' ),
				'newBest'   => __( 'New best', 'skyhop-404' ),
				'retry'     => __( 'Tap to try again', 'skyhop-404' ),
				/* translators: %d: best score. */
				'best'      => __( 'Best %d', 'skyhop-404' ),
				/* translators: 1: score, 2: best score. */
				'scoreLine' => __( 'Score %1$d · Best %2$d', 'skyhop-404' ),
				'soundOn'   => __( 'Mute sound', 'skyhop-404' ),
				'soundOff'  => __( 'Turn sound on', 'skyhop-404' ),
			),
		);

		wp_add_inline_script( self::HANDLE, 'window.Skyhop404Config = ' . wp_json_encode( $config ) . ';', 'before' );
	}
}
