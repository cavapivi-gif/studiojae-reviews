<?php
namespace ReyCore\Modules\Quickview;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-catalog-product-item/marker=atc', [$this, 'add_controls']);
	}

	public function add_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Quickview', 'rey-core' ),
		]);

		$section->add_control( array(
			'type'        => 'select',
			'settings'    => 'loop_quickview',
			'label'       => esc_html__('Status', 'rey-core'),
			'help' => [
				__('Choose if you want the quickview button to be displayed.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				''     => esc_attr__('- Inherit (from skin) -', 'rey-core'),
				'1'    => esc_attr__('Enabled - Text Button', 'rey-core'),
				'icon' => esc_attr__('Enabled - Icon Button', 'rey-core'),
				'2'    => esc_attr__('Disabled', 'rey-core'),
			]
		));

		$section->start_controls_group( [
			'label'    => esc_html__( 'Quickview options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_quickview',
					'operator' => '!=',
					'value'    => '2',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview_style',
			'label'       => esc_html__( 'Button style', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_attr__('- Inherit (from skin) -', 'rey-core'),
				'under' => esc_html__( 'Default (underlined)', 'rey-core' ),
				'hover' => esc_html__( 'Hover Underlined', 'rey-core' ),
				'primary' => esc_html__( 'Primary', 'rey-core' ),
				'primary-out' => esc_html__( 'Primary Outlined', 'rey-core' ),
				'clean' => esc_html__( 'Clean', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview_icon_type',
			'label'       => esc_html__( 'Choose Icon', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_attr__('- Inherit (from skin) -', 'rey-core'),
				'eye' => esc_html__( 'Eye icon', 'rey-core' ),
				'dots' => esc_html__( 'Dots', 'rey-core' ),
				// 'bubble' => esc_html__( 'Bubble', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview_position',
			'label'       => esc_html__( 'Button Position', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_attr__('- Inherit (from skin) -', 'rey-core'),
				'bottom' => esc_html__( 'Bottom', 'rey-core' ),
				'topright' => esc_html__( 'Thumb. top right', 'rey-core' ),
				'bottomright' => esc_html__( 'Thumb. bottom right', 'rey-core' ),
			],
		] );


		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'loop_quickview_accent_text_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  => '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
					'property' => '--accent-text-color',
				],
				[
					'element'  => '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
					'property' => '--btn-color',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'loop_quickview_accent_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
					'property' 		=> '--accent-color',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'loop_quickview_accent_hover_color',
			'label'       => esc_html__( 'Background Hover Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
					'property' 		=> '--accent-hover-color',
				],
			],
		] );


		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'loop_quickview__link_all',
			'label'       => esc_html__( 'Quickview ONLY', 'rey-core' ),
			'help' => [
				__('This will force all links in product item, to link to the Quickview panel (desktop only).', 'rey-core')
			],
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'loop_quickview__link_all_hide',
			'label'       => esc_html__( 'Quickview ONLY - Hide button', 'rey-core' ),
			'help' => [
				__('Enable if you want to hide Quickview button.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'loop_quickview__link_all',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_title( esc_html__('Popup settings', 'rey-core') );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview__panel_style',
			'label'       => esc_html__( 'Open effect', 'rey-core' ),
			'default'     => 'curtain',
			'choices'     => [
				'curtain' => esc_html__( 'Curtain', 'rey-core' ),
				'slide' => esc_html__( 'Slide up', 'rey-core' ),
			],
		] );

		$section->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'loop_quickview_specifications',
			'label'       => esc_html__('Specifications Content', 'rey-core'),
			'help' => [
				__('Choose if you want to show specifications table in quickview panel.', 'rey-core')
			],
			'default'     => true,
		));

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview_gallery_type',
			'label'       => esc_html__( 'Gallery Nav. Type', 'rey-core' ),
			'default'     => 'thumbs',
			'choices'     => [
				'thumbs' => esc_html__( 'Thumbnails', 'rey-core' ),
				'bullets' => esc_html__( 'Bullets', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_quickview_image_fit',
			'label'       => esc_html__( 'Image Fit', 'rey-core' ),
			'help' => [
				__('Contain will make sure the main gallery image is uncut, while cover will makes sure to be fit for its container.', 'rey-core')
			],
			'default'     => 'cover',
			'choices'     => [
				'cover' => esc_html__( 'Cover', 'rey-core' ),
				'contain' => esc_html__( 'Contain', 'rey-core' ),
			],
		] );

		$section->end_controls_group();

		$section->end_controls_accordion();

	}
}
