# Changelog

## Unreleased
Added
* Built-in Layout, Footer credit and Featured image size components, registered before the theme's own components. Each takes its settings from a filter (`backdrop/customize/layout_args`, `backdrop/customize/footer_credit_args` and `backdrop/customize/featured_image_args`) and can be turned off by returning `false` from `backdrop/customize/enable_layout`, `backdrop/customize/enable_footer_credit` or `backdrop/customize/enable_featured_image`.
* `Sanitize::html()`.

Removed
* The unused `BackgroundSvg` control. It was never registered.

## 1.0.3 - November 17, 2026
Changed
* The component now takes an array of customizable component classes, registers the panels `theme_global`, `theme_header`, `theme_content` and `theme_footer`, moves the core sections into them, and calls each component to register its own sections, settings, controls and partials. Use it from your own service provider, as in the readme.
* Requires PHP 7.4 and `backdrop-dev/framework` ^2.0, which the package uses but didn't declare, and uses the `Backdrop\Proxies\App` proxy instead of the `Backdrop\App` alias.
* A core section that doesn't exist is skipped instead of causing a fatal error. The Colors section is still removed, and the `backdrop/customize/remove_colors_section` filter keeps it.
* The radio image control now has styles, printed in the customizer.
* Sanitize helpers: fixed the parameter types, which didn't work with the values the customizer passes. `choices()` was added, and `layouts()` calls it.

Added
* Dependency free tests (`php tests/run.php`) and a GitHub Action that lints and runs them on PHP 7.4 to 8.3.
