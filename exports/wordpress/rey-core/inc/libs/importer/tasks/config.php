<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Config extends TaskBase
{

	private $config = [];
	private $map_data;

	public function get_id(){
		return 'config';
	}

	public function get_status(){
		return esc_html__('Configuring ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['config']) && ($this->config = reycore__clean($data['config'])) ) ){
			return $this->add_error( 'Cannot retrieve config data.' );
		}

		$this->map_data = Base::get_map();

		$config_types = [
			'rey-theme'               => 'theme_settings',
			'options'                 => 'options',
			'nav'                     => 'nav',
			'rey_mega_menus'          => 'mega_menus',
			'pages'                   => 'pages',
			'elements_manager'        => 'elements_manager',
			'elementor-site-settings' => 'elementor_site_settings',
			'elementor-experiments'   => 'elementor_experiments',
		];

		foreach ($this->config as $c_task => $c_data) {

			if( ! isset( $config_types[$c_task] ) ){
				continue;
			}

			$method = 'set__' . $config_types[$c_task];

			if( ! method_exists($this, $method) ){
				continue;
			}

			call_user_func([$this, $method], $c_data);

		}

		do_action('reycore/import/' . $this->get_id(), $data, $this);
	}

	public function set__theme_settings($data)
	{
		foreach( (array) $data as $key => $value )
		{
			$value = Helper::process_paths($value);
			$value = Helper::process_ids($value, $this->map_data);
			update_field( $key, $value, REY_CORE_THEME_NAME);
		}
	}

	public function set__options($data)
	{
		foreach( (array) $data as $key => $value )
		{
			if( ! in_array($key, array_keys(Helper::get_options()), true) ){
				continue;
			}
			update_option($key, $value );
		}
	}

	public function set__nav($data)
	{
		foreach( (array) $data as $menu_slug => $menu )
		{
			$the_menu = get_term_by( 'name', $menu, 'nav_menu' );
			if( $the_menu && $the_menu->term_id ){
				set_theme_mod( 'nav_menu_locations', [
					$menu_slug => $the_menu->term_id,
				] );
			}
		}

	}

	public function set__mega_menus($data)
	{
		if( ! (class_exists('\ReyCore\Modules\MegaMenus\Base') && ! empty( $data )) ) {
			return;
		}

		$mega__menus = [];

		foreach( (array) $data as $value ){
			$menu_tax = get_term_by( 'slug', $value, 'nav_menu' );
			if( isset($menu_tax->term_id) ){
				$mega__menus[] = $menu_tax->term_id;
			}
		}

		if( ! empty( $mega__menus ) ){
			update_option(\ReyCore\Modules\MegaMenus\Base::SUPPORTED_MENUS, $mega__menus);
		}

	}

	public function set__pages($data)
	{
		foreach( (array) $data as $option_key => $value ){
			if( ! in_array($option_key, Helper::get_page_options(), true) ){
				continue;
			}
			if( is_array($value) ) {
				$initial_field = get_option( $option_key );
				foreach ($value as $k => $v) {
					if( isset($this->map_data[ $v ]) && ($mapped_id = $this->map_data[ $v ]) ){
						// set the page id do the sub item
						$initial_field[ $k ] = $mapped_id;
						// update option
						update_option($option_key, $initial_field);
					}
				}
			}
			else {
				if( isset($this->map_data[ $value ]) && ($mapped_id = $this->map_data[ $value ]) ){
					update_option($option_key, $mapped_id);
				}
			}
		}
	}


	public function set__elements_manager($data){
		if( class_exists('\ReyCore\Elementor\WidgetsManager') ){
			update_option( \ReyCore\Elementor\WidgetsManager::DB_OPTION, $data );
		}
	}

	public function set__elementor_experiments($data){

		if( ! ( $data && class_exists('\Elementor\Plugin') ) ){
			return;
		}

		// At some point `get_feature_option_key` was private but changed to public.
		$can = ($experiments = get_class_methods( \Elementor\Plugin::$instance->experiments )) && in_array('get_feature_option_key', $experiments, true);

		foreach ($data as $key => $value) {
			if( $can ){
				$option_key = \Elementor\Plugin::$instance->experiments->get_feature_option_key($key);
				update_option($option_key, $value);
			}
		}
	}

	public function set__elementor_site_settings($data){

		if( ! ( $data && class_exists('\Elementor\Plugin') ) ){
			return;
		}

		// Elementor Kit (3.8+)
		if( class_exists('\Elementor\App\Modules\ImportExport\Runners\Import\Site_Settings') ){

			$site_settings = new \Elementor\App\Modules\ImportExport\Runners\Import\Site_Settings();

			if( ! (isset($data['settings']) && ! empty($data['settings'])) ){
				return;
			}

			$site_settings->import( [
				'site_settings' => $data,
			], [] );
		}

		// Pre 3.8
		else if ( class_exists('\Elementor\Core\App\Modules\ImportExport\Directories\Root') ){

			$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
			$old_settings = $kit->get_meta( \Elementor\Core\Settings\Page\Manager::META_KEY );
			if ( ! $old_settings ) {
				$old_settings = [];
			}
			$new_settings = $data['settings'];
			if ( ! empty( $old_settings['custom_colors'] ) ) {
				$new_settings['custom_colors'] = array_merge( $old_settings['custom_colors'], $new_settings['custom_colors'] );
			}
			if ( ! empty( $old_settings['custom_typography'] ) ) {
				$new_settings['custom_typography'] = array_merge( $old_settings['custom_typography'], $new_settings['custom_typography'] );
			}
			$new_settings = array_replace_recursive( $old_settings, $new_settings );
			\Elementor\Plugin::$instance->kits_manager->create_new_kit( '', $new_settings );
		}

	}

}
