<?php
namespace ReyCore\Modules\ProductSizeGuides;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer
{
	public function __construct()
	{
		add_action( 'reycore/customizer/section=woo-product-page-summary-components/marker=before_meta', [$this, 'add_pdp_controls'], 20);
	}

	public function add_pdp_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Size Guides', 'rey-core' ),
		]);

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'pdp_size_guides',
				'label'       => esc_html__( 'Default Size Guide', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					''  => esc_attr__( '- None -', 'rey-core' )
				],
				'query_args' => [
					'type' => 'posts',
					'post_type' => Base::POST_TYPE,
				],
				'placeholder' => esc_html__('Select guide', 'rey-core'),
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'pdp_size_guides_button_position',
				'label'       => esc_html__( 'Button Position', 'rey-core' ),
				'default'     => 'before_atc',
				'choices'     => [
					''                 => esc_html__('- Disabled -', 'rey-core'),
					'before_atc'       => esc_html__('Before Add to cart button', 'rey-core'),
					'after_atc'        => esc_html__('After Add to cart button', 'rey-core'),
					'inline_atc'       => esc_html__('Inline with Add to cart button', 'rey-core'),
					'inline_attribute' => esc_html__('Inline with Attributes', 'rey-core'),
				],
				'help' => [
					_x('Select the placement of the button. You can disable the button in favor of using the guide content with <code>[rey_size_guide]</code> shortcode somewhere in the page or <code>[rey_size_guide_button]</code> for the button itself.', 'Customizer control', 'rey-core'),
					'clickable' => true,
				]
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'pdp_size_guides_button_attribute',
				'label'       => esc_html__( 'Select Attribute', 'rey-core' ),
				'default'     => '',
				'active_callback' => [
					[
						'setting'  => 'pdp_size_guides_button_position',
						'operator' => '==',
						'value'    => 'inline_attribute',
					],
					[
						'setting'  => 'pdp_size_guides_button_position',
						'operator' => '!=',
						'value'    => '',
					],
				],
				'choices'      => [
					'' => esc_html__('- Select -', 'rey-core')
				],
				'ajax_choices' => 'get_woo_attributes_list',
			] );

			$section->add_control( [
				'type'        => 'text',
				'settings'    => 'pdp_size_guides_button_text',
				'label'       => esc_html__( 'Button Text', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('ex: Size Guide', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'pdp_size_guides_button_position',
						'operator' => '!=',
						'value'    => '',
					],
				],
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'pdp_size_guides_button_style',
				'label'       => esc_html__( 'Button Style', 'rey-core' ),
				'default'     => 'line-active',
				'choices'     => [
					'primary'     => 'Primary',
					'secondary'   => 'Secondary',
					'line-active' => 'Underlined',
					'line'        => 'Underlined on hover',
					'simple'      => 'Simple',
					'minimal'     => 'Minimal',
				],
				'active_callback' => [
					[
						'setting'  => 'pdp_size_guides_button_position',
						'operator' => '!=',
						'value'    => '',
					],
				],
			] );

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'pdp_size_guides_button_icon',
				'label'       => esc_html__( 'Show icon', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					[
						'setting'  => 'pdp_size_guides_button_position',
						'operator' => '!=',
						'value'    => '',
					],
				],
			] );

		$section->end_controls_accordion();
	}

}
