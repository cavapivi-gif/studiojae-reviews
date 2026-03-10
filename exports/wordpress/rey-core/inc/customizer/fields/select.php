<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReySelect extends \Kirki_Control_Base {

		public $type = 'rey-select';

		public $query_args;
		public $multiple;

		/*
		'query_args' => [
			'type' => 'posts',
			'post_type' => 'product',
		],

		'query_args' => [
			'type' => 'terms',
			'taxonomy' => 'product_cat',
			'field' => 'slug',
		],
		*/

		/**
		 * Constructor.
		 * Supplied `$args` override class property defaults.
		 * If `$args['settings']` is not defined, use the $id as the setting ID.
		 *
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    {@see WP_Customize_Control::__construct}.
		 */
		public function __construct( $manager, $id, $args = [] ) {

			parent::__construct( $manager, $id, $args );

			if ( empty( $args['query_args'] ) || ! is_array( $args['query_args'] ) ) {
				$args['query_args'] = [];
			}

			$this->query_args = $args['query_args'];
			$this->multiple = isset($args['multiple']) && absint($args['multiple']) > 1 ? $args['multiple'] : 1;

		}

		public function render_content() {

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
			$name = $this->id;

			$the_value = (array) $this->value();

			$attr = '';

			if( ! empty($this->query_args) ){
				$attr .= ' data-ajax ';
			}

			if( $this->multiple > 1 ){
				$attr .= ' data-multiple="'. esc_attr($this->multiple) . '" multiple ';
			} ?>

			<div class="rey-control-select rey-control-wrap ">

				<?php if( !empty( $label ) ) : ?>
					<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
				<?php endif; ?>

				<?php if( !empty( $description ) ) : ?>
					<span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
				<?php endif; ?>

				<div class="customize-control-content">

					<select id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $name ); ?>[]" <?php $this->link(); ?> <?php echo $attr; ?>>
 						<?php

						if( ! empty($this->query_args) ){

							foreach ($the_value as $id) {

								if( ! $id ){
									continue;
								}

								$title = $id;

								if( $this->query_args['type'] === 'terms' ){

									$field = 'term_id';

									if( isset($this->query_args['field']) ){
										$field = $this->query_args['field'];
									}

									if( ($term = get_term_by( $field, $id, $this->query_args['taxonomy'] )) && isset($term->name) ){
										$title = $term->name;
									}
								}

								elseif( $this->query_args['type'] === 'posts' ){
									$title = get_the_title($id);
								}

								// $this->choices[$id] = $title;
							}

						}

						foreach ($this->choices as $key => $value) {
							$selected = in_array( $key, $the_value ) ? ' selected="selected" ' : '';
							printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr($key), $value, $selected );
						}
						?>
					</select>

				</div>
			</div>

            <?php
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {

			// Get the basics from the parent class.
			parent::to_json();

			$this->json['query_args'] = $this->query_args;

			$this->json['multiple']   = $this->multiple;

		}


	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-select'] = 'Kirki_Controls_ReySelect';
		return $controls;
	} );
} );
