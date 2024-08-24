<?php
/**
 * Customize component.
 *
 * Integrates the theme's settings into the customizer.
 *
 * @package   backdrop
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2024 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://luthemes.com/portfolio/backdrop
 */

namespace backdrop\Customize;

use Backdrop\App;
use Backdrop\Contracts\Bootable;
use function Backdrop\Mix\asset;
use backdrop\Tools\Collection;
use backdrop\Tools\Mod;
use WP_Customize_Manager;
use backdrop\Template\Footer;

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

		// Register panels, sections, settings, controls, and partials.
		array_map( function( $callback ) {
			add_action( 'customize_register', [ $this, $callback ] );
		}, [
			'registerPanels',
			'registerSections',
			'registerSettings',
			'registerControls'
		] );
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
				'backdrop_theme_global'  => esc_html__( 'Theme: Global',  'backdrop' ),
				'backdrop_theme_header'  => esc_html__( 'Theme: Header',  'backdrop' ),
				'backdrop_theme_content' => esc_html__( 'Theme: Content', 'backdrop' ),
				'backdrop_theme_footer'  => esc_html__( 'Theme: Footer',  'backdrop' )
		];

		foreach ( $panels as $panel => $label ) {
				$manager->add_panel( $panel, [
						'title'    => $label,
						'priority' => 100
				] );
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

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerSettings( $manager );
		}
	}

    /**
     * Add our controls for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {

		foreach ( $this->components as $component ) {

			App::resolve( $component )->registerControls( $manager );
		}
	}
}