<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Modules\ProductLoopGs\Base;

class WooBase extends \Elementor\Widget_Base {

	public $_product_id;
	public $_product;
	public $_product_type;
	public $_settings = [];

	public static $_element;

	public function render_template(){}
	public function get_name(){}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		if( ! $this->should_render() ){
			return;
		}

		$this->inner_before_render();

			if( ($this->_product = wc_get_product()) && is_object($this->_product) ){
				$this->_product_type = $this->_product->get_type();
				$this->_settings = $this->get_settings_for_display();
				$this->render_template();
			}

		$this->inner_after_render();

	}

	public function get_categories() {
		return [ Base::WIDGET_CAT ];
	}

	public static function is_element(){
		return strpos(self::$_element->get_name(), Base::WIDGET_PREFIX) !== false;
	}

	public static function is_preview(){
		$is[] = isset($_REQUEST['action'], $_REQUEST['post']) && 'elementor' === $_REQUEST['action'];
		$is[] = isset($_REQUEST['action'], $_REQUEST['actions']) && 'elementor_ajax' === $_REQUEST['action'];
		$is[] = isset($_REQUEST[\ReyCore\Elementor\GlobalSections::POST_TYPE], $_REQUEST['preview_id']) && get_post_type() === \ReyCore\Elementor\GlobalSections::POST_TYPE;
		return in_array(true, $is, true);
	}

	/**
	 * Determine if the element should render based on the Product Object.
	 *
	 * @return bool
	 */
	public function should_render(){

		self::$_element = $this;

		if( ! self::is_element() ){
			return;
		}

		// just run in preview mode
		if( self::is_preview() ){
			return true;
		}

		return true;
	}

	/**
	 * Run & load code before rendering the element
	 *
	 * @param object $element
	 * @return void
	 */
	public function inner_before_render()
	{
		// set a custom CSS class
		// self::$_element->add_render_attribute('_wrapper', 'class', ['rey-pLoop-el']);
		// setup product ID in preview mode
		$this->preview__elements_before();
	}

	public function inner_after_render()
	{
		// load styles
		reycore_assets()->add_styles(['rey-wc-general', 'rey-wc-general-deferred', 'rey-wc-loop', Base::ASSET_HANDLE]);
		// setup product ID in preview mode
		$this->preview__elements_after();
	}

	/**
	 * Logic to setup data before rendering the element, in preview mode
	 *
	 * @param object $element
	 * @return void
	 */
	public function preview__elements_before(){

		if( self::is_preview() ){

			$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( get_the_ID() );
			$doc_settings = $document->get_data( 'settings' );

			// check for the custom Product ID
			// but be sure to check inside the existing Product Object
			if( isset($doc_settings['grid_product_id']) && $pid = $doc_settings['grid_product_id'] ){
				$this->_product_id = $pid;
			}
			else {
				// get the latest product's ID
				if( $preview_id = Base::get_default_preview_id() ){
					$this->_product_id = $preview_id;
				}
			}

		}

		if( is_null($this->_product_id) ){
			return;
		}

		// setup POST data
		$GLOBALS['post'] = get_post( $this->_product_id ); // WPCS: override ok.
		setup_postdata( $GLOBALS['post'] );

	}

	public function preview__elements_after(){

		if( is_null($this->_product_id) ) {
			return;
		}

		wp_reset_postdata();

		$this->_product_id = null;

	}

	protected function register_controls(){

		$this->element_register_controls();

	}

	public function add_wrapper_css_class(){
		$this->add_control(
			'_el_css_class',
			[
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => 'rey-pLoop-el',
				'prefix_class' => '',
			]
		);
	}

	protected function element_register_controls(){}


	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}

	public function get_icon_class(){

		$class = 'general';
		$name = $this->get_name();

		if( strpos($name, 'reycore-woo-') === 0 ){
			$class = str_replace('reycore-woo-', '', $name);
		}

		return sprintf('rey-editor-icons --%s', $class);
	}

	public function maybe_show_in_panel(){
		return (bool) reycore__get_purchase_code();
	}

}
