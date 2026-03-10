<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WidgetsManager extends \ReyCore\ManagerBase
{

	const DB_OPTION = 'reycore-disabled-elements';

	public function __construct()
	{
		if( ! reycore__get_props('elements_manager') ){
			return;
		}

		// add_action('reycore/elementor/before_process_template', [$this, 'check_template_elements']);

		parent::__construct();
	}

	public static function manager_is_enabled(){
		return ! \ReyCore\ACF\Helper::admin_menu_item_is_hidden('elements');
	}

	public function get_id(){
		return 'widgets';
	}

	public function get_menu_title(){
		return __( 'Elements Manager', 'rey-core' );
	}

	public function set_page_config(){
		return [
			'title' => esc_html__('Elements Manager (Widgets for Elementor)', 'rey-core'),
			'description' => __('Use this tool to control what elements to load in Elementor. If  you\'re not sure which elements to disable, try Scanning the site to see what elements are actually in use.', 'rey-core'),
			'singular_item' => esc_html__('Element', 'rey-core'),
			'plural_item' => esc_html__('Elements', 'rey-core'),
			'not_in_use' => esc_html__('Not in use. Safe to deactivate element.', 'rey-core'),
		];
	}

	public function get_items_prefix(){
		return Widgets::PREFIX;
	}

	public function get_default_disabled_items(){
		return apply_filters('reycore/elementor_widgets/disabled_defaults', [
			'reycore-cover-distortion',
		]);
	}

	public function before_render_page(){
		wp_enqueue_style( 'font-awesome' );
		if( class_exists('\Elementor\Icons_Manager') ){
			\Elementor\Icons_Manager::enqueue_shim();
		}
	}

	public function prepare_items(){

		$friendly_named_categories = [
			'rey-theme' => 'General',
			'rey-theme-covers' => 'Covers (Sliders)',
			'rey-header' => 'Header',
			'rey-woocommerce' => 'WooCommerce',
		];

		$rey_widgets_instance = \ReyCore\Plugin::instance()->elementor->widgets;

		$all_widgets = [];

		foreach ( $rey_widgets_instance::get_default_widgets_list() as $widget_id ) {

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $widget_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Elementor\Widgets');

			// bail if class is missing
			if ( ! class_exists( $class_name ) ) {

				$widget_dir = sprintf( '%1$s/%2$s/', REY_CORE_DIR . $rey_widgets_instance::WIDGETS_FOLDER, $widget_id);

				$file_path = $widget_dir . $widget_id . '.php';

				if ( ! is_file( $file_path ) ) {
					continue;
				}

				// load widget
				include_once $file_path;

				if ( ! class_exists( $class_name ) ) {
					if( \ReyCore\Plugin::is_dev_mode() ) {
						error_log(var_export( sprintf('Cannot find Class %s in Widgets Manager.', $class_name), true));
					}
					continue;
				}

			}

			// get widget config
			$widget = $class_name::get_rey_config();

			$all_widgets[ $widget_id ] = $widget;

		}

		$elementor_categories = \Elementor\Plugin::$instance->elements_manager->get_categories();

		foreach ( $all_widgets as $widget_id => $widget) {

			$item_category = $widget['categories'][0];

			$widget['id'] = $this->get_items_prefix() . $widget_id;

			$this->categories[$item_category] = $friendly_named_categories[ $item_category ];

			$this->groups[ $item_category ]['category'] = str_replace(\ReyCore\Elementor\Helper::rey_badge(), '', $elementor_categories[ $item_category ]['title']);
			$this->groups[ $item_category ]['items'][$widget_id] = $widget;
		}

	}

	public function get_all_items(){
		return array_map(function($item){
			return $this->get_items_prefix() . $item;
		}, array_keys( \ReyCore\Plugin::instance()->elementor->widgets->get_all_widgets() ));
	}

	public function ajax__scan_unused( $data ){

		if ( ! current_user_can('install_plugins') ) {
			return [
				'errors' => [ 'Operation not allowed!' ]
			];
		}

		if( ! isset($data['item']) ){
			return [
				'errors' => [ 'Item not passed!' ]
			];
		}

		// check the element name
		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'element', $data['item'] );

		return ! empty( $results );
	}

	/**
	 * Enable template elements
	 *
	 * @param array $data
	 * @return void
	 */
	public function check_template_elements($data){

		foreach ($this->get_disabled_items() as $element_id) {
			if( isset($data['content']) && strpos(wp_json_encode($data['content']), $element_id) !== false ){
				// enable element
				$this->change_item_status($element_id, true);
			}
		}

	}

}
