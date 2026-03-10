<?php
namespace ReyCore\Modules\LoopProductSkinMinimal;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Skin extends \ReyCore\WooCommerce\LoopSkins\Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return Base::KEY;
	}

	public function get_name(){
		return Base::NAME;
	}

	public function __add_hooks()
	{
		add_action( 'woocommerce_shop_loop_item_title', [$this, 'wrap_title_start'], 9); // right before the title
		add_action( 'woocommerce_shop_loop_item_title', [$this, 'wrap_title_end'], 15); // after title
		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);
	}

	public function __remove_hooks()
	{
		remove_action( 'woocommerce_shop_loop_item_title', [$this, 'wrap_title_start'], 9);
		remove_action( 'woocommerce_shop_loop_item_title', [$this, 'wrap_title_end'], 15);
		remove_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);
	}

	public function get_default_settings(){
		return [
			'loop_discount_label'      => 'top',
			'loop_add_to_cart_style'   => 'under',
			'loop_quickview'           => '2',
			'loop_quickview_style'     => 'primary-out',
			'loop_quickview_position'  => 'bottomright',
			'loop_quickview_icon_type' => 'dots',
			'loop_wishlist_position'   => 'topright',

		];
	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_schemes(){

		return [
			'prices'         => [
				'type'     => 'action',
				'tag'      => 'woocommerce_shop_loop_item_title',
				'callback' => 'woocommerce_template_loop_price',
				'priority' => 11,
			],
		];
	}


	/**
	 * Wrap title and price
	 *
	 * @since 1.0.0
	 */
	function wrap_title_start()
	{
		echo '<div class="rey-comp rey-comp--title-price">';
	}

	/**
	 * Wrap title and price
	 *
	 * @since 1.0.0
	 */
	function wrap_title_end()
	{
		echo '</div>';
	}

	/**
	 * Wrap add to cart link into special markup
	 *
	 * @since 1.0.0
	 */
	function wrap_add_to_cart_button($html)
	{
		return sprintf( '<div class="rey-comp rey-comp--addtocart">%s</div>' , $html);
	}

	/**
	* Add the icon, wrapped in custom div
	*
	* @since 1.0.0
	*/
	public function component_wishlist()
	{
		do_action('reycore/woocommerce/loop/wishlist_button', [
			'position' => 'bottomright',
			'before' => '<div class="rey-comp rey-comp--wishlist">',
			'after' => '</div>',
		]);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			$this->get_asset_key() => [
				'src'     => Base::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);
	}

	public function move_discount_pos(){
		return 'top';
	}

}
