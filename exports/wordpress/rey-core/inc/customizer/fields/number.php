<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyNumber extends \Kirki_Control_Base {

		public $type = 'rey-number';

		public function render_content() {

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
            $name = $this->id;
            $value = $this->value();
            ?>
                <div class="rey-control-wrap">

                    <?php if( !empty( $label ) ) : ?>
						<label for="<?php echo esc_attr( $input_id ); ?>">
							<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
						</label>
					<?php endif; ?>

                    <?php if( !empty( $description ) ) : ?>
                            <span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
                    <?php endif; ?>

					<div class="customize-control-content">
						<input type = "number" id="<?php echo esc_attr( $input_id ); ?>"
							name = "<?php echo esc_attr( $name ); ?>"
							min = "<?php echo isset($this->choices['min']) ? esc_attr( $this->choices['min'] ) : '' ?>"
							max = "<?php echo esc_attr( $this->choices['max'] ); ?>"
							step = "<?php echo esc_attr( $this->choices['step'] ); ?>"
							placeholder = "<?php echo isset($this->choices['placeholder']) ? $this->choices['placeholder'] : ''; ?>"
							value="<?php echo esc_attr( $value ); ?>"
							<?php $this->link(); ?>
						/>
					</div>
                </div>
            <?php
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-number'] = 'Kirki_Controls_ReyNumber';
		return $controls;
	} );
} );


class Kirki_Field_Rey_Number extends Kirki_Field {

	protected function set_type() {
		$this->type = 'rey-number';
	}

	/**
	 * Sets the $sanitize_callback
	 *
	 * @access protected
	 */
	protected function set_sanitize_callback() {
		$this->sanitize_callback = array( $this, 'sanitize' );
	}

	/**
	 * Sets the $choices
	 *
	 * @access protected
	 */
	protected function set_choices() {
		$this->choices = wp_parse_args(
			$this->choices,
			[
				'min'  => '',
				'max'  => 999999999,
				'step' => 1,
				'placeholder' => '',
			]
		);

		if( isset($this->choices['min']) && $this->choices['min'] !== '' ){
			$this->choices['min']  = filter_var( $this->choices['min'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}

		// Make sure min, max & step are all numeric.
		$this->choices['max']  = filter_var( $this->choices['max'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$this->choices['step'] = filter_var( $this->choices['step'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

	}

	/**
	 * Sanitizes numeric values.
	 *
	 * @access public
	 * @param integer|string $value The checkbox value.
	 * @return bool
	 */
	public function sanitize( $value = 0 ) {
		$this->set_choices();

		$value = filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		// Allow empty if default is empty
		if( '' === $value && isset($this->default) && '' === $this->default ){
			return $value;
		}

		if( '' !== $this->choices['min'] && '' !== $value ):

			// Minimum & maximum value limits.
			if ( $value < $this->choices['min'] || $value > $this->choices['max'] ) {
				return max( min( $value, $this->choices['max'] ), $this->choices['min'] );
			}

			// Only multiple of steps.
			$steps = ( $value - $this->choices['min'] ) / $this->choices['step'];
			if ( ! is_int( $steps ) ) {
				$value = $this->choices['min'] + ( round( $steps ) * $this->choices['step'] );
			}

		endif;

		return $value;
	}
}
