<?php
/**
 * Admin: Settings → Skyhop 404.
 *
 * @package Skyhop404
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings screen.
 */
class Skyhop404_Admin {

	const PAGE  = 'skyhop-404';
	const GROUP = 'skyhop404';

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private static $hook = '';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SKYHOP404_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the submenu under Settings.
	 */
	public static function add_page() {
		self::$hook = (string) add_options_page(
			__( 'Skyhop 404', 'skyhop-404' ),
			__( 'Skyhop 404', 'skyhop-404' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register the option.
	 */
	public static function register_setting() {
		register_setting(
			self::GROUP,
			SKYHOP404_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => Skyhop404_Plugin::defaults(),
			)
		);
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = Skyhop404_Plugin::defaults();
		$themes   = Skyhop404_Plugin::themes();
		$levels   = Skyhop404_Plugin::difficulties();

		$theme      = isset( $input['theme'] ) ? sanitize_key( $input['theme'] ) : $defaults['theme'];
		$difficulty = isset( $input['difficulty'] ) ? sanitize_key( $input['difficulty'] ) : $defaults['difficulty'];
		$visibility = isset( $input['visibility'] ) ? sanitize_key( $input['visibility'] ) : $defaults['visibility'];

		$headline = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';

		return array(
			'enabled'      => empty( $input['enabled'] ) ? 0 : 1,
			'visibility'   => in_array( $visibility, array( 'everyone', 'admins' ), true ) ? $visibility : $defaults['visibility'],
			'theme'        => ( 'random' === $theme || isset( $themes[ $theme ] ) ) ? $theme : $defaults['theme'],
			'allow_switch' => empty( $input['allow_switch'] ) ? 0 : 1,
			'difficulty'   => isset( $levels[ $difficulty ] ) ? $difficulty : $defaults['difficulty'],
			'sound'        => empty( $input['sound'] ) ? 0 : 1,
			'headline'     => '' !== $headline ? $headline : $defaults['headline'],
			'message'      => isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '',
			'show_home'    => empty( $input['show_home'] ) ? 0 : 1,
			'show_search'  => empty( $input['show_search'] ) ? 0 : 1,
		);
	}

	/**
	 * Load assets on our screen only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( $hook ) {
		if ( $hook !== self::$hook ) {
			return;
		}

		wp_enqueue_style( 'skyhop-404-admin', SKYHOP404_URL . 'assets/css/admin.css', array(), SKYHOP404_VERSION );
		wp_enqueue_script( 'skyhop-404-engine', SKYHOP404_URL . 'assets/js/game.js', array(), SKYHOP404_VERSION, true );
		wp_enqueue_script( 'skyhop-404-admin', SKYHOP404_URL . 'assets/js/admin.js', array( 'skyhop-404-engine' ), SKYHOP404_VERSION, true );
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'skyhop-404' ) . '</a>' );
		return $links;
	}

	/**
	 * Field name helper.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return SKYHOP404_OPTION . '[' . $key . ']';
	}

	/**
	 * Render a toggle row.
	 *
	 * @param string $key         Setting key.
	 * @param string $label       Label.
	 * @param string $description Help text.
	 * @param array  $settings    Current settings.
	 */
	private static function toggle( $key, $label, $description, array $settings ) {
		$id = 'sh-' . str_replace( '_', '-', $key );
		?>
		<div class="sh-row">
			<div class="sh-row__label">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php if ( $description ) : ?>
					<p class="sh-help"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
			<span class="sh-toggle">
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( self::name( $key ) ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>>
				<span class="sh-toggle__track" aria-hidden="true"></span>
			</span>
		</div>
		<?php
	}

	/**
	 * Render the settings screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = Skyhop404_Plugin::get_settings();
		$themes   = Skyhop404_Plugin::themes();
		$levels   = Skyhop404_Plugin::difficulties();
		$preview  = add_query_arg( 'skyhop404_preview', '1', home_url( '/skyhop-404-preview/' ) );
		$is_live  = $settings['enabled'] && 'everyone' === $settings['visibility'];
		?>
		<div class="wrap sh-admin">
			<header class="sh-header">
				<div class="sh-header__brand">
					<span class="sh-mark" aria-hidden="true">
						<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="currentColor"/><path d="M7 20.5c3.2-1.2 5-3.6 6.4-7.2.4-1 1.8-1 2.1 0 .9 3 2.6 4.8 5.1 5.4 1 .2 1.9-.6 1.7-1.6l-.3-1.3" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="23.5" cy="11.5" r="2" fill="#fff"/></svg>
					</span>
					<div>
						<h1><?php esc_html_e( 'Skyhop 404', 'skyhop-404' ); ?></h1>
						<p class="sh-header__tagline"><?php esc_html_e( 'Turn broken links into a quick game instead of a dead end.', 'skyhop-404' ); ?></p>
					</div>
				</div>
				<div class="sh-header__actions">
					<span class="sh-status <?php echo $is_live ? 'is-live' : 'is-off'; ?>">
						<?php
						if ( $is_live ) {
							esc_html_e( 'Live for visitors', 'skyhop-404' );
						} elseif ( $settings['enabled'] ) {
							esc_html_e( 'Admins only', 'skyhop-404' );
						} else {
							esc_html_e( 'Off', 'skyhop-404' );
						}
						?>
					</span>
					<a class="button" id="sh-preview" href="<?php echo esc_url( $preview ); ?>" data-base="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Preview 404 page', 'skyhop-404' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'skyhop-404' ); ?></span>
					</a>
				</div>
			</header>
			<hr class="wp-header-end">

			<form method="post" action="options.php" class="sh-form">
				<?php settings_fields( self::GROUP ); ?>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Status', 'skyhop-404' ); ?></h2>
					</div>
					<?php self::toggle( 'enabled', __( 'Replace the 404 page with the game', 'skyhop-404' ), __( 'When off, your theme’s regular 404 template is used.', 'skyhop-404' ), $settings ); ?>
					<div class="sh-row">
						<div class="sh-row__label">
							<span class="sh-row__title" id="sh-visibility-label"><?php esc_html_e( 'Who sees it', 'skyhop-404' ); ?></span>
							<p class="sh-help"><?php esc_html_e( 'Use “Administrators only” to try it out before going live.', 'skyhop-404' ); ?></p>
						</div>
						<div class="sh-segmented" role="radiogroup" aria-labelledby="sh-visibility-label">
							<?php
							$visibility = array(
								'everyone' => __( 'Everyone', 'skyhop-404' ),
								'admins'   => __( 'Administrators only', 'skyhop-404' ),
							);
							foreach ( $visibility as $value => $label ) :
								?>
								<label>
									<input type="radio" name="<?php echo esc_attr( self::name( 'visibility' ) ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings['visibility'], $value ); ?>>
									<span><?php echo esc_html( $label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Theme', 'skyhop-404' ); ?></h2>
						<p><?php esc_html_e( 'Each theme comes with its own character, obstacles and scenery. Hover a card to see it move.', 'skyhop-404' ); ?></p>
					</div>
					<div class="sh-themes" role="radiogroup" aria-label="<?php esc_attr_e( 'Game theme', 'skyhop-404' ); ?>">
						<?php foreach ( $themes as $key => $meta ) : ?>
							<label class="sh-theme">
								<input type="radio" name="<?php echo esc_attr( self::name( 'theme' ) ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $settings['theme'], $key ); ?>>
								<span class="sh-theme__frame">
									<canvas class="sh-theme__canvas" data-theme="<?php echo esc_attr( $key ); ?>" aria-hidden="true"></canvas>
									<span class="sh-theme__check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m6 12.5 4 4 8-9" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
								</span>
								<span class="sh-theme__name"><?php echo esc_html( $meta['label'] ); ?></span>
								<span class="sh-theme__desc"><?php echo esc_html( $meta['description'] ); ?></span>
							</label>
						<?php endforeach; ?>
						<label class="sh-theme">
							<input type="radio" name="<?php echo esc_attr( self::name( 'theme' ) ); ?>" value="random" <?php checked( $settings['theme'], 'random' ); ?>>
							<span class="sh-theme__frame sh-theme__frame--random">
								<span class="sh-theme__mosaic" aria-hidden="true">
									<?php foreach ( array_keys( $themes ) as $key ) : ?>
										<canvas class="sh-theme__canvas" data-theme="<?php echo esc_attr( $key ); ?>" data-static="1"></canvas>
									<?php endforeach; ?>
								</span>
								<span class="sh-theme__check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m6 12.5 4 4 8-9" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
							</span>
							<span class="sh-theme__name"><?php esc_html_e( 'Surprise me', 'skyhop-404' ); ?></span>
							<span class="sh-theme__desc"><?php esc_html_e( 'A different theme on every visit.', 'skyhop-404' ); ?></span>
						</label>
					</div>
					<?php self::toggle( 'allow_switch', __( 'Let visitors switch themes', 'skyhop-404' ), __( 'Shows small theme swatches on the 404 page. Their choice is remembered in their browser.', 'skyhop-404' ), $settings ); ?>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Gameplay', 'skyhop-404' ); ?></h2>
					</div>
					<div class="sh-row">
						<div class="sh-row__label">
							<span class="sh-row__title" id="sh-difficulty-label"><?php esc_html_e( 'Difficulty', 'skyhop-404' ); ?></span>
							<p class="sh-help"><?php esc_html_e( 'Controls gap size, speed and gravity. Speed also ramps up gently as the score climbs.', 'skyhop-404' ); ?></p>
						</div>
						<div class="sh-segmented" role="radiogroup" aria-labelledby="sh-difficulty-label">
							<?php foreach ( $levels as $value => $label ) : ?>
								<label>
									<input type="radio" name="<?php echo esc_attr( self::name( 'difficulty' ) ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings['difficulty'], $value ); ?>>
									<span><?php echo esc_html( $label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
					<?php self::toggle( 'sound', __( 'Sound effects on by default', 'skyhop-404' ), __( 'Short synthesized blips. Visitors can always mute them.', 'skyhop-404' ), $settings ); ?>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Content', 'skyhop-404' ); ?></h2>
						<p><?php esc_html_e( 'The info card that sits beside the game. It slides away while someone is playing.', 'skyhop-404' ); ?></p>
					</div>
					<div class="sh-field">
						<label for="sh-headline"><?php esc_html_e( 'Headline', 'skyhop-404' ); ?></label>
						<input type="text" id="sh-headline" class="regular-text" name="<?php echo esc_attr( self::name( 'headline' ) ); ?>" value="<?php echo esc_attr( $settings['headline'] ); ?>">
					</div>
					<div class="sh-field">
						<label for="sh-message"><?php esc_html_e( 'Message', 'skyhop-404' ); ?></label>
						<textarea id="sh-message" rows="3" name="<?php echo esc_attr( self::name( 'message' ) ); ?>"><?php echo esc_textarea( $settings['message'] ); ?></textarea>
					</div>
					<?php self::toggle( 'show_search', __( 'Show search field', 'skyhop-404' ), '', $settings ); ?>
					<?php self::toggle( 'show_home', __( 'Show “Back to homepage” button', 'skyhop-404' ), '', $settings ); ?>
				</section>

				<div class="sh-actions">
					<?php submit_button( __( 'Save changes', 'skyhop-404' ), 'primary', 'submit', false ); ?>
					<span class="sh-actions__note"><?php esc_html_e( 'The page keeps returning a real 404 status, so search engines are unaffected.', 'skyhop-404' ); ?></span>
				</div>
			</form>
		</div>
		<?php
	}
}
