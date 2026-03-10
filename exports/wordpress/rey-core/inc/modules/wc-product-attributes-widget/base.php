<?php
namespace ReyCore\Modules\WcProductAttributesWidget;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-product-catattr-widget';

	public function __construct()
	{
		include_once __DIR__ . '/widget.php';
		include_once __DIR__ . '/walker.php';

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
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
				'deps'    => ['reycore-woocommerce', 'rey-simple-scrollbar'],
				'version'   => REY_CORE_VERSION,
			]
		]);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Navigation Widget for Product Categories and Attributes', 'Module name', 'rey-core'),
			'description' => esc_html_x('A purely simple and navigational (not filtering) widget for catalog sidebars.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			// 'help'        => reycore__support_url('kb/'),
			'video' => true,
		];
	}

	public function module_in_use(){

		$in_use = false;

		foreach (wp_get_sidebars_widgets() as $sidebar_widgets) {
			foreach ($sidebar_widgets as $widget) {
				if( strpos($widget, 'rey_woocommerce_product_categories') === 0 ){
					$in_use = true;
					break;
				}
			}
			if( $in_use ){
				break;
			}
		}

		return $in_use;
	}
}
