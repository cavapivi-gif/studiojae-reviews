<?php
namespace ReyCore\WooCommerce\LoopSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Wrapped extends Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'wrapped';
	}

	public function get_name(){
		return esc_html__('Wrapped', 'rey-core');
	}

	public function __add_hooks()
	{
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 20);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 20);
		add_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		add_filter( 'reycore/woocommerce/product_badges_positions', [$this, 'set_product_badges_positions'] );
		add_filter( 'theme_mod_loop_product_titles_height', '__return_empty_string', 10 );
	}


	public function __remove_hooks()
	{
		remove_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 20);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		remove_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 20);
		remove_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		remove_filter( 'reycore/woocommerce/product_badges_positions', [$this, 'set_product_badges_positions'] );
		remove_filter( 'theme_mod_loop_product_titles_height', '__return_empty_string', 10 );
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
				'tag'           => 'woocommerce_after_shop_loop_item',
				'priority'      => 60,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'priority'      => 70,
			],
			'prices'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_price',
				'priority'      => 60,
			],
			'title'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_product_title',
				'priority'      => 50,
			],
			'excerpt'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'priority'      => 50,
			],
			'new_badge'         => [
				'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			],
			'variations'         => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-left',
				'priority'      => 10,
			],
		];

	}

	/**
	 * Wraps the product details so it can be absolutely positioned
	 *
	 * @since 1.0.0
	 */
	public function wrap_product_details(){
		?>
		<div class="rey-loopWrapper-details">
		<?php
	}

	public function skin_classes(){

		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {

			if( get_theme_mod('wrapped_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}

			foreach (['', '_tablet', '_mobile'] as $bp) {
				if( get_theme_mod('wrapped_loop_item_height' . $bp, '') !== '' ) {
					$classes['custom-height'] = '--custom-height';
				}
			}

		}

		return $classes;
	}

	function set_product_badges_positions($positions){

		return [
			'top_left' => 'reycore/loop_inside_thumbnail/top-left',
			'top_right' => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left' => ['woocommerce_before_shop_loop_item_title', 21],
			'bottom_right' => ['woocommerce_before_shop_loop_item_title', 21],
			'before_title' => ['woocommerce_before_shop_loop_item_title', 21],
			'after_content' => ['woocommerce_after_shop_loop_item', 199],
		];

	}

	public function get_script_params(){

		$params = [];

		if( isset($params['equalize_selectors']) ){
			$params['equalize_selectors'] = [];
		}

		return $params;
	}

}
