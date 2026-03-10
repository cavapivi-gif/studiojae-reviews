<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;


class ImageCarousel {

	function __construct(){
		add_action( 'elementor/widget/image-carousel/skins_init', [$this,'custom_skins'] );
		add_action( 'elementor/element/image-carousel/section_image_carousel/before_section_end', [$this, 'add_settings']);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
	}

	/**
	 * Add custom skins into Elementor's Image Carousel widget
	 *
	 * @since 1.6.0
	 */
	function custom_skins( $element )
	{
		$element->add_skin( new \ReyCore\Elementor\Custom\ImageCarouselLinks( $element ) );
	}


	function before_render( $element )
	{

		if( $element->get_unique_name() !== 'image-carousel' ){
			return;
		}

		$settings = $element->get_data('settings');

		if( isset($settings['rey_toggle_boxes_id']) && $tg_boxes_id = $settings['rey_toggle_boxes_id'] ){
			$element->add_render_attribute( 'carousel-wrapper', 'data-carousel-id', esc_attr($tg_boxes_id) );
		}

		if( isset($settings['rey_variable_width']) && $settings['rey_variable_width'] !== '' ){
			reycore_assets()->add_styles('reycore-elementor-el-image-carousel');
			reycore_assets()->add_scripts('reycore-elementor-elem-image-carousel');
			$element->add_render_attribute( 'carousel-wrapper', 'class', '--variable-width' );
		}

	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function add_settings( $element )
	{
		$carousel = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'carousel' );
		if( $carousel && ! is_wp_error($carousel) ){
			$carousel['condition']['_skin'] = '';
			$element->update_control( 'carousel', $carousel );
		}

		$element->start_injection( [
			'of' => 'carousel',
		] );

			$items = new \Elementor\Repeater();

			$items->add_control(
				'image',
				[
					'label' => __( 'Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$items->add_control(
				'link',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'default' => [
						'url' => '#',
					],
				]
			);

			$element->add_control(
				'rey_items',
				[
					'label' => __( 'Items', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $items->get_controls(),
					'default' => [
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'link' => [
								'url' => '#',
							],
						],
						[
							'image' => [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
							'link' => [
								'url' => '#',
							],
						],
					],
					'condition' => [
						'_skin' => 'carousel_links',
					],
				]
			);

		$element->end_injection();

		$element->add_control(
			'rey_toggle_boxes_id',
			[
				'label' => __( 'Carousel ID', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('#carousel-'),
				'placeholder' => __( 'eg: #some-unique-id', 'rey-core' ),
				'description' => __( 'Copy the ID above and paste it into the link text-fields, where specified.', 'rey-core' ),
				'separator' => 'before',
				'render_type' => 'none',
				'style_transfer' => false,
			]
		);

		$element->add_control(
			'rey_variable_width',
			[
				'label' => esc_html__( 'Variable Width', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_responsive_control(
			'rey_variable_width_height',
			[
				'label' => esc_html__( 'Custom height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 80,
						'max' => 1080,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 260,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--variable-width-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'rey_variable_width!' => '',
				],
			]
		);

	}

}
