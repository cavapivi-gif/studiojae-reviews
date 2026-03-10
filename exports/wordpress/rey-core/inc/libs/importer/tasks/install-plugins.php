<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class InstallPlugins extends TaskBase
{

	public function get_id(){
		return 'install-plugins';
	}

	public function get_status(){
		return esc_html__('Setting-up plugins ...', 'rey-core');
	}

	public function get_offset_status( $rey_plugins, $slug ){

		$status = '';

		if( isset($rey_plugins[$slug], $rey_plugins[$slug]['status']) ){
			if( 'inactive' === $rey_plugins[$slug]['status'] ){
				$status = sprintf(esc_html__('Activating %s .. ', 'rey-core'), $rey_plugins[$slug]['name']);
			}
			else if( false === $rey_plugins[$slug]['status'] ){
				$status = sprintf(esc_html__('Installing %s .. ', 'rey-core'), $rey_plugins[$slug]['name']);
			}
		}

		return $status;
	}

	public function run(){

		$plugins = [];

		if( ! class_exists('\Rey\Libs\Plugins') ){
			return $this->add_error( 'Please update Rey theme.' );
		}

		$plugins_ob = new \Rey\Libs\Plugins();
		$rey_plugins = $plugins_ob->get_plugins();

		if( isset($this->importer->task_data['task'], $this->importer->task_data['offset']) &&
			$this->get_id() === $this->importer->task_data['task'] ){
			$plugins = reycore__clean($this->importer->task_data['offset']);
		}
		else {
			if( ($config = get_transient('rey_demo_config')) && isset($config['plugins']) && ($plugins = $config['plugins']) ){
				$this->set_offset($this->get_id(), $plugins, $this->get_offset_status($rey_plugins, $plugins[0]) );
				return;
			}
		}

		foreach ($plugins as $i => $slug) {

			if( ! isset($rey_plugins[$slug]) ){
				continue;
			}

			do_action('reycore/import_demo/before_plugins', $slug);

			$this->before_plugins();

			// Install & Activate the plugin
			if ( false === $rey_plugins[$slug]['status'] ) {
				if( $plugins_ob->install_plugin($slug) ){
					unset($plugins[$i]);
					break;
				}
				else {
					$this->add_notice(sprintf('Cannot install "%s".', $slug));
				}
			}

			// activate_plugin
			// Activate the plugin if already installed.
			if ( 'inactive' === $rey_plugins[$slug]['status'] ) {
				if( ! is_wp_error( $plugins_ob->activate_plugin_by_slug($slug) ) ){
					unset($plugins[$i]);
					break;
				}
				else {
					$this->add_notice(sprintf('Cannot activate "%s".', $slug));
				}
			}
			else {
				unset($plugins[$i]);
				break;
			}
		}

		$this->after_plugins();

		do_action('reycore/import_demo/after_plugins');

		if( ! empty($plugins) ){
			$offset_status = $this->get_offset_status($rey_plugins, array_values($plugins)[0]);
			$this->set_offset($this->get_id(), $plugins, $offset_status);
		}

	}

	public function before_plugins(){
		add_filter('woocommerce_create_pages', '__return_empty_array'); // prevent woo from creating pages. Likely added by Rey
		add_filter('woocommerce_enable_setup_wizard', '__return_false');

		// Fixes a Fatal error:
		// PHP Fatal error:  Call to a member function set_rating_counts() on boolean in woocommerce/includes/class-wc-comments.php on line 200
		if( class_exists('\WC_Comments') ){
			remove_action( 'wp_update_comment_count', 'WC_Comments::clear_transients' );
		}
	}

	public function after_plugins(){
		delete_transient( '_wc_activation_redirect' );
		delete_transient( 'elementor_activation_redirect' );
		delete_transient( '_revslider_welcome_screen_activation_redirect' );
	}

}
