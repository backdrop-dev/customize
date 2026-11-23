<?php
/**
 * Customize class.
 *
 * Registers JS-based panel, section, and/or control types if booted. Otherwise,
 * theme authors must manually register these.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize;

use Backdrop\Contracts\Bootable;
use Backdrop\Customize\Controls\RadioImage;
use Backdrop\Customize\Defaults\FeaturedImage;
use Backdrop\Customize\Defaults\FooterCredit;
use Backdrop\Customize\Defaults\Layout;
use WP_Customize_Manager;
use Backdrop\Proxies\App;

/**
 * Customize class.
 */
class Component implements Bootable {

    /**
     * Array of `Customizable` components bound to the container.
     *
     * @since  1.0.0
     * @access protected
     * @var    array
     */
    protected $components = [];

    /**
     * The built-in components that are turned on. Built the first time it's needed.
     *
     * @since  1.0.4
     * @access protected
     * @var    array|null
     */
    protected $defaults = null;

    /**
     * Sets up initial object properties.
     *
     * @since  1.0.0
     * @access public
     * @param  array  $components  Array `Customizable` component names.
     * @return void
     */
    public function __construct( array $components = [] ) {

        $this->components = $components;
    }

    /**
     * Adds our customizer-related actions to the appropriate hooks.
     *
     * @since  1.0.0
     * @return void
     *
     * @access public
     */
    public function boot(): void {

		array_map( function( $callback ) {

            // Register panels, sections, settings, controls, and partials.
			add_action( 'customize_register', [ $this, $callback ] );
		}, [
			'registerPanels',
			'registerSections',
			'registerSettings',
			'registerControls',
			'registerPartials',
		] );

		add_action( 'customize_controls_print_styles', [ $this, 'printStyles' ] );
	}

    /**
     * Callback for registering panels.
     *
     * @link   https://developer.wordpress.org/themes/customize-api/customizer-objects/#panels
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager  $manager  Instance of the customize manager.
     * @return void
     */
    public function registerPanels( WP_Customize_Manager $manager ) {
		$panels = [
				'theme_global'  => esc_html__( 'Theme: Global',  'backdrop' ),
				'theme_header'  => esc_html__( 'Theme: Header',  'backdrop' ),
				'theme_content' => esc_html__( 'Theme: Content', 'backdrop' ),
				'theme_footer'  => esc_html__( 'Theme: Footer',  'backdrop' )
		];

		foreach ( $panels as $panel => $label ) {
				$manager->add_panel( $panel, [
						'title'    => $label,
						'priority' => 100
				] );
		}

		foreach ( $this->defaults() as $default ) {

			$default->registerPanels( $manager );
		}

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerPanels( $manager );
		}
    }

	/**
	 * Callback for registering sections.
	 *
	 * @link   https://developer.wordpress.org/themes/customize-api/customizer-objects/#sections
	 * @since  1.0.0
	 * @access public
	 * @param  WP_Customize_Manager  $manager  Instance of the customize manager.
	 * @return void
	 */
    public function registerSections( WP_Customize_Manager $manager ) {

        $this->moveSection( $manager, 'custom_css', 'theme_global' );
        $this->moveSection( $manager, 'title_tagline', 'theme_header', [ 'title' => esc_html__( 'Branding', 'backdrop' ) ] );
        $this->moveSection( $manager, 'header_image', 'theme_header' );
        $this->moveSection( $manager, 'static_front_page', 'theme_content', [ 'priority' => 5 ] );

        // The core Colors section is removed by default. Return false from this filter to keep it.
        if ( apply_filters( 'backdrop/customize/remove_colors_section', true ) ) {
            $manager->remove_section( 'colors' );
        }

		foreach ( $this->defaults() as $default ) {

			$default->registerSections( $manager );
		}

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerSections( $manager );
		}
    }

	/**
	 * Callback for registering controls.
	 *
	 * @link   https://developer.wordpress.org/themes/customize-api/customizer-objects/#controls
	 * @since  1.0.0
	 * @access public
	 * @param  WP_Customize_Manager  $manager  Instance of the customize manager.
	 * @return void
	 */
    public function registerSettings( WP_Customize_Manager $manager ) {

		foreach ( $this->defaults() as $default ) {

			$default->registerSettings( $manager );
		}

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerSettings( $manager );
		}
	}

    /**
     * Registers our JS-based custom control types with WordPress.
     *
     * @param object $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {

        $controls = [
            RadioImage::class,
        ];

        array_map( static function ( $control ) use ( $manager ) {
            $manager->register_control_type( $control );
        }, $controls );

		foreach ( $this->defaults() as $default ) {

			$default->registerControls( $manager );
		}

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerControls( $manager );
		}
    }

	/**
	 * Registers customizer partials.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  WP_Customize_Manager  $manager
	 * @return void
	 */
	public function registerPartials( WP_Customize_Manager $manager ) {

		foreach ( $this->defaults() as $default ) {

			$default->registerPartials( $manager );
		}

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerPartials( $manager );
		}
	}

    /**
     * Returns the built-in components that the theme turned on. They are all off
     * until a theme returns `true` from its filter: `backdrop/customize/enable_layout`,
     * `backdrop/customize/enable_footer_credit` or `backdrop/customize/enable_featured_image`.
     *
     * They are registered before the theme's own components, so a theme that
     * registers the same section, setting or control replaces the built-in one.
     *
     * @since  1.0.4
     * @access protected
     * @return array
     */
    protected function defaults(): array {

        if ( null === $this->defaults ) {
            $this->defaults = [];

            if ( apply_filters( 'backdrop/customize/enable_layout', false ) ) {
                $this->defaults[] = new Layout();
            }

            if ( apply_filters( 'backdrop/customize/enable_footer_credit', false ) ) {
                $this->defaults[] = new FooterCredit();
            }

            if ( apply_filters( 'backdrop/customize/enable_featured_image', false ) ) {
                $this->defaults[] = new FeaturedImage();
            }
        }

        return $this->defaults;
    }

    /**
     * Moves a core section into a panel, if the section exists.
     *
     * @param  WP_Customize_Manager  $manager
     * @param  string                $id
     * @param  string                $panel
     * @param  array                 $properties  Other section properties to set.
     * @return void
     */
    protected function moveSection( WP_Customize_Manager $manager, string $id, string $panel, array $properties = [] ): void {

        $section = $manager->get_section( $id );

        if ( ! $section ) {
            return;
        }

        $section->panel = $panel;

        foreach ( $properties as $property => $value ) {
            $section->$property = $value;
        }
    }

    /**
     * Prints the styles for the radio image control in the customizer.
     *
     * @return void
     */
    public function printStyles(): void { ?>
        <style id="backdrop-customize-styles">
            .customize-control-backdrop-radio-image .radio-image { display: inline-block; margin: 0 8px 8px 0; position: relative; vertical-align: top; }
            .customize-control-backdrop-radio-image .radio-image__radio { opacity: 0; position: absolute; }
            .customize-control-backdrop-radio-image .radio-image__image { border: 2px solid transparent; border-radius: 3px; box-sizing: border-box; cursor: pointer; display: block; height: auto; max-width: 100%; width: 100%; }
            .customize-control-backdrop-radio-image .radio-image__radio:checked + .radio-image__label + .radio-image__image { border-color: #0073aa; }
            .customize-control-backdrop-radio-image .radio-image__radio:focus + .radio-image__label + .radio-image__image { box-shadow: 0 0 0 1px #fff, 0 0 0 3px #0073aa; }
        </style>
    <?php }
}
