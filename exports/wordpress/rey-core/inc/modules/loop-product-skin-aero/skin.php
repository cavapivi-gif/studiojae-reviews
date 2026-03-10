<?php
namespace ReyCore\Modules\LoopProductSkinAero;

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
		return esc_html__('Aero', 'rey-core');
	}

	public function __add_hooks()
	{

		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);

	}

	public function __remove_hooks()
	{
		remove_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'wrap_add_to_cart_button'], 20);
	}

	function add_customizer_options( $control_args, $section ){
		new CustomizerOptions( $control_args, $section );
	}

	public function get_default_settings(){
		return [
			'loop_add_to_cart_style'   => 'primary',
			'loop_quickview'           => '2',
			'loop_quickview_style'     => 'primary-out',
			'loop_quickview_position'  => 'bottomright',
			'loop_quickview_icon_type' => 'dots',
			'loop_wishlist_position'   => 'topright',
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

			if( get_theme_mod('aero_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
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

}
