<?php
namespace ReyCore\Modules\LoopProductSkinCards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Skin extends \ReyCore\WooCommerce\LoopSkins\Skin
{
	const BORDER_SIZE = 12;

	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'cards';
	}

	public function get_name(){
		return esc_html__('Cards', 'rey-core');
	}

	public function __add_hooks()
	{
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_start'], 9);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_end'], 900);
		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);

	}

	public function __remove_hooks()
	{
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_start'], 9);
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'product_details_wrapper_end'], 900);
		remove_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);

	}

	function add_customizer_options( $control_args, $section ){
		new CustomizerOptions( $control_args, $section );
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
			'quickview-bottom'      => [
				'type'          => 'action',
				'tag'           => 'reycore/woocommerce/after_shop_loop_item',
				'callback'      => [ $this, 'component_quickview_button' ],
				'priority'      => 30,
			],
			'wishlist-bottom' => [
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
	}

	/**
	 * Wrap add to cart link into special markup
	 *
	 * @since 1.0.0
	 */
	function wrap_add_to_cart_button($html)
	{
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

	public function skin_classes()
	{
		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {
			if( get_theme_mod('cards_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		if( get_theme_mod('cards_loop_square_corners', false) ) {
			$classes[] = '--square';
		}

		if( get_theme_mod('cards_loop_expand_thumbnails', false) ) {
			$classes['cards_expand_thumbs'] = '--expand-thumbs';
		}

		return $classes;
	}

	public function carousel_settings( $settings ){

		return $settings;
	}

	public function register_scripts($assets){

		$styles[ $this->get_asset_key() ] = [
			'src'     => Base::get_path( basename( __DIR__ ) ) . '/style.css',
			'deps'    => [],
			'version'   => REY_CORE_VERSION,
		];

		$assets->register_asset('styles', $styles);

	}

}
