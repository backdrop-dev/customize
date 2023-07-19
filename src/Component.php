<?php
/**
 * Customize component.
 *
 * @package   Backdrop
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019-2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

/**
 * Define namespace
 */
namespace Backdrop\Customize;

use Backdrop\Contracts\Bootable;
use Backdrop\Customize\Contracts\Customize;

use WP_Customize_Manager;

/**
 * Customize class.
 *
 * @since  1.0.0
 * @access public
 */
class Component implements Customize {

    public function boot() {
        add_action( 'customize_register', [ $this, 'panels' ] );
		add_action( 'customize_register', [ $this, 'sections' ] );
		add_action( 'customize_register', [ $this, 'settings' ] );
		add_action( 'customize_register', [ $this, 'controls' ] );
    }

    /**
     * Add our panels for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function panels( WP_Customize_Manager $manager ) {
		$panels = [
			'theme_global'  => esc_html__( 'Theme: Global', 'silver-quantum' ),
			'theme_header'  => esc_html__( 'Theme: Header', 'silver-quantum' ),
			'theme_content' => esc_html__( 'Theme: Content', 'silver-quantum' ),
			'theme_footer'  => esc_html__( 'Theme: Footer', 'silver-quantum' )
		];

		foreach ( $panels as $panel => $label ) {
			$manager->add_panel( $panel, [
				'title'    => $label,
				'priority' => 100
			] );
		}
    }

    /**
     * Add our sections for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function sections( WP_Customize_Manager $manager ) {

    }

    /**
     * Add our settings for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function settings( WP_Customize_Manager $manager ) {

    }

    /**
     * Add our controls for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function controls( WP_Customize_Manager $manager ) {

    }
}