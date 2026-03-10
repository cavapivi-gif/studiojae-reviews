<?php
namespace ReyCore\Modules\LoopSingleVariations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-single-variations';

	const QUERY_KEY = 'rey_single_variations';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'pre_get_posts', [$this, 'pre_get_posts'] );
		add_action( 'woocommerce_product_query', [$this, 'woocommerce_product_query'], 20 );
		add_filter( 'posts_clauses', [$this, 'posts_clauses'], 100, 2 );

		// add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		// add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	public function get_settings( $setting = null ){

		$s = [
			'hide_parents'    => true,
			'show_in_shop'    => true,
			'show_in_grid'    => true,
			'show_in_search'  => true,
			'show_in_filters' => true,
		];

		if( ! is_null($setting) ){
			if( isset($s[$setting]) ){
				return $s[$setting];
			}
			return false;
		}

		return $s;
	}

	private function set_ss($query)
	{

		if( is_search() ){
			if( ! $this->get_settings('show_in_search') ){
				return;
			}
		}

		$query->set( 'post_type', ['product', 'product_variation'] );
		$query->set( self::QUERY_KEY, true );
	}

	public function woocommerce_product_query( $query )
	{
		$this->set_ss($query);
	}

	public function pre_get_posts( $query )
	{
		if ( ( is_admin() && ( ! isset( $_REQUEST['action'] ) ) ) || isset( $query->query['product'] ) ) {
			return;
		}

		global $pagenow;

		$post_type = array_filter( (array) $query->get( 'post_type' ) );

		if ( ! (in_array( 'product', $post_type, true ) && 'edit.php' !== $pagenow) ) {
			return;
		}

		$this->set_ss($query);
	}

	public function posts_clauses( $clauses, $query ) {

		global $wpdb;

		if ( empty( $query->query_vars[self::QUERY_KEY] ) ) {
			return $clauses;
		}

		$hide_parent = $this->get_settings('hide_parents');

		if ( strripos( $clauses['where'], 'wp_wc_product_attributes_lookup' ) ) {

			if ( $hide_parent ) {
				$clauses['where'] = str_replace( 'product_or_parent_id', 'product_id', $clauses['where'] );
			}
			else {
				$data_requests = explode( ') temp )', $clauses['where'] );

				foreach ( $data_requests as $key => $request ) {
					if ( ')' === $request ) {
						continue;
					}

					$data_requests[ $key ] .= ' UNION SELECT product_id FROM wp_wc_product_attributes_lookup lt ' . strrchr( $request, 'WHERE' );
				}
				$clauses['where'] = implode( ') temp )', $data_requests );
			}
		}
		else {

			if ( $hide_parent ) {
				$clauses['where'] .= " AND 0 = (select count(*) as totalpart from {$wpdb->posts} as posts where posts.post_parent = {$wpdb->posts}.ID and posts.post_type = 'product_variation' ) ";
			}

		}

		return $clauses;
	}

	public function enqueue_scripts(){
		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
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

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('List variations as single products', 'Module name', 'rey-core'),
			// 'description' => esc_html_x('Override the content of any page with custom Elementor templates, assigned to a specific target location.', 'Module description', 'rey-core'),
			// 'icon'        => '',
			// 'categories'  => ['woocommerce'],
			// 'keywords'    => ['Elementor', 'Product Page', 'Product catalog'],
			// 'help'        => reycore__support_url('kb/custom-templates/'),
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
