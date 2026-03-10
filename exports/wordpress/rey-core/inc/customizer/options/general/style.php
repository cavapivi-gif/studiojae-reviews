<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Style extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-style';
	}

	public function get_title(){
		return esc_html__('Site Style', 'rey-core');
	}

	public function get_priority(){
		return 10;
	}

	public function get_icon(){
		return 'site-style';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#style-settings');
	}

	public function controls(){

		$this->add_control_bg_group([
			'settings'    => 'style_bg_image',
			'label'       => __('Background image', 'rey-core'),
			'description' => __('Change the site background.', 'rey-core'),
			'supports' => [
				'color', 'image', 'repeat', 'attachment', 'size', 'position'
			],
			'output_element' => ':root',
			'color' => [
				'default' => '#ffffff',
				'output_property' => '--body-bg-color',
			],
			'image' => [
				'output_property' => '--body-bg-image',
			],
			'repeat' => [
				'output_property' => '--body-bg-repeat',
			],
			'attachment' => [
				'output_property' => '--body-bg-attachment',
			],
			'size' => [
				'output_property' => '--body-bg-size',
			],
			'positionx' => [
				'output_property' => '--body-bg-posx',
			],
			'positiony' => [
				'output_property' => '--body-bg-posy',
			],
		]);


		$this->add_control( array(
			'type'        => 'rey-color',
			'settings'    => 'style_text_color',
			'label'       => __('Text Color', 'rey-core'),
			'help' => [
				__('Change the site text color.', 'rey-core')
			],
			'default'     => '',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--body-color',
				],
				[
					'element'  => reycore__wp_editor_selector(),
					'property' => '--body-color',
					'context'  => [ 'editor' ],
				],
			],
		));

		$this->add_control( array(
			'type'        => 'rey-color',
			'settings'    => 'style_link_color',
			'label'       => __('Link Color', 'rey-core'),
			'help' => [
				__('Change the links color.', 'rey-core')
			],
			'default'     => '',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--link-color',
				],
				[
					'element'  => reycore__wp_editor_selector(),
					'property' => '--link-color',
					'context'  => [ 'editor' ],
				],
			],
		));

		$this->add_control( array(
			'type'        => 'rey-color',
			'settings'    => 'style_link_color_hover',
			'label'       => __('Link Color Hover', 'rey-core'),
			'help' => [
				__('Change the links hover color.', 'rey-core')
			],
			'default'     => '',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--link-color-hover',
				],
				[
					'element'  => reycore__wp_editor_selector(),
					'property' => '--link-color-hover',
					'context'  => [ 'editor' ],
				],
			],
		));

		/* ------------------------------------ ACCENT COLOR ------------------------------------ */

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'style_accent_color',
			'label'       => __('Accent Color', 'rey-core'),
			'help' => [
				__('Change the accent color. Some elements are using this color, such as primary buttons.', 'rey-core')
			],
			'default'     => '#212529',
			'separator' => 'before'
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'style_accent_color_hover',
			'label'       => __('Accent Hover Color', 'rey-core'),
			'help' => [
				__('Change the hover accent color.', 'rey-core')
			],
			'default'     => '',
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'style_accent_color_text',
			'label'       => __('Accent Text Color', 'rey-core'),
			'help' => [
				__('Change the text accent color.', 'rey-core')
			],
			'default'     => '',
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'style_accent_color_text_hover',
			'label'       => __('Accent Text Hover Color', 'rey-core'),
			'help' => [
				__('Change the text hover accent color.', 'rey-core')
			],
			'default'     => '',
		]);

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'style_neutral_hue',
			'label'       => esc_html__( 'Neutral Colors Hue', 'rey-core' ),
			'default'     => 210,
			'choices'     => [
				'min'  => 0,
				'max'  => 360,
				'step' => 1,
			],
			'transport' => 'auto',
			'input_attrs' => [
				'data-control-class' => '--hue-slider',
			],
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--neutral-hue',
				],
			],
			'separator' => 'before'
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'style_neutrals_theme',
			'label'       => esc_html__( 'Neutral Colors Theme', 'rey-core' ),
			'default'     => 'light',
			'choices'     => [
				'light'  => esc_html__('Light', 'rey-core'),
				'dark'  => esc_html__('Dark', 'rey-core'),
			],
		] );

		$this->add_title( esc_html__('Misc. settings', 'rey-core') );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'style_arrow_long',
			'label'       => esc_html__( 'Long arrow format', 'rey-core' ),
			'default'     => 'classic',
			'choices'     => [
				'classic'  => esc_html__('Classic', 'rey-core'),
				'alt'  => esc_html__('Alternative', 'rey-core'),
			],
		] );

		/*

		$this->add_title( esc_html__('Site overlays', 'rey-core') );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'site_overlay__enable',
			'label'       => esc_html__( 'Enable site overlay', 'rey-core' ),
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'site_overlay__bg',
			'label'       => esc_html__( 'Overlay Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'active_callback' => [
				[
					'setting'  => 'site_overlay__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'image',
			'settings'    => 'site_overlay__cursor',
			'label'       => esc_html__( 'Overlay Cursor', 'rey-core' ),
			'description' => esc_html__( 'You can change the overlay cursor image.', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'save_as' => 'id',
			],
			'active_callback' => [
				[
					'setting'  => 'site_overlay__enable',
					'operator' => '==',
					'value'    => true,
					],
			],
		] );

*/
	}
}
