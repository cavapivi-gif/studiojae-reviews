<?php
namespace ReyCore\Modules\FooterReveal;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-footer-reveal';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
		add_action('reycore/customizer/section=footer-general', [$this, 'add_customizer_options'], 10, 2);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'body_class', [$this, 'body_classes'], 20 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

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
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function body_classes($classes){

		$classes['footer_reveal'] = '--footer-reveal';

		if( get_theme_mod('footer_reveal_fade', false) ){
			$classes['footer_reveal_fate'] = '--footer-reveal-fade';
		}

		return $classes;
	}

	public function add_customizer_options( $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'footer_reveal',
			'label'       => esc_html_x( 'Footer Reveal on Scroll', 'Customizer control label', 'rey-core' ),
			'default'     => false,
			'separator'   => 'before',
		] );

		$section->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'footer_reveal',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'footer_reveal_fade',
				'label'       => esc_html_x( 'Fade while revealing', 'Customizer control label', 'rey-core' ),
				'default'     => false,
			] );

		$section->end_controls_group();

		// Fixed Reveleaing footer
		// Overlay, color picker

	}

	public function is_enabled() {
		return get_theme_mod('footer_reveal', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Footer Reveal', 'Module name', 'rey-core'),
			'description' => esc_html_x('Will make the site footer fixed behind the site, to reveal on scrolling.', 'Module description', 'rey-core'),
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
