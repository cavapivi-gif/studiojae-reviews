<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	class Kirki_Controls_ReyTitle extends \Kirki_Control_Base {

		public $type = 'rey-title';

		public $title_args = [];

		public function to_json() {

			parent::to_json();

			$this->json['titleArgs'] = $this->title_args;
		}
	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-title'] = 'Kirki_Controls_ReyTitle';
		return $controls;
	} );

} );

add_action( 'customize_controls_print_footer_scripts', function(){

	echo '<script type="text/html" id="tmpl-rey-title">'; ?>

	<#
		var classes = [
			'--fz-' + data.titleArgs['size'],
			'--border-' + data.titleArgs['separator'],
			'--border-size-' + data.titleArgs['separator_size'],
			'--color-' + data.titleArgs['color'],
		];

		if( data.titleArgs['upper'] ){
			classes.push('--upper');
		}
	#>

	<div class="rey-customizerTitle-wrapper {{ classes.join(' ') }}" style="{{ data.titleArgs['style_attr'] }}" data-id="{{ data.id }}" >
		<# if ( data.label ) { #>
			<h2 class="rey-customizerTitle">{{{ data.label }}}</h2>
		<# } #>
		<# if ( data.description ) { #>
			<p class="description">{{{ data.description }}}</p>
		<# } #>
	</div>

	<?php
	echo '</script>';
} );


/**
 * Ensure properties are passed on
 */
class Kirki_Field_Rey_Title extends \Kirki_Field {

	public $title_args = [];

	public function __construct( $config_id = 'global', $args = [] ) {

		if( isset($args['title_args']) ){
			$this->title_args = (array) $args['title_args'];
		}

		parent::__construct( $config_id, $args );

	}

	protected function set_type() {
		$this->type = 'rey-title';
	}

}
