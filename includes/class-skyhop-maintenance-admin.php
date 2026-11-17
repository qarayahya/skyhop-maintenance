<?php
/**
 * Admin: Settings → Skyhop Maintenance.
 *
 * @package SkyhopMaintenance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings screen.
 */
class Skyhop_Maintenance_Admin {

	const GROUP = 'skyhop_maintenance';

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
		add_filter( 'plugin_action_links_' . plugin_basename( SKYHOP_MAINT_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the submenu under Settings.
	 */
	public static function add_page() {
		self::$hook = (string) add_options_page(
			__( 'Skyhop Maintenance', 'skyhop-maintenance' ),
			__( 'Skyhop Maintenance', 'skyhop-maintenance' ),
			'manage_options',
			Skyhop_Maintenance_Plugin::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register the option.
	 */
	public static function register_setting() {
		register_setting(
			self::GROUP,
			SKYHOP_MAINT_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => Skyhop_Maintenance_Plugin::defaults(),
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
		$defaults = Skyhop_Maintenance_Plugin::defaults();
		$themes   = Skyhop_Maintenance_Plugin::themes();
		$levels   = Skyhop_Maintenance_Plugin::difficulties();

		$theme      = isset( $input['theme'] ) ? sanitize_key( $input['theme'] ) : $defaults['theme'];
		$difficulty = isset( $input['difficulty'] ) ? sanitize_key( $input['difficulty'] ) : $defaults['difficulty'];
		$bypass     = isset( $input['bypass'] ) ? sanitize_key( $input['bypass'] ) : $defaults['bypass'];
		$headline   = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';
		$email      = isset( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : '';

		$return_at = isset( $input['return_at'] ) ? sanitize_text_field( $input['return_at'] ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $return_at ) || ! Skyhop_Maintenance_Plugin::return_timestamp( $return_at ) ) {
			$return_at = '';
		}

		return array(
			'enabled'        => empty( $input['enabled'] ) ? 0 : 1,
			'bypass'         => in_array( $bypass, array( 'admins', 'logged_in' ), true ) ? $bypass : $defaults['bypass'],
			'return_at'      => $return_at,
			'show_countdown' => empty( $input['show_countdown'] ) ? 0 : 1,
			'theme'          => ( 'random' === $theme || isset( $themes[ $theme ] ) ) ? $theme : $defaults['theme'],
			'allow_switch'   => empty( $input['allow_switch'] ) ? 0 : 1,
			'difficulty'     => isset( $levels[ $difficulty ] ) ? $difficulty : $defaults['difficulty'],
			'sound'          => empty( $input['sound'] ) ? 0 : 1,
			'headline'       => '' !== $headline ? $headline : $defaults['headline'],
			'message'        => isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '',
			'contact_email'  => is_email( $email ) ? $email : '',
			'show_login'     => empty( $input['show_login'] ) ? 0 : 1,
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

		wp_enqueue_style( 'skyhop-maintenance-admin', SKYHOP_MAINT_URL . 'assets/css/admin.css', array(), SKYHOP_MAINT_VERSION );
		wp_enqueue_script( 'skyhop-maintenance-engine', SKYHOP_MAINT_URL . 'assets/js/game.js', array(), SKYHOP_MAINT_VERSION, true );
		wp_enqueue_script( 'skyhop-maintenance-admin', SKYHOP_MAINT_URL . 'assets/js/admin.js', array( 'skyhop-maintenance-engine' ), SKYHOP_MAINT_VERSION, true );
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . Skyhop_Maintenance_Plugin::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'skyhop-maintenance' ) . '</a>' );
		return $links;
	}

	/**
	 * Field name helper.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return SKYHOP_MAINT_OPTION . '[' . $key . ']';
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
	 * Render a segmented radio control.
	 *
	 * @param string $key      Setting key.
	 * @param array  $options  Value => label.
	 * @param string $label_id ID of the element labelling the group.
	 * @param array  $settings Current settings.
	 */
	private static function segmented( $key, array $options, $label_id, array $settings ) {
		?>
		<div class="sh-segmented" role="radiogroup" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<?php foreach ( $options as $value => $label ) : ?>
				<label>
					<input type="radio" name="<?php echo esc_attr( self::name( $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings[ $key ], $value ); ?>>
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
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

		$settings = Skyhop_Maintenance_Plugin::get_settings();
		$themes   = Skyhop_Maintenance_Plugin::themes();
		$preview  = add_query_arg( 'skyhop_preview', '1', home_url( '/' ) );
		$is_on    = ! empty( $settings['enabled'] );
		$check    = '<svg viewBox="0 0 24 24"><path d="m6 12.5 4 4 8-9" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		?>
		<div class="wrap sh-admin">
			<header class="sh-header">
				<div class="sh-header__brand">
					<span class="sh-mark" aria-hidden="true">
						<svg viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="currentColor"/><path d="M7 20.5c3.2-1.2 5-3.6 6.4-7.2.4-1 1.8-1 2.1 0 .9 3 2.6 4.8 5.1 5.4 1 .2 1.9-.6 1.7-1.6l-.3-1.3" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="23.5" cy="11.5" r="2" fill="#fff"/></svg>
					</span>
					<div>
						<h1><?php esc_html_e( 'Skyhop Maintenance', 'skyhop-maintenance' ); ?></h1>
						<p class="sh-header__tagline"><?php esc_html_e( 'A maintenance page your visitors can actually enjoy.', 'skyhop-maintenance' ); ?></p>
					</div>
				</div>
				<div class="sh-header__actions">
					<span class="sh-status <?php echo $is_on ? 'is-on' : 'is-off'; ?>">
						<?php $is_on ? esc_html_e( 'Maintenance mode is on', 'skyhop-maintenance' ) : esc_html_e( 'Site is public', 'skyhop-maintenance' ); ?>
					</span>
					<a class="button" id="sh-preview" href="<?php echo esc_url( $preview ); ?>" data-base="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Preview page', 'skyhop-maintenance' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'skyhop-maintenance' ); ?></span>
					</a>
				</div>
			</header>
			<hr class="wp-header-end">

			<form method="post" action="options.php" class="sh-form" data-option="<?php echo esc_attr( SKYHOP_MAINT_OPTION ); ?>">
				<?php settings_fields( self::GROUP ); ?>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Status', 'skyhop-maintenance' ); ?></h2>
					</div>
					<?php self::toggle( 'enabled', __( 'Enable maintenance mode', 'skyhop-maintenance' ), __( 'Visitors get the maintenance page with a 503 “temporarily unavailable” status, which tells search engines to come back later.', 'skyhop-maintenance' ), $settings ); ?>
					<div class="sh-row">
						<div class="sh-row__label">
							<span class="sh-row__title" id="sh-bypass-label"><?php esc_html_e( 'Who still sees the site', 'skyhop-maintenance' ); ?></span>
							<p class="sh-help"><?php esc_html_e( 'Everyone else gets the maintenance page. The login screen and dashboard always stay reachable.', 'skyhop-maintenance' ); ?></p>
						</div>
						<?php
						self::segmented(
							'bypass',
							array(
								'admins'    => __( 'Administrators', 'skyhop-maintenance' ),
								'logged_in' => __( 'Any logged-in user', 'skyhop-maintenance' ),
							),
							'sh-bypass-label',
							$settings
						);
						?>
					</div>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Schedule', 'skyhop-maintenance' ); ?></h2>
						<p><?php esc_html_e( 'Optional. When set, visitors see a live countdown and search engines are told when to retry.', 'skyhop-maintenance' ); ?></p>
					</div>
					<div class="sh-row">
						<div class="sh-row__label">
							<label for="sh-return-at"><?php esc_html_e( 'Expected back online', 'skyhop-maintenance' ); ?></label>
							<p class="sh-help">
								<?php
								/* translators: %s: site timezone, e.g. "Europe/London" or "UTC+2". */
								echo esc_html( sprintf( __( 'Site time zone: %s', 'skyhop-maintenance' ), wp_timezone_string() ) );
								?>
							</p>
						</div>
						<input type="datetime-local" id="sh-return-at" class="sh-datetime" name="<?php echo esc_attr( self::name( 'return_at' ) ); ?>" value="<?php echo esc_attr( $settings['return_at'] ); ?>">
					</div>
					<?php self::toggle( 'show_countdown', __( 'Show a countdown on the page', 'skyhop-maintenance' ), __( 'Hidden automatically once the time has passed.', 'skyhop-maintenance' ), $settings ); ?>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Theme', 'skyhop-maintenance' ); ?></h2>
						<p><?php esc_html_e( 'Each theme comes with its own character, obstacles and scenery. Hover a card to see it move.', 'skyhop-maintenance' ); ?></p>
					</div>
					<div class="sh-themes" role="radiogroup" aria-label="<?php esc_attr_e( 'Game theme', 'skyhop-maintenance' ); ?>">
						<?php foreach ( $themes as $key => $meta ) : ?>
							<label class="sh-theme">
								<input type="radio" name="<?php echo esc_attr( self::name( 'theme' ) ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $settings['theme'], $key ); ?>>
								<span class="sh-theme__frame">
									<canvas class="sh-theme__canvas" data-theme="<?php echo esc_attr( $key ); ?>" aria-hidden="true"></canvas>
									<span class="sh-theme__check" aria-hidden="true"><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
								</span>
								<span class="sh-theme__name"><?php echo esc_html( $meta['label'] ); ?></span>
								<span class="sh-theme__desc"><?php echo esc_html( $meta['description'] ); ?></span>
							</label>
						<?php endforeach; ?>
						<label class="sh-theme">
							<input type="radio" name="<?php echo esc_attr( self::name( 'theme' ) ); ?>" value="random" <?php checked( $settings['theme'], 'random' ); ?>>
							<span class="sh-theme__frame">
								<span class="sh-theme__mosaic" aria-hidden="true">
									<?php foreach ( array_keys( $themes ) as $key ) : ?>
										<canvas class="sh-theme__canvas" data-theme="<?php echo esc_attr( $key ); ?>" data-static="1"></canvas>
									<?php endforeach; ?>
								</span>
								<span class="sh-theme__check" aria-hidden="true"><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
							</span>
							<span class="sh-theme__name"><?php esc_html_e( 'Surprise me', 'skyhop-maintenance' ); ?></span>
							<span class="sh-theme__desc"><?php esc_html_e( 'A different theme on every visit.', 'skyhop-maintenance' ); ?></span>
						</label>
					</div>
					<?php self::toggle( 'allow_switch', __( 'Let visitors switch themes', 'skyhop-maintenance' ), __( 'Shows small theme swatches on the page. Their choice is remembered in their browser.', 'skyhop-maintenance' ), $settings ); ?>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Gameplay', 'skyhop-maintenance' ); ?></h2>
					</div>
					<div class="sh-row">
						<div class="sh-row__label">
							<span class="sh-row__title" id="sh-difficulty-label"><?php esc_html_e( 'Difficulty', 'skyhop-maintenance' ); ?></span>
							<p class="sh-help"><?php esc_html_e( 'Controls gap size, speed and gravity. Speed also ramps up gently as the score climbs.', 'skyhop-maintenance' ); ?></p>
						</div>
						<?php self::segmented( 'difficulty', Skyhop_Maintenance_Plugin::difficulties(), 'sh-difficulty-label', $settings ); ?>
					</div>
					<?php self::toggle( 'sound', __( 'Sound effects on by default', 'skyhop-maintenance' ), __( 'Short synthesized blips. Visitors can always mute them.', 'skyhop-maintenance' ), $settings ); ?>
				</section>

				<section class="sh-card">
					<div class="sh-card__head">
						<h2><?php esc_html_e( 'Content', 'skyhop-maintenance' ); ?></h2>
						<p><?php esc_html_e( 'The info card beside the game. It slides away while someone is playing.', 'skyhop-maintenance' ); ?></p>
					</div>
					<div class="sh-field">
						<label for="sh-headline"><?php esc_html_e( 'Headline', 'skyhop-maintenance' ); ?></label>
						<input type="text" id="sh-headline" class="regular-text" name="<?php echo esc_attr( self::name( 'headline' ) ); ?>" value="<?php echo esc_attr( $settings['headline'] ); ?>">
					</div>
					<div class="sh-field">
						<label for="sh-message"><?php esc_html_e( 'Message', 'skyhop-maintenance' ); ?></label>
						<textarea id="sh-message" rows="3" name="<?php echo esc_attr( self::name( 'message' ) ); ?>"><?php echo esc_textarea( $settings['message'] ); ?></textarea>
					</div>
					<div class="sh-field">
						<label for="sh-contact-email"><?php esc_html_e( 'Contact email', 'skyhop-maintenance' ); ?></label>
						<input type="email" id="sh-contact-email" class="regular-text" name="<?php echo esc_attr( self::name( 'contact_email' ) ); ?>" value="<?php echo esc_attr( $settings['contact_email'] ); ?>" placeholder="<?php esc_attr_e( 'Optional', 'skyhop-maintenance' ); ?>" aria-describedby="sh-contact-email-help">
						<p class="sh-help" id="sh-contact-email-help"><?php esc_html_e( 'Adds a “Contact us” button. The address is obfuscated against spam bots.', 'skyhop-maintenance' ); ?></p>
					</div>
					<?php self::toggle( 'show_login', __( 'Show a “Log in” link', 'skyhop-maintenance' ), __( 'A small link for your team, in the corner of the info card.', 'skyhop-maintenance' ), $settings ); ?>
				</section>

				<div class="sh-actions">
					<?php submit_button( __( 'Save changes', 'skyhop-maintenance' ), 'primary', 'submit', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
