<?php
namespace ReyCore\Libs\PluginsManager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\ManagerBase
{
	public $is_manager = false;

	public function __construct()
	{

		// determine if is page
		$this->is_manager = (isset($_REQUEST['page']) && $this->get_menu_slug() === reycore__clean($_REQUEST['page']));

		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action( 'reycore/manager/plugins/before_start', [$this, 'add_essential_plugins_separator']);
		add_action( 'reycore/manager/plugins/after_item=woocommerce', [$this, 'add_recommended_plugins_separator']);

		parent::__construct();
	}

	public static function manager_is_enabled(){

		if( ! class_exists('\Rey\Libs\Plugins') ){
			return;
		}

		// maybe don't show
		if( ! reycore__get_props('plugins_manager') ){
			return;
		}

		if( ! reycore__get_purchase_code() ){
			return;
		}

		return ! \ReyCore\ACF\Helper::admin_menu_item_is_hidden('plugins');
	}

	public function get_id(){
		return 'plugins';
	}

	public function get_menu_title(){
		return __( 'Plugins Manager', 'rey-core' );
	}

	public function set_page_config(){
		return [
			'cols'              => 3,
			'title'             => esc_html__('Plugins Manager', 'rey-core'),
			'description'       => sprintf(__('These are some of the plugins Rey is using throughout the demos or just general recommendations. Please know that they are not dependencies. Please <a href="%s" target="_blank">visit the documentation</a> for more information.', 'rey-core'), reycore__support_url('kb/installing-rey-plugins/') ),
			'singular_item'     => esc_html__('Plugin', 'rey-core'),
			'plural_item'       => esc_html__('Plugins', 'rey-core'),
			'toggles'           => false,
			'vis_filter'        => false,
			'cat_filter'        => false,
			'not_in_use_notice' => false,
		];
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'run_plugin_action', [$this, 'ajax__run_action'], 1 );
		$ajax_manager->register_ajax_action( 'run_plugins_refresh', [$this, 'ajax__refresh_plugins'], 1 );
	}

	public function ajax__refresh_plugins(){
		$plugins_ob = new \Rey\Libs\Plugins();
		return ! empty($plugins_ob->refresh());
	}

	public function ajax__run_action( $data ){

		if( empty($data['action']) ){
			return ['error' => 'Please provide an action.'];
		}

		if( empty($data['slug']) ){
			return ['error' => 'Please provide the plugin slug.'];
		}

		$plugins_ob = new \Rey\Libs\Plugins();
		$rey_plugins = $plugins_ob->get_plugins();

		if( ! isset($rey_plugins[$data['slug']]) ){
			return ['error' => 'Not a registered plugin.'];
		}

		$new_action = '';
		$version = '';

		switch ($data['action']) {
			case 'activate':
				if( ! is_wp_error( $plugins_ob->activate_plugin_by_slug($data['slug']), true ) ){
					$new_action = 'deactivate';
				}
				break;
			case 'deactivate':
				if( ! is_wp_error( $plugins_ob->deactivate_plugin_by_slug($data['slug']) ) ){
					$new_action = 'activate';
				}
				break;
			case 'install':
				if( $plugins_ob->install_plugin($data['slug']) ){
					$new_action = 'deactivate';
				}
				break;
			case 'update':
				if( $update = $plugins_ob->upgrade_plugins_by_slug([$data['slug']]) ){
					$new_action = 'deactivate';
					$version = array_values($update)[0];
				}
				break;
		}

		if( ! $new_action ){
			return ['error' => 'Something went wrong'];
		}

		$actions = self::actions();

		return [
			'text'       => $actions[$new_action],
			'new_action' => $new_action,
			'version'    => $version,
		];
	}

	public function enqueue_scripts(){

		if( ! self::manager_is_enabled() ){
			return;
		}

		if( ! $this->is_manager ){
			return;
		}

		$rtl = reycore_assets()::rtl();

		wp_enqueue_style('rey-plugin-manager-style', REY_CORE_URI . 'assets/css/general-components/admin/plugin-manager' . $rtl . '.css', [], REY_CORE_VERSION);
		wp_enqueue_script('rey-plugin-manager-script', REY_CORE_URI . 'assets/js/lib/plugin-manager.js', ['rey-script'], REY_CORE_VERSION, true);
		wp_localize_script('rey-plugin-manager-script', 'reyPluginManagerParams', [
			'text' => [
				'reloading' => esc_html_x('Reloading page..', 'Demo import text', 'rey-core'),
			],
		]);
	}

	public function render_media(){
		if( empty($this->_item['image']) ){
			return;
		} ?>
		<a href="<?php echo esc_url($this->_item['url']) ?>" target="_blank" class="rey-itemManager-media">
			<img src="<?php echo $this->_item['image']; ?>" alt="<?php echo esc_attr($this->_item['title']); ?>" class="" loading="lazy">
			<?php printf('<span class="rey-itemManager-action --image">%s</span>', reycore__get_svg_icon(['id' => 'external-link'])); ?>
		</a>
		<?php
	}

	public function add_essential_plugins_separator(){
		printf('<div class="__separator"><h2>%s</h2><p>%s</p></div>', esc_html__('Essential plugins', 'rey-core'), esc_html__('These plugins are the foundation of the store. WooCommerce is optional if you want to create just a regulard presentation website.', 'rey-core'));
	}

	public function add_recommended_plugins_separator(){
		printf('<div class="__separator"><h2>%s</h2><p>%s</p></div>', esc_html__('Recommended plugins', 'rey-core'), esc_html__('These are various plugins either recommended for their quality or used throughout the premade stores.', 'rey-core'));
	}

	public function render_title(){

		printf('<h2><span class="__title-text">%s</span> <span class="__version">%s</span></h2>', $this->_item['title'], $this->_item['version']);

	}

	public function render_icon(){ }

	public function render_buttons_bar(){

		if( ! reycore__get_purchase_code() ){
			return;
		} ?>

		<div class="rey-itemManager-buttons">

			<button class="rey-adminBtn --btn-primary __manager-refresh">
				<span><?php echo esc_html__('Refresh list', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'sync']); ?>
				<span class="rey-spinnerIcon"></span>
			</button>

		</div>
		<?php
	}



	public static function actions(){
		return [
			'install' => esc_html__('Install', 'rey-core'),
			'activate' => esc_html__('Activate', 'rey-core'),
			'update' => esc_html__('Update', 'rey-core'),
			'deactivate' => esc_html__('Deactivate', 'rey-core'),
		];
	}

	public function get_button_attributes(){

		$actions = self::actions();

		$status = 'install'; // default

		if( 'inactive' === $this->_item['status'] ){
			$status = 'activate';
		}

		else if( false === $this->_item['status'] ){
			$status = 'install';
		}

		else if( 'active' === $this->_item['status'] ){

			$status = 'deactivate';

			// check for update and get the new version to update to
			if( $new_version = \Rey\Libs\Plugins::get_plugin_update_status($this->_item) ){
				$status = 'update';
				$actions['update'] .= sprintf(' to %s', $new_version);
			}
		}

		return [
			'status' => $status,
			'text' => $actions[$status],
		];
	}

	public function after_item_content(){

		$button_data = $this->get_button_attributes(); ?>

		<div class="rey-itemManager-actions">

			<a class="rey-adminBtn --btn-primary" data-action="<?php echo $button_data['status'] ?>" data-slug="<?php echo esc_attr($this->_item['id']); ?>">
				<span><?php echo $button_data['text'] ?></span>
				<span class="rey-spinnerIcon"></span>
			</a>

			<a href="<?php echo esc_url( $this->_item['help_url'] ) ?>" target="_blank" class="rey-adminBtn --btn-outline __preview">
				<span><?php echo esc_html__('Help', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'external-link']) ?>
			</a>

		</div>
		<?php
	}

	public static function default_keywords(){
		return [
			'essential' => 'Essential',
			'ecommerce' => 'E-commerce',
			'premium'   => 'Premium',
			'misc'      => 'Misc.',
			'utility'   => 'Utility',
			'module'    => 'Rey Module',
		];
	}

	public function prepare_items(){

		$plugins_ob = new \Rey\Libs\Plugins();
		$plugins = $plugins_ob->get_plugins();

		if( empty($plugins) ){
			return;
		}

		$this->items = array_reverse($plugins);

		$def_keywords = self::default_keywords();

		foreach ($this->items as $key => $item) {
			if( isset($item['keywords']) && ! empty($item['keywords']) ){
				foreach ( $item['keywords'] as $kw) {
					$this->keywords[$kw] = isset($def_keywords[$kw]) ? $def_keywords[$kw] : ucfirst($kw);
				}
			}

			// hide some plugins
			if( in_array($item['slug'], ['rey-module-fullscreen-menu'], true) ){
				$this->items[$key]['css_class'] = '--hidden-admin';
			}

		}

	}

	public function prepare_item( $item ){

		if( ! isset($item['url']) ){
			$item['url'] = sprintf('https://wordpress.org/plugins/%s/', $item['slug']);
		}

		$plugin_api = $item['type'] === 'repo' ? self::get_wp_plugins_api($item['slug']) : [];

		// rey plugin
		if( isset($plugin_api['banners']['low']) ){
			$item['image'] = $plugin_api['banners']['low'];
		}

		if( isset($item['desc']) ){
			$item['description'] = $item['desc'];
		}

		$item['version'] = \Rey\Libs\Plugins::get_plugin_version($item['file_path']);

		$item['id'] = $item['slug'];
		$item['title'] = $item['name'];

		if( ! isset($item['help_url']) ){
			$item['help_url'] = $item['url'];
		}

		if( ! empty($item['keywords'])  ){
			$item['categories'] = $item['keywords']; // remap categories
		}

		return $item;
	}

	protected static function get_wp_plugins_api( $slug ) {

		static $api = []; // Cache received responses.

		if ( ! isset( $api[ $slug ] ) ) {

			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$api[ $slug ] = plugins_api( 'plugin_information', ['slug' => $slug, 'fields' => ['sections' => false]] );
		}

		return (array) $api[ $slug ];
	}

	public function get_all_items(){
		return [];
	}

	public function get_default_disabled_items(){
		return [];
	}

}
