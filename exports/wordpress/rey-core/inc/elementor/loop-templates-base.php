<?php
namespace ReyCore\Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class LoopTemplatesBase
{

	protected $_widget_tid = null;

	public function __construct()
	{
		if( ! ($this->get_key() || $this->get_title()) ){
			return;
		}

		add_filter( 'reycore/woocommerce/loop/control_skins', [$this, 'add_skin_choice']);
		add_action( 'reycore/customizer/control=loop_skin', [ $this, 'customizer_add_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_alignment', [ $this, 'customizer_update_options' ], 10, 2 );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/woocommerce/loop/before_grid', [ $this, 'add_custom_content'] );
		add_action( 'reycore/woocommerce/loop/after_grid', [ $this, 'remove_custom_content'] );
		add_action( 'reycore/elementor/products/after_loop_skin', [$this, 'add_template_control_in_widgets']);
		add_action( 'elementor/widget/before_render_content', [$this, 'before_render_widget_editor'], 10);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render_widget'], 10);
		add_action( 'elementor/frontend/widget/after_render', [$this, 'after_render_widget'], 10);

	}

	abstract public function get_key();

	abstract public function get_title();

	abstract public function register_actions( $ajax_manager );

	abstract public function get_option_name();

	abstract public function get_option_title();

	abstract public function get_option_help();

	abstract public function get_ajax_method();

	abstract public function get_css_class();

	abstract public function get_query_pt_args();

	abstract public function get_content($template_id);

	/**
	 * Retrieve the saved template ID
	 *
	 * @return null|int
	 */
	public function get_template(){

		if( ! $this->get_option_name() ){
			return;
		}

		if( $this->get_key() !== get_theme_mod('loop_skin') ){
			return;
		}

		if( ! ($template_id = get_theme_mod( $this->get_option_name()) ) ){
			return;
		}

		return absint($template_id);
	}


	public function render( $product ){

		if( ! ($template_id = $this->get_template()) ){
			return;
		}

		if( 'publish' !== get_post_status($template_id) ){
			return;
		}

		$this->print_dynamic_css( $product->get_id(), $template_id );

		$this->get_content($template_id);

	}

	protected function print_dynamic_css( $post_id, $post_id_for_data ) {

		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id_for_data );

		if ( ! $document ) {
			return;
		}

		\Elementor\Plugin::$instance->documents->switch_to_document( $document );

		$css_file = \ReyCore\Elementor\Loop_Dynamic_CSS::create( $post_id, $post_id_for_data );
		$post_css = $css_file->get_content();

		if ( empty( $post_css ) ) {
			return;
		}

		$css = '';
		$css = str_replace( '.elementor-' . $post_id, '.' . $this->get_css_class() . $post_id, $post_css );
		$css = sprintf( '<style id="%s">%s</style>', 'loop-dynamic-' . $post_id_for_data, $css );

		echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		\Elementor\Plugin::$instance->documents->restore_document();

	}

	/**
	 * Hook custom product content.
	 * Prevent leaking the widget option outside of it
	 *
	 * @return void
	 */
	public function add_custom_content(){
		add_action( 'reycore/woocommerce/content_product/custom', [ $this, 'render'] );
	}

	/**
	 * Remove the hook custom product content.
	 * Prevent leaking the widget option outside of it
	 *
	 * @return void
	 */
	public function remove_custom_content(){
		remove_action( 'reycore/woocommerce/content_product/custom', [ $this, 'render'] );
	}

	/**
	 * Add skin choice in Customizer Product Skin list
	 *
	 * @param array $choices
	 * @return array
	 */
	public function add_skin_choice( $choices ){
		$choices[$this->get_key()] = $this->get_title();
		return $choices;
	}

