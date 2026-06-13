=== Margarita Measurements ===
Contributors: TODO-wordpress-org-username
Tags: margarita, cocktail, calculator, bar, drinks
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 3.0.0
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A margarita calculator for WordPress with recipe ratios, batch scaling, flavour variations, ABV estimates and embeddable cocktail widgets.

== Description ==
Whether you're squeezing limes for two or mixing a larger batch for guests, Margarita Measurements does the maths so you don't have to.

Margarita Measurements helps WordPress sites publish a practical cocktail calculator with preset recipes, unit conversion, flavour variations, pitcher/batch output, print-friendly results, and responsible recipe-planning estimates.

= Who it is for =
* Home users: scale a classic, Tommy's, frozen or skinny margarita without mental arithmetic.
* Cocktail bloggers: embed the full calculator or a compact inline widget inside recipe posts.
* Bars and hospitality: adjust tequila and triple sec ABV to match the bottle being used.
* Event hosts: use batch, pitcher and party-planning outputs where available to plan ingredients.

= Features =
* Margarita recipe calculator with Classic, Tommy's, Frozen and Skinny presets.
* Batch scaling by drink count and pitcher mode by total ml.
* Unit conversion for ml, oz, shot and nip.
* Flavour variations: spicy, mango, watermelon, strawberry, coconut and virgin.
* Salt rim estimate and print recipe card.
* Optional ABV estimate.
* Nutrition and standard drinks estimator for AU, UK and US standard-drink definitions.
* Tequila and triple sec ABV customiser.
* Full shortcode: `[margarita_measurements]`.
* Compact recipe-post shortcode: `[margarita_widget]`.
* Dynamic Gutenberg block for the full calculator.
* Custom preset builder in the WordPress settings screen.
* Themeable CSS variables with dark mode support.
* AJAX form with no page reload.
* REST endpoint for calculations and preset listings.

= Shortcodes =
`[margarita_measurements]`

`[margarita_measurements preset="tommys" drinks="4" unit="ml"]`

`[margarita_measurements preset="tommys" drinks="4" tequila_abv="38" standard_region="AU" show_nutrition="true"]`

`[margarita_widget]`

`[margarita_widget preset="classic" drinks="2" align="right"]`

= Privacy =
Recipe calculations run in WordPress and the browser through the plugin's local AJAX/REST handlers. The plugin does not send recipe calculations to third-party services, does not add analytics, and does not track visitors.

== Installation ==
1. Upload the ZIP via Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Add `[margarita_measurements]` to a page, insert the Gutenberg block, or place `[margarita_widget]` inside a recipe post.

== Screenshots ==
1. Full calculator on a light theme.
2. Result card for a scaled margarita batch.
3. Flavour selector with margarita variants.
4. Pitcher or batch output.

== Frequently Asked Questions ==
= Does it work without Gutenberg? =
Yes. Use the `[margarita_measurements]` shortcode in any shortcode-capable editor, template or widget area.

= Can I use it in the Classic Editor? =
Yes. Paste the shortcode into the Classic Editor content area.

= Can I embed a smaller version inside a recipe post? =
Yes. Use `[margarita_widget]` for a compact calculator. It supports preset, unit, drinks, flavour, title, show_abv, show_nutrition and align attributes.

= Can I change the tequila ABV? =
Yes. The Advanced panel includes tequila and triple sec ABV fields, and the shortcode supports `tequila_abv` and `triple_sec_abv` attributes.

= Does it calculate standard drinks? =
Yes. The nutrition panel estimates alcohol grams and standard drinks using AU (10g), UK (8g) or US (14g) definitions. These are approximate recipe-planning values only.

= Does it store visitor data? =
No. The plugin does not store visitor recipe calculations or visitor account data.

= Does it call third-party services? =
No. It does not call third-party APIs or load remote tracking services for calculations.

== Changelog ==
= 3.0.0 =
* Added nutrition and standard drinks estimator.
* Added AU, UK and US standard-drink region support.
* Added tequila and triple sec ABV customisation.
* Added compact `[margarita_widget]` shortcode for recipe-post embedding.
* Refreshed WordPress.org readme positioning.

= 2.4.0 =
* Feature: Flavour variations for spicy, mango, watermelon, strawberry, coconut, and virgin margaritas.
* Feature: Shortcode attributes for per-instance preset, unit, flavour, drinks, show_abv, mode, and title overrides.
* Enhancement: Copy action now uses inline "Copied" feedback with a clipboard fallback and no alert.
* Enhancement: Frontend styles now use CSS custom properties and support prefers-color-scheme dark mode.

= 2.3.0 =
* WordPress 7.0 compatibility confirmed; bumped Tested up to.
* Fixed MM_VERSION constant mismatch.
* Security: REST and AJAX now clamp drinks to mm_max_drinks and validate preset against allowlist.
* Security: ABV calculation now uses per-preset triple_abv value.
* Feature: Custom Preset Builder, Pitcher Mode, Salt Rim Estimator and Print Recipe Card.

= 2.2.0 =
* Presets, units, ABV, REST, AJAX, block, settings and uninstall.
* Minor UI polish and docs update.

== Upgrade Notice ==
= 3.0.0 =
Adds responsible recipe-planning estimates, ABV customisation and a compact widget shortcode for recipe posts.

== License ==
GPLv2 or later.
