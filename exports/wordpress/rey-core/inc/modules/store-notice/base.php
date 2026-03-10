<?php
namespace ReyCore\Modules\StoreNotice;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-store-notice';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		remove_action( 'wp_footer', 'woocommerce_demo_store' );
		remove_action( 'wp_body_open', 'woocommerce_demo_store' );

		$hook = 'rey/before_site_wrapper';

		if( get_theme_mod('header_layout_type', 'default') !== 'none' ){
			$hook = 'rey/header/content';
		}

		add_action( $hook, 'woocommerce_demo_store', 5 );

		add_filter('woocommerce_demo_store', [$this, 'notice_markup'], 20, 2);
	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function notice_markup( $html, $notice ){

		$notice_id = md5( $notice );

		$style = 'style="display:block;"';

		// is hidden
		if( isset($_COOKIE['store_notice' . $notice_id]) && $_COOKIE['store_notice' . $notice_id] === 'hidden' ){
			$style = 'style="display:none;"';
		}

		$notice_start = '<div class="woocommerce-store-notice demo_store" data-notice-id="' . esc_attr( $notice_id ) . '" '. $style .'>';
		$notice_end = '</div>';

		$close_text = $original_close_text = esc_html__( 'Dismiss', 'rey-core' );
		$close_style = get_theme_mod('woocommerce_store_notice_close_style', 'default');

		if( $close_style !== 'default' ){
			$close_text = reycore__get_svg_icon(['id' => 'close']);
		}

		$notice_close = sprintf('<a href="#" class="woocommerce-store-notice__dismiss-link --%1$s" aria-label="%3$s">%2$s</a>', $close_style, $close_text, $original_close_text);

		reycore_assets()->add_styles(['rey-wc-general', 'rey-wc-general-deferred']);

		return sprintf( '%3$s <div class="woocommerce-store-notice-content">%1$s %2$s</div> %4$s',
			wp_kses_post( $notice ),
			$notice_close,
			$notice_start,
			$notice_end
		);
	}

	public function is_enabled() {
		return is_store_notice_showing();
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Store Notice (Promo Header Bar)', 'Module name', 'rey-core'),
			'description' => esc_html_x('Show promotion or any alert at the very top edge of the site.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			// 'help'        => reycore__support_url('kb/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
