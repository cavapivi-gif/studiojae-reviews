<?php
namespace ReyCore\Modules\ProductsPerPage;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-ppp';

	const KEY = 'products-per-page';

	public $posts_per_page;
	public $options;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_component']);
	}

	public function init(){

		new Customizer();

		if( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'woocommerce_product_query', [ $this, 'woocommerce_product_query'], 100);

	}

	public function register_component($base){
		$base->register_component( new LoopComponent );
	}

	public function set_posts_per_page(){
		if( is_null($this->posts_per_page) ){
			$this->posts_per_page = absint(reycore_wc_get_columns('desktop') * wc_get_default_product_rows_per_page());
		}
	}

	public function set_options(){
		if( is_null($this->options) ){
			$this->options = [
				$this->posts_per_page,
				$this->posts_per_page * 2,
				$this->posts_per_page * 4
			];
		}
	}

	public static function get_key(){
		return apply_filters('reycore/woocommerce/ppp_selector/key', self::KEY);
	}

	public function get_settings(){

		$settings = apply_filters('reycore/woocommerce/ppp_selector/settings', [
			'label'    => esc_html_x('SHOW', 'Label for "show selector" in catalog', 'rey-core'),
			'options'  => $this->options,
			'selected' => $this->posts_per_page,
		], $this);

		if ( ($count = $this->get_ppp()) && in_array($count, $this->options, true) ) {
			$settings['selected'] = $count;
		}

		return $settings;
	}

	public function woocommerce_product_query( $q )
	{

		$this->set_posts_per_page();
		$this->set_options();

		if ( ! ($count = $this->get_ppp() ) ) {
			return;
		}

		$q->set( 'posts_per_page', $count );
	}

	public function get_ppp(){

		if ( ! (isset($_REQUEST[self::get_key()]) && ($count = absint( $_REQUEST[self::get_key()] )) ) ) {
			return false;
		}

		// force only predefined options to be allowed
		if ( ! in_array($count, $this->options, true) ) {
			return false;
		}

		return $count;
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-style.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop-inlinelist'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low',
			],
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reycore_ppp_params',
					'params' => [
						'key' => self::get_key(),
					],
				],
			],
		]);

	}

	public function is_enabled(){
		return get_theme_mod('loop_switcher_ppp', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Products per page switcher in catalog', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a switcher to change how many numbers of products to be listed in catalog.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
			// 'help'        => reycore__support_url('kb/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
