<?php
namespace ReyCore\Modules\Wishlist;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementorProductsGrid {

	const CUSTOMER_KEY = 'wishlist';
	const TOP_KEY = 'top_wishlist';

	public function __construct(){
		add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_start', [$this,'exclude_query_type']);
		add_action( 'elementor/element/reycore-product-grid/section_query/before_section_end', [$this,'add_query_option']);
		add_action( 'elementor/widget/before_render_content', [$this,'set_product_ids']);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this,'after_grid']);
	}

	public function exclude_query_type( $element ){
		$element->exclude_query_type(self::CUSTOMER_KEY);
		$element->exclude_query_type(self::TOP_KEY);
	}

	public function add_query_option( $element ){

		$query_type = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'query_type' );

		if( ! empty($query_type['options']) && ! is_wp_error($query_type) ){
			$query_type['options'][self::CUSTOMER_KEY] = esc_html__('Customer Wishlist products', 'rey-core');
			$query_type['options'][self::TOP_KEY] = esc_html__('Top Wishlisted products', 'rey-core');
			$element->update_control( 'query_type', $query_type );
		}
	}

	public function set_product_ids($element){

		if( $element->get_unique_name() !== 'reycore-product-grid' ){
			return;
		}

		$settings = $element->get_settings();

		if( ! isset($settings['query_type']) ){
			return;
		}

		if( self::CUSTOMER_KEY === $settings['query_type'] ){
			$element->set_custom_product_ids( ($ids = Base::get_ids()) ? $ids : [] );
		}

		elseif( self::TOP_KEY === $settings['query_type'] ){
			$element->set_custom_product_ids( ($ids = array_keys(MostWishlisted::get_most_wishlisted_products( $settings['limit'] ))) ? $ids : [] );
			add_filter('theme_mod_wishlist__top_label', '__return_false');
		}

	}

	public function after_grid(){
		remove_filter('theme_mod_wishlist__top_label', '__return_false');
	}

}
