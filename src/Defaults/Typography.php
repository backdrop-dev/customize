<?php
/**
 * Built-in component: Typography (Chassis).
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

/**
 * Typography: the body and heading fonts, the base size and the line height, in
 * Theme: Global.
 *
 * Off by default, and needs Chassis. A theme turns it on with the
 * `backdrop/customize/typography` filter and changes it with
 * `backdrop/customize/typography_args`:
 *
 * * `panel`, `section`, `title`, `priority`, `transport`  As in Colors
 *   (the section is `theme_global_typography`).
 * * `fonts`   Font stacks as `[ 'CSS font-family' => 'Label' ]`, used by the font
 *             controls. The stack the theme already has is added to the list. The
 *             default list is three system stacks; a theme that loads its own fonts
 *             adds them here.
 * * `fields`  As in Colors, with `type` `select` (uses `fonts`), `text` (a CSS
 *             length such as `1.125rem`), `number` (such as a line height) or `color`. The setting ID is
 *             `theme_type_{key}`. Chassis also has `settings.font.code` and
 *             `settings.font.meta`.
 *
 * @since 1.1.0
 */
class Typography extends ChassisBase {

    protected function filter(): string {

        return 'backdrop/customize/typography_args';
    }

    protected function prefix(): string {

        return 'theme_type_';
    }

    protected function defaults(): array {

        $fonts = [
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' => esc_html__( 'System sans-serif', 'backdrop' ),
            'Georgia, "Times New Roman", Times, serif'                                                  => esc_html__( 'Serif', 'backdrop' ),
            'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace'                                  => esc_html__( 'Monospace', 'backdrop' ),
        ];

        return [
            'panel'     => 'theme_global',
            'section'   => 'theme_global_typography',
            'title'     => esc_html__( 'Typography', 'backdrop' ),
            'priority'  => null,
            'transport' => 'postMessage',
            'fonts'     => $fonts,
            'fields'    => [
                'body'        => [ 'path' => 'settings.font.body',           'label' => esc_html__( 'Body font', 'backdrop' ),    'type' => 'select' ],
                'heading'     => [ 'path' => 'settings.font.heading',        'label' => esc_html__( 'Heading font', 'backdrop' ), 'type' => 'select' ],
                'size'        => [ 'path' => 'styles.typography.size',       'label' => esc_html__( 'Base font size', 'backdrop' ), 'type' => 'text' ],
                'line_height' => [ 'path' => 'styles.typography.line-height', 'label' => esc_html__( 'Line height', 'backdrop' ),  'type' => 'number' ],
            ],
        ];
    }

    /**
     * Gives each select field the `fonts` list.
     *
     * @return array
     */
    protected function fields(): array {

        $fonts = (array) $this->args()['fonts'];

        return array_map( static function ( $field ) use ( $fonts ) {
            return 'select' === $field['type'] && ! isset( $field['choices'] ) ? $field + [ 'choices' => $fonts ] : $field;
        }, parent::fields() );
    }
}
