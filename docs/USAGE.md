# Margarita Measurements usage guide

This guide covers the practical ways to embed Margarita Measurements and choose defaults for a WordPress site. For project overview, requirements, and release notes, see the main [README.md](../README.md).

## Embedding the calculator

1. Install the plugin by uploading the plugin ZIP in WordPress or by copying the repository folder to `wp-content/plugins/margarita-measurements`.
2. Activate **Margarita Measurements** in **Plugins → Installed Plugins**.
3. Add the full calculator shortcode to a post or page, insert the Gutenberg block, or use the compact widget shortcode in recipe content.
4. Review site-wide defaults in **Settings → Margarita Measurements**.

## Shortcode examples

Full calculator with site defaults:

```text
[margarita_measurements]
```

Full calculator with a Tommy's preset, four drinks, and millilitres:

```text
[margarita_measurements preset="tommys" drinks="4" unit="ml"]
```

Full calculator with a flavour and ABV estimate visible:

```text
[margarita_measurements preset="classic" flavour="mango" drinks="6" show_abv="true"]
```

Full calculator with recipe-planning estimate options:

```text
[margarita_measurements preset="tommys" drinks="4" tequila_abv="38" standard_region="AU" show_nutrition="true"]
```

## Compact widget

Use the compact widget when you want a smaller calculator inside recipe posts or side content.

```text
[margarita_widget]
```

```text
[margarita_widget preset="classic" drinks="2" align="right"]
```

The widget supports defaults such as `preset`, `unit`, `drinks`, `flavour`, `title`, `show_abv`, `show_nutrition`, and `align`.

## Common attributes

Common shortcode attributes include:

| Attribute | Example | Notes |
| --- | --- | --- |
| `preset` | `preset="tommys"` | Opens the calculator with a built-in or custom preset. |
| `unit` | `unit="ml"` | Supports `ml`, `oz`, `shot`, and `nip`. |
| `drinks` | `drinks="4"` | Initial drink count, limited by the Max Drinks setting. |
| `flavour` | `flavour="mango"` | Opens with a flavour variation selected. |
| `show_abv` | `show_abv="true"` | Overrides the site default for that calculator instance. |
| `show_nutrition` | `show_nutrition="true"` | Shows nutrition and standard-drink estimates when supported. |
| `tequila_abv` | `tequila_abv="38"` | Initial tequila ABV estimate value. |
| `triple_sec_abv` | `triple_sec_abv="40"` | Initial triple sec ABV estimate value. |
| `standard_region` | `standard_region="AU"` | Supports `AU`, `US`, and `UK`. |
| `align` | `align="right"` | Widget alignment: `none`, `left`, `right`, or `center`. |

## Admin defaults

The settings page at **Settings → Margarita Measurements** controls site-wide defaults:

- Default unit.
- Default recipe preset.
- Maximum drinks per calculation.
- Whether estimated ABV is shown by default.
- Standard-drink region for estimates.
- Party-planning bottle sizes for tequila, triple sec, citrus, agave, and flavour mixers.
- Custom presets stored as WordPress options for use in shortcodes, blocks, and the calculator UI.

Site defaults apply when no shortcode or block override is provided. Shortcode and block settings override these defaults for that specific calculator instance.

Nutrition, ABV and standard-drink values are estimates for recipe planning only.

## Visitor selections and privacy

Visitor choices are not saved. Calculator selections are used only for the current calculation. Use site defaults, shortcode attributes or block settings when you want a calculator to open with specific values.

Visitor selections are not stored. The calculator does not use localStorage, cookies or user accounts to remember recipe choices.

The plugin does not store visitor preferences, calculations, or recipe choices. It also does not add saved recipes, visitor accounts, favourites, URL sharing, analytics, or third-party calculation services.

## Gutenberg block

The plugin provides a dynamic **Margarita Measurements** block for the full calculator. Block attributes can set block-level defaults for a particular block instance. Those block-level defaults override site defaults for that instance only.

## Troubleshooting

- If a shortcode displays as plain text, confirm that it was added to a shortcode-capable content area.
- If a preset is not selected, confirm the preset key is valid or create a custom preset in the settings screen.
- If the drink count is lower than expected, check the **Max Drinks** setting.
- If ABV is not visible, check the **Show ABV** setting or add `show_abv="true"` to that shortcode instance.
- For bugs and support, use the [GitHub issues page](https://github.com/bchetcuti/margaritaWP/issues).
