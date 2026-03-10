<?php
namespace ReyCore\Modules\ProductLoopGs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TemplateLoopSkin extends \ReyCore\WooCommerce\LoopSkins\Skin
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'template';
	}

	public function get_name(){
		return esc_html__('Template', 'rey-core');
	}

	public function get_list_option_key(){
		return esc_html__('- Template (Global Section) -', 'rey-core');
	}

	/**
	 * Adds custom CSS Classes
	 *
	 * @since 1.1.2
	 */
	public function skin_classes()
	{
		$classes = [];

		if ( \ReyCore\WooCommerce\Loop::is_product() ) {
			if( get_theme_mod('loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		return $classes;
	}

	public function add_customizer_options( $control_args, $section ){

		$section->add_control( [
			'type'     => 'select',
			'settings' => 'product_loop_template',
			'label'    => __('Select Template Item', 'rey-core'),
			'help'     => [
				__('Please select the template global section for the product items.', 'rey-core')
			],
			'default'    => '',
			'query_args' => [
				'type'        => 'posts',
				'post_type'   => \ReyCore\Elementor\GlobalSections::POST_TYPE,
				'meta'        => [
					'meta_key'   => 'gs_type',
					'meta_value' => Base::GSTYPE,
				],
				// 'edit_link'   => true,
			],
			'placeholder' => esc_html_x('- Select -', 'Customizer control description', 'rey-core'),
			'css_class' => '--block-label',
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => $this->get_id(),
				],
			],
		] );

	}

}
