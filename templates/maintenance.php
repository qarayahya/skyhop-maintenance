<?php
/**
 * Standalone maintenance page. Rendered from template_redirect, so the theme
 * never loads and the full-screen canvas has the page to itself.
 *
 * @package SkyhopMaintenance
 */

defined( 'ABSPATH' ) || exit;

$skyhop_settings = Skyhop_Maintenance_Frontend::render_settings();
$skyhop_themes   = Skyhop_Maintenance_Plugin::themes();
$skyhop_theme    = isset( $skyhop_themes[ $skyhop_settings['theme'] ] ) ? $skyhop_settings['theme'] : 'meadow';
$skyhop_site     = get_bloginfo( 'name' );
$skyhop_target   = Skyhop_Maintenance_Frontend::countdown_target( $skyhop_settings );
$skyhop_email    = is_email( $skyhop_settings['contact_email'] ) ? $skyhop_settings['contact_email'] : '';

$skyhop_left  = max( 0, $skyhop_target - time() );
$skyhop_units = array(
	'd' => array( (int) floor( $skyhop_left / DAY_IN_SECONDS ), __( 'days', 'skyhop-maintenance' ) ),
	'h' => array( (int) floor( ( $skyhop_left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS ), __( 'hours', 'skyhop-maintenance' ) ),
	'm' => array( (int) floor( ( $skyhop_left % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS ), __( 'min', 'skyhop-maintenance' ) ),
	's' => array( $skyhop_left % MINUTE_IN_SECONDS, __( 'sec', 'skyhop-maintenance' ) ),
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( sprintf( /* translators: %s: site name. */ __( '%s – Down for maintenance', 'skyhop-maintenance' ), $skyhop_site ) ); ?></title>
	<?php wp_print_styles( Skyhop_Maintenance_Frontend::HANDLE ); ?>
</head>
<body class="sh-body sh-theme-<?php echo esc_attr( $skyhop_theme ); ?>" data-state="ready">

	<canvas id="sh-canvas" role="img" aria-label="<?php esc_attr_e( 'Skyhop: a tap-to-fly mini game. Press Space or tap to fly through the gaps.', 'skyhop-maintenance' ); ?>"></canvas>

	<div class="sh-score" aria-hidden="true">0</div>

	<div class="sh-message" aria-live="polite">
		<p class="sh-message__title"></p>
		<p class="sh-message__sub"></p>
	</div>

	<div class="sh-toolbar">
		<button type="button" class="sh-icon-btn sh-sound" aria-pressed="true">
			<svg class="sh-icon sh-icon--on" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9.5v5h3.5L12 18.5v-13L7.5 9.5H4Z" fill="currentColor"/><path d="M15.5 8.8a4.5 4.5 0 0 1 0 6.4M18 6.3a8 8 0 0 1 0 11.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			<svg class="sh-icon sh-icon--off" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9.5v5h3.5L12 18.5v-13L7.5 9.5H4Z" fill="currentColor"/><path d="m16 9.5 5 5m0-5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			<span class="screen-reader-text"></span>
		</button>
	</div>

	<aside class="sh-panel" aria-labelledby="sh-headline">
		<div class="sh-panel__top">
			<span class="sh-panel__site"><?php echo esc_html( $skyhop_site ); ?></span>
			<?php if ( $skyhop_settings['show_login'] ) : ?>
				<a class="sh-panel__login" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Log in', 'skyhop-maintenance' ); ?></a>
			<?php endif; ?>
		</div>

		<p class="sh-panel__eyebrow"><?php esc_html_e( 'Down for maintenance', 'skyhop-maintenance' ); ?></p>
		<h1 id="sh-headline" class="sh-panel__title"><?php echo esc_html( $skyhop_settings['headline'] ); ?></h1>

		<?php if ( '' !== trim( $skyhop_settings['message'] ) ) : ?>
			<p class="sh-panel__text"><?php echo nl2br( esc_html( $skyhop_settings['message'] ) ); ?></p>
		<?php endif; ?>

		<?php if ( $skyhop_target ) : ?>
			<div class="sh-countdown" data-target="<?php echo esc_attr( $skyhop_target * 1000 ); ?>" data-now="<?php echo esc_attr( time() * 1000 ); ?>">
				<p class="sh-countdown__label"><?php esc_html_e( 'Back online in', 'skyhop-maintenance' ); ?></p>
				<div class="sh-countdown__units" role="timer">
					<?php foreach ( $skyhop_units as $skyhop_unit => $skyhop_part ) : ?>
						<span class="sh-countdown__unit"<?php echo ( 'd' === $skyhop_unit && ! $skyhop_part[0] ) ? ' hidden' : ''; ?> data-unit="<?php echo esc_attr( $skyhop_unit ); ?>">
							<span class="sh-countdown__value"><?php echo esc_html( 'd' === $skyhop_unit ? $skyhop_part[0] : str_pad( (string) $skyhop_part[0], 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="sh-countdown__name"><?php echo esc_html( $skyhop_part[1] ); ?></span>
						</span>
					<?php endforeach; ?>
				</div>
				<p class="sh-countdown__done" hidden><?php esc_html_e( 'Any minute now. Refresh the page to check.', 'skyhop-maintenance' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $skyhop_email ) : ?>
			<a class="sh-btn" href="<?php echo esc_url( 'mailto:' . antispambot( $skyhop_email ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m4.5 7 7.5 6 7.5-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<?php esc_html_e( 'Contact us', 'skyhop-maintenance' ); ?>
			</a>
		<?php endif; ?>

		<div class="sh-panel__footer">
			<?php if ( $skyhop_settings['allow_switch'] ) : ?>
				<div class="sh-themes" role="group" aria-label="<?php esc_attr_e( 'Game theme', 'skyhop-maintenance' ); ?>">
					<?php foreach ( $skyhop_themes as $skyhop_key => $skyhop_meta ) : ?>
						<button type="button" class="sh-swatch sh-swatch--<?php echo esc_attr( $skyhop_key ); ?>" data-theme="<?php echo esc_attr( $skyhop_key ); ?>" title="<?php echo esc_attr( $skyhop_meta['label'] ); ?>" aria-pressed="false">
							<span class="screen-reader-text"><?php echo esc_html( $skyhop_meta['label'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<p class="sh-keys"><kbd><?php esc_html_e( 'Space', 'skyhop-maintenance' ); ?></kbd> <?php esc_html_e( 'fly', 'skyhop-maintenance' ); ?> <kbd>P</kbd> <?php esc_html_e( 'pause', 'skyhop-maintenance' ); ?></p>
		</div>
	</aside>

	<?php wp_print_scripts( Skyhop_Maintenance_Frontend::HANDLE ); ?>
</body>
</html>
