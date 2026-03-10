<?php
namespace ReyCore\Modules\LoopProductSkinMinimal;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const KEY = 'minimal';
	const NAME = 'Minimal';

	public function __construct(){

		parent::__construct();

		add_action('reycore/woocommerce/loop/init', [$this, 'register_loop_skin']);
		add_action( 'reycore/customizer/control=loop_item_inner_padding', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_item_inner_padding_tablet', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_item_inner_padding_mobile', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_components_spacing', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_components_spacing_tablet', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_components_spacing_mobile', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_border_size', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_border_color', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_bg_color', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_radius', [ $this, 'customizer_options' ], 10, 2 );
		add_filter('reycore/woocommerce/discounts/top_hook', [$this, 'move_discount']);
	}

	public function register_loop_skin($base){
		$base->register_skin( new Skin() );
	}

	public function move_discount($scheme){
		$scheme['tag'] = 'reycore/loop_inside_thumbnail/top-left';
		return $scheme;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => sprintf(esc_html_x('%s Skin for Products in Catalog', 'Module name', 'rey-core'), self::NAME),
			'description' => esc_html_x('Shows product catalog products without the Add to cart button and price on the right.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
		];
	}


	public function customizer_options( $control_args, $section ){

		$current_control = $section->get_control( $control_args['settings'] );
		$current_control['active_callback'][0]['value'][] = self::KEY;
		$section->update_control( $current_control );

	}

	public function module_in_use(){
		// @todo needs scanning in products grid elements as well
		return self::KEY === \ReyCore\Plugin::instance()->woocommerce_loop->get_active_skin();
	}

}
