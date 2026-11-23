<?php
/**
 * Built-in component: Colors (Chassis).
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

/**
 * Colors: a color control for each Chassis color token, in Theme: Global.
 *
 * Off by default, and needs Chassis. A theme turns it on with the
 * `backdrop/customize/colors` filter and changes it with `backdrop/customize/colors_args`:
 *
 * * `panel`     The panel (default `theme_global`).
 * * `section`   The section ID (default `theme_global_colors`).
 * * `title`     The section title.
 * * `priority`  The section priority (default none).
 * * `transport` `postMessage` (the default, with a live preview) or `refresh`.
 * * `fields`    `[ 'key' => [ 'path' => 'styles.color.accent', 'label' => 'Accent' ] ]`.
 *               The setting ID is `theme_color_{key}`. Returning `fields` replaces the
 *               list, so a theme can drop colors it doesn't use. The page background is
 *               in Background, not here.
 *
 * @since 1.1.0
 */
class Colors extends ChassisBase {

    protected function filter(): string {

        return 'backdrop/customize/colors_args';
    }

    protected function prefix(): string {

        return 'theme_color_';
    }

    protected function defaults(): array {

        $fields = [
            'text'           => [ 'styles.color.text',                   esc_html__( 'Text', 'backdrop' ) ],
            'muted'          => [ 'styles.color.muted',                  esc_html__( 'Muted text', 'backdrop' ) ],
            'divider'        => [ 'styles.color.divider',                esc_html__( 'Dividers', 'backdrop' ) ],
            'alt'            => [ 'styles.color.alt',                    esc_html__( 'Alternate background', 'backdrop' ) ],
            'accent'         => [ 'styles.color.accent',                 esc_html__( 'Accent', 'backdrop' ) ],
            'accent_text'    => [ 'styles.color.accent-text',            esc_html__( 'Text on accent', 'backdrop' ) ],
            'link'           => [ 'styles.elements.link.color.text',     esc_html__( 'Links', 'backdrop' ) ],
            'link_hover'     => [ 'styles.elements.link.:hover.color.text', esc_html__( 'Links on hover', 'backdrop' ) ],
            'heading'        => [ 'styles.elements.heading.color.text',  esc_html__( 'Headings', 'backdrop' ) ],
            'header_bg'      => [ 'styles.header.color.background',      esc_html__( 'Header background', 'backdrop' ) ],
            'header_text'    => [ 'styles.header.color.text',            esc_html__( 'Header text', 'backdrop' ) ],
            'footer_bg'      => [ 'styles.footer.color.background',      esc_html__( 'Footer background', 'backdrop' ) ],
            'footer_text'    => [ 'styles.footer.color.text',            esc_html__( 'Footer text', 'backdrop' ) ],
        ];

        foreach ( $fields as $key => $field ) {
            $fields[ $key ] = [ 'path' => $field[0], 'label' => $field[1], 'type' => 'color' ];
        }

        return [
            'panel'     => 'theme_global',
            'section'   => 'theme_global_colors',
            'title'     => esc_html__( 'Colors', 'backdrop' ),
            'priority'  => null,
            'transport' => 'postMessage',
            'fields'    => $fields,
        ];
    }
}
