<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Accordion
{

    /**
	 * Accordion Overrides and customizations
	 *
	 * @since 1.0.0
	 */

	function __construct(){
		add_action( 'elementor/element/accordion/section_title/before_section_end', [$this, 'settings'], 10);
		add_action( 'elementor/element/toggle/section_toggle/before_section_end', [$this, 'settings'], 10);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function settings( $element )
	{

		$element->start_injection( [
			'of' => 'selected_active_icon',
		] );

			$element->add_control(
				'rey_start_closed',
				[
					'label' => esc_html__( 'First is open/closed?', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => 'rey-accClosed-',
					'render_type' => 'template',
				]
			);

		$element->end_injection();
	}

	function before_render($element){

		if( ! in_array($element->get_unique_name(), ['accordion', 'toggle'], true) ){
			return;
		}

		$settings = $element->get_settings();

		if( $settings['rey_start_closed'] !== '' ){
			reycore_assets()->add_scripts('reycore-elementor-elem-accordion');
		}

	}
}
