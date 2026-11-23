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
 * * `handles` `[ 'CSS font-family' => 'font-slug' ]`: the stacks in `fonts` that are a font
 *             from `backdrop-dev/fonts` (such as `fira-sans`), which this component then loads.
 *             On the front end only the fonts in use are loaded, and in the customizer preview
 *             every one is, so a pick shows at once. A stack whose font can't be loaded (the
 *             fonts package isn't installed, or has no such font) is left out of the list, so
 *             the control never offers a font that wouldn't show. A stack with no handle is
 *             never loaded: it is a system stack, or a font the theme already loads itself.
 * * `fields`  As in Colors, with `type` `select` (uses `fonts`), `text` (a CSS
 *             length such as `1.125rem`), `number` (such as a line height) or `color`. The setting ID is
 *             `theme_type_{key}`. Chassis also has `settings.font.code` and
 *             `settings.font.meta`.
 *
 * @since 1.1.0
 */
class Typography extends ChassisBase {

    /**
     * The `handles` that can be loaded, by font stack. Built once.
     *
     * @var array|null
     */
    protected $loadable = null;

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
            'handles'   => [],
            'fields'    => [
                'body'        => [ 'path' => 'settings.font.body',           'label' => esc_html__( 'Body font', 'backdrop' ),    'type' => 'select' ],
                'heading'     => [ 'path' => 'settings.font.heading',        'label' => esc_html__( 'Heading font', 'backdrop' ), 'type' => 'select' ],
                'size'        => [ 'path' => 'styles.typography.size',       'label' => esc_html__( 'Base font size', 'backdrop' ), 'type' => 'text' ],
                'line_height' => [ 'path' => 'styles.typography.line-height', 'label' => esc_html__( 'Line height', 'backdrop' ),  'type' => 'number' ],
            ],
        ];
    }

    /**
     * Gives each select field the `fonts` list, without the fonts that can't be loaded.
     *
     * @return array
     */
    protected function fields(): array {

        $fonts = array_diff_key( (array) $this->args()['fonts'], array_diff_key( (array) $this->args()['handles'], $this->loadable() ) );

        return array_map( static function ( $field ) use ( $fonts ) {
            return 'select' === $field['type'] && ! isset( $field['choices'] ) ? $field + [ 'choices' => $fonts ] : $field;
        }, parent::fields() );
    }

    /**
     * The `handles` whose font the fonts package can load, by font stack.
     *
     * @return array
     */
    protected function loadable(): array {

        if ( null === $this->loadable ) {
            $this->loadable = [];

            foreach ( (array) $this->args()['handles'] as $stack => $handle ) {
                if ( is_string( $handle ) && $this->canLoad( $handle ) ) {
                    $this->loadable[ $stack ] = $handle;
                }
            }
        }

        return $this->loadable;
    }

    /**
     * Whether `backdrop-dev/fonts` has the font, or it was registered with it.
     *
     * @param  string  $handle
     * @return bool
     */
    protected function canLoad( string $handle ): bool {

        if ( ! function_exists( 'Backdrop\\Fonts\\enqueue' ) ) {
            return false;
        }

        if ( \Backdrop\Fonts\is_registered( $handle ) ) {
            return true;
        }

        return function_exists( 'Backdrop\\Fonts\\asset_path' ) && is_file( \Backdrop\Fonts\asset_path( "{$handle}/{$handle}.css" ) );
    }

    /**
     * Loads the fonts from `handles`: the ones the font settings use now, and in the
     * customizer preview all of them. Called on `wp_enqueue_scripts`, after Chassis has
     * applied the overrides, so the value Chassis has is the one the page uses.
     *
     * @return void
     */
    public function enqueueFonts(): void {

        $chassis = $this->chassis();
        $loadable = $this->loadable();

        if ( null === $chassis || ! $loadable ) {
            return;
        }

        $handles = [];

        if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
            $handles = array_values( $loadable );
        } else {
            foreach ( $this->fields() as $field ) {
                $stack = 'select' === $field['type'] ? $chassis->value( $field['path'] ) : null;

                if ( null !== $stack && isset( $loadable[ $stack ] ) ) {
                    $handles[] = $loadable[ $stack ];
                }
            }
        }

        foreach ( array_unique( $handles ) as $handle ) {
            \Backdrop\Fonts\enqueue( $handle );
        }
    }
}
