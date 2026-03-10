<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Webfonts {

	const PRIMARY_VAR = 'var(--primary-ff)';
	const SECONDARY_VAR = 'var(--secondary-ff)';

	const PRIMARY_NICE_NAME = 'Rey Primary';
	const SECONDARY_NICE_NAME = 'Rey Secondary';

	const HINTS_TRANSIENT = 'rey_webfonts_hints';

	/**
	 * Holds the font types
	 *
	 * @var array
	 */
	public $font_types = [];

	/**
	 * Extra symbol to differentiate google custom font is lists.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var string
	 */
	const SYMBOL = '__';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init(){

		add_filter( 'upload_mimes', [$this, 'force_custom_fonts_extensions']);
		add_action( 'wp_head', [ $this, 'elementor_pro_custom_fonts' ] );
		add_action( 'admin_head', [ $this, 'add_revolution_fonts_css' ] );

		add_filter( 'reycore/customizer/extra_font_list_groups', [ $this, 'customizer_extra_font_list' ] );
		add_filter( 'elementor/fonts/groups', [ $this, 'elementor_group' ] );
		add_filter( 'elementor/fonts/additional_fonts', [ $this, 'add_elementor_fonts' ] );
		add_filter( 'revslider_data_get_font_familys', [ $this, 'add_revolution_fonts' ] );

		add_filter( 'rey/css_styles', [ $this, 'add_css' ], 100 );

		add_filter( 'wp_resource_hints', [ $this, 'resource_hints' ], 10, 2 );

		add_action( 'acf/save_post', [ $this, 'clear_fonts_transient_on_theme_settings_save' ], 20);
		add_action( 'rey/flush_cache_after_updates', [$this, 'clear_fonts_transient_after_updates'], 20);

		foreach ( [
			'Google',
			'Adobe',
			'Custom',
		] as $type) {
			$class_name = '\\ReyCore\\Webfonts' . $type;
			$this->register_font_type( new $class_name() );
		}

		do_action('reycore/webfonts/init', $this);
	}

	/**
	 * Register font type
	 *
	 * @param object $font_type
	 * @return void
	 */
	public function register_font_type( $font_type ){

		if( ! ($font_type_id = $font_type->get_id()) ){
			return;
		}

		$this->font_types[ $font_type_id ] = $font_type;

	}

	/**
	 * Allow uploading font files
	 *
	 * @param array $mimes
	 * @return array
	 */
	public function force_custom_fonts_extensions($mimes) {
		$mimes['otf'] = 'application/x-font-otf';
		$mimes['woff'] = 'application/x-font-woff';
		$mimes['woff2'] = 'application/x-font-woff';
		$mimes['ttf'] = 'application/x-font-ttf';
		$mimes['svg'] = 'image/svg+xml';
		$mimes['eot'] = 'application/vnd.ms-fontobject';
		return $mimes;
	}

	public static function get_default_subsets($include_all = false){

		$default = [
			'latin-ext'    => 'Latin Extended',
			'cyrillic'     => 'Cyrillic',
			'cyrillic-ext' => 'Cyrillic Extended',
			'greek'        => 'Greek',
			'greek-ext'    => 'Greek Extended',
			'hebrew'       => 'Hebrew',
			'malayalam'    => 'Malayalam',
			'vietnamese'   => 'Vietnamese',
			'devanagari'   => 'Devanagari',
			'khmer'        => 'Khmer',
			'arabic'       => 'Arabic',
			'bengali'      => 'Bengali',
			'gujarati'     => 'Gujarati',
			'tamil'        => 'Tamil',
			'telugu'       => 'Telugu',
			'thai'         => 'Thai',
		];

		if( $include_all ){
			$default['all'] = '- All Subsets -';
		}

		return $default;
	}

	public static function get_google_fonts_subsets(){

		$subsets = [];

		$pre_subsets = [
			'ru_RU' => 'cyrillic',
			'bg_BG' => 'cyrillic',
			'he_IL' => 'hebrew',
			'el'    => 'greek',
			'vi'    => 'vietnamese',
			'uk'    => 'cyrillic',
			'cs_CZ' => 'latin-ext',
			'ro_RO' => 'latin-ext',
			'pl_PL' => 'latin-ext',
			'hr_HR' => 'latin-ext',
			'hu_HU' => 'latin-ext',
			'sk_SK' => 'latin-ext',
			'tr_TR' => 'latin-ext',
			'lt_LT' => 'latin-ext',
		];

		$locale = get_locale();

		if ( isset( $pre_subsets[ $locale ] ) ) {
			$subsets[] = $pre_subsets[ $locale ];
		}

		$setting_subsets = (array) reycore__acf_get_field('font_optimisations_subsets', REY_CORE_THEME_NAME);

		if( ! empty($setting_subsets) ){
			if( in_array('all', $setting_subsets, true) ){
				$subsets = array_keys(self::get_default_subsets());
			}
			else {
				$subsets = $setting_subsets;
			}
		}

		return $subsets;
	}

	public static function get_default_weights( $include_all = false ){

		$default = [
			100 => '100 (Ultra-Light)',
			'100i' => '100 Italic (Ultra-Light)',
			200 => '200 (Light)',
			'200i' => '200 Italic (Light)',
			300 => '300 (Book)',
			'300i' => '300 Italic (Book)',
			400 => '400 (Normal)',
			'400i' => '400 Italic (Normal)',
			500 => '500 (Medium)',
			'500i' => '500 Italic (Medium)',
			600 => '600 (Semi-Bold)',
			'600i' => '600 Italic (Semi-Bold)',
			700 => '700 (Bold)',
			'700i' => '700 Italic (Bold)',
			800 => '800 (Extra-Bold)',
			'800i' => '800 Italic (Extra-Bold)',
			900 => '900 (Ultra-Bold)',
			'900i' => '900 Italic (Ultra-Bold)',
		];

		if( $include_all ){
			$default['all'] = '- All Weights -';
		}

		return $default;
	}

	public static function get_google_fonts_weights( $weights = [] ){

		$setting_weights = (array) reycore__acf_get_field('font_optimisations_weights', REY_CORE_THEME_NAME);

		if( ! empty($setting_weights) ){
			if( in_array('all', $setting_weights, true) ){
				$weights = array_keys(self::get_default_weights());
			}
			else {
				$weights = array_unique(array_merge($setting_weights, $weights));
			}
		}

		return $weights;
	}

	public static function get_typography_vars(){

		static $fonts;

		if( is_null($fonts) ){

			$fonts = [];

			foreach ([
				'typography_primary' => self::PRIMARY_NICE_NAME,
				'typography_secondary' => self::SECONDARY_NICE_NAME,
			] as $control_key => $nice_name ) {
				if( $mod = get_theme_mod($control_key, []) ){
					$mod['nice-name'] = $nice_name;
					$fonts[$control_key] = $mod;
				}
			}

		}

		return $fonts;
	}

	public function elementor_pro_custom_fonts_css(){

		/**
		 * Elementor PRO custom fonts
		 * create primary/secondary instances
		 */
		if( class_exists('\ElementorPro\Modules\AssetsManager\AssetTypes\Fonts_Manager') ){

			$elementor_custom_fonts = get_option( \ElementorPro\Modules\AssetsManager\AssetTypes\Fonts_Manager::FONTS_OPTION_NAME, false );

			if( ! empty($elementor_custom_fonts) && is_array($elementor_custom_fonts) ){

				$css = '';

				foreach (self::get_typography_vars() as $vars ) {

					if( empty($vars['font-family']) ){
						continue;
					}

					if( ! isset($elementor_custom_fonts[ $vars['font-family'] ]) ){
						continue;
					}

					$font_contents = $elementor_custom_fonts[ $vars['font-family'] ];

					if( ! isset($font_contents['font_face']) ){
						continue;
					}

					$FR_contents = str_replace(
						"'{$vars['font-family']}';",
						"'{$vars['nice-name']}';",
						stripslashes($font_contents['font_face']),
						$FR_counter
					);

					if( $FR_counter ){
						$css .= $FR_contents;
					}
				}

				return $css;
			}
		}

		return '';
	}


	/**
	 * Almost the same structure as \Elementor\Plugin::instance()->frontend->get_stable_google_fonts_url
	 * @param string $font
	 * @return string
	 */
	public static function elementor_global_fonts__get_google_fonts_url( $font ): string {

		$fonts_url = sprintf( 'https://fonts.googleapis.com/css?family=%s:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&display=swap', str_replace( ' ', '+', $font ) );

		$subsets = [
			'ru_RU' => 'cyrillic',
			'bg_BG' => 'cyrillic',
			'he_IL' => 'hebrew',
			'el' => 'greek',
			'vi' => 'vietnamese',
			'uk' => 'cyrillic',
			'cs_CZ' => 'latin-ext',
			'ro_RO' => 'latin-ext',
			'pl_PL' => 'latin-ext',
			'hr_HR' => 'latin-ext',
			'hu_HU' => 'latin-ext',
			'sk_SK' => 'latin-ext',
			'tr_TR' => 'latin-ext',
			'lt_LT' => 'latin-ext',
		];

		/**
		 * Google font subsets.
		 *
		 * Filters the list of Google font subsets from which locale will be enqueued in frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param array $subsets A list of font subsets.
		 */
		$subsets = apply_filters( 'elementor/frontend/google_font_subsets', $subsets );

		$locale = get_locale();

		if ( isset( $subsets[ $locale ] ) ) {
			$fonts_url .= '&subset=' . $subsets[ $locale ];
		}

		return $fonts_url;
	}

	public function elementor_global_fonts_to_rey_typo(){

		static $elementor_global_fonts;

		$css = '';

		foreach (self::get_typography_vars() as $vars ) {

			if( empty($vars['font-family']) ){
				continue;
			}

			// optimize
			if( is_null($elementor_global_fonts) ){

				$elementor_global_fonts = [];

				$system_typography = self::elementor_get_kit_settings( 'system_typography' );
				$custom_typography = self::elementor_get_kit_settings( 'custom_typography' );

				foreach (array_merge($system_typography, $custom_typography)  as $value) {
					$key = sprintf('var(--e-global-typography-%s-font-family)', $value['_id']);
					if( isset($value['typography_font_family'])) {
						$elementor_global_fonts[$key] = $value['typography_font_family'];
					}
				}

			}

			if( ! (isset($elementor_global_fonts[ $vars['font-family'] ]) && ($font_family = $elementor_global_fonts[ $vars['font-family'] ])) ){
				continue;
			}

			$c_transient_name = 'rey_fonts_elementor_global_fonts_' . md5($font_family);

			// check if transient exists
			if( false === ( $ff_css = get_transient($c_transient_name) ) ){

				$url = self::elementor_global_fonts__get_google_fonts_url($font_family);

				$font_contents = self::get_remote_url_contents($url);
				$ff_css = '';

				if( $font_contents ){

					$FR_contents = str_replace(
						"'{$font_family}';",
						"'{$vars['nice-name']}';",
						stripslashes($font_contents),
						$FR_counter
					);

					if( $FR_counter ){
						$ff_css = $FR_contents;
					}

					set_transient($c_transient_name, $ff_css, MONTH_IN_SECONDS);
				}
			}

			$css .= $ff_css;
		}

		return $css;
	}

	/**
	 * Appends fonts CSS into Rey's cached CSS
	 *
	 * @param array $css
	 * @return array
	 */
	public function add_css( $css = [] ) {

		if( ! is_array($css) ){
			return $css;
		}

		do_action( 'qm/debug', 'Webfonts:: generate CSS' );

		if( defined('REY_DEBUG_LOG_CUSTOMIZER_CSS') && REY_DEBUG_LOG_CUSTOMIZER_CSS ){
			error_log(var_export( 'Webfonts:: generate CSS', true));
		}

		foreach ($this->font_types as $font_type) {
			$css[] = $font_type->get_css();
		}

		$css[] = $this->elementor_pro_custom_fonts_css();
		$css[] = $this->elementor_global_fonts_to_rey_typo();
		$css[] = $this->system_font_alias();

		return $css;
	}

	/**
	 * Add system font alias CSS
	 *
	 * @return string
	 */
	public function system_font_alias() {

		$css = '';
		$safe_fonts = \Kirki_Fonts::get_standard_fonts_by_stack();

		foreach (self::get_typography_vars() as $vars ) {

			if( empty($vars['font-family']) ){
				continue;
			}

			if ( isset($safe_fonts[$vars['font-family']]) ) {
				$local = $safe_fonts[$vars['font-family']]['local'];
				$css .= sprintf(
					"@font-face {
						font-family: '%s';
						src: local('%s');
					}",
					$vars['nice-name'],
					$local
				);
			}
		}

		return $css;
	}

	/**
	 * Get the CSS as string
	 *
	 * @return string
	 */
	public function get_string_css(){
		$custom_fonts_css = $this->add_css();
		$custom_fonts_css = implode(' ', $custom_fonts_css);
		return str_replace(array("\r\n", "\r", "\n"), '', $custom_fonts_css); // remove newlines
	}

	/**
	 * Get list of fonts, to be inserted inside Customizer typography font-families list
	 *
	 * @param array $choices
	 * @return void
	 */
	public function customizer_extra_font_list( $choices ){

		$children = $variants = [];

		$pff = $sff = '';

		// if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) ){
		// 	$pff = "( {$primary_typo['font-family']} )";
		// }

		$children[] = [
			'id' => self::PRIMARY_VAR,
			'text' => sprintf(esc_html__('Primary Font %s', 'rey-core'), str_replace(self::SYMBOL, '', $pff)),
		];

		// if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) ){
		// 	$sff = sprintf( "( %s )", $secondary_typo['font-family'] ? $secondary_typo['font-family'] : esc_html__('not selected', 'rey-core') );
		// }

		$children[] = [
			'id' => self::SECONDARY_VAR,
			'text' => sprintf(esc_html__('Secondary Font %s', 'rey-core'), str_replace(self::SYMBOL, '', $sff)),
		];

		$variants[self::PRIMARY_VAR] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];
		$variants[self::SECONDARY_VAR] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];

		foreach ($this->font_types as $font_type) {

			if( ! ($fonts = $font_type->get_list()) ) {
				continue;
			}

			foreach( $fonts as $font ){

				$font_name = $font['font_name'];

				if( isset($font['css_handle']) && $css_handle = $font['css_handle'] ){
					$font_name = $css_handle;
				}

				// add custom symbol
				if( isset($font['type']) && $font['type'] == 'google' ){
					$font_name = $font['font_name'];
				}

				$children[] = [
					'id' => $font_name,
					'text' => $font['font_name']
				];

				// $variants[$font_name] = $font['font_variants'];
			}
		}

		if( ! empty( $children ) ){
			$choices['custom'] = [
				'text'     => sprintf(esc_html__('%s Fonts', 'rey-core'), reycore__get_props('theme_title') ),
				'children' => $children,
			];
		}

		return array_merge_recursive(
			$choices,
			self::elementor_pro_custom_fonts_to_customizer_choices(),
			self::elementor_kit_global_fonts_to_customizer_choices()
		);

	}

	/**
	 * Get Elementor PRO Custom fonts list, into Customizer
	 *
	 * @return void
	 */
	public static function elementor_pro_custom_fonts_to_customizer_choices(){

		$choices = [];

		if( ! class_exists('\ElementorPro\Modules\AssetsManager\Classes\Font_Base') ){
			return $choices;
		}

		$elementor_pro_fonts = [];

		if( defined('\ElementorPro\Modules\AssetsManager\Classes\Font_Base::FONTS_OPTION_NAME') ){
			$fonts = get_option( \ElementorPro\Modules\AssetsManager\Classes\Font_Base::FONTS_OPTION_NAME, [] );
			$elementor_pro_fonts = $fonts;
		}

		if( empty( $elementor_pro_fonts ) ){
			return $choices;
		}

		$choices['elementor_pro']['text'] = esc_html__('Elementor Pro Fonts', 'rey-core');

		foreach ($elementor_pro_fonts as $font => $font_data) {
			$choices['elementor_pro']['children'][] = [
				'id' => $font,
				'text' => $font
			];
		}

		return $choices;
	}

	public static function elementor_get_kit_settings( $type ){

		if( ! (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->kits_manager) ) ){
			return [];
		}

		$kits_manager = \Elementor\Plugin::$instance->kits_manager;

		// sometimes returns an error
		if( ! method_exists($kits_manager, 'get_current_settings') ){
			return [];
		}

		return array_filter( (array) $kits_manager->get_current_settings( $type ) );
	}

	/**
	 * Add Elementor's Kit, global fonts list, into Customizer's typograhy
	 * font families list
	 *
	 * @return array
	 */
	public static function elementor_kit_global_fonts_to_customizer_choices(){

		$fonts = [];

		$system_typography = self::elementor_get_kit_settings( 'system_typography' );

		foreach ($system_typography as $value) {
			$id = sprintf('var(--e-global-typography-%s-font-family)', $value['_id']);
			$system_typography_title = $value['title'];
			if( $value['typography_font_family'] ){
				$system_typography_title .= sprintf(' (%s)', $value['typography_font_family'] );
			}
			$fonts[ $id ] = $system_typography_title;
		}

		$custom_typography = self::elementor_get_kit_settings( 'custom_typography' );

		foreach ($custom_typography as $value) {
			$id = sprintf('var(--e-global-typography-%s-font-family)', $value['_id']);
			$custom_typography_title = $value['title'];
			if( $value['typography_font_family'] ){
				$custom_typography_title .= sprintf(' (%s)', $value['typography_font_family'] );
			}
			$fonts[ $id ] = $custom_typography_title;
		}

		$kit_fonts = [];

		if( !empty($fonts) ){

			$kit_fonts['elementor_global_fonts']['text'] = esc_html__('Elementor Global Fonts', 'rey-core');

			foreach ($fonts as $font => $font_data) {
				$kit_fonts['elementor_global_fonts']['children'][] = [
					'id' => $font,
					'text' => $font_data
				];
				// $kit_fonts['fonts']['variants'][$font] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900'];
			}
		}

		if( ! empty( $kit_fonts) && is_array($kit_fonts) ){
			return $kit_fonts;
		}

		return [];
	}

	/**
	 * Add Custom Font group to elementor font list.
	 *
	 * Group name "Rey Fonts" is added as the first element in the array.
	 *
	 * @since  1.0.0
	 * @param  Array $font_groups default font groups in elementor.
	 * @return Array              Modified font groups with newly added font group.
	 */
	public function elementor_group( $font_groups ) {
		$new_group[ 'rey_font_group' ] = sprintf(esc_html__('%s Fonts', 'rey-core'), reycore__get_props('theme_title') );
		$font_groups                   = $new_group + $font_groups;

		return $font_groups;
	}

	/**
	 * Add Custom Fonts to the Elementor Page builder's font param.
	 *
	 * @since  1.0.0
	 */
	public function add_elementor_fonts( $fonts ) {

		if( ! reycore__elementor_edit_mode() ){
			return $fonts;
		}

		// Add Arial Black to websafe
		$fonts [ 'Arial Black' ] = 'system';

		// Primary
		if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) && ! empty($primary_typo['font-family']) ){
			$fonts[ self::PRIMARY_NICE_NAME ] = 'rey_font_group';
		}

		// Secondary
		if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) && ! empty($secondary_typo['font-family']) ){
			$fonts[ self::SECONDARY_NICE_NAME ] = 'rey_font_group';
		}

		foreach ($this->font_types as $font_type) {

			if( ! ($font_list = $font_type->get_list()) ) {
				continue;
			}

			foreach( $font_list as $font ){

				$font_name = $font['font_name'];

				if( isset($font['css_handle']) && $css_handle = $font['css_handle'] ){
					$font_name = $css_handle;
				}

				if( isset($font['type']) && $font['type'] == 'google' ){
					// add custom symbol
					$font_name = $font['font_name'];
				}

				$fonts[ $font_name ] = 'rey_font_group';

			}
		}

		return $fonts;
	}

	/**
	 * Add Rey's Fonts to Revolution Slider lists.
	 *
	 * @since  1.0.0
	 */
	public function add_revolution_fonts( $fonts ) {

		foreach ($this->font_types as $font_type) {

			if( ! ($font_list = $font_type->get_list()) ) {
				continue;
			}

			foreach( $font_list as $font ){

				$font_to_add = [
					'type' => $font['type'],
					'version' => '',
					'variants' => $font['font_variants'],
					'category' => REY_CORE_THEME_NAME,
					'subsets' => ! empty($font['font_subsets']) ? $font['font_subsets'] : [],
				];

				if( isset($font['type']) ){

					$font_name = $font['font_name'];

					if( isset($font['css_handle']) && $css_handle = $font['css_handle'] ){
						$font_name = $css_handle;
					}

					if( $font['type'] == 'google' ){
						// add custom symbol
						$font_to_add['label'] = $font_name;
					}
					elseif( $font['type'] == 'adobe' ){
						$font_to_add['label'] = $font_name;
					}
					elseif( $font['type'] == 'custom' ){
						$font_to_add['label'] = $font_name;
					}
				}

				array_unshift($fonts, $font_to_add );
			}
		}

		foreach ([
			'rey_secondary' => self::SECONDARY_NICE_NAME,
			'rey_primary' => self::PRIMARY_NICE_NAME,
		] as $key => $value) {

			array_unshift($fonts, [
				'label' => $value,
				'variants' => [ '100', '200', '300', '400', '500', '600', '700', '800', '900'],
				'category' => REY_CORE_THEME_NAME,
				'type' => 'custom',
				'version' => '',
			]);
		}

		return $fonts;
	}

	/**
	 * Add preconnect for Google Fonts.
	 *
	 * @access public
	 * @param array  $urls           URLs to print for resource hints.
	 * @param string $relation_type  The relation type the URLs are printed.
	 * @return array $urls           URLs to print for resource hints.
	 */
	public function resource_hints( $urls, $relation_type ) {

		if ( 'preconnect' !== $relation_type ) {
			return $urls;
		}

		if( is_admin() ){
			return $urls;
		}

		if( false !== ($stored = get_transient(self::HINTS_TRANSIENT)) ){
			return array_merge($stored, $urls);
		}

		$pre_urls = [];

		foreach ($this->font_types as $font_type) {
			foreach ($font_type->preconnect_urls() as $preconnect_urls) {
				$pre_urls[] = $preconnect_urls;
			}
		}

		if( ! empty($pre_urls) ){
			set_transient(self::HINTS_TRANSIENT, $pre_urls, MONTH_IN_SECONDS);
		}

		return array_merge($pre_urls, $urls);
	}

	/**
	 * Adds compatibility with Elementor PRO's custom fonts
	 *
	 * @since 1.0.3
	 */
	public function elementor_pro_custom_fonts(){

		if( ! (
			class_exists('\ElementorPro\Modules\AssetsManager\Classes\Font_Base') &&
			defined('\ElementorPro\Modules\AssetsManager\Classes\Font_Base::FONTS_OPTION_NAME')
		) ){
			return;
		}

		$elpro_fonts = get_option( \ElementorPro\Modules\AssetsManager\Classes\Font_Base::FONTS_OPTION_NAME, [] );
		$print_style = '';

		foreach ($elpro_fonts as $key => $font) {
			if( isset($font['font_face']) ){
				$print_style .= $font['font_face'];
			}
		}

		if( $print_style ){
			printf('<style>%s</style>', $print_style);
		}

	}

	/**
	 * Add fonts CSS into Revolution Slider Editor
	 *
	 * @since 1.0.0
	 */
	public function add_revolution_fonts_css() {

		if ( ! (($screen = get_current_screen()) && 'toplevel_page_revslider' === $screen->id) ) {
			return;
		}

		printf('<style type="text/css">%s</style>', $this->get_string_css());
	}

	/**
	 * Gets the remote URL contents.
	 *
	 * @since 1.0.0
	 * @param string $url  The URL we want to get.
	 * @param array  $args An array of arguments for the wp_remote_retrieve_body() function.
	 * @return string The contents of the remote URL.
	 */
	public static function get_remote_url_contents( $url ) {

		$args = [
			'headers' => [
				/**
				 * Set user-agent to firefox so that we get woff files.
				 * If we want woff2, use this instead: 'Mozilla/5.0 (X11; Linux i686; rv:64.0) Gecko/20100101 Firefox/64.0'
				 */
				'user-agent' => 'Mozilla/5.0 (X11; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0',
			],
		];

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$html = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $html ) ) {
			return '';
		}

		return $html;
	}

	/**
	 * Flush transients
	 */
	public function clear_transients() {
		foreach ($this->font_types as $font_type) {
			foreach ($font_type->get_transients() as $transient_name) {
				delete_transient($transient_name);
			}
		}
	}

	/**
	 * Refresh fonts cache on save options (Theme Settings)
	 *
	 * @since 1.0.0
	 */
	public function clear_fonts_transient_on_theme_settings_save( $post_id ) {
		if ($post_id === REY_CORE_THEME_NAME) {
			$this->clear_transients();
			delete_transient(self::HINTS_TRANSIENT);
			Helper::clean_db_transient( 'rey_local_fonts_contents_' );
		}
	}

	/**
	 * Flush transients after Core update
	 *
	 * @return void
	 */
	public function clear_fonts_transient_after_updates() {
		$this->clear_transients();
	}

	/**
	 * @deprecated
	 */
	public function get_google_fonts_list(){return [];}

	/**
	 * @deprecated
	 */
	public function enqueue_css(){return [];}

	/**
	 * @deprecated
	 */
	public function set_fonts($a){}

	/**
	 * @deprecated
	 */
	public static function kirki_font_choices( $option = false ) {}

}
