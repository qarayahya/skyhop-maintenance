=== Skyhop Maintenance ===
Tags: maintenance mode, coming soon, maintenance, game, under construction
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A maintenance mode page your visitors can play.

== Description ==

Skyhop Maintenance puts your site into maintenance mode and shows visitors a full-screen, tap-to-fly arcade game while you work. Administrators keep seeing the real site, and search engines get a proper 503 status with a Retry-After header.

* Six hand-drawn themes: Sunny Meadow, Midnight City, Neon Drive, Deep Reef, Cosmic Drift and Candy Clouds, plus a "Surprise me" option
* Optional "back online" time with a live countdown, also used for the Retry-After header
* Choose who still sees the site: administrators, or any logged-in user
* Toolbar badge while maintenance mode is on, so you never forget it
* Optional contact button and log-in link
* Optional theme switcher for visitors
* Three difficulty levels with a gentle speed ramp
* Best score saved in the visitor's browser
* Synthesized sound effects with a mute button
* Keyboard (Space / Arrow Up / W, P to pause), mouse and touch input
* No external requests, no tracking, no images to load

wp-admin, wp-login.php, admin-ajax, WP-Cron and the REST API are never blocked.

== Installation ==

1. Upload the `skyhop-maintenance` folder to `/wp-content/plugins/`.
2. Activate the plugin. Maintenance mode starts switched off.
3. Go to Settings → Skyhop Maintenance, check the page with "Preview page", then switch it on.

== Frequently Asked Questions ==

= Does this hurt SEO? =

No. Visitors and crawlers get a 503 "Service Unavailable" status with a Retry-After header, which is how search engines expect temporary downtime to be signalled.

= Can I let certain visitors through? =

Use the `skyhop_maintenance_can_bypass` filter and return `true`, or the `skyhop_maintenance_should_render` filter to skip specific requests.

== Changelog ==

= 1.0.0 =
* Initial release.
