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

use WP_Customize_Manager;

/**
 * Customize class.
 *
 * @since  1.0.0
 * @access public
 */
class Component extends Bootable {

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
    }
}
