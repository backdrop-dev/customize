<?php
/**
 * Base class for the built-in customize components.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

use WP_Customize_Manager;

/**
 * A built-in component. It has the same five methods as a theme's own
 * components, and its settings come from a filter so a theme can change them.
 */
abstract class Base {

    /**
     * The arguments after the filter has run.
     *
     * @var array|null
     */
    protected $args = null;

    /**
     * The name of the filter that returns the arguments.
     *
     * @return string
     */
    abstract protected function filter(): string;

    /**
     * The arguments used when a theme doesn't change them.
     *
     * @return array
     */
    abstract protected function defaults(): array;

    /**
     * Returns the arguments, with the theme's changes from the filter merged in.
     * It is built once, the first time it's needed, which is during
     * `customize_register`, after the theme has added its filters.
     *
     * @return array
     */
    protected function args(): array {

        if ( null === $this->args ) {
            $defaults   = $this->defaults();
            $args       = apply_filters( $this->filter(), $defaults );
            $this->args = array_merge( $defaults, is_array( $args ) ? $args : [] );
        }

        return $this->args;
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerPanels( WP_Customize_Manager $manager ) {}

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSections( WP_Customize_Manager $manager ) {}

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSettings( WP_Customize_Manager $manager ) {}

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {}

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerPartials( WP_Customize_Manager $manager ) {}
}
