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
namespace Backdrop\Customize\Contracts;

use Backdrop\Contracts\Bootable;

use WP_Customize_Manager;

/**
 * Customize class.
 *
 * @since  1.0.0
 * @access public
 */
interface Customize extends Bootable {

    /**
     * Add our panels for customizer.
     *
     * @since  1.0.0
     * @access public
     * @param  WP_Customize_Manager $manager
     * @return void
     */
    public function panels( WP_Customize_Manager $manager );
}
