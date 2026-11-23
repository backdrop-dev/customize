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
* Registers three built-in components: Layout, Footer credit and Featured image size. See [Built-in components](#built-in-components).
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
Layout, Footer credit and Featured image size are registered for you, before your own components. Each one takes its settings from an array filter, and each can be turned off by returning `false` from its `enable` filter. A section, setting or control that your own component registers with the same ID replaces the built-in one.

| Component | Settings | Arguments filter | Turn off |
| --- | --- | --- | --- |
| Layout | `{panel}_layout`, a radio image setting | `backdrop/customize/layout_args` | `backdrop/customize/enable_layout` |
| Footer credit | `theme_footer_powered_by` and `theme_footer_custom_credit` in Theme: Footer, Credit | `backdrop/customize/footer_credit_args` | `backdrop/customize/enable_footer_credit` |
| Featured image size | `theme_content_feature_image` in Theme: Content, Feature Image | `backdrop/customize/featured_image_args` | `backdrop/customize/enable_featured_image` |

**Layout** needs your layouts, because the package can't know them or their images, so nothing is registered until you give `choices`. `panel` sets where it goes, `theme_content` by default, and the setting ID follows it: `theme_content_layout`, or `theme_global_layout` for `theme_global`. Set `id` to use another ID.

```php
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

### Sanitize helpers
`Backdrop\Customize\Helpers\Sanitize`: `choices()` (a value must be one of the control's choices), `layouts()` (same), `checkbox()`, `dropdown()` (the ID of a published page) and `html()` (text that keeps only the allowed tags).

## Tests
```
php tests/run.php
```

## Copyright and Licenses
This project is licensed under the GNU GPL, version 2 or later.

2019–2026 © Benjamin Lu
