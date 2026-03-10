<?php
namespace ReyCore\Modules\ScrollToTop;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-scrolltotop';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/customizer/panel=general', [$this, 'load_customizer_options']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'rey/after_site_wrapper', [$this, 'render']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function enqueue_scripts(){
		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style'. $assets::rtl() .'.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	function render()
	{

		$style = $this->option();

		$html = sprintf('<span class="rey-scrollTop-text">%s</span>', get_theme_mod('scroll_to_top__text', esc_html__('TOP', 'rey-core') ));

		$classes[] = '--' . $style;

		// Hide devices
		$hide_devices = get_theme_mod('scroll_to_top__hide_devices', []);
		$devices_map = [
			'desktop' => 'lg',
			'tablet' => 'md',
			'mobile' => 'sm',
		];
		foreach ($hide_devices as $value) {
			$classes[] = '--dnone-' . $devices_map[$value];
		}

		$classes[] = '--pos-' . get_theme_mod('scroll_to_top__position', 'right');

		if(
			($svg = \ReyCore\Plugin::instance()->svg) &&
			($custom_icon = get_theme_mod('scroll_to_top__custom_icon', '')) &&
			($svg_code = $svg->get_inline_svg( [ 'id' => $custom_icon ] )) ){
			$html .= $svg_code;
		}
		else {

			if( $style === 'style1' ){
				$html .= reycore__svg_arrows([
					'echo'   => false,
					'single' => 'right'
				]);
			}

			else if( $style === 'style2' ){
				$html .= reycore__svg_arrows([
					'type'   => 'chevron',
					'echo'   => false,
					'single' => 'right'
				]);
			}
		}

		printf(
			'<a href="#scrolltotop" class="rey-scrollTop %1$s" data-entrance="%3$d" data-lazy-hidden>%2$s</a>',
			implode(' ', $classes),
			apply_filters('reycore/scroll_to_top/html', $html),
			get_theme_mod('scroll_to_top__entrance_point', 0)
		);

		$this->enqueue_scripts();

	}

	public function option() {
		return get_theme_mod('scroll_to_top__enable', '');
	}

	public function is_enabled() {
		return $this->option() !== '';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Scroll to top button', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a button on the edge of the site which can point back to the top of the site.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['frontend'],
			'keywords'    => [''],
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
