<?php
/**
 * Radio image customize control.
 *
 * The radio image customize control allows developers to create a list of image
 * radio inputs.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/backdrop-dev/customize
 */

namespace Backdrop\Customize\Controls;

use Backdrop\Customize\Contracts\Control;

/**
 * Radio image customize control.
 */
class RadioImage extends Control {

    /**
     * The type of customize control being rendered.
     *
     * @var string
     */
    public $type = 'backdrop-radio-image';

    /**
     * Passes data to the JavaScript via JSON.
     *
     * @return void
     */
    public function to_json() {
        parent::to_json();

        // Ensure choices is always an array
        if ( ! is_array( $this->choices ) ) {
            $this->choices = [];
        }

        foreach ( $this->choices as $key => &$args ) {
            if ( is_string( $args ) ) {
                // If a string is passed instead of an array, convert it
                $args = [
                    'label' => ucfirst( str_replace( '-', ' ', $key ) ),
                    'url'   => $args,
                ];
            }

            if ( isset( $args['url'] ) ) {
                $args['url'] = esc_url(
                    sprintf(
                        $args['url'],
                        get_template_directory_uri(),
                        get_stylesheet_directory_uri()
                    )
                );
            } else {
                $args['url'] = '';
            }

            if ( ! isset( $args['label'] ) ) {
                $args['label'] = ucfirst( str_replace( '-', ' ', $key ) );
            }
        }

        $this->json['choices'] = $this->choices;
        $this->json['link']    = $this->get_link();
        $this->json['value']   = $this->value();
        $this->json['id']      = $this->id;
    }

    /**
     * JS template for rendering the control.
     *
     * @return void
     */
    protected function content_template() {
        ?>
        <# if ( data.label ) { #>
            <span class="customize-control-title">{{ data.label }}</span>
        <# } #>

        <# if ( data.description ) { #>
            <span class="description customize-control-description">{{{ data.description }}}</span>
        <# } #>

        <# if ( data.choices ) { #>
            <div class="radio-image-control">
                <# _.each( data.choices, function( args, key ) { #>
                    <label class="radio-image">
                        <input type="radio" class="radio-image__radio"
                               value="{{ key }}"
                               name="_customize-{{ data.type }}-{{ data.id }}"
                               {{{ data.link }}}
                               <# if ( key === data.value ) { #> checked="checked" <# } #> />

                        <span class="radio-image__label screen-reader-text">{{ args.label }}</span>

                        <img class="radio-image__image" src="{{ args.url }}" alt="{{ args.label }}" />
                    </label>
                <# }); #>
            </div>
        <# } #>
        <?php
    }
}
