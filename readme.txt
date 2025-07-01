=== Smart Switcher for VK and YouTube ===
Contributors: bebych
Tags: youtube, vk, video, geo, switcher, block
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smartly switches between VK and YouTube videos based on user geolocation. Adds a button for the Classic Editor and a block for Gutenberg.

== Description ==
This plugin automatically shows a YouTube or VK video depending on the user's geolocation. It provides a convenient button for the classic editor and a block for Gutenberg.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/smart-video-switcher` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

== External services ==
This plugin uses a third-party service to determine the user's country based on their IP address. This is necessary to automatically select the appropriate video player (YouTube or VK).

* **Service:** ipwho.is
* **Data Sent:** The user's IP address is sent to the service each time a page with the video block is loaded.
* **Terms of Use:** https://ipwhois.io/terms-of-service
* **Privacy Policy:** https://ipwhois.io/privacy-policy

== Changelog ==
= 2.0.0 =
* Major update to comply with WordPress.org guidelines.
* FIXED: Replaced short `svs` prefix with a more unique `ssvy` prefix for all functions and hooks.
* FIXED: Properly enqueued all admin and frontend scripts and styles using `wp_enqueue_script` and `wp_enqueue_style`.
* FIXED: Added documentation for the external service `ipwho.is` in the readme.txt file.
* IMPROVED: Refactored shortcode logic and scripts.

= 1.9.1 =
* First public version.
