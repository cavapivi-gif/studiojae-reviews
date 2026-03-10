<?php
namespace ReyCore\Modules\LoopProductSkinCards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public function __construct(){

		parent::__construct();

		add_action('reycore/woocommerce/loop/init', [$this, 'register_loop_skin']);
	}

	public function register_loop_skin($base){
		$base->register_skin( new Skin() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Cards Skin for Products in Catalog', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows product catalog products with a border and hover animation.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
		];
	}

	public function module_in_use(){
		// @todo needs scanning in products grid elements as well
		return 'cards' === \ReyCore\Plugin::instance()->woocommerce_loop->get_active_skin();
	}

}
