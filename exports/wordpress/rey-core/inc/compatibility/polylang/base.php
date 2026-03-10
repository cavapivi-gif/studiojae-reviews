<?php
namespace ReyCore\Compatibility\Polylang;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action( 'rey/header/row', [$this, 'header'], 60);
		add_action( 'reycore/elementor/header_language/render', [$this, 'header'], 10);
		add_action( 'rey/mobile_nav/footer', [$this, 'mobile'], 10);
		add_action( 'reycore/ajax/register_actions', [$this, 'ajax_compatibility'], 0);
		add_filter( 'reycore/woocommerce/variations/terms_transients', [$this, 'variation_transients'] );
		add_filter( 'reycore/elementor/gs_id', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/theme_mod/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
		add_filter( 'reycore/is_multilanguage', [$this, 'is_multilanguage'] );
		add_filter( 'rey/main_script_params', [$this, 'script_params'] );
		add_filter( 'reycore/acf/get_field_from_elementor/args', [$this, 'acf_get_field_from_elementor_args'] );
		add_filter( 'reycore/acf/get_field_from_elementor/data', [$this, 'acf_get_field_from_elementor_data'], 10, 4 );
		add_filter( 'reycore/elementor/global_sections/name', [$this, 'get_posts_title_gs'], 10, 2 );
	}

	/**
	 * Get PolyLang data
	 *
	 * @since 1.0.0
	 **/
	function data(){

		if( function_exists('pll_current_language') && function_exists('pll_the_languages') ):
			$languages = [];
			$translations = pll_the_languages([
				'raw' => 1,
				'hide_if_empty' => 0
			]);

			$flag = false;

			if( !empty($translations) ){

				foreach ($translations as $key => $language) {
					$languages[$key] = [
						'code' => $key,
						'flag' => $language['flag'],
						'name' => $language['name'],
						'active' => $language['current_lang'],
						'url' => $language['url']
					];

					if( $language['current_lang'] ){
						$flag = $language['flag'];
					}
				}

				return [
					'current' => pll_current_language(),
					'current_flag' => $flag,
					'languages' => $languages,
					'type' => 'polylang'
				];
			}
		endif;

		return false;
	}

	/**
	 * Add language switcher for PolyLang into Header
	 *
	 * @since 1.0.0
	 **/
	function header($options = []){
		if($data = $this->data()) {
			echo reycore__language_switcher_markup($data, $options);
		}
	}

	/**
	 * Add language switcher for PolyLang into Mobile menu panel
	 *
	 * @since 1.0.0
	 **/
	function mobile(){
		if($data = $this->data()) {
			echo reycore__language_switcher_markup_mobile($data);
		}
	}

	function variation_transients( $transients ){

		if( function_exists('pll_current_language') ){
			foreach ($transients as $name => $transient) {
				$transients[$name] = sprintf('%s_%s', $transient, pll_current_language());
			}
		}

		return $transients;
	}

	public function maybe_translate_id( $data, $post_type = '' ){

		if( ! function_exists('pll_get_post') ){
			return $data;
		}

		if( ! apply_filters('reycore/multilanguage/translate_ids', true) ){
			return $data;
		}

		$current_lang = pll_current_language();

		if( isset($_REQUEST['lang']) ){
			$current_lang = reycore__clean($_REQUEST['lang']);
		}

		if ( is_array( $data ) ) {
			$translated_ids = [];
			foreach ($data as $post_id) {
				if( !empty($post_type) && in_array($post_type, ['product_cat'], true) ){
					$translated_ids[] = pll_get_term($post_id);
				}
				else if( ($tid = pll_get_post($post_id, $current_lang)) && 'publish' === get_post_status( $tid ) ){
					$translated_ids[] = $tid;
				}
			}
			if( !empty($translated_ids) ){
				return $translated_ids;
			}
		} else {
			if( !empty($post_type) && in_array($post_type, ['product_cat'], true) ){
				return pll_get_term($data );
			}
			else if( ($translated_id = pll_get_post($data, $current_lang)) && 'publish' === get_post_status( $translated_id ) ){
				return $translated_id;
			}
		}

		return $data;
	}

	public function is_multilanguage() {
		return (function_exists('pll_current_language') && ($curr = pll_current_language('slug'))) ? $curr : false;
	}

	public function script_params( $params ){
		$params['lang'] = pll_current_language();
		return $params;
	}

	public function ajax_compatibility(){

		global $polylang;

    	if ( isset( $polylang ) && $polylang && isset( $_REQUEST['lang'] ) && ($lang = reycore__clean( $_REQUEST['lang'] )) ) {
			$polylang->curlang = $polylang->model->get_language( $lang );
		}

	}

	public function acf_get_field_from_elementor_args( $args ){
		$current_lang = pll_current_language();
		$args['initial_key'] = $args['key'];
		$args['lang'] = $current_lang;
		$args['key'] = str_replace('{lang}', $current_lang, $args['key']);
		return $args;
	}

	public function acf_get_field_from_elementor_data( $data, $post_id, $fname, $args ){

		// if not empty, just return back
		if( ! empty($data) ){
			return $data;
		}

		if( ! (isset($args['lang']) && isset($args['initial_key'])) ){
			return $data;
		}

		$default_lang = pll_default_language();

		if( $default_lang === $args['lang'] ){
			return $data;
		}

		$field_name = str_replace('{lang}', $default_lang, $args['initial_key']);

		return get_field( $field_name, $post_id );
	}

	function get_posts_title_gs($title, $post_id) {
		return $title . ' (' . pll_get_post_language( $post_id ) . ')';
	}

}
