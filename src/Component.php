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
use WP_Customize_Manager;

/**
 * Customize class.
 */
class Component implements Bootable {

    /**
     * Adds our customizer-related actions to the appropriate hooks.
     *
     * @return void
     */
    public function boot(): void {

        // Register panels, sections, settings, controls, and partials.
        add_action( 'customize_register', [ $this, 'registerControls' ], 0 );
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
    }

}
