<?php
/**
 * Standalone 404 game page. Loaded through template_include, so the theme's
 * header and footer stay out of the way of the full-screen canvas.
 *
 * @package Skyhop404
 */

defined( 'ABSPATH' ) || exit;

$skyhop_settings = Skyhop404_Frontend::render_settings();
$skyhop_themes   = Skyhop404_Plugin::themes();
$skyhop_theme    = isset( $skyhop_themes[ $skyhop_settings['theme'] ] ) ? $skyhop_settings['theme'] : 'meadow';
$skyhop_site     = get_bloginfo( 'name' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex, follow">
	<title><?php echo esc_html( sprintf( /* translators: %s: site name. */ __( 'Page not found – %s', 'skyhop-404' ), $skyhop_site ) ); ?></title>
	<?php wp_print_styles( Skyhop404_Frontend::HANDLE ); ?>
</head>
<body class="sh-body sh-theme-<?php echo esc_attr( $skyhop_theme ); ?>" data-state="ready">

	<canvas id="sh-canvas" role="img" aria-label="<?php esc_attr_e( 'Skyhop: a tap-to-fly mini game. Press Space or tap to fly through the gaps.', 'skyhop-404' ); ?>"></canvas>

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
		<a class="sh-panel__site" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $skyhop_site ); ?></a>
		<p class="sh-panel__eyebrow"><?php esc_html_e( 'Error 404', 'skyhop-404' ); ?></p>
		<h1 id="sh-headline" class="sh-panel__title"><?php echo esc_html( $skyhop_settings['headline'] ); ?></h1>

		<?php if ( '' !== trim( $skyhop_settings['message'] ) ) : ?>
			<p class="sh-panel__text"><?php echo nl2br( esc_html( $skyhop_settings['message'] ) ); ?></p>
		<?php endif; ?>

		<?php if ( $skyhop_settings['show_search'] ) : ?>
			<form class="sh-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="sh-search-input"><?php esc_html_e( 'Search this site', 'skyhop-404' ); ?></label>
				<input id="sh-search-input" class="sh-search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search this site', 'skyhop-404' ); ?>" autocomplete="off">
				<button class="sh-search__btn" type="submit" aria-label="<?php esc_attr_e( 'Search', 'skyhop-404' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6" fill="none" stroke="currentColor" stroke-width="2"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</form>
		<?php endif; ?>

		<?php if ( $skyhop_settings['show_home'] ) : ?>
			<a class="sh-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to homepage', 'skyhop-404' ); ?>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		<?php endif; ?>

		<div class="sh-panel__footer">
			<?php if ( $skyhop_settings['allow_switch'] ) : ?>
				<div class="sh-themes" role="group" aria-label="<?php esc_attr_e( 'Game theme', 'skyhop-404' ); ?>">
					<?php foreach ( $skyhop_themes as $skyhop_key => $skyhop_meta ) : ?>
						<button type="button" class="sh-swatch sh-swatch--<?php echo esc_attr( $skyhop_key ); ?>" data-theme="<?php echo esc_attr( $skyhop_key ); ?>" title="<?php echo esc_attr( $skyhop_meta['label'] ); ?>" aria-pressed="false">
							<span class="screen-reader-text"><?php echo esc_html( $skyhop_meta['label'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<p class="sh-keys"><kbd><?php esc_html_e( 'Space', 'skyhop-404' ); ?></kbd> <?php esc_html_e( 'fly', 'skyhop-404' ); ?> <kbd>P</kbd> <?php esc_html_e( 'pause', 'skyhop-404' ); ?></p>
		</div>
	</aside>

	<?php wp_print_scripts( Skyhop404_Frontend::HANDLE ); ?>
</body>
</html>
