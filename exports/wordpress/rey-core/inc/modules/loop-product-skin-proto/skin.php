<?php
namespace ReyCore\Modules\LoopProductSkinProto;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Skin extends \ReyCore\WooCommerce\LoopSkins\Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'proto';
	}

	public function get_name(){
		return esc_html__('Proto', 'rey-core');
	}

	public function __add_hooks()
	{

		add_action( 'woocommerce_after_shop_loop_item', [$this, 'wrap_product_buttons'], 9);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 199);
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'reycore/woocommerce/loop/before_product_inner', [$this, 'add_shadow_holder'], 10 );
	}

	public function __remove_hooks()
	{
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'wrap_product_buttons'], 9);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 199);
		remove_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 19);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		remove_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 19);
		remove_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		remove_action( 'reycore/woocommerce/loop/before_product_inner', [$this, 'add_shadow_holder'], 10 );
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
			'quickview-bottom'      => [
				'callback'      => [ $this, 'component_quickview_button' ],
			],
			'quickview-topright'      => [
				'callback'      => [ $this, 'component_quickview_button' ],
			],
			'quickview-bottomright'      => [
				'callback'      => [ $this, 'component_quickview_button' ],
			],
			'wishlist-bottom' => [
				'callback'      => [ $this, 'component_wishlist' ],
			],
			'wishlist-topright' => [
				'callback'      => [ $this, 'component_wishlist' ],
			],
			'wishlist-bottomright' => [
				'callback'      => [ $this, 'component_wishlist' ],
			],
		];

	}

	function is_padded(){
		return get_theme_mod('proto_loop_padded', false);
	}

	/**
	 * Wraps the product details so it can be absolutely positioned
	 *
	 * @since 1.0.0
	 */
	public function wrap_product_details(){
		?>
		<div class="rey-loopDetails <?php echo $this->is_padded() ? '--padded' : '' ?>">
		<?php
	}

	/**
	 * Wrap product buttons
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_buttons()
	{ ?>
		<div class="rey-loopButtons">
		<?php
	}


	/**
	 * Item Component - Quickview button
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button(){
		$start = $end = '';

		if( reycore_wc__get_setting('loop_quickview_position') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		echo $start;

		do_action('reycore/woocommerce/loop/quickview_button');

		echo $end;
	}

	/**
	 * Item Component - Wishlist
	 *
	 * @since 1.3.0
	 */
	public function component_wishlist(){
		$start = $end = '';

		if( reycore_wc__get_setting('loop_wishlist_position') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		do_action('reycore/woocommerce/loop/wishlist_button', [
			'position' => '',
			'before' => $start,
			'after' => $end,
		]);
	}


	public function skin_classes()
	{
		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {
			if( get_theme_mod('proto_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		if( $this->is_padded() ){
			$classes['shadow_active'] = '--shadow-' . get_theme_mod('proto_loop_shadow', '1');
			$classes['shadow_hover'] = '--shadow-h-' . get_theme_mod('proto_loop_shadow_hover', '3');
		}

		return $classes;
	}

	public function add_shadow_holder(){
		echo '<div class="rey-protoShadowHolder"></div>';
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
