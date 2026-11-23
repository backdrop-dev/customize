# Backdrop Customize
Backdrop Customize adds a shared structure to the theme customizer for ClassicPress and WordPress themes built on [Backdrop](https://github.com/backdrop-dev/framework): four panels, the core sections moved into them, a radio image control and a few sanitize helpers. Your theme adds its own sections, settings and controls.

## Requirements
* PHP 7.4 or later
* [backdrop-dev/framework](https://github.com/backdrop-dev/framework) ^2.0

## Installation
```
composer require backdrop-dev/customize
```

## What it does
* Registers the panels `theme_global` (Theme: Global), `theme_header` (Theme: Header), `theme_content` (Theme: Content) and `theme_footer` (Theme: Footer).
* Moves the core sections: Additional CSS to Global, Site Identity (renamed Branding) and Header Image to Header, and Homepage Settings to Content. It removes the Colors section; return `false` from the `backdrop/customize/remove_colors_section` filter to keep it.
* Registers the `radio-image` control type (`Backdrop\Customize\Controls\RadioImage`) and prints its styles in the customizer.
* Registers built-in components: Layout, Footer credit and Featured image size, plus Colors, Typography and Background for themes that use [Chassis](https://github.com/techkodeninja/chassis). See [Built-in components](#built-in-components) and [Chassis components](#chassis-components).
* Calls your components so they can register their own panels, sections, settings, controls and partials.

## Usage
Create a component class with the methods you need (`registerPanels`, `registerSections`, `registerSettings`, `registerControls` and `registerPartials`, each receiving the `WP_Customize_Manager`), and give the class names to the component in a service provider of your own. Don't register this package's provider as well, or the component boots twice.

```php
use Backdrop\Core\ServiceProvider;
use Backdrop\Customize\Component;

class Provider extends ServiceProvider {

	public function register(): void {

		$this->app->singleton( Component::class, function() {
			return new Component( [ Layout::class, Footer::class ] );
		} );
	}

	public function boot(): void {

		$this->app->resolve( Component::class )->boot();
	}
}
```

A component, with a radio image setting in the Global panel:

```php
class Layout {

	public function registerSections( $manager ) {
		$manager->add_section( 'theme_global_layout', [
			'panel' => 'theme_global',
			'title' => __( 'Layout', 'my-theme' ),
		] );
	}

	public function registerSettings( $manager ) {
		$manager->add_setting( 'theme_global_layout', [
			'default'           => 'sidebar',
			'sanitize_callback' => [ \Backdrop\Customize\Helpers\Sanitize::class, 'choices' ],
		] );
	}

	public function registerControls( $manager ) {
		$manager->add_control( new \Backdrop\Customize\Controls\RadioImage( $manager, 'theme_global_layout', [
			'section' => 'theme_global_layout',
			'label'   => __( 'Layout', 'my-theme' ),
			'choices' => [
				// %1$s is the parent theme URL and %2$s the child theme URL.
				'sidebar' => [ 'label' => __( 'Sidebar', 'my-theme' ), 'url' => '%1$s/public/images/layout-sidebar.svg' ],
				'full'    => [ 'label' => __( 'Full width', 'my-theme' ), 'url' => '%1$s/public/images/layout-full.svg' ],
			],
		] ) );
	}
}
```

Methods you don't need can be left out of a component only if the class defines all five, so extend or copy a base class that defines them as empty methods.

## Built-in components
Layout, Footer credit and Featured image size are built in, and all three are off until your theme turns them on, so a theme only gets the sections it uses. Turn one on by returning `true` from its filter (`backdrop/customize/layout`, `backdrop/customize/footer_credit` or `backdrop/customize/featured_image`). Each one takes its settings from an array filter. They register before your own components, and a section, setting or control that your own component registers with the same ID replaces the built-in one.

| Component | Settings | Arguments filter | Turn on |
| --- | --- | --- | --- |
| Layout | `{panel}_layout`, a radio image setting | `backdrop/customize/layout_args` | `backdrop/customize/layout` |
| Footer credit | `theme_footer_powered_by` and `theme_footer_custom_credit` in Theme: Footer, Credit | `backdrop/customize/footer_credit_args` | `backdrop/customize/footer_credit` |
| Featured image size | `theme_content_feature_image` in Theme: Content, Feature Image | `backdrop/customize/featured_image_args` | `backdrop/customize/featured_image` |

**Layout** needs your layouts, because the package can't know them or their images, so nothing is registered until you give `choices`. `panel` sets where it goes, `theme_content` by default, and the setting ID follows it: `theme_content_layout`, or `theme_global_layout` for `theme_global`. Set `id` to use another ID.

```php
add_filter( 'backdrop/customize/layout', '__return_true' );

add_filter( 'backdrop/customize/layout_args', function( $args ) {
	$args['panel']   = 'theme_global';
	$args['default'] = 'sidebar';
	$args['choices'] = [
		'sidebar' => [ 'label' => __( 'Sidebar', 'my-theme' ), 'url' => '%1$s/public/images/layout-sidebar.svg' ],
		'full'    => [ 'label' => __( 'Full width', 'my-theme' ), 'url' => '%1$s/public/images/layout-full.svg' ],
	];

	return $args;
} );
```

Layout also takes `title`, `priority`, `default` (the first choice if left out) and `transport`.

**Footer credit** takes `panel`, `section`, `title`, `priority`, `powered_by` (`false` leaves out the generated credit checkbox), `powered_by_default`, `powered_by_label`, `credit_default`, `credit_label` and `allowed_html` (the tags kept in the custom credit). Give `selector` and `render_callback` to update the credit in the preview without a reload. Your theme prints the credit.

**Featured image size** takes `panel`, `id`, `title`, `label`, `priority`, `choices` and `default`. Without `choices` it offers every registered image size and `full`, and the default is `post-thumbnail`. Your theme reads the setting where it prints the image.

**Preview:** all three settings use the `postMessage` transport by default, so the preview doesn't reload when they change. Footer credit updates the preview itself once it has a `selector` and `render_callback`. Layout and Featured image size need a preview script in your theme that listens to the setting with `wp.customize()`. Until there is one, the preview doesn't show the change until the setting is saved. Each component takes a `transport` argument, and `refresh` reloads the preview instead.

### Chassis components
Colors, Typography and Background write to [Chassis](https://github.com/techkodeninja/chassis)'s design (the custom properties that `chassis.json` sets), so a theme that uses Chassis can offer them without writing the controls. They are off until a theme returns `true` from `backdrop/customize/colors`, `backdrop/customize/typography` or `backdrop/customize/background`, and they take their settings from `backdrop/customize/colors_args`, `backdrop/customize/typography_args` and `backdrop/customize/background_args`.

Chassis is a soft dependency: it isn't in `composer.json`, and without Chassis (a version with `overrides()`, registered in the container as `chassis`, which `Techkodeninja\Chassis\Providers\Backdrop` does) these components register nothing. They are not for an editor palette: ClassicPress has no block editor.

| Component | Section | Settings |
| --- | --- | --- |
| Colors | Theme: Global, Colors (`theme_global_colors`) | `theme_color_{key}`: text, muted text, dividers, alternate background, accent, text on accent, links, links on hover, headings, header and footer colors |
| Typography | Theme: Global, Typography (`theme_global_typography`) | `theme_type_{key}`: body font, heading font, base font size, line height |
| Background | Theme: Global, Background (`theme_global_background`) | `theme_background_color`, the page background color; no image |

Each control starts at the value Chassis has now for its path (from `chassis.json`). A setting that has never been changed overrides nothing, so the file keeps winning; once it is set, its value is returned from the `chassis/overrides` filter, and Chassis puts it on top of the file. Nothing is written to `chassis.json`.

All three take `panel`, `section`, `title`, `priority`, `transport` and `fields`. A field is `[ 'path' => 'styles.color.accent', 'label' => 'Accent', 'type' => 'color' ]`, where `path` is one of the paths in `Chassis::stylesMap()` and `type` is `color`, `select`, `text` (a CSS length) or `number`; a path Chassis doesn't have is left out. Returning `fields` replaces the list, so a theme can drop the colors it doesn't use or add `settings.font.code`. Typography also takes `fonts`, the stacks the font controls offer as `[ 'CSS font-family' => 'Label' ]`; the stack the theme already has is added to the list. A font in the list only shows if the page loads it, so Typography also takes `handles`, `[ 'CSS font-family' => 'font-slug' ]`, for fonts from [`backdrop-dev/fonts`](https://github.com/backdrop-dev/fonts): the component loads the fonts the settings use (and every listed font in the customizer preview, so a pick shows at once), and leaves a font out of the list when the fonts package can't load it. A stack with no handle is never loaded, which suits system stacks and fonts the theme loads itself. End each stack with a generic family (`sans-serif`, `serif`) so a font that hasn't loaded yet falls back to a similar one.

```php
add_filter( 'backdrop/customize/colors', '__return_true' );
add_filter( 'backdrop/customize/typography', '__return_true' );
add_filter( 'backdrop/customize/background', '__return_true' );

add_filter( 'backdrop/customize/typography_args', function( $args ) {
	$args['fonts']['"Fira Sans", sans-serif'] = 'Fira Sans';
	$args['fonts']['Merriweather, serif']     = 'Merriweather';

	// Loaded from backdrop-dev/fonts when picked.
	$args['handles']['"Fira Sans", sans-serif'] = 'fira-sans';
	$args['handles']['Merriweather, serif']     = 'merriweather';

	return $args;
} );
```

**Preview:** the transport is `postMessage` by default, and the package adds the script that sets the custom property in the preview as the setting changes. Use `refresh` to reload the preview instead.

### Sanitize helpers
`Backdrop\Customize\Helpers\Sanitize`: `choices()` (a value must be one of the control's choices), `layouts()` (same), `checkbox()`, `dropdown()` (the ID of a published page) `html()` (text that keeps only the allowed tags), `color()` (a hex color), `cssLength()` (such as `1.125rem`) and `number()` (a number from 0.5 to 4).

## Tests
```
php tests/run.php
```

## Copyright and Licenses
This project is licensed under the GNU GPL, version 2 or later.

2019–2026 © Benjamin Lu
