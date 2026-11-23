<?php
/**
 * Layout customize component.
 *
 * A radio image setting for the theme layout. The package can't know a theme's
 * layouts or layout images, so nothing is registered until the theme gives the
 * choices with the `backdrop/customize/layout_args` filter.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

use Backdrop\Customize\Controls\RadioImage;
use Backdrop\Customize\Helpers\Sanitize;
use WP_Customize_Manager;

/**
 * Layout component.
 */
class Layout extends Base {

    /**
     * @return string
     */
    protected function filter(): string {

        return 'backdrop/customize/layout_args';
    }

    /**
     * Arguments:
     *
     * * `panel`     The panel the section goes in. Default `theme_content`.
     * * `id`        The section and setting ID. Default is the panel followed by
     *               `_layout`, so `theme_content_layout` or `theme_global_layout`.
     * * `title`     The section and control label.
     * * `priority`  The section priority.
     * * `choices`   Layouts as `[ 'key' => [ 'label' => '', 'url' => '' ] ]`, where
     *               `url` works like the `RadioImage` control's. Without choices
     *               the component registers nothing.
     * * `default`   The default layout. Default is the first choice.
     * * `transport` `postMessage` by default, so the theme needs a preview script that
     *               listens to the setting. Use `refresh` to reload the preview instead.
     *
     * @return array
     */
    protected function defaults(): array {

        return [
            'panel'     => 'theme_content',
            'id'        => '',
            'title'     => esc_html__( 'Layout', 'backdrop' ),
            'priority'  => 10,
            'choices'   => [],
            'default'   => '',
            'transport' => 'postMessage',
        ];
    }

    /**
     * @return string
     */
    protected function id(): string {

        $args = $this->args();

        return '' !== $args['id'] ? $args['id'] : $args['panel'] . '_layout';
    }

    /**
     * @return bool
     */
    protected function enabled(): bool {

        $choices = $this->args()['choices'];

        return is_array( $choices ) && ! empty( $choices );
    }

    /**
     * @return string
     */
    protected function defaultChoice(): string {

        $args = $this->args();

        if ( array_key_exists( $args['default'], $args['choices'] ) ) {
            return $args['default'];
        }

        return (string) key( $args['choices'] );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSections( WP_Customize_Manager $manager ) {

        if ( ! $this->enabled() ) {
            return;
        }

        $args = $this->args();

        $manager->add_section( $this->id(), [
            'panel'    => $args['panel'],
            'title'    => $args['title'],
            'priority' => $args['priority'],
        ] );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSettings( WP_Customize_Manager $manager ) {

        if ( ! $this->enabled() ) {
            return;
        }

        $manager->add_setting( $this->id(), [
            'default'           => $this->defaultChoice(),
            'sanitize_callback' => [ Sanitize::class, 'choices' ],
            'transport'         => $this->args()['transport'],
        ] );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {

        if ( ! $this->enabled() ) {
            return;
        }

        $args = $this->args();

        $manager->add_control( new RadioImage( $manager, $this->id(), [
            'label'   => $args['title'],
            'section' => $this->id(),
            'choices' => $args['choices'],
        ] ) );
    }
}
