<?php
namespace ReyCore\Modules\LoopProductSkinAero;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const KEY = 'aero';

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
		add_filter( 'reycore/elementor/product_grid/carousel_settings', [ $this, 'carousel_settings' ], 10, 2 );
	}

	public function register_loop_skin($base){
		$base->register_skin( new Skin() );
	}

	public function carousel_settings( $carousel_settings, $element ){

		$global = get_theme_mod('loop_skin', 'basic');
		$local = $element->_settings['loop_skin'];

		if( (self::KEY === $global && '' === $local) || self::KEY === $local ){

			$spacing = (($_s = get_theme_mod('loop_item_inner_padding', '')) ? $_s : 15) + 1; // 1px border (already enforced in CSS too)

			if( empty($carousel_settings['carousel_padding']['left']) ){
				$carousel_settings['carousel_padding']['left'] = $spacing;
			}

			if( empty($carousel_settings['carousel_padding']['right']) ){
				$carousel_settings['carousel_padding']['right'] = $spacing;
			}

		}

		return $carousel_settings;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Aero Skin for Products in Catalog', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows product catalog products with the Add to cart button stretched, last.', 'Module description', 'rey-core'),
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
