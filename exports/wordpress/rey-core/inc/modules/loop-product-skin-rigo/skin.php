<?php
namespace ReyCore\Modules\LoopProductSkinRigo;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Skin extends \ReyCore\WooCommerce\LoopSkins\Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'rigo';
	}

	public function get_name(){
		return esc_html__('Rigo', 'rey-core');
	}

	public function __add_hooks()
	{
		add_filter( 'reycore/woocommerce/loop/thumbnail_slideshow/dots_position', [$this, 'dots_position']);
		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);

	}

	public function __remove_hooks()
	{
		remove_filter( 'reycore/woocommerce/loop/thumbnail_slideshow/dots_position', [$this, 'dots_position']);
		remove_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);
	}

	function add_customizer_options( $control_args, $section ){
		new CustomizerOptions( $control_args, $section );
	}

	public function get_default_settings(){
		return [
			'loop_add_to_cart_style'   => 'primary-out',
			'loop_quickview'           => '2',
			'loop_quickview_style'     => 'primary-out',
			'loop_quickview_position'  => 'bottomright',
			'loop_wishlist_position'   => 'topright',
			'loop_quickview_icon_type' => 'dots',
		];
	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_schemes(){

		return [
			'brands' => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'priority'      => 60,
			],
			'category' => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'priority'      => 70,
			],
			'add_to_cart' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/bottom-center',
				'callback'      => 'woocommerce_template_loop_add_to_cart',
				'priority'      => 10,
			],
			'quickview-bottomright' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/bottom-center',
				'callback'      => [ $this, 'component_quickview_button' ],
				'priority'      => 20,
			],
			'wishlist-bottomright' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/bottom-center',
				'callback'      => [ $this, 'component_wishlist' ],
				'priority'      => 30,
			],
			'new_badge'         => [
				'tag'           => 'reycore/loop_inside_thumbnail/top-left',
			],
		];

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
	 * Get quickview button HTML Markup
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button() {
		?>
		<div class="rey-comp rey-comp--quickview">
			<?php do_action('reycore/woocommerce/loop/quickview_button'); ?>
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
			'position' => 'bottomright',
			'before' => '<div class="rey-comp rey-comp--wishlist">',
			'after' => '</div>',
		]);
	}

	public function skin_classes()
	{
		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {

			if( get_theme_mod('rigo_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}

			if( get_theme_mod('rigo_loop_item_invert', true) ) {
				$classes['invert_color'] = '--invert';
			}

			if( get_theme_mod('loop_expand_thumbnails', false) ) {
				$classes['rigo_expand_thumbs'] = '--expand-thumbs';
			}
		}

		return $classes;
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

	public function dots_position(){
		return 'bottom-left';
	}

}
