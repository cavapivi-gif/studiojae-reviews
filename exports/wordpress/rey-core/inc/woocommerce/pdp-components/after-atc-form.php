<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AfterAtcForm extends Component {

	public function init(){
		add_action( 'woocommerce_after_add_to_cart_form', [$this, 'render']);
	}

	public function get_id(){
		return 'after_atc_form';
	}

	public function get_name(){
		return 'After Add to Cart Form';
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		ob_start();
		do_action('reycore/woocommerce/single/after_add_to_cart_form');
		$content = ob_get_clean();

		if( !empty($content) ){
			printf('<div class="rey-after-atcForm">%s</div>', $content);
		}
	}
}
