=== Skyhop 404 ===
Tags: 404, game, error page, fun, arcade
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn your 404 page into a one-tap flying game.

== Description ==

Skyhop 404 replaces your theme's 404 template with a full-screen, tap-to-fly arcade game. Visitors still get a search field and a link home, and the page keeps returning a proper 404 status code.

* Six hand-drawn themes: Sunny Meadow, Midnight City, Neon Drive, Deep Reef, Cosmic Drift and Candy Clouds, plus a "Surprise me" option
* Optional theme switcher for visitors
* Three difficulty levels with a gentle speed ramp
* Best score saved in the visitor's browser
* Synthesized sound effects with a mute button
* Keyboard (Space / Arrow Up / W, P to pause), mouse and touch input
* Admin-only mode and a live preview link for testing before going live
* No external requests, no tracking, no images to load

== Installation ==

1. Upload the `skyhop-404` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Go to Settings → Skyhop 404.

== Frequently Asked Questions ==

= Does this hurt SEO? =

No. The response is still a 404 with a `noindex` meta tag.

= Can I turn it off for certain URLs? =

Use the `skyhop404_should_render` filter and return `false`.

== Changelog ==

= 1.0.0 =
* Initial release.
