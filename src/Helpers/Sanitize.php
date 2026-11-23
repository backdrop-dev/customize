<?php
/**
 * Sanitize helpers.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Helpers;

/**
 * Sanitize callbacks for customize settings.
 */
class Sanitize {

    /**
     * Sanitizes a value against the choices of the control for the setting, and falls back
     * to the setting's default if it isn't one of them.
     *
     * @param  mixed   $input
     * @param  object  $setting  The `WP_Customize_Setting` being sanitized.
     * @return mixed
     */
    public static function choices( $input, $setting ) {

        $control = $setting->manager->get_control( $setting->id );
        $choices = $control ? (array) $control->choices : [];

        return array_key_exists( $input, $choices ) ? $input : $setting->default;
    }

    /**
     * Same as `choices()`, kept for existing themes that call it for layouts.
     *
     * @param  mixed   $input
     * @param  object  $setting
     * @return mixed
     */
    public static function layouts( $input, $setting ) {

        return static::choices( $input, $setting );
    }

    /**
     * Sanitizes a checkbox.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function checkbox( $value ): bool {

        return wp_validate_boolean( $value );
    }

    /**
     * Sanitizes a page ID. Only the ID of a published page is allowed.
     *
     * @param  mixed   $page_id
     * @param  object  $setting
     * @return int
     */
    public static function dropdown( $page_id, $setting ): int {

        $page_id = absint( $page_id );

        return 'publish' === get_post_status( $page_id ) ? $page_id : absint( $setting->default );
    }

    /**
     * Sanitizes text that may have HTML, keeping only the allowed tags.
     *
     * @param  mixed  $value
     * @param  array  $allowed  Allowed tags and attributes, as for `wp_kses()`.
     * @return string
     */
    public static function html( $value, array $allowed ): string {

        return wp_kses( (string) $value, $allowed );
    }

    /**
     * Sanitizes a hex color (`#fff` or `#ffffff`). Anything else is an empty string,
     * which means "no override".
     *
     * @param  mixed  $value
     * @return string
     */
    public static function color( $value ): string {

        $value = trim( (string) $value );

        return preg_match( '/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value ) ? strtolower( $value ) : '';
    }

    /**
     * Sanitizes a CSS length such as `18px`, `1.125rem`, `110%` or `2.5em`. Anything
     * else is an empty string.
     *
     * @param  mixed  $value
     * @return string
     */
    public static function cssLength( $value ): string {

        $value = trim( (string) $value );

        return preg_match( '/^\d*\.?\d+(?:px|em|rem|%|ch|vw|vh)$/', $value ) ? $value : '';
    }

    /**
     * Sanitizes a unitless number between 0.5 and 4, such as a line height. Anything
     * else is an empty string.
     *
     * @param  mixed  $value
     * @return string
     */
    public static function number( $value ): string {

        if ( ! is_numeric( $value ) || $value < 0.5 || $value > 4 ) {
            return '';
        }

        return (string) ( 0 + $value );
    }
}
