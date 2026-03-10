<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BeforeAtcText extends Component {

	public function init(){
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'render' ], 9);
	}

	public function get_id(){
		return 'before_add_to_cart';
	}

	public function get_name(){
		return 'Text before Add to Cart';
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		$this->render_gs();
		$this->render_text();

	}

	public function render_gs(){

		$gs = reycore__get_option('gs_before_atc', 'no');

		if( 'no' === $gs ){
			return;
		}

		$content = \ReyCore\Elementor\GlobalSections::do_section( $gs, true, true );

		printf('<div class="rey-cartBtn-beforeGs">%s</div>', $content );

	}

	public function render_text(){

		$en = reycore__get_option( 'enable_text_before_add_to_cart', false );

		if( $en === false || $en === 'false' ){
			return;
		}

		$content = reycore__get_option( 'text_before_add_to_cart', false, ($en !== 'custom') );

		printf('<div class="rey-cartBtn-beforeText">%s</div>', reycore__parse_text_editor( $content )  );

	}
}
