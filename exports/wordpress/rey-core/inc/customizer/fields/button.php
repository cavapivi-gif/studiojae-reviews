<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyButton extends \Kirki_Control_Base {

		public $type = 'rey-button';

		public function render_content() {

			if( !current_user_can('manage_options') ){
				return;
			}

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
			$name = $this->id;

            ?>
                <div class="rey-control-wrap <?php echo $this->choices['class']; ?>">

                    <?php if( !empty( $label ) ) : ?>
						<label for="<?php echo esc_attr( $input_id ); ?>">
							<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
						</label>
					<?php endif; ?>

                    <?php if( !empty( $description ) ) : ?>
                            <span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
                    <?php endif; ?>

					<div class="customize-control-content">
						<?php
							$attributes[] = sprintf('id="%s"', esc_attr( $input_id ) );

							if( isset($this->choices['action']) && $action = $this->choices['action'] ){
								$attributes[] = sprintf('data-action="%s"', esc_attr( $action ) );
							}

							if( isset($this->choices['ajax_action']) && $ajax_action = $this->choices['ajax_action'] ){
								$attributes[] = sprintf('data-ajax-action="%s"', esc_attr( $ajax_action ) );
							}

							if( isset($this->choices['params']) && $params = $this->choices['params'] ){
								$attributes[] = sprintf('data-params="%s"', esc_attr( $params ) );
							}

							if( isset($this->choices['default']) && $default = $this->choices['default'] ){
								$attributes[] = sprintf('data-default="%s"', esc_attr( $default ) );
							}

							if( isset($this->choices['href']) && $href = $this->choices['href'] ){
								$attributes[] = sprintf('href="%s"', esc_attr( $href ) );

								if( isset($this->choices['target']) && $target = $this->choices['target'] ){
									$attributes[] = sprintf('target="%s"', esc_attr( $target ) );
								}
							}
						?>
						<a class="button js-rey-button" <?php echo implode(' ', $attributes) ?>>
							<?php echo $this->choices['text']; ?>
						</a>
					</div>

                </div>
            <?php
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-button'] = 'Kirki_Controls_ReyButton';
		return $controls;
	} );

} );


class Kirki_Field_Rey_Button extends \Kirki_Field {

	protected function set_type() {
		$this->type = 'rey-button';
	}

	/**
	 * Sets the $choices
	 *
	 * @access protected
	 */
	protected function set_choices() {
		$this->choices = wp_parse_args(
			$this->choices,
			array(
				'action'  => '',
				'ajax_action'  => '',
				'href'  => '',
				'target'  => '',
				'text'  => 'Click',
				'class'  => '',
			)
		);
		$this->choices['action']  = esc_attr($this->choices['action']);
		$this->choices['ajax_action']  = esc_attr($this->choices['ajax_action']);
		$this->choices['href']  = esc_attr($this->choices['href']);
		$this->choices['target']  = esc_attr($this->choices['target']);
		$this->choices['text']  = esc_html($this->choices['text']);
		$this->choices['default'] = $this->default;
	}

}
