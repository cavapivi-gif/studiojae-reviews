<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {
		/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyColor extends \Kirki_Control_Base {

		public $type = 'rey-color';

		public $palette = false;
		public $mode = 'full';

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @access public
		 */
		public function to_json() {
			parent::to_json();

			$this->json['palette']          = $this->palette;
			$this->json['choices']['alpha'] = ( isset( $this->choices['alpha'] ) && $this->choices['alpha'] ) ? 'true' : 'false';
			$this->json['mode']             = $this->mode;
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-color'] = 'Kirki_Controls_ReyColor';
		return $controls;
	} );

} );

add_action( 'customize_controls_print_footer_scripts', function(){

	$colors = [];

	if( class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->kits_manager) ){

		$kits_manager = \Elementor\Plugin::$instance->kits_manager;

		$system_colors = $kits_manager->get_current_settings( 'system_colors' );

		foreach ($system_colors as $value) {
			$value['var'] = sprintf('var(--e-global-color-%s)', $value['_id']);
			$colors[] = $value;
		}

		$custom_colors = $kits_manager->get_current_settings( 'custom_colors' );

		foreach ($custom_colors as $value) {
			$value['var'] = sprintf('var(--e-global-color-%s)', $value['_id']);
			$colors[] = $value;
		}
	}

	?><script type="text/html" id="tmpl-rey-color-markup">

		<#
		data = _.defaults( data, {
			label: '',
			description: '',
			mode: 'full',
			inputAttrs: '',
			'data-default-color': data['data-default-color'] ? data['data-default-color'] : '',
			'data-alpha': data['data-alpha'] ? data['data-alpha'] : false,
			value: '',
			'data-id': ''
		} );
		#>

		<div class="rey-control-wrap">

			<label>
				<# if ( data.label ) { #>
					<span class="customize-control-title rey-control-title">{{{ data.label }}}</span>
				<# } #>
				<# if ( data.description ) { #>
					<span class="customize-control-description rey-control-description">{{{ data.description }}}</span>
				<# } #>
			</label>

			<div class="customize-control-content">

				<?php if( ! empty($colors) ): ?>
				<div class="rey-colorGlobal">
					<span class="dashicons dashicons-admin-site-alt3"></span>
				</div>

				<div class="rey-colorGlobal-pop">
					<span class="rey-colorGlobal-popClose"><?php echo reycore__get_svg_icon(['id' => 'close']) ?></span>
					<h3><?php esc_html_e('Global Colors (Elementor)', 'rey-core') ?></h3>
					<div class="__list">

						<?php foreach($colors as $c): ?>
						<div class="__item" data-color-id="<?php echo $c['_id'] ?>" data-color-var="<?php echo $c['var'] ?>" data-color="<?php echo $c['color'] ?>">
							<span class="__color" style="background-color:<?php echo $c['color'] ?>"></span>
							<span class="__title"><?php echo $c['title'] ?></span>
							<span class="__hex"><?php echo $c['color'] ?></span>
						</div>
						<?php endforeach; ?>

					</div>
					<div class="__tip"><?php printf( __('These colors are pre-defined in Elementor Site Settings. <a href="%s" target="_blank">Learn more</a>.'), 'https://elementor.com/help/global-layout-settings/' ); ?></div>
				</div>
				<?php endif; ?>

				<input
					type="text"
					data-type="{{ data.mode }}"
					{{{ data.inputAttrs }}}
					data-default-color="{{ data['data-default-color'] }}"
					data-alpha="{{ data['data-alpha'] }}"
					value="{{ data.value }}"
					data-id="{{ data['data-id'] }}"
					class="rey-color-control"
					{{{ data.link }}}
				/>

			</div>
		</div>
	</script> <?php
} );



if( class_exists('Kirki_Field') && !class_exists('Kirki_Field_Rey_Color') ){

	class Kirki_Field_Rey_Color extends Kirki_Field {

		protected $alpha = true;
		protected $mode = 'full';

		protected function set_type() {
			$this->type = 'rey-color';
		}

		/**
		 * Sets the $sanitize_callback
		 *
		 * @access protected
		 */
		protected function set_sanitize_callback() {

			// If a custom sanitize_callback has been defined,
			// then we don't need to proceed any further.
			if ( ! empty( $this->sanitize_callback ) ) {
				return;
			}

			if ( 'hue' === $this->mode ) {
				$this->sanitize_callback = 'absint';
				return;
			}

			// $this->sanitize_callback = array( 'Kirki_Sanitize_Values', 'color' );
			$this->sanitize_callback = 'sanitize_text_field';
		}

		/**
		 * Sets the $choices
		 *
		 * @access protected
		 */
		protected function set_choices() {

			if ( ! is_array( $this->choices ) ) {
				$this->choices = [];
			}

			$this->choices['alpha'] = isset( $this->choices['alpha'] ) ? $this->choices['alpha'] : $this->alpha;

			if ( ( ! isset( $this->choices['mode'] ) ) || ( 'hex' !== $this->choices['mode'] || 'hue' !== $this->choices['mode'] ) ) {
				$this->choices['mode'] = 'hex';
			}

			$this->choices['color'] = $this->default;
		}

	}
}
