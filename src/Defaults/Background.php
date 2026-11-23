<?php
/**
 * Built-in component: Background (Chassis).
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

/**
 * Background: the page background color, in Theme: Global.
 *
 * Off by default, and needs Chassis. A theme turns it on with the
 * `backdrop/customize/background` filter and changes it with
 * `backdrop/customize/background_args`: `panel`, `section` (`theme_global_background`),
 * `title`, `priority`, `transport` and `fields` work as in Colors (the setting ID is
 * `theme_background_{key}`). Color only: a background image isn't included.
 *
 * @since 1.1.0
 */
class Background extends ChassisBase {

    protected function filter(): string {

        return 'backdrop/customize/background_args';
    }

    protected function prefix(): string {

        return 'theme_background_';
    }

    protected function defaults(): array {

        return [
            'panel'     => 'theme_global',
            'section'   => 'theme_global_background',
            'title'     => esc_html__( 'Background', 'backdrop' ),
            'priority'  => null,
            'transport' => 'postMessage',
            'fields'    => [
                'color' => [ 'path' => 'styles.color.background', 'label' => esc_html__( 'Background color', 'backdrop' ), 'type' => 'color' ],
            ],
        ];
    }
}
