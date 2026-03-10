<?php
namespace ReyCore\Compatibility\Wpml;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	/**
	 * WPML Plugin Compatibility
	 *
	 * @since 1.0.0
	 */

	public function __construct()
	{
		add_action( 'rey/header/row', [$this, 'header'], 60);
		add_action( 'reycore/elementor/header_language/render', [$this, 'header'], 10);
		add_action( 'rey/mobile_nav/footer', [$this, 'mobile'], 10);
		add_filter( 'reycore/woocommerce/variations/terms_transients', [$this, 'variation_transients'] );
		add_filter( 'reycore/elementor/gs_id', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/theme_mod/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/multilanguage/original_id', [$this, 'get_original_id'] );
		add_filter( 'wcml_multi_currency_ajax_actions', [$this, 'multi_currency_ajax_actions'] );
		add_filter( 'wcml_load_multi_currency_in_ajax', [$this, 'multi_currency_ajax_actions_load'] );
		add_filter( 'reycore/woocommerce/cache_discounts', [$this, 'disable_discounts_badge_caching'] );
		add_filter( 'reycore/is_multilanguage', [$this, 'is_multilanguage'] );
	}

	/**
	 * Get WPML data
	 *
	 * @since 1.0.0
	 **/
	function data(){

		if( ! defined('ICL_LANGUAGE_CODE') ){
			return false;
		}

		$languages = [];

		$translations = apply_filters( 'wpml_active_languages', NULL, [
			'skip_missing' => 0
		] );

		if( !empty($translations) ){
			foreach ($translations as $key => $language) {
				$languages[$key] = [
					'code' => $key,
					'flag' => $language['country_flag_url'],
					'name' => $language['native_name'],
					'active' => $language['active'],
					'url' => $language['url']
				];
				if( $language['active'] ){
					$flag = $language['country_flag_url'];
				}
			}
			return [
				'current' => ICL_LANGUAGE_CODE,
				'current_flag' => $flag,
				'languages' => $languages,
				'type' => 'wpml'
			];
		}

	}

	/**
	 * Add language switcher for WPML into Header
	 *
	 * @since 1.0.0
	 **/
	function header($options = []){
		if($data = $this->data()) {
			echo reycore__language_switcher_markup($data, $options);
		}
	}

	/**
	 * Add language switcher for WPML into Mobile menu panel
	 *
	 * @since 1.0.0
	 **/
	function mobile(){
		if($data = $this->data()) {
			echo reycore__language_switcher_markup_mobile($data);
		}
	}

	function variation_transients( $transients ){

		foreach ($transients as $name => $transient) {
			$transients[$name] = sprintf('%s_%s', $transient, defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '');
		}

		return $transients;
	}

	public function get_original_id( $id ) {
		return apply_filters( 'wpml_original_element_id', '', $id);
	}

	/**
	 * See if there is a translated page available for the global sections IDs.
	 *
	 * @since 1.6.3
	 * @see    https://wpml.org/documentation/support/creating-multilingual-wordpress-themes/language-dependent-ids/#2
	 */
	public function maybe_translate_id( $data, $post_type = '' ) {

		if( ! apply_filters('reycore/multilanguage/translate_ids', true) ){
			return $data;
		}

		if ( is_array( $data ) ) {
			$translated_ids = [];
			foreach ($data as $post_id) {

				if( ! is_numeric($post_id) && in_array($post_type, ['product_cat'], true) ){
					$term = get_term_by('slug', $post_id, $post_type );
					$post_id = is_object($term) && $term->term_id ? $term->term_id : 0;
				}

				if( $tid = apply_filters( 'wpml_object_id', $post_id, $post_type, true ) ){
					$translated_ids[] = $tid;
				}
			}
			if( !empty($translated_ids) ){
				return $translated_ids;
			}
		} else {

			if( ! is_numeric($data) && in_array($post_type, ['product_cat'], true) ){
				$term = get_term_by('slug', $data, $post_type );
				$data = is_object($term) && $term->term_id ? $term->term_id : 0;
			}

			if( $translated_id = apply_filters( 'wpml_object_id', $data, $post_type, true ) ){
				return $translated_id;
			}
		}

		return $data;
	}

	function multi_currency_ajax_actions_load( $load ) {

		$actions = [
			'element_lazy',
			'ajax_search',
			'get_quickview_product',
			'after_add_to_cart_popup',
			'load_more_reviews',
			'get_wishlist_data',
		];

		if( isset( $_REQUEST[ \ReyCore\Ajax::ACTION_KEY ] ) && in_array($_REQUEST[ \ReyCore\Ajax::ACTION_KEY ], $actions, true) ){
			return true;
		}

		return $load;
	}

	function multi_currency_ajax_actions( $ajax_actions ) {

		$ajax_actions[] = 'reycore_ajax_add_to_cart';
		$ajax_actions[] = 'rey_update_minicart';
		$ajax_actions[] = 'reycore_load_more_reviews';
		$ajax_actions[] = 'reycore_ajax';

		return $ajax_actions;
	}

	function disable_discounts_badge_caching($status){

		if( class_exists('\WCML_Multi_Currency') ){
			return false;
		}

		return $status;
	}

	public function is_multilanguage() {
		return defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : false;
	}
}
