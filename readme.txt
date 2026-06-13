=== Margarita Measurements ===
Contributors: Bryan Chetcuti
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 2.4.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Calculate perfect margarita ratios with presets, unit switching, and ABV estimate. Shortcode and block included.

== Description ==
- Presets: Classic, Tommy’s, Frozen, Skinny
- Units: ml, oz, shot, nip
- ABV estimate (toggle)
- Flavour variations: none, spicy, mango, watermelon, strawberry, coconut, virgin
- Shortcode attributes for preset, unit, flavour, drinks, show_abv, mode, and title
- Inline clipboard copy confirmation
- Themeable CSS variables with dark mode support
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
= 2.4.0 =
- Feature: Flavour variations for spicy, mango, watermelon, strawberry, coconut, and virgin margaritas.
- Feature: Shortcode attributes for per-instance preset, unit, flavour, drinks, show_abv, mode, and title overrides.
- Enhancement: Copy action now uses inline “✓ Copied!” feedback with a clipboard fallback and no alert.
- Enhancement: Frontend styles now use CSS custom properties and support prefers-color-scheme dark mode.

= 2.3.0 =
- WordPress 7.0 compatibility confirmed; bumped Tested up to.
- Fixed MM_VERSION constant mismatch (was 2.1.0, now 2.3.0).
- Removed deprecated load_plugin_textdomain call (auto-loaded since WP 4.6).
- Security: REST and AJAX now clamp drinks to mm_max_drinks and validate preset against allowlist.
- Security: ABV calculation now uses per-preset triple_abv value (default 0.40).
- Feature: Custom Preset Builder — create, save, and delete named ratio presets from the Settings page.
- Feature: Pitcher Mode — calculate total quantities for a given pitcher volume instead of per-drink.
- Feature: Salt Rim Estimator — displays estimated salt grams and teaspoons per batch (wet/dry toggle).
- Feature: Print Recipe Card — styled A5 print card injected at print time, no page theme bleed.

= 2.2.0 =
- Presets, units, ABV, REST, AJAX, block, settings, uninstall.
- Minor UI polish and docs update.


== License ==
GPLv2 or later.
