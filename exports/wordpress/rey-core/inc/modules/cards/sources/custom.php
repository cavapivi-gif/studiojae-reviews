<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Custom extends Base {

	public function get_id(){
		return 'custom';
	}

	public function get_title(){
		return esc_html__( 'Custom content', 'rey-core' );
	}

	public function controls($element){

		$element->start_controls_section(
			'section_custom_content',
			[
				'label' => __( 'Custom Items', 'rey-core' ),
				'condition' => [
					'source' => 'custom',
				],
			]
		);

		$items = new \Elementor\Repeater();

		$items->start_controls_tabs( 'custom__tabs' );

		$items->start_controls_tab( 'custom__tab_image', [ 'label' => esc_html__( 'Image', 'rey-core' ) ] );

		$items->add_control(
			'image',
			[
				'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$items->add_control(
			'image_position',
			[
				'label' => _x( 'Image Position', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'eg: 50% 50% (x / y)',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--bg-size-position: {{VALUE}}',
				],
				'dynamic' => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'wpml' => false,
			]
		);

		/* TODO: Revisit based on requests

		$items->add_control(
			'overlay_color',
			[
				'label' => __( 'Overlay Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__overlay' => 'background-color: {{VALUE}}',
				],
			]
		); */

		$items->end_controls_tab();

		$items->start_controls_tab( 'custom__tab_video', [ 'label' => esc_html__( 'Video', 'rey-core' ) ] );

		$items->add_control(
			'video',
			[
				'label' => __( 'Video URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'description' => __( 'Link to video (YouTube, or self-hosted mp4 is recommended).', 'rey-core' ),
				'wpml' => false,
			]
		);

		$items->add_control(
			'video_autoplay',
			[
				'label' => esc_html__( 'Autoplay (Muted)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'conditions' => [
					'terms' => [
						[
							'name' => 'video',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->end_controls_tab();

		$items->start_controls_tab( 'custom__tab_content', [ 'label' => esc_html__( 'Content', 'rey-core' ) ] );

		$items->add_control(
			'captions',
			[
				'label' => __( 'Enable Captions', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'label',
			[
				'label'       => __( 'Label Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		/* TODO: Revisit based on requests
		$items->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__caption' => 'color: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'separator' => 'after'
			]
		); */

		$items->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'button_url',
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
				'separator' => 'after'
			]
		);

		$items->end_controls_tab();

		$items->end_controls_tabs();

		// No 2nd button because they would need too many options, style, color, hover color, per button

		$element->add_control(
			'carousel_items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'default' => [
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
						'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
						'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
						'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
						'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
						'button_url' => [
							'url' => '#',
						],
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$element->add_control(
			'limit_custom_items',
			[
				'label' => esc_html__( 'Limit displayed items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 100,
				'step' => 1,
			]
		);

		$element->end_controls_section();

	}

	public function query($element){

		$items = $element->_settings['carousel_items'];

		if( ! (isset($element->_settings['limit_custom_items']) && ($limit = absint($element->_settings['limit_custom_items']))) ){
			return $items;
		}

		$offset = 0;

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			if( $the_offset = $element->get_offset() ){
				$offset = $the_offset;
			}
		}

		return array_slice($items, $offset, $limit);

	}

	public function parse_item($element){

		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		$item['custom_content'] = true;

		return $item;
	}

	public function load_more_button_per_page($element){
		return isset($element->_settings['limit_custom_items']) && ($limit = absint($element->_settings['limit_custom_items'])) ? $limit : false;
	}
}
