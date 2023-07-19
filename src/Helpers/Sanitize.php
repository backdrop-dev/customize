<?php
/**
 * Sanitize Helpers
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu ( benlumia007@gmail.com )
 * @copyright 2019-2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

/**
 * Define namespace
 */
namespace Backdrop\Customize\Helpers;

/**
 * Regiser Menu Class
 */
class Sanitize {
    /**
     * Loads choices for layouts
     *
     * @since 3.0.0
     * @access public
     * @param string $input     String containing a layout string.
     * @param mixed  $setting  Object containing info about settings/controls that's being sanitized.
     */
    public static function layouts( string $input, $setting ) {

        $choices = $setting->manager->get_control( $setting->id )->choices;
        return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
    }

    /**
     * Santize checkbox
     *
     * @since 2.0.0
     * @access public
     * @param bool $value
     * @return bool
     */
    public static function checkbox( bool $value ): bool {

        return true === $value;
    }

    /**
     * 1.0 - Customize ( Validations )
     *
     * @param array $page_id output.
     * @param array $setting output.
     */
    public static function dropdown( array $page_id, array $setting ) {

        $page_id = absint( $page_id );
        return ( 'publish' === get_post_status( $page_id ) ? $page_id : $setting->default );
    }
}