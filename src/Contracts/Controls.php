<?php
/**
 * Base customize control.
 *
 * This is a base customize control class for our other controls to extend.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Contracts;

use WP_Customize_Control;

/**
 * Multiple checkbox customize control class.
 */
abstract class Control extends WP_Customize_Control {

    /**
     * This is the PHP callback for rendering the control content. JS-based
     * controls require this method to be empty. Because most of our classes
     * utilize JS templates, we're defining this in the base class to not
     * worry about it in our sub-classes.
     *
     * @return bool
     */
    protected function render_content() {}

}