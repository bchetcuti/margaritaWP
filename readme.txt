=== Margarita Measurements ===
Contributors: you
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Calculate perfect margarita ratios with presets, unit switching, and ABV estimate. Shortcode and block included.

== Description ==
- Presets: Classic, Tommy’s, Frozen, Skinny
- Units: ml, oz, shot, nip
- ABV estimate (toggle)
- Gutenberg block + shortcode `[margarita_measurements]`
- REST endpoint: `/wp-json/margarita/v1/calculate?preset=classic&drinks=4&unit=ml`
- AJAX form (no page reload), accessible, i18n-ready
- Uninstall removes options

== Installation ==
1. Upload the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate.
3. Add the shortcode to a page or insert the block.

== Screenshots ==
1. Shortcode on a page.
2. Presets and units.
3. ABV details.

== Frequently Asked Questions ==
= Can I change default units or preset? =
Yes, in **Settings → Margarita Measurements**.

= Does it support Classic Editor? =
Yes. The shortcode works anywhere.

== Changelog ==
= 2.1.0 =
- Removed Party Mode and Cost features. Simplified UI.
- Minor UI polish and docs update.

= 2.0.0 =
- Presets, units, ABV, REST, AJAX, block, settings, uninstall.

== License ==
GPLv2 or later.
