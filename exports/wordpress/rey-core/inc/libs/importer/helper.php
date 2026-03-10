<?php
namespace ReyCore\Libs\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {

	public static function fs(){

		static $fs;

		if( is_null($fs) ){
			$fs = reycore__wp_filesystem();
		}

		return $fs;
	}

	public static function process_paths( $data = '' ){

		if( '' === $data ){
			return $data;
		}

		if( is_array($data) ){
			return array_map( function($item) {
				return self::process_paths($item);
			}, $data );
		}

		if( ! is_string($data) ){
			return $data;
		}

		$map = [
			'__R_PLUGINS_URL'   => WP_PLUGIN_URL,
			'__R_UPLOAD_URL'    => REY_CORE_IMPORT_UPLOAD_PATH,
			'__R_SITE_URL'      => REY_CORE_IMPORT_SITE_URL,
		];

		if( REY_CORE_IMPORT_MULTISITE_URL ){
			$map['__R_MSITE_URL'] = REY_CORE_IMPORT_MULTISITE_URL;
		}

		foreach ($map as $placeholder => $value) {

			$data = str_replace( $placeholder . '_T__', trim(wp_json_encode($value), '"' ), $data );
			$data = str_replace( $placeholder . '_C__', $value, $data);

		}

		return $data;
	}

	public static function process_ids( $data = '', $map = [] ){

		if( empty($map) ){
			return $data;
		}

		if( is_array($data) ){
			return array_map( function($item) use ($map) {
				$item = self::process_paths($item, $map);
				$item = self::process_ids($item, $map);
				return $item;
			}, $data );
		}

		if( ! is_string($data) ){
			return $data;
		}

		return str_replace(array_keys($map), $map, $data);
	}

	public static function get_customizer_options(){
		return [
			'woocommerce_demo_store',
			'woocommerce_demo_store_notice',
			'woocommerce_shop_page_display',
			'woocommerce_category_archive_display',
			'woocommerce_default_catalog_orderby',
			'woocommerce_catalog_columns',
			'woocommerce_catalog_rows',
			'woocommerce_single_image_width',
			'woocommerce_thumbnail_image_width',
			'woocommerce_thumbnail_cropping',
			'woocommerce_thumbnail_cropping_custom_width',
			'woocommerce_thumbnail_cropping_custom_height',
			'woocommerce_checkout_company_field',
			'woocommerce_checkout_address_2_field',
			'woocommerce_checkout_phone_field',
			'woocommerce_checkout_highlight_required_fields',
			'woocommerce_checkout_terms_and_conditions_checkbox_text',
			'woocommerce_checkout_privacy_policy_text',
		];
	}

	public static function get_page_options(){
		return [
			// woo
			'woocommerce_shop_page_id',
			'woocommerce_cart_page_id',
			'woocommerce_checkout_page_id',
			'woocommerce_myaccount_page_id',
			'woocommerce_terms_page_id',
			'wp_page_for_privacy_policy',
			// wp
			'page_on_front',
			'page_for_posts',
		];
	}

	public static function get_options(){
		return [
			// Rey
			'reycore-disabled-modules'                          => get_option('reycore-disabled-modules'),
			// Elementor
			'elementor_disable_color_schemes'                   => 'yes',
			'elementor_disable_typography_schemes'              => 'yes',
			'elementor_container_width'                         => '',
			'elementor_space_between_widgets'                   => '',
			'elementor_stretched_section_container'             => '',
			'elementor_page_title_selector'                     => '',
			'elementor_viewport_lg'                             => '',
			'elementor_viewport_md'                             => '',
			// woocommerce
			'woocommerce_enable_myaccount_registration'         => get_option('woocommerce_enable_myaccount_registration'),
			'woocommerce_enable_signup_and_login_from_checkout' => get_option('woocommerce_enable_signup_and_login_from_checkout'),
			'woocommerce_enable_checkout_login_reminder'        => get_option('woocommerce_enable_checkout_login_reminder'),
			// wp
			'show_on_front' => get_option('show_on_front'),
		];
	}

}
