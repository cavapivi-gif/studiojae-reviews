<?php
namespace ReyCore\Modules\Compare;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CompatStickyAtc {

	public function __construct(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart', false) ){
			return;
		}

		add_action( 'reycore/customizer/control=product_page_sticky_add_to_cart__arrows', [ $this, 'add_customizer_options_sticky_bar' ], 10, 2 );
		add_filter( 'reycore/woocommerce/pdp/render/compare', [ $this, 'disable_default_button' ]);
		add_action( 'reycore/module/satc/before_markup', [ $this, 'display_in_position' ]);

	}

	/**
	 * Disable the default inherited button from summary
	 *
	 * @param bool $status
	 * @return bool
	 */
	public function disable_default_button( $status ){

		if( get_query_var('product_page_sticky_bar') ){
			return;
		}

		return $status;
	}

	public function display_in_position(){
		add_action( 'reycore/woocommerce/single/after_add_to_cart_form', [ $this, 'display' ], 30);
	}

	public function is_simple_product(){
		return ($product = wc_get_product()) && 'simple' === $product->get_type();
	}

	public function display(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart__compare', false) ){
			return;
		}

		add_filter( 'reycore/woocommerce/pdp/render/compare', '__return_true', 20);

		add_filter('theme_mod_compare__pdp_btn_style', function( $value ){

			if( $this->is_simple_product() ){
				return 'btn-primary-outline';
			}

			return $value;
		});

		add_filter('theme_mod_compare__pdp_wtext', function( $value ){

			if( $this->is_simple_product() ){
				return '';
			}

			return $value;
		});

		Base::instance()->output_pdp_button();
	}

	public function add_customizer_options_sticky_bar( $control_args, $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_sticky_add_to_cart__compare',
			'label'       => esc_html__( 'Display "Compare" button', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'compare__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}

}
