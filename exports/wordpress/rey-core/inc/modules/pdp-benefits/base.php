<?php
namespace ReyCore\Modules\PdpBenefits;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public $settings = [];

	const ASSET_HANDLE = 'reycore-module-pdp-benefits';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		new Customizer();

		$this->set_settings();


		if( isset($this->settings['hooks'][ $this->settings['position'] ]) ){
			add_action($this->settings['hooks'][ $this->settings['position'] ]['hook'], [ $this, 'render' ], $this->settings['hooks'][ $this->settings['position'] ]['priority']);
		}

	}

	public function set_settings(){

		$this->settings = apply_filters('reycore/module/pdp_benefits/settings', [
			'position' => get_theme_mod('pdp_benefits_pos', 'before_meta'),
			'hooks' => [
				'after_atc' => [
					'hook'     => 'woocommerce_after_add_to_cart_form',
					'priority' => 11,
				],
				'before_meta' => [
					'hook'     => 'reycore/woocommerce_product_meta/outside/before',
					'priority' => 30,
				],
				'after_meta' => [
					'hook'     => 'reycore/woocommerce_product_meta/outside/after',
					'priority' => 10,
				],
			],
		]);

	}

	public function render(){

		if( ! $this->is_enabled() ){
			return;
		}

		printf('<div class="rey-benefitsList">%s</div>', \ReyCore\Elementor\GlobalSections::do_section( $this->opt(), true, true ));

	}

	public function opt() {
		return get_theme_mod('pdp_benefits_gs', 'no');
	}

	public function is_enabled() {
		return $this->opt() !== 'no';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Page Benefits List', 'Module name', 'rey-core'),
			'description' => esc_html_x('Show a list of benefits inside the product page, built with an Elementor Global section.', 'Module description', 'rey-core'),
			'categories'  => ['woocommerce'],
			'keywords'    => ['Elementor', 'Product Page'],
			'help'        => reycore__support_url('kb/product-benefits-list//'),
			'video' => true
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
