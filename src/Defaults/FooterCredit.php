<?php
/**
 * Footer credit customize component.
 *
 * A checkbox for generated credit text and a textarea for a custom credit, in
 * the footer panel. The theme prints the credit; this registers the settings.
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
 * Footer credit component.
 */
class FooterCredit extends Base {

    /**
     * @return string
     */
    protected function filter(): string {

        return 'backdrop/customize/footer_credit_args';
    }

    /**
     * Arguments:
     *
     * * `panel`               The panel the section goes in.
     * * `section`             The section ID.
     * * `title`               The section title.
     * * `priority`            The section priority, or `null` for the default.
     * * `powered_by`          `false` leaves out the generated credit checkbox.
     * * `powered_by_default`  Whether the generated credit is on by default.
     * * `powered_by_label`    The checkbox label.
     * * `credit_default`      The default custom credit text.
     * * `credit_label`        The textarea label.
     * * `allowed_html`        The tags kept in the custom credit, for `wp_kses()`.
     * * `selector`            The CSS selector of the credit in the footer.
     * * `render_callback`     Returns the credit markup. With a selector and a
     *                         callback the credit updates without reloading the
     *                         preview.
     * * `transport`           `postMessage` by default. Without a selector and a
     *                         callback nothing updates the preview, so it only
     *                         shows the change after the setting is saved. Use
     *                         `refresh` to reload the preview instead.
     *
     * The settings are `theme_footer_powered_by` and `theme_footer_custom_credit`.
     *
     * @return array
     */
    protected function defaults(): array {

        return [
            'panel'              => 'theme_footer',
            'section'            => 'theme_footer_credit',
            'title'              => esc_html__( 'Credit', 'backdrop' ),
            'priority'           => null,
            'powered_by'         => true,
            'powered_by_default' => false,
            'powered_by_label'   => esc_html__( 'Enable random credit text', 'backdrop' ),
            'credit_default'     => '',
            'credit_label'       => esc_html__( 'Custom Credit', 'backdrop' ),
            'allowed_html'       => [
                'a'       => [ 'href' => true, 'title' => true, 'class' => true ],
                'abbr'    => [ 'title' => true ],
                'acronym' => [ 'title' => true ],
                'b'       => [ 'class' => true ],
                'br'      => [],
                'code'    => [ 'class' => true ],
                'em'      => [ 'class' => true ],
                'i'       => [ 'class' => true ],
                'span'    => [ 'class' => true ],
                'strong'  => [ 'class' => true ],
            ],
            'selector'           => '.app-footer__credit',
            'render_callback'    => null,
            'transport'          => 'postMessage',
        ];
    }

    /**
     * Whether the credit updates in the preview without a reload.
     *
     * @return bool
     */
    protected function hasPartial(): bool {

        $args = $this->args();

        return '' !== $args['selector'] && is_callable( $args['render_callback'] );
    }

    /**
     * @return string
     */
    protected function transport(): string {

        return $this->args()['transport'];
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

        $manager->add_section( $args['section'], $section );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSettings( WP_Customize_Manager $manager ) {

        $args = $this->args();

        if ( $args['powered_by'] ) {
            $manager->add_setting( 'theme_footer_powered_by', [
                'default'           => (bool) $args['powered_by_default'],
                'sanitize_callback' => [ Sanitize::class, 'checkbox' ],
                'transport'         => $this->transport(),
            ] );
        }

        $allowed = (array) $args['allowed_html'];

        $manager->add_setting( 'theme_footer_custom_credit', [
            'default'           => (string) $args['credit_default'],
            'sanitize_callback' => static function ( $value ) use ( $allowed ) {
                return Sanitize::html( $value, $allowed );
            },
            'transport'         => $this->transport(),
        ] );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {

        $args = $this->args();

        if ( $args['powered_by'] ) {
            $manager->add_control( 'theme_footer_powered_by', [
                'section' => $args['section'],
                'type'    => 'checkbox',
                'label'   => $args['powered_by_label'],
            ] );
        }

        $manager->add_control( 'theme_footer_custom_credit', [
            'section' => $args['section'],
            'type'    => 'textarea',
            'label'   => $args['credit_label'],
        ] );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerPartials( WP_Customize_Manager $manager ) {

        if ( ! $this->hasPartial() || ! isset( $manager->selective_refresh ) ) {
            return;
        }

        $args     = $this->args();
        $settings = [ 'theme_footer_custom_credit' ];

        if ( $args['powered_by'] ) {
            array_unshift( $settings, 'theme_footer_powered_by' );
        }

        $manager->selective_refresh->add_partial( 'theme_footer_credit', [
            'selector'            => $args['selector'],
            'container_inclusive' => true,
            'settings'            => $settings,
            'render_callback'     => $args['render_callback'],
        ] );
    }
}
