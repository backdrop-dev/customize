<?php
/**
 * Backdrop Core ( src/Tools/ServiceProvider.php )
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

/**
 * Define namespace
 */
namespace Backdrop\Customize;

use Backdrop\Core\ServiceProvider;

class Provider extends ServiceProvider {

    /**
     * Registration callback that adds a single instance of the customize
     * object to the container.
     *
     * @since  1.0.0
     * @return void
     *
     * @access public
     */
    public function register(): void {

        $this->app->singleton( Component::class );
    }

    /**
     * Boots the customize component by firing its hooks in the `boot()` method.
     *
     * @since  1.0.0
     * @return void
     *
     * @access public
     */
    public function boot(): void {

        $this->app->resolve( Component::class )->boot();
    }
}
