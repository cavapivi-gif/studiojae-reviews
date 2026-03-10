<?php
namespace ReyCore\Modules\PdpTabsAccordion;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-pdp-tba';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
	}

	public function init() {

		new Customizer();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'woocommerce_single_product_summary', [$this, 'prevent_short_desc_if_in_accordions'], 0 );
		add_action( 'woocommerce_single_product_summary', [$this, 'display_summary_accordion_tabs'], $this->get_position());
		add_action( 'reycore/woocommerce/pdp', [$this, 'product_page_classes']);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-acc' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/acc.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-tabs' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/tabs.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function get_position(){
		return absint( get_theme_mod('single__accordion_position', '39') );
	}

	public function display_summary_accordion_tabs(){

		add_filter('woocommerce_product_description_heading', '__return_false');
		add_filter('woocommerce_product_additional_information_heading', '__return_false');
		add_filter('reycore/woocommerce/blocks/headings', '__return_false');

		reycore__get_template_part('template-parts/woocommerce/single-accordion-tabs');

		remove_filter('woocommerce_product_description_heading', '__return_false');
		remove_filter('woocommerce_product_additional_information_heading', '__return_false');
		remove_filter('reycore/woocommerce/blocks/headings', '__return_false');

		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE . '-' . esc_attr(get_theme_mod('single__accordion_layout', 'acc')) );

	}


	public static function determine_acc_tab_to_start_opened( $index ){

		$acc_to_start_opened = [];

		if( get_theme_mod('single__accordion_first_active', false) || 'tabs' === get_theme_mod('single__accordion_layout', 'acc') ){
			$acc_to_start_opened[] = 1;
		}

		$should_start_opened = apply_filters('reycore/woocommerce/single_acc_tabs/start_opened', $acc_to_start_opened);

		// for easier, human readability
		$should_start_opened_minus = [];
		foreach ($should_start_opened as $key => $value) {
			$should_start_opened_minus[] = $value - 1;
		}

		return in_array($index, $should_start_opened_minus, true);
	}


	public function prevent_short_desc_if_in_accordions(){

		$accordion_tabs = $this->option();

		if( empty(wp_list_filter($accordion_tabs, ['item' => 'short_desc'])) ){
			return;
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 35 );
	}

	public function product_page_classes( $pdp ){

		if( empty(wp_list_filter($this->option(), ['item' => 'reviews'])) ){
			return;
		}

		$pdp->add_wrapper_classes('--acc-reviews');
	}


	public function option() {
		return get_theme_mod('single__accordion_items', []);
	}

	public function is_enabled() {
		return ! empty( $this->option() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Accordion/Tabs in Product Page', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows an accordion (or tabs) block inside the product page\'s summary.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/create-product-page-custom-tabs-blocks/#add-accordions-or-as-tabs-layout-in-product-summary'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
