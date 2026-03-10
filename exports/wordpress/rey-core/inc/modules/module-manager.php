<?php
namespace ReyCore\Modules;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ModuleManager extends \ReyCore\ManagerBase
{

	const DB_OPTION = 'reycore-disabled-modules';

	public $disabled_modules; // update.php

	public $is_manager = false;

	public function __construct()
	{

		$this->is_manager = (isset($_REQUEST['page']) && $this->get_menu_slug() === reycore__clean($_REQUEST['page']));

		parent::__construct();
	}

	public static function manager_is_enabled(){

		if( ! reycore__get_props('modules_manager') ){
			return;
		}

		return ! \ReyCore\ACF\Helper::admin_menu_item_is_hidden('modules');
	}

	public function get_id(){
		return 'modules';
	}

	public function get_menu_title(){
		return __( 'Modules Manager', 'rey-core' );
	}

	public function set_page_config(){
		return [
			'title' => esc_html__('Modules Manager', 'rey-core'),
			'description' => __('Use this tool to control what modules and features can run in your site. Disabling a module will prevent any trace of its code to run.<br>If you\'re not sure if a certain module is actually used inside the site, you can use the Scan function on the right side.', 'rey-core'),
			'singular_item' => esc_html__('Module', 'rey-core'),
			'plural_item' => esc_html__('Modules', 'rey-core'),
			'not_in_use' => esc_html__('Not in use. Safe to deactivate module.', 'rey-core'),
		];
	}

	public function get_default_disabled_items(){
		return Base::default_disabled_modules();
	}

	public function render_media(){

		$s3_path = 'https://rey-theme.s3.us-west-2.amazonaws.com/public/modules-screenshots/';
		$path = $s3_path . $this->_item['id'];
		$has_video = isset($this->_item['video']) && $this->_item['video'];
		$url = $path . ($has_video ? '.mp4' : '.jpg');

		$modal_config = [
			'id'    => $this->_item['id'],
			'type'  => $has_video ? 'video': 'image',
			'src'   => $url,
			'width' => 900,
			'caption' => htmlentities($this->_item['title'], ENT_QUOTES),
		]; ?>

		<a href="<?php echo $url ?>" class="rey-itemManager-media __admin-lazy-img-container" data-reymodal='<?php echo esc_attr( wp_json_encode($modal_config) ); ?>'>

			<span class="__admin-lazy-img-loader"></span>

			<img data-src="<?php echo $path . '.jpg'; ?>" alt="<?php echo esc_attr($this->_item['title']); ?>" class="__admin-lazy-img" loading="lazy">

			<?php
			if( $has_video ){
				printf('<span class="rey-itemManager-action --video">%s</span>', reycore__get_svg_icon(['id' => 'play']));
			}
			else {
				printf('<span class="rey-itemManager-action --image">%s</span>', reycore__get_svg_icon(['id' => 'search']));
			}
		?>
		</a>

		<?php

		wp_enqueue_style(
			'reycore-modals',
			REY_CORE_URI . 'assets/css/general-components/modals/modals' . (is_rtl() ? '-rtl' : '') . '.css',
			[],
			REY_CORE_VERSION
		);
		wp_enqueue_script(
			'reycore-modals',
			REY_CORE_URI . 'assets/js/general/c-modal.js',
			[],
			REY_CORE_VERSION
		);

	}

	public function default_categories(){

		$this->categories = [
			'elementor' => 'Elementor',
			'woocommerce' => 'WooCommerce',
			'frontend' => 'Frontend',
			'misc' => 'Miscellaneous',
		];

	}


	public function prepare_items(){

		$this->default_categories();

		$groups = [];

		$modules_instance = \ReyCore\Plugin::instance()->modules;

		foreach ( $modules_instance->get_modules_list() as $module_id => $supported) {

			if( ! $supported ){
				continue;
			}

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $module_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Modules', 'Base');

			if( ! class_exists($class_name) ){
				trigger_error('Module classname is incorrect and cannot be loaded.', E_USER_NOTICE);
				continue;
			}

			$module = $class_name::get_module_data();

			if( ! $module['show_in_manager'] ){
				continue;
			}

			$item_category = $module['categories'][0];

			$groups[ $item_category ]['category'] = $this->categories[ $item_category ];
			$groups[ $item_category ]['items'][$module_id] = $module;

		}

		$this->groups = $groups;

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

		// if for some reason the module instance can't be retrieved
		// mark it as in use
		if( ! ($module = \ReyCore\Plugin::instance()->modules->get_module($data['item'])) ){
			return true;
		}

		return $module->module_in_use();

	}

	public function get_all_items(){
		return array_keys( \ReyCore\Plugin::instance()->modules->get_modules_list() );
	}

}
