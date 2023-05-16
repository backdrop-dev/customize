<?php
/**
 * Radio image customize control.
 *
 * The radio image customize control allows developers to create a list of image
 * radio inputs.
 *
 * @package   Backdrop Customize
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2019-2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-customize
 */

namespace Backdrop\Customize\Controls;

/**
 * Radio image customize control.
 *
 * @since  1.0.0
 * @access public
 */
class RadioImage extends Control {

    /**
     * The type of customize control being rendered.
     *
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $type = 'backdrop-radio-image';

    /**
     * Loads the template
     *
     * @return void
     */
    protected function render_content() {
        if ( empty( $this->choices ) ) {
            return;
        }

        $name = '_customize-radio-' . $this->id;
        ?>
        <span class="customize-control-title">
			<?php echo esc_attr( $this->label ); ?>
		</span>
        <?php if ( ! empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        <?php endif; ?>

        <div id="input_<?php echo esc_attr( $this->id ); ?>" class="image">
            <?php foreach ( $this->choices as $value => $label ) : ?>
                <label for="<?php echo esc_attr( $this->id . '_' . $value ); ?>">
                    <input class="image-select" type="radio" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $this->id . '_' . $value ); ?>" name="<?php echo esc_attr( $name ); ?>"
                        <?php
                        esc_attr( $this->link() );
                        checked( $this->value(), esc_attr( $value ) );
                        ?>
                    >
                    <img src="<?php echo esc_url( $label ); ?>" alt="<?php echo esc_attr( $value ); ?>" title="<?php echo esc_attr( $value ); ?>">
                </label>
                </input>
            <?php endforeach; ?>
        </div>
        <?php
    }
}