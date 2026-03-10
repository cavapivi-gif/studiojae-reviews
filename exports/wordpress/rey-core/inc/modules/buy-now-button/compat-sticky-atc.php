<?php
namespace ReyCore\Modules\BuyNowButton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CompatStickyAtc {

	public function __construct(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart', false) ){
			return;
		}

		add_action( 'reycore/customizer/control=product_page_sticky_add_to_cart__arrows', [ $this, 'add_customizer_options_sticky_bar' ], 10, 2 );
		add_filter( 'reycore/woocommerce/pdp/render/buy_now', [ $this, 'disable_default_button' ]);
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
		add_action( 'reycore/woocommerce/single/after_add_to_cart_form', [ $this, 'display' ], 10);
	}

	public function display(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart__buy_now', false) ){
			return;
		}

		add_filter( 'reycore/woocommerce/pdp/render/buy_now', '__return_true', 20);

		Base::instance()->display();
	}

	public function add_customizer_options_sticky_bar( $control_args, $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_sticky_add_to_cart__buy_now',
			'label'       => esc_html__( 'Display "Buy now" button', 'rey-core' ),
			'default'     => false,
			'active_callback' => [

				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}

}
