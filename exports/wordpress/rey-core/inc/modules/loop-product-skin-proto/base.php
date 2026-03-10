<?php
namespace ReyCore\Modules\LoopProductSkinProto;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public function __construct(){

		parent::__construct();

		add_action('reycore/woocommerce/loop/init', [$this, 'register_loop_skin']);
		add_action( 'reycore/customizer/control=loop_item_inner_padding', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_item_inner_padding_tablet', [ $this, 'customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_item_inner_padding_mobile', [ $this, 'customizer_options' ], 10, 2 );
	}

	public function register_loop_skin($base){
		$base->register_skin( new Skin() );
	}

	public function customizer_options( $control_args, $section ){

		$current_control = $section->get_control( $control_args['settings'] );
		$current_control['active_callback'][0]['value'][] = 'proto';
		$section->update_control( $current_control );

	}


	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Proto Skin for Products in Catalog', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows product catalog products with a hover shadow effect.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
		];
	}


	public function module_in_use(){
		// @todo needs scanning in products grid elements as well
		return 'proto' === \ReyCore\Plugin::instance()->woocommerce_loop->get_active_skin();
	}

}
