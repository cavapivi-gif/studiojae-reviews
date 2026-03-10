<?php
namespace ReyCore\Modules\Wishlist;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CompatStickyAtc {

	public function __construct(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart', false) ){
			return;
		}

		add_action( 'reycore/customizer/control=product_page_sticky_add_to_cart__arrows', [ $this, 'add_customizer_options_sticky_bar' ], 10, 2 );
		add_filter( 'reycore/woocommerce/pdp/render/wishlist', [ $this, 'disable_default_button' ]);
		add_action( 'reycore/module/satc/after_atc', [ $this, 'display' ]);

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

	public function display(){

		if( ! get_theme_mod('product_page_sticky_add_to_cart__wishlist', false) ){
			return;
		}

		add_filter( 'reycore/woocommerce/pdp/render/wishlist', '__return_true', 20);

		add_filter('theme_mod_wishlist_pdp__btn_style', function( $value ){
			return 'btn-primary-outline';
		});

		add_filter('theme_mod_wishlist_pdp__wtext', function( $value ){
			return '';
		});

		Base::instance()->output_pdp_button();
	}

	public function add_customizer_options_sticky_bar( $control_args, $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_sticky_add_to_cart__wishlist',
			'label'       => esc_html__( 'Display "Wishlist" button', 'rey-core' ),
			'default'     => false,
			'active_callback' => [

				[
					'setting'  => 'wishlist__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}

}
