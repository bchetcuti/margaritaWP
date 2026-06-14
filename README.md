# Margarita Measurements

Margarita Measurements is a WordPress plugin for publishing an interactive margarita recipe calculator. Version 3.0.0 focuses on recipe ratios, batch scaling, flavour variations, ABV estimates, standard-drink estimates, and embeddable calculator views for WordPress content.

For practical shortcode and admin setup notes, see [`docs/USAGE.md`](docs/USAGE.md). For the WordPress.org directory-format listing, see [`readme.txt`](readme.txt). That file is the canonical plugin-directory readme for submission metadata, screenshots, FAQ, changelog, and upgrade notices.

## Features

- Full margarita calculator shortcode for pages and posts.
- Compact recipe-post widget shortcode for inline use in articles.
- Dynamic Gutenberg block that renders the full calculator on the frontend.
- Built-in presets for Classic, Tommy's, Frozen, and Skinny margaritas.
- Custom preset builder in the WordPress settings screen.
- Batch scaling by drink count plus pitcher and party-planning modes.
- Unit conversion for ml, oz, shot, and nip.
- Flavour variations for spicy, mango, watermelon, strawberry, coconut, and virgin margaritas.
- Optional ABV estimate with tequila and triple sec ABV controls.
- Nutrition and standard-drink estimates for AU, UK, and US standard-drink definitions.
- Salt-rim estimate, print-friendly recipe card output, responsive styles, and dark-mode-aware CSS variables.
- Local AJAX calculations and REST endpoints for calculation and preset data.
- Visitor selections are not stored. The calculator does not use localStorage, cookies or user accounts to remember recipe choices.

## Requirements

- WordPress: 5.8 or later.
- PHP: 8.1 or later.
- Plugin version: 3.0.0.

These requirements should stay aligned with the plugin header in `margarita-measurements.php` and the WordPress.org `readme.txt` metadata.

## Installation from source

1. Clone or download this repository.
2. Copy the repository directory to `wp-content/plugins/margarita-measurements` in a WordPress install.
3. Activate **Margarita Measurements** from **Plugins → Installed Plugins**.
4. Add the shortcode to content or insert the block from the block editor.

## Shortcodes

Full calculator:

```text
[margarita_measurements]
```

Full calculator with defaults:

```text
[margarita_measurements preset="tommys" drinks="4" unit="ml"]
```

Full calculator with ABV and standard-drink options:

```text
[margarita_measurements preset="tommys" drinks="4" tequila_abv="38" standard_region="AU" show_nutrition="true"]
```

Compact recipe widget:

```text
[margarita_widget]
```

Compact widget with alignment and defaults:

```text
[margarita_widget preset="classic" drinks="2" align="right"]
```

## Gutenberg block

The plugin registers a dynamic **Margarita Measurements** block. The block editor script uses WordPress editor packages, and the block assets are registered from the `block/` directory without adding a separate build step.

## Settings summary

The WordPress settings screen supports site-wide defaults. Site defaults apply when no shortcode or block override is provided, and shortcode or block settings override these defaults for that specific calculator instance.

The WordPress settings screen supports:

- Default unit.
- Default preset.
- Maximum drinks per calculation.
- ABV display toggle.
- Standard-drink region default.
- Bottle-size defaults for planning output.
- Custom margarita presets.

## Privacy and external services

Margarita Measurements does not add analytics, tracking, visitor accounts, saved visitor recipes, URL sharing, remote API calls, or third-party calculation services. Recipe calculations run in the browser and through the plugin's local WordPress AJAX/REST handlers. Visitor selections are not stored. The calculator does not use localStorage, cookies or user accounts to remember recipe choices.

## Architecture notes

- `margarita-measurements.php` contains the plugin header, constants, autoloader, activation defaults, and bootstrap hooks.
- `includes/class-plugin.php` renders shortcodes, registers frontend assets, and registers the dynamic block.
- `includes/class-calculator.php` contains recipe, unit, ABV, nutrition, standard-drink, and planning calculations.
- `includes/class-settings.php` registers and sanitizes admin settings.
- `includes/class-rest.php` exposes REST endpoints for presets and calculations.
- `includes/class-ajax.php` handles frontend AJAX calculations and admin custom-preset deletion.
- `block/` contains block metadata, editor script/style, frontend block style, and editor asset dependencies.
- `languages/` contains the translation template.
- `assets-wporg/` documents WordPress.org listing artwork planning; those assets are not part of the plugin runtime.

## Development and testing

Useful local checks before packaging:

```bash
find . -name "*.php" -not -path "./dist/*" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l
```

```bash
vendor/bin/phpunit -c tests/phpunit.xml
```

```bash
wp i18n make-pot . languages/margarita-measurements.pot --exclude=dist,tests,.git,node_modules,vendor
```

PHPUnit and WP-CLI depend on the local development environment; the repository does not vendor large development dependencies.

## WordPress.org release notes

Before WordPress.org submission, the owner still needs to confirm the final WordPress.org contributor username, final Plugin URI/Author URI decisions if they change, final banner/icon/screenshot artwork, and the actual WordPress.org submission workflow.

## License

GPLv2 or later. See [`LICENSE`](LICENSE).
