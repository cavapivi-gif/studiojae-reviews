<?php
namespace ReyCore\Compatibility\WoocommerceProductTable;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	const ASSET_HANDLE = 'reycore-woo-product-table';

	public function __construct() {
		add_action('wc_product_table_before_get_table', [$this, 'disable_qty']);
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	function disable_qty(){
		add_filter('reycore/woocommerce/wrap_quantity', '__return_false');
		add_filter('reycore/woocommerce/add_quantity_controls', '__return_false');
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

}
