<?php
/**
 * Customizer Controls Init.
 *
 * @package     Kirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.17
 */

/**
 * Controls.
 */
class Kirki_Controls {

	private static $__enqueued = null;

	/**
	 * An array of templates to load.
	 *
	 * @access private
	 * @since 3.0.17
	 * @var array
	 */
	private $templates = array(
		'code',
		'color',
		'generic',
		'image',
		'number',
		'radio',
		'select',
		'textarea',
	);

	/**
	 * Path to controls views.
	 *
	 * @access private
	 * @since 3.0.17
	 * @var string
	 */
	private $views_path;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0.17
	 */
	public function __construct() {
		if ( ! $this->views_path ) {
			$this->views_path = wp_normalize_path( dirname( KIRKI_PLUGIN_FILE ) . '/controls/views/' );
		}
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'underscore_templates' ) );
	}

	/**
	 * Adds underscore.js templates to the footer.
	 *
	 * @access public
	 * @since 3.0.17
	 */
	public function underscore_templates() {
		foreach ( $this->templates as $template ) {
			if ( file_exists( $this->views_path . $template . '.php' ) ) {
				echo '<script type="text/html" id="tmpl-kirki-input-' . esc_attr( $template ) . '">';
				include $this->views_path . $template . '.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
				echo '</script>';
			}
		}
	}


	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 */
	public static function enqueue() {

		if( self::$__enqueued ){
			return;
		}

		self::$__enqueued = true;

		// Build the suffix for the script.
		$suffix  = '';
		$suffix .= ( ! defined( 'SCRIPT_DEBUG' ) || true !== SCRIPT_DEBUG ) ? '.min' : '';

		// The Kirki plugin URL.
		$kirki_url = trailingslashit( Kirki::$url );

		// Enqueue ColorPicker.
		wp_enqueue_script( 'wp-color-picker-alpha', trailingslashit( Kirki::$url ) . 'assets/vendor/wp-color-picker-alpha/wp-color-picker-alpha.js', array( 'wp-color-picker', 'wp-i18n' ), KIRKI_VERSION, true );
		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue selectWoo.
		wp_enqueue_script( 'selectWoo', trailingslashit( Kirki::$url ) . 'assets/vendor/selectWoo/js/selectWoo.full.js', array( 'jquery' ), '1.0.1', true );
		wp_enqueue_style( 'selectWoo', trailingslashit( Kirki::$url ) . 'assets/vendor/selectWoo/css/selectWoo.css', array(), '1.0.1' );
		wp_enqueue_style( 'kirki-selectWoo', trailingslashit( Kirki::$url ) . 'assets/vendor/selectWoo/kirki.css', array(), KIRKI_VERSION );

		// Enqueue the script.
		wp_enqueue_script(
			'kirki-script',
			"{$kirki_url}controls/js/script.js",
			array(
				'jquery',
				'customize-base',
				'customize-controls',
				'wp-color-picker-alpha',
				'selectWoo',
				'jquery-ui-button',
				'jquery-ui-datepicker',
			),
			KIRKI_VERSION,
			false
		);

		$localize = [
			'isScriptDebug'        => ( defined( 'KIRKI_DEBUG' ) && true === KIRKI_DEBUG ),
			'noFileSelected'       => esc_html__( 'No File Selected', 'kirki' ),
			'remove'               => esc_html__( 'Remove', 'kirki' ),
			'default'              => esc_html__( 'Default', 'kirki' ),
			'selectFile'           => esc_html__( 'Select File', 'kirki' ),
			'standardFonts'        => esc_html__( 'Standard Fonts', 'kirki' ),
			'googleFonts'          => esc_html__( 'Google Fonts', 'kirki' ),
			'defaultCSSValues'     => esc_html__( 'CSS Defaults', 'kirki' ),
			'defaultBrowserFamily' => esc_html__( 'Default font', 'kirki' ),
		];

		$localize['standardFontList'] = Kirki_Fonts::get_standard_fonts();
		$localize['googleFontList'] = Kirki_Fonts_Google::get_cached_google_fonts_list();
		$localize['extraFontListGroups'] = apply_filters('reycore/customizer/extra_font_list_groups', []);
		$localize['fontsVariants'] = [ '100', '200', '300', '400', '500', '600', '700', '800', '900', 'regular', 'bold'];
		$localize['allFontsVariants'] = Kirki_Fonts::get_all_variants();

		wp_localize_script( 'kirki-script', 'kirkiL10n', $localize);

		$suffix = str_replace( '.min', '', $suffix );

		// Enqueue the style.
		wp_enqueue_style(
			'kirki-styles',
			"{$kirki_url}controls/css/styles{$suffix}.css",
			array(),
			KIRKI_VERSION
		);
	}
}
