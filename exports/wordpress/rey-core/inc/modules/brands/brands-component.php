<?php
namespace ReyCore\Modules\Brands;

use ReyCore\WooCommerce\LoopComponents\Component;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BrandsComponent extends Component {

	public function init(){
		add_action('reycore/elementor/product_grid/mini_grid/content', [$this, 'render_mini_skin'], 5);
	}

	public function status(){
		return get_theme_mod('loop_show_brads', '1');
	}

	public function get_id(){
		return 'brands';
	}

	public function get_name(){
		return 'Brands';
	}

	public function scheme(){
		return [
			'type'     => 'action',
			'tag'      => 'woocommerce_shop_loop_item_title',
			'priority' => 4,
		];
	}

	public function render(){
		Base::instance()->get_brands_html();
	}

	public function render_mini_skin( $element ){
		if( isset( $element->_settings['hide_brands'] ) && $element->_settings['hide_brands'] !== 'yes' ){
			$this->render();
		}
	}

}
