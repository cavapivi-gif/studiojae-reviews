<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Customizer extends TaskBase
{

	private $map_data;

	public function get_id(){
		return 'customizer';
	}

	public function get_status(){
		return esc_html__('Process Customizer ...', 'rey-core');
	}

	public function run(){

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return $this->add_notice( 'No permissions for importing Customizer data.' );
		}

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['customizer']) && ($customizer = $data['customizer']) ) ){
			return $this->add_error( 'Cannot retrieve Customizer data.' );
		}

		if( $this->maybe_skip('customizer') ){
			return $this->add_notice( 'Skipping customizer as requested.' );
		}

		$this->map_data = Base::get_map();

		// If wp_css is set then import it.
		if( function_exists( 'wp_update_custom_css_post' ) && isset( $customizer['wp_css'] ) && '' !== $customizer['wp_css'] ) {
			wp_update_custom_css_post( $customizer['wp_css'] );
		}

		if( isset( $customizer['options'] ) && is_array($customizer['options']) ) {
			foreach ( $customizer['options'] as $key => $value ) {
				if( ! in_array($key, Helper::get_customizer_options(), true) ){
					continue;
				}
				update_option($key, $value);
			}
		}

		global $wp_customize;

		// Call the customize_save action.
		if( $wp_customize ){
			do_action( 'customize_save', $wp_customize );
		}

		// Loop through the mods.
		foreach ( $customizer['mods'] as $key => $val ) {

			// Call the customize_save_ dynamic action.
			if( $wp_customize ){
				do_action( 'customize_save_' . $key, $wp_customize );
			}

			$processed_val = $this->process_mod($val);

			// Save the mod.
			set_theme_mod( $key, $processed_val );
		}

		// Call the customize_save_after action.
		if( $wp_customize ){
			do_action( 'customize_save_after', $wp_customize );
		}

	}

	public function process_mod($value){

		if( is_array($value) ){
			return array_map( function($item) {
				return $this->process_mod($item);
			}, $value );
		}

		if( isset($this->map_data[ $value ]) && ($mapped_id = $this->map_data[ $value ]) ){
			return $mapped_id;
		}

		$value = Helper::process_paths($value);
		$value = Helper::process_ids($value, $this->map_data);

		return $value;
	}


}
