<?php
namespace ReyCore\Compatibility\Translatepress;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter('trp_force_search', [$this, 'force_search'], 10);
		add_action('reycore/woocommerce/search/before_get_data', [$this, 'ajax_search_before_get_data']);
		add_filter( 'reycore/is_multilanguage', [$this, 'is_multilanguage'] );

		add_action( 'rey/header/row', [$this, 'header'], 60);
		add_action( 'reycore/elementor/header_language/render', [$this, 'header'], 10);
		add_action( 'rey/mobile_nav/footer', [$this, 'mobile'], 10);

	}

	function ajax_search_before_get_data(){

		// force translated title
		add_filter( 'the_title', function($title){
			$trp = \TRP_Translate_Press::get_trp_instance();
			$translation_render = $trp->get_component( 'translation_render' );
			return $translation_render->translate_page($title);
		}, 20);

	}

	function force_search( $status ){

		if( isset( $_REQUEST[ \ReyCore\Ajax::ACTION_KEY ] ) && $_REQUEST[ \ReyCore\Ajax::ACTION_KEY ] === 'ajax_search' ){
			return true;
		}

		return $status;
	}

	public function is_multilanguage() {
		return true;
	}

	/**
	 * Add language switcher for TranslatePress into Header
	 *
	 * @since 1.0.0
	 **/
	function header($options = []){

		if($data = $this->data()) {
			echo reycore__language_switcher_markup($data, $options);
		}
	}

	/**
	 * Add language switcher for TranslatePress into Mobile menu panel
	 *
	 * @since 1.0.0
	 **/
	function mobile(){
		if($data = $this->data()) {
			echo reycore__language_switcher_markup_mobile($data);
		}
	}

	/**
	 * Get TranslatePress data
	 *
	 * @since 1.0.0
	 **/
	function data(){

		if( ! class_exists('\TRP_Translate_Press') ){
			return false;
		}

		global $TRP_LANGUAGE;

		$trp = \TRP_Translate_Press::get_trp_instance();
		$trp_languages = $trp->get_component( 'languages' );

		$settings_ob = new \TRP_Settings();
		$settings = $settings_ob->get_settings();

        if ( current_user_can( apply_filters( 'trp_translating_capability', 'manage_options' ) ) ) {
            $languages_to_display = $settings['translation-languages'];
        }else{
            $languages_to_display = $settings['publish-languages'];
        }

		$published_languages = $trp_languages->get_language_names( $languages_to_display );

		if( empty($published_languages) ){
			return false;
		}

		$url_converter = $trp->get_component( 'url_converter' );
		$wp_languages = $trp_languages->get_wp_languages();

		foreach ($published_languages as $language_code => $language_name) {

			// Path to folder with flags images
			$flags_path = apply_filters( 'trp_flags_path', TRP_PLUGIN_URL .'assets/images/flags/', $language_code );

			// File name for specific flag
			$flag_file_name = apply_filters( 'trp_flag_file_name', $language_code .'.png', $language_code );

			$code = $language_code;

			if( ! empty($wp_languages[$language_code]) && ! empty($wp_languages[$language_code]['iso']) && is_array($wp_languages[$language_code]['iso']) ){
				$code = array_shift($wp_languages[$language_code]['iso']);
			}

			$languages[$language_code] = [
				'code'   => $code,
				'flag'   => esc_url( $flags_path . $flag_file_name ),
				'name'   => $language_name,
				'active' => $language_code == $TRP_LANGUAGE,
				'url'    => esc_url( $url_converter->get_url_for_language( $language_code ) )
			];

		}

		return [
			'current'      => $languages[$TRP_LANGUAGE]['code'],
			'current_flag' => $languages[$TRP_LANGUAGE]['flag'],
			'languages'    => $languages,
			'type'         => 'translatepress'
		];

	}

}
