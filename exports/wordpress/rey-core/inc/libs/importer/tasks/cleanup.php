<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Cleanup extends TaskBase
{
	public function get_id(){
		return 'cleanup';
	}

	public function get_status(){
		return esc_html__('Cleaning up ...', 'rey-core');
	}

	public function run(){

		$demo_config_map = [];

		if( false !== ($config = get_transient('rey_demo_config')) ){
			$demo_config_map = [
				'rey_demo_config' => [
					'id' => $config['demo'],
					'name' => $config['name'],
				]
			];
		}

		if( $demo_config_map ){
			Base::update_map($demo_config_map);
		}
		else {
			return $this->add_notice( 'Couldnt store demo data in mapper.' );
		}

		delete_transient('rey_demo_dld_path');
		delete_transient('rey_demo_data');
		delete_transient('rey_demo_config');

		if( $location = get_transient('rey_demo_temp_path') ){
			Helper::fs()->delete( $location, true );
			delete_transient('rey_demo_temp_path');
		}

		wp_suspend_cache_invalidation( false );

		$this->remove_pages();
		$this->wp_stuff();
		$this->flush_customizer_css();
		// $this->flush_rey_cache();
		$this->regenerate_lookup_tables();
		$this->unset_hiddens_from_admin_menu();
	}

	private function remove_pages(){
		foreach( [
			'hello-world' => 'post',
			'sample-page' => 'page',
		] as $slug => $pt ){
			if( $id = \ReyCore\Helper::get_post_id_by_slug($slug, $pt) ){
				wp_delete_post( $id );
			}
		}
	}

	private function wp_stuff(){
		wp_cache_flush();

		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		flush_rewrite_rules();

	}

	private function flush_customizer_css(){
		do_action('rey/customizer/regenerate_css');
	}

	private function flush_rey_cache(){
		do_action('rey/flush_cache_after_updates');
	}

	private function regenerate_lookup_tables(){

		// regenerate lookup tables
		if( function_exists('wc_update_product_lookup_tables_is_running') && function_exists('wc_update_product_lookup_tables') ){
			if ( ! wc_update_product_lookup_tables_is_running() ) {
				wc_update_product_lookup_tables();
			}
		}

	}

	private function unset_hiddens_from_admin_menu(){

		$user = wp_get_current_user();

		if ( ! $user ) {
			wp_die( -1 );
		}

		$scheme = [
			'metaboxhidden_nav-menus' => [
				'add-product_cat',
				'add-product_tag',
				'woocommerce_endpoints_nav_link'
			],
			'managenav-menuscolumnshidden' => [
				'css-classes',
			],
		];

		foreach ($scheme as $metakey => $cols) {

			$scheme_item = (array) get_user_meta($user->ID, $metakey, true);

			foreach ($scheme_item as $key => $value) {
				if( in_array($value, $cols, true) ){
					unset($scheme_item[$key]);
				}
			}

			update_user_meta( $user->ID, $metakey, $scheme_item );
		}

	}

}
