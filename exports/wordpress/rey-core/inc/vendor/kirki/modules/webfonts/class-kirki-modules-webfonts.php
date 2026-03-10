<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Kirki_Modules_Webfonts {

	const CONFIG_ID = 'rey_core_kirki';

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * The class constructor
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function __construct() {

		include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
		include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts-google.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude

		add_action( 'init', array( $this, 'init' ), 20 );

	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init other objects depending on the method we'll be using.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function init() {
		if ( is_customize_preview() ) {
			new Kirki_Modules_Webfonts_Async();
		}
		new Kirki_Modules_Webfonts_Embed();
	}

}