	/**
	 * Adds Customizer control
	 *
	 * @param array $control_args
	 * @param object $section
	 * @return void
	 */
	public function customizer_add_options( $control_args, $section ){

		if( ! ($this->get_option_name() || $this->get_ajax_method()) ){
			return;
		}

		$section->add_control( [
			'type'        => 'select',
			'settings'    => $this->get_option_name(),
			'label'       => $this->get_option_title(),
			'default'     => '',
			'choices'     => [],
			'ajax_choices' => $this->get_ajax_method(),
			'css_class'    => '--block-label',
			'help' => [
				$this->get_option_help(),
				'clickable' => true,
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => $this->get_key(),
				],
			],
		] );

	}


	public function customizer_update_options($control_args, $section){

		$current_control = $section->get_control( $control_args['settings'] );
		$current_control['active_callback'][] = [
			'setting'  => 'loop_skin',
			'operator' => '!=',
			'value'    => $this->get_key(),
		];
		$section->update_control( $current_control );

	}


	/**
	 * Adds control into Elementor's Product Grid & Product Archive elements
	 *
	 * @param object $stack
	 * @return void
	 */
	public function add_template_control_in_widgets( $stack ){

		$quary_args = $this->get_query_pt_args();

		$stack->add_control( $this->get_option_name(), [
			'type'     => 'rey-query',
			'label_block' => true,
			'label'    => __('Select Loop Template', 'rey-core'),
			'default'    => '',
			'query_args' => [
				'type'        => 'posts',
				'post_type'   => $quary_args['post_type'],
				'meta'        => [
					'meta_key'   => $quary_args['meta_key'],
					'meta_value' => $quary_args['meta_value'],
				],
				'edit_link'   => true,
			],
			'description' => $this->get_option_help(),
			'condition' => [
				'loop_skin' => $this->get_key(),
				'_skin' => ['', 'carousel'],
			],
		] );

	}

	/**
	 * Handle edit mode in Elementor
	 *
	 * @param object $element
	 * @return void
	 */
	public function before_render_widget_editor($element) {
		if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
			$this->before_render_widget($element);
		}
	}

	/**
	 * Assign the template ID before rendering the widget
	 *
	 * @param object $element
	 * @return void
	 */
	public function before_render_widget($element) {

		if( 'reycore-product-grid' !== $element->get_unique_name() ){
			return;
		}

		if( ! is_null($this->_widget_tid) ){
			$this->_widget_tid = null;
		}

		$widget_settings = $element->get_settings();

		// Check if Skin is set on template
		if( ! (isset($widget_settings['loop_skin']) && $this->get_key() === $widget_settings['loop_skin']) ){
			return;
		}

		// check for the template ID
		if( ! isset($widget_settings[$this->get_option_name()]) ){
			return;
		}

		// it might exist, but if not, relies on the Customizer global setting
		if( ! ($template_id = absint($widget_settings[$this->get_option_name()])) ){
			// if Customizer has look_skin on something else
			// but has a template ID set, should not inherit it.
			if ( ($mods = get_theme_mods()) && isset( $mods[ 'loop_skin' ] ) && $this->get_key() !== $mods[ 'loop_skin' ] ) {
				$this->_widget_tid = 0;
			}
			// just stop if that's not the casea
			else {
				return;
			}
		}

		$this->_widget_tid = $template_id;

		add_filter( 'theme_mod_' . $this->get_option_name(), [$this, 'set_widget_template'] );

	}

	/**
	 * Cancel the hooks to prevent leaking
	 *
	 * @param object $element
	 * @return void
	 */
	public function after_render_widget($element) {

		if( 'reycore-product-grid' !== $element->get_unique_name() ){
			return;
		}

		if( is_null($this->_widget_tid) ){
			return;
		}

		remove_filter( 'theme_mod_' . $this->get_option_name(), [$this, 'set_widget_template'] );

		$this->_widget_tid = null;

	}

	public function set_widget_template( $mod ){

		if( is_null($this->_widget_tid) ){
			return $mod;
		}

		return $this->_widget_tid;

		// // Check if Skin is set on template
		// if( isset($this->_widget_settings['loop_skin']) && $this->get_key() === $this->_widget_settings['loop_skin'] ){

		// 	// check for the template ID
		// 	if( isset($this->_widget_settings[$this->get_option_name()]) ){

		// 		if( $template_id = absint($this->_widget_settings[$this->get_option_name()]) ){
		// 			return $template_id;
		// 		}


		// 	}
		// }

		// return $mod;
	}
}
