<?php
namespace ReyCore\WooCommerce\LoopSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Basic extends Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'basic';
	}

	public function get_name(){
		return esc_html__('Basic', 'rey-core');
	}

	public function __add_hooks()
	{
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_start'], 9);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_end'], 900);
		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20, 3);
	}

	public function __remove_hooks()
	{
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_start'], 9);
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_end'], 900);
		remove_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20, 3);
	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_schemes(){

		return [
			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'priority'      => 60,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'priority'      => 70,
			],
			'prices'         => [
				'type'          => 'action',
				'tag'           => 'reycore/woocommerce/after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_price',
				'priority'      => 10,
			],
			'add_to_cart'    => [
				'type'          => 'action',
				'tag'           => 'reycore/woocommerce/after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_add_to_cart',
				'priority'      => 20,
			],
			'quickview-bottom' => [
				'type'          => 'action',
				'tag'           => 'reycore/woocommerce/after_shop_loop_item',
				'callback'      => [ $this, 'component_quickview_button' ],
				'priority'      => 30,
			],
			'wishlist-bottom'       => [
				'type'          => 'action',
				'tag'           => 'reycore/woocommerce/after_shop_loop_item',
				'callback'      => [ $this, 'component_wishlist'],
				'priority'      => 40,
			],
		];
	}


	/**
	 * Wrap product info - start
	 *
	 * @since 1.0.0
	 **/
	function product_details_wrapper_start()
	{ ?>
		<div class="rey-productLoop-footer">
		<?php
	}

	/**
	 * Wrap product info - end
	 *
	 * @since 1.0.0
	 **/
	function product_details_wrapper_end()
	{
		/**
		 * Adds wrapper after shop loop item (QuickView & Wishlist)
		 *
		 * @since 1.0.0
		 */
		do_action('reycore/woocommerce/after_shop_loop_item'); ?>

		</div>
		<!-- /.rey-productLoop-footer -->
		<?php

		do_action('reycore/woocommerce/after_shop_loop_item/footer');
	}

	/**
	 * Wrap add to cart link into special markup
	 *
	 * @since 1.0.0
	 */
	function wrap_add_to_cart_button($html, $products = [], $args = [])
	{
		if( isset($args['wrap_button']) && ! $args['wrap_button'] ){
			return $html;
		}
		return sprintf( '<div class="__break"></div><div class="rey-productFooter-item rey-productFooter-item--addtocart"><div class="rey-productFooter-inner">%s</div></div>' , $html);
	}

	/**
	 * Get quickview button HTML Markup
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button()
	{ ?>
		<div class="rey-productFooter-item rey-productFooter-item--quickview">
			<div class="rey-productFooter-inner">
				<?php do_action('reycore/woocommerce/loop/quickview_button'); ?>
			</div>
		</div><?php
	}

	/**
	* Add the icon, wrapped in custom div
	*
	* @since 1.0.0
	*/
	public function component_wishlist()
	{
		do_action('reycore/woocommerce/loop/wishlist_button', [
			'position' => 'bottom',
			'before' => '<div class="rey-productFooter-item rey-productFooter-item--wishlist"><div class="rey-productFooter-inner">',
			'after' => '</div></div>',
		]);
	}

	/**
	 * Adds custom CSS Classes
	 *
	 * @since 1.1.2
	 */
	public function skin_classes()
	{
		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {

			if( get_theme_mod('loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		return $classes;
	}

}
