<?php
/**
 * Base class for the built-in components that write to Chassis.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Defaults;

use Backdrop\Customize\Helpers\Sanitize;
use Backdrop\Proxies\App;
use WP_Customize_Color_Control;
use WP_Customize_Manager;

/**
 * A built-in component whose settings become Chassis design values.
 *
 * Each field is one setting that maps to a path in Chassis's `STYLES_MAP`
 * (`styles.color.accent`). The setting starts at the value `chassis.json` gives
 * that path, and when it is changed the value is passed to Chassis through the
 * `chassis/overrides` filter, so it wins over the file. Nothing is saved to
 * `chassis.json`, and a setting that was never changed overrides nothing.
 *
 * Chassis is a soft dependency. Without it the component registers nothing.
 *
 * @since 1.1.0
 */
abstract class ChassisBase extends Base {

    /**
     * Returns the Chassis instance, or null when Chassis (with its override
     * layer) isn't installed or isn't registered in the container.
     *
     * @return object|null
     */
    protected function chassis() {

        try {
            $chassis = App::resolve( 'chassis' );
        } catch ( \Throwable $e ) {
            return null;
        }

        return is_object( $chassis ) && method_exists( $chassis, 'value' ) && method_exists( $chassis, 'overrides' ) ? $chassis : null;
    }

    /**
     * @return bool
     */
    protected function available(): bool {

        return null !== $this->chassis();
    }

    /**
     * `postMessage` when the preview updates live, or `refresh`.
     *
     * @return string
     */
    public function transport(): string {

        return $this->args()['transport'];
    }

    /**
     * The section to register: `id`, `panel`, `title` and `priority`.
     *
     * @return array
     */
    protected function section(): array {

        $args = $this->args();

        return [
            'id'       => $args['section'],
            'panel'    => $args['panel'],
            'title'    => $args['title'],
            'priority' => $args['priority'],
        ];
    }

    /**
     * The fields, each with `id` (the setting ID), `path` (a `STYLES_MAP` path),
     * `label`, `type` (`color`, `select`, `text` for a CSS length, or `number`) and, for a select, `choices`.
     * A field whose path Chassis doesn't have is left out.
     *
     * @return array
     */
    protected function fields(): array {

        $chassis = $this->chassis();

        if ( null === $chassis ) {
            return [];
        }

        $class = get_class( $chassis );
        $map   = $class::stylesMap();
        $found = [];

        foreach ( (array) $this->args()['fields'] as $key => $field ) {
            if ( is_array( $field ) && isset( $field['path'], $map[ $field['path'] ] ) ) {
                $found[ $key ] = $field + [ 'type' => 'color', 'label' => $key, 'id' => $this->prefix() . $key ];
            }
        }

        return $found;
    }

    /**
     * The start of each setting ID: the key of a field is added to it.
     *
     * @return string
     */
    abstract protected function prefix(): string;

    /**
     * The sanitize callback for a field.
     *
     * @param  array  $field
     * @return callable
     */
    protected function sanitizer( array $field ): callable {

        if ( 'select' === $field['type'] ) {
            $choices = array_keys( $this->choices( $field ) );

            return static function ( $value ) use ( $choices ) {
                return in_array( $value, $choices, true ) ? $value : '';
            };
        }

        if ( 'text' === $field['type'] ) {
            return [ Sanitize::class, 'cssLength' ];
        }

        if ( 'number' === $field['type'] ) {
            return [ Sanitize::class, 'number' ];
        }

        return [ Sanitize::class, 'color' ];
    }

    /**
     * The choices of a select field, with the value Chassis has now added when
     * it isn't one of them, so the control can show it.
     *
     * @param  array  $field
     * @return array
     */
    protected function choices( array $field ): array {

        $choices = isset( $field['choices'] ) && is_array( $field['choices'] ) ? $field['choices'] : [];
        $current = $this->current( $field );

        if ( null !== $current && ! isset( $choices[ $current ] ) ) {
            $choices = [ $current => esc_html__( 'Theme default', 'backdrop' ) ] + $choices;
        }

        return $choices;
    }

    /**
     * The value Chassis gives the field's path now (from `chassis.json`), or the
     * field's own `default`, or an empty string.
     *
     * @param  array  $field
     * @return string|null
     */
    protected function current( array $field ) {

        $chassis = $this->chassis();
        $value   = null !== $chassis ? $chassis->value( $field['path'] ) : null;

        if ( null === $value && isset( $field['default'] ) ) {
            return (string) $field['default'];
        }

        return $value;
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSections( WP_Customize_Manager $manager ) {

        if ( ! $this->fields() ) {
            return;
        }

        $section = $this->section();
        $args    = [
            'panel' => $section['panel'],
            'title' => $section['title'],
        ];

        if ( null !== $section['priority'] ) {
            $args['priority'] = $section['priority'];
        }

        $manager->add_section( $section['id'], $args );
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerSettings( WP_Customize_Manager $manager ) {

        foreach ( $this->fields() as $field ) {
            $manager->add_setting( $field['id'], [
                'default'           => (string) $this->current( $field ),
                'sanitize_callback' => $this->sanitizer( $field ),
                'transport'         => $this->args()['transport'],
            ] );
        }
    }

    /**
     * @param  WP_Customize_Manager  $manager
     * @return void
     */
    public function registerControls( WP_Customize_Manager $manager ) {

        $section = $this->section()['id'];

        foreach ( $this->fields() as $field ) {
            $control = [
                'section'  => $section,
                'settings' => $field['id'],
                'label'    => $field['label'],
            ];

            if ( 'color' === $field['type'] ) {
                $manager->add_control( new WP_Customize_Color_Control( $manager, $field['id'], $control ) );
                continue;
            }

            $control['type'] = in_array( $field['type'], [ 'select', 'number' ], true ) ? $field['type'] : 'text';

            if ( 'number' === $field['type'] ) {
                $control['input_attrs'] = [ 'min' => 0.5, 'max' => 4, 'step' => 0.05 ];
            }

            if ( 'select' === $field['type'] ) {
                $control['choices'] = $this->choices( $field );
            }

            $manager->add_control( $field['id'], $control );
        }
    }

    /**
     * The values to give Chassis: each field's setting, when it has been set,
     * by its path. Added to the `chassis/overrides` filter by the component.
     *
     * @param  array  $overrides
     * @return array
     */
    public function overrides( array $overrides ): array {

        foreach ( $this->fields() as $field ) {
            $value = call_user_func( $this->sanitizer( $field ), get_theme_mod( $field['id'], '' ) );

            if ( '' !== $value && null !== $value ) {
                $overrides[ $field['path'] ] = (string) $value;
            }
        }

        return $overrides;
    }

    /**
     * Setting ID => custom property, for the live preview.
     *
     * @return array
     */
    public function properties(): array {

        $chassis = $this->chassis();

        if ( null === $chassis ) {
            return [];
        }

        $class      = get_class( $chassis );
        $map        = $class::stylesMap();
        $properties = [];

        foreach ( $this->fields() as $field ) {
            $properties[ $field['id'] ] = $map[ $field['path'] ][0];
        }

        return $properties;
    }
}
