<?php
/**
 * Featured image size customize component.
 *
 * A select for the image size used by featured images, in the content panel.
 * The theme reads the setting where it prints the image.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

use Backdrop\Customize\Helpers\Sanitize;
use WP_Customize_Manager;

/**
 * Featured image size component.
 */
class FeaturedImage extends Base {

    /**
     * @return string
     */
    protected function filter(): string {

        return 'backdrop/customize/featured_image_args';
    }

    /**
     * Arguments:
     *
     * * `panel`     The panel the section goes in.
     * * `id`        The section and setting ID.
     * * `title`     The section title.
     * * `label`     The control label.
     * * `priority`  The section priority, or `null` for the default.
     * * `choices`   Sizes as `[ 'size' => 'Label' ]`. Without choices, every
     *               registered image size is offered, plus `full`.
     * * `default`   The default size. Default is `post-thumbnail`, or the first
     *               choice if that isn't one of them.
     * * `transport` `postMessage` by default, so the theme needs a preview script
     *               that listens to the setting. Use `refresh` to reload the
     *               preview instead.
     *
     * @return array
     */
    protected function defaults(): array {

        return [
            'panel'    => 'theme_content',
            'id'       => 'theme_content_feature_image',
            'title'    => esc_html__( 'Feature Image', 'backdrop' ),
            'label'    => esc_html__( 'Feature Image Size', 'backdrop' ),
            'priority' => null,
            'choices'  => [],
            'default'  => 'post-thumbnail',
            'transport' => 'postMessage',
        ];
    }

    /**
     * The choices the theme gave, or every registered image size.
     *
     * @return array
     */
    protected function choices(): array {

        $choices = $this->args()['choices'];

        if ( is_array( $choices ) && ! empty( $choices ) ) {
            return $choices;
        }

        $choices = [];

        foreach ( get_intermediate_image_sizes() as $size ) {
            $choices[ $size ] = ucwords( str_replace( [ '-', '_' ], ' ', $size ) );
        }

        $choices['full'] = esc_html__( 'Full', 'backdrop' );

        return $choices;
    }

    /**
     * @return string
     */
    protected function defaultChoice(): string {

        $choices = $this->choices();
        $default = $this->args()['default'];

        return array_key_exists( $default, $choices ) ? $default : (string) key( $choices );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSections( WP_Customize_Manager $manager ) {

        $args    = $this->args();
        $section = [
            'panel' => $args['panel'],
            'title' => $args['title'],
        ];

        if ( null !== $args['priority'] ) {
            $section['priority'] = $args['priority'];
        }

        $manager->add_section( $args['id'], $section );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSettings( WP_Customize_Manager $manager ) {

        $manager->add_setting( $this->args()['id'], [
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

        $args = $this->args();

        $manager->add_control( $args['id'], [
            'label'    => $args['label'],
            'section'  => $args['id'],
            'settings' => $args['id'],
            'type'     => 'select',
            'choices'  => $this->choices(),
        ] );
    }
}
