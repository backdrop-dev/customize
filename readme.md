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

### Sanitize helpers
`Backdrop\Customize\Helpers\Sanitize`: `choices()` (a value must be one of the control's choices), `layouts()` (same), `checkbox()` and `dropdown()` (the ID of a published page).

## Tests
```
php tests/run.php
```

## Copyright and Licenses
This project is licensed under the GNU GPL, version 2 or later.

2019–2026 © Benjamin Lu
