# Changelog

## Unreleased
Added
* Typography loads the fonts it offers. A new `handles` argument (`backdrop/customize/typography_args`) names the `backdrop-dev/fonts` font behind each stack in `fonts`; the component enqueues the fonts the settings use on the front end and every listed font in the customizer preview. A font the fonts package can't load is left out of the list, so the control never offers a font that wouldn't show. A stack with no handle is never loaded, as before. `backdrop-dev/fonts` is a soft dependency.

## 1.0.5 - November 23, 2026
Added
* Built-in Colors, Typography and Background components (Background is the page color only) that write to Chassis's design. They are off until a theme returns `true` from `backdrop/customize/colors`, `backdrop/customize/typography` or `backdrop/customize/background`, take their settings from `backdrop/customize/colors_args`, `backdrop/customize/typography_args` and `backdrop/customize/background_args`, and register nothing without Chassis (a version with `overrides()`), which is a soft dependency. A changed setting is passed to Chassis through the `chassis/overrides` filter, so it wins over `chassis.json`; one that was never changed overrides nothing. They use `postMessage` with a live preview script the package prints.
* `Sanitize::color()`, `Sanitize::cssLength()` and `Sanitize::number()`.

## 1.0.4 - November 23, 2026
Added
* Built-in Layout, Footer credit and Featured image size components, registered before the theme's own components. All three are off until a theme returns `true` from `backdrop/customize/layout`, `backdrop/customize/footer_credit` or `backdrop/customize/featured_image`. Each takes its settings from a filter (`backdrop/customize/layout_args`, `backdrop/customize/footer_credit_args` and `backdrop/customize/featured_image_args`) and uses the `postMessage` transport, which each takes a `transport` argument to change.
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
