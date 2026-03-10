<?php
namespace ReyCore\Modules\MiniCartRecents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-mini-cart-recents';

	const COOKIE_NAME = 'reycore-mini-cart-recents';

	public function __construct()
	{
		if( ! class_exists('\WooCommerce') ){
			return;
		}

		add_action('init', [$this, 'init']);

		new Customizer();

	}

	public function init(){

		if( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/woocommerce/mini-cart/tabs', [ $this, 'add_tab_button' ] );
		add_action( 'reycore/woocommerce/mini-cart/after_content', [ $this, 'add_tab_content' ] );
		add_filter( 'woocommerce_add_to_cart_fragments', [$this, 'add_cart_fragment'] );
		add_action( 'reycore/woocommerce/minicart/cart_panel', [$this, 'render_markup'], 10);
		add_action( 'woocommerce_before_mini_cart', [$this, 'add_cart_assets']);
		add_filter( 'reycore/woocommerce/minicarts/supports_products', '__return_true' );

	}

	public function add_cart_fragment( $fragments ) {
		$fragments['.__recent-count'] = $this->count_span();
		return $fragments;
	}

	public function get_recent_product_ids(){

		$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];

		if( empty($viewed_products) ){
			return [];
		}

		$viewed_products = array_filter($viewed_products, function($product_id) {
			return wc_get_product($product_id);
		});

		return array_slice( array_reverse( array_map( 'absint', $viewed_products ) ), 0, 10 );
	}

	public function register_actions( $ajax_manager ) {

		$ajax_manager->register_ajax_action( 'cart_recent_items', [$this, 'ajax_response__output'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );

		$ajax_manager->register_ajax_action( 'recount_cart_recent_items', [$this, 'ajax__recount'], [
			'auth'   => 3,
			'nonce'  => false,
		] );

	}

	public function add_cart_assets(){
		reycore_assets()->add_styles([self::ASSET_HANDLE, 'rey-simple-scrollbar']);
		reycore_assets()->add_scripts([self::ASSET_HANDLE, 'rey-simple-scrollbar']);
	}

	function add_tab_button(){
		$title_tag = reycore__header_cart_params('title_tag'); ?>
		<div class="__tab" data-item="recent">
			<<?php echo \ReyCore\Helper::get_valid_title_tag($title_tag, 'div') ?> class="rey-cartPanel-title">
				<?php echo esc_html__('RECENTLY VIEWED', 'rey-core') ?>
				<?php echo $this->count_span(); ?>
			</<?php echo \ReyCore\Helper::get_valid_title_tag($title_tag, 'div') ?>>
		</div>
		<?php
	}

	function add_tab_content(){
		?>
		<div class="__tab-content rey-cartRecent-itemsWrapper --loading" data-item="recent">
			<div class="rey-lineLoader"></div>
		</div>
		<?php
	}

	function ajax_response__output(){

		$ids = $this->get_recent_product_ids();

		if( reycore__is_multilanguage() ){
			$ids = apply_filters('reycore/translate_ids', $ids, 'product');
		}

		if( empty( $ids ) ){
			return;
		}

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return;
		}

		return $minicart_tag->prepare_products_data_fragment($ids, 'recent', [
			'purchasable' => false,
			'stock' => false,
		]);
	}

	public function count_span(){
		return sprintf('<span class="__recent-count __nb">%d</span>', count( $this->get_recent_product_ids() ));
	}

	public function ajax__recount(){
		return count( $this->get_recent_product_ids() );
	}

	public function render_markup(){
		reycore__get_template_part('template-parts/woocommerce/header-shopping-cart-recent');
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/frontend-style.css',
				'deps'     => ['woocommerce-general'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low'
			],
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-script.js',
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
			],
		]);

	}

	public function is_enabled(){
		return get_theme_mod('header_cart__recent', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Recent Products in Mini-Cart', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a new tab inside the Mini Cart, containing a list of the most recently viewed products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/#how-to-enable-a-list-of-recently-viewed-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
