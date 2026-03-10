<?php
namespace ReyCore\WooCommerce\Tags;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class VariationsLoop
{
	const TRANSIENT_PRODUCT_VARIATIONS = 'rey_product_variations_';

	public function __construct()
	{
		// Clean transients
		add_action( 'customize_save_woocommerce_loop_variation', [$this, 'customizer__clear_transients']);
		add_action( 'customize_save_woocommerce_loop_variation_limit', [$this, 'customizer__clear_transients']);
		add_action( 'woocommerce_delete_product_transients', [$this, 'clear_product_transient']);
		add_action( 'save_post', [$this, 'clear_product_transient'] );
		add_action( 'woocommerce_attribute_updated', [ $this, 'clean_db_transients' ] );
		add_action( 'woocommerce_attribute_deleted', [$this, 'clean_db_transients'] );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );

	}

	function clean_db_transients(){
		return \ReyCore\Helper::clean_db_transient( self::TRANSIENT_PRODUCT_VARIATIONS );
	}

	/**
	 * Clear some transients on Customizer Publish
	 *
	 * @since 1.0.0
	 */
	function customizer__clear_transients( $setting )
	{

		if( ! current_user_can('administrator') ){
			return;
		}

		if( ! method_exists($setting, 'value') ){
			return;
		}

		$setting_data = $setting->id_data();
		// old
		$value = $setting->value();
		// new
		$post_value = $setting->post_value();

		$clear = false;

		if( $value === $post_value ){
			return;
		}

		if( ! isset($setting_data['base']) ){
			return;
		}

		if( 'woocommerce_loop_variation' === $setting_data['base'] ){
			if( $post_value !== 'disabled' ){
				$clear = true;
			}
		}

		elseif( 'woocommerce_loop_variation_limit' === $setting_data['base'] ){
			$clear = true;
		}

		if( $clear ){
			$this->clean_db_transients();
		}
	}

	public static function transient_name( $product_id, $v2 = true ){
		return self::TRANSIENT_PRODUCT_VARIATIONS . ($v2 ? '_v2_' : '') . $product_id;
	}

	function clear_product_transient( $post_id = 0 ){

		if( get_post_type($post_id) !== 'product' ){
			return;
		}

		if ( $post_id === 0 ) {
			$this->clean_db_transients();
		}

		if ( $post_id > 0 ) {
			delete_transient( self::transient_name($post_id) );
			delete_transient( self::transient_name($post_id, false) );
		}
	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'get_woo_attributes_list', [$this, 'ajax__woo_attributes_list'], 1 );
		$ajax_manager->register_ajax_action( 'get_pa_attributes_list', [$this, 'ajax__pa_attributes_list'], 1 );
		$ajax_manager->register_ajax_action( 'get_loop_variation_list', [$this, 'ajax__loop_attributes_list'], 1 );
	}

	public function ajax__woo_attributes_list(){
		return reycore_wc__get_attributes_list();
	}

	public function ajax__pa_attributes_list(){
		return reycore_wc__get_attributes_list(true);
	}

	public function ajax__loop_attributes_list(){
		return apply_filters('reycore/woocommerce/loop/attributes_list', reycore_wc__get_attributes_list());
	}

}
