<?php
namespace ReyCore\Modules\ScrollToTop;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'scroll-to-top';
	}

	public function get_title(){
		return esc_html__('Scroll to top button', 'rey-core');
	}

	public function get_priority(){
		return 105;
	}

	public function get_icon(){
		return 'scroll-to-top-button';
	}

	public function controls(){


		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'scroll_to_top__enable',
			'label'       => esc_html__( 'Select style', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'style1' => esc_html__( 'Style #1 - Minimal', 'rey-core' ),
				'style2' => esc_html__( 'Style #2 - Box', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'scroll_to_top__text',
			'label'       => esc_html__( 'Button text', 'rey-core' ),
			'default'     => esc_html__('TOP', 'rey-core'),
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: TOP', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'scroll_to_top__color',
			'label'       => esc_html__( 'Button Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--scrolltotop-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'image',
			'settings'    => 'scroll_to_top__custom_icon',
			'label'       => esc_html__( 'Custom Icon', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'save_as' => 'id',
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'scroll_to_top__bottom_position',
			'label'       => esc_html__( 'Distance from bottom', 'rey-core' ) . ' (vh)',
			'default'     => 10,
			'choices'     => [
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--scroll-top-bottom',
					'units'    		=> 'vh',
				],
			],
			'responsive' => true,
		] );


		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'scroll_to_top__entrance_point',
			'label'       => esc_html__( 'Entrance point', 'rey-core' ) . ' (%)',
			'default'     => 0,
			'choices'     => [
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'scroll_to_top__position',
			'label'       => esc_html__( 'Select Position', 'rey-core' ),
			'default'     => 'right',
			'choices'     => [
				'right' => esc_html__( 'Right', 'rey-core' ),
				'left' => esc_html__( 'Left', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'multicheck',
			'settings'    => 'scroll_to_top__hide_devices',
			'label'       => esc_html__( 'Select visibility', 'rey-core' ),
			'default'     => [],
			'choices'     => [
				'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
				'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
				'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'scroll_to_top__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

	}
}
