<?php
namespace ReyCore\Modules\CardLoop;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);

	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		new LoopTemplates();
	}

	public static function show_in_manager(){
		return false;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Card Global Section for Product Listing', 'Module name', 'rey-core'),
			'description' => esc_html_x('Build global sections which are used as templates for products listing items.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['elementor', 'carousel', 'woocommerce', 'products', 'loop'],
			// 'video'       => true,
			'help'        => reycore__support_url('kb/card-global-section/'),
		];
	}

	public function module_in_use(){
		return false;
	}

}
