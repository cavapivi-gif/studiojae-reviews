<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Kit {

	public function __construct(){

		// add_filter('pre_option_elementor_experiment-e_optimized_control_loading', function(){
		// 	return 'inactive';
		// } );

		/**
		 * Temporary fix for unloaded assets (css/js). Rey has its own assets integration separated from the standard queue.
		 * @since 3.0.0
		 */
		add_filter('pre_option_elementor_experiment-e_element_cache', function(){
			return 'inactive';
		} );

		add_filter('pre_option_elementor_element_cache_ttl', function(){
			return 'disable';
		});

		add_action( 'elementor/element/kit/section_settings-layout/before_section_end', [$this, 'kit_layout_settings']);
		add_action( 'elementor/element/kit/section_layout-settings/before_section_end', [$this, 'kit_layout_settings']);
		add_action( 'elementor/kit/register_tabs', [$this, 'kit_buttons'] );
		add_action( 'elementor/element/kit/section_typography/before_section_end', [$this, 'typo_settings']);
	}

	/**
	 * Remove Container width as it directly conflicts with Rey's container settings
	 *
	 * @since 1.6.12
	 */
	public function kit_layout_settings( $stack ){

		/**
		 * Temporary fix for https://github.com/elementor/elementor/issues/27773
		 * @since 3.0.0
		 */
		$stack->update_responsive_control(
			'container_width',
			[
				'selectors' => [
					'.e-con' => '--container-max-width-x: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$stack->remove_responsive_control( 'container_width' );

	}

	public function kit_buttons($kit){
		$kit->register_tab( 'theme-style-buttons', KitButtons::class );
	}

	public function typo_settings( $stack ){

		if( ! apply_filters('reycore/elementor/kit/typo_selectors', true) ){
			return;
		}

		$map = [
			'body_color'        => '--body-color',
			'link_normal_color' => '--link-color',
			'link_hover_color'  => '--link-color-hover',
		];

		foreach ($map as $control_key => $css_variable) {

			$stack->update_control(
				$control_key,
				[
					'selectors' => [
						'{{WRAPPER}}' => sprintf('%s:{{VALUE}};', $css_variable)
					],
					'render_type' => 'ui',
				]
			);
		}

		$stack->update_responsive_control(
			'paragraph_spacing',
			[
				'selectors' => [
					// '{{WRAPPER}}' => sprintf('%s:{{VALUE}};', $css_variable)
					'{{WRAPPER}}' => '--paragraph-spacing:{{SIZE}}{{UNIT}};'
				],
				'render_type' => 'ui',
			]
		);

	}
}
