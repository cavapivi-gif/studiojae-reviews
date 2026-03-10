<?php
namespace ReyCore\Modules\ElementorAcf;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public function __construct()
	{

		parent::__construct();

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );

	}

	public function register_widgets( $widgets_manager ) {

		foreach ( [
			'text',
			'heading',
			'button',
			'image',
			'table',
			// 'icon', // field uses 2 values, icon string and library
		] as $class_name ) {


			$class_name = ucwords( str_replace( '-', ' ', $class_name ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Modules\\ElementorAcf');

			$widgets_manager->register( new $class_name );
		}
	}

	/**
	 * Add Rey Widget Categories
	 *
	 * @since 1.0.0
	 */
	public function add_elementor_widget_categories( $elements_manager ) {

		$categories = [
			'rey-acf' => [
				'title' => __( 'ACF Fields', 'rey-core' ). \ReyCore\Elementor\Helper::rey_badge(),
			],
		];

		foreach( $categories as $key => $data ){
			$elements_manager->add_category($key, $data);
		}
	}

	public static function get_field( $key, $index = null ){

		if( ! class_exists('\ReyCore\ACF\Helper') ){
			return;
		}

		return \ReyCore\ACF\Helper::get_field_from_elementor([
			'key'            => $key,
			'parts_count'    => 3,
			'index'          => $index,
			'provider_aware' => true,
		]);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('ACF Fields for Elementor', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds new Elementor elements (Text, Image, Button, etc) which can render the contents of any Advanced Custom Field is picked. Great tool for dynamic pages.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/adding-acf-fields-inside-pages'),
			'video' => true,
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'element', [
			\ReyCore\Elementor\Widgets::PREFIX . 'acf-button',
			\ReyCore\Elementor\Widgets::PREFIX . 'acf-heading',
			\ReyCore\Elementor\Widgets::PREFIX . 'acf-text',
			\ReyCore\Elementor\Widgets::PREFIX . 'acf-image',
		] );

		return ! empty($results);

	}

}
