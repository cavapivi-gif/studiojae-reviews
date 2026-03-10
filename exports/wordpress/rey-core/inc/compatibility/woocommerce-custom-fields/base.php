<?php
namespace ReyCore\Compatibility\WoocommerceCustomFields;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action( 'admin_init', [$this, 'disable_nags'] );
		add_action( 'admin_head', [$this, 'disable_nags_css'] );
		add_action( 'woocommerce_loop_add_to_cart_link', [$this, 'add_to_cart_link'], 15, 3);
	}

	function disable_nags_css(){ ?>
		<style>
			.rightpress-updates-update-nag {
				display:none !important;
			}
		</style><?php
	}

	function disable_nags(){
		if( get_site_option('rightpress_up_dis_woocommerce_custom_fields') != 1 ){
			update_site_option('rightpress_up_dis_woocommerce_custom_fields', 1);
		}
	}

	function footer_scripts(){
		$this->refresh_quickview();
	}

	function refresh_quickview(){

		if( ! reycore_wc__get_loop_component_status('quickview') ){
			return;
		}

		if ( class_exists('\WCCF_Assets') ) {
			\WCCF_Assets::enqueue_general_assets('frontend');
		}

	}

	function add_to_cart_link( $html, $product, $args ){

		// Check if there are any fields to display for this product
		if ( class_exists('\WCCF_WC_Product') && \WCCF_WC_Product::product_has_fields_to_display($product, (\WCCF_Settings::get('change_add_to_cart_text') === '1'))) {

			// Get product id
			$product_id = $product->get_id();

			// Format new link
			$html = sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button %s product_type_%s">%s</a>',
				esc_url(get_permalink($product_id)),
				esc_attr($product_id),
				esc_attr($product->get_sku()),
				esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
				($atc = reycore_wc__get_loop_component('add_to_cart')) ? $atc::add_to_cart_classes([ 'class' => $args['class'] ]) : [],
				esc_attr($product->get_type()),
				esc_html(apply_filters('wccf_category_add_to_cart_text', \WCCF_Settings::get('change_add_to_cart_text_label'), $product_id))
			);
		}

		return $html;

	}

}
