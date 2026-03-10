<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Images extends Base {

	public function get_id(){
		return 'images';
	}

	public function get_title(){
		return esc_html__( 'Images', 'rey-core' );
	}

	public function controls( $element ){

		$element->start_controls_section(
			'section_images',
			[
				'label' => __( 'Images', 'rey-core' ),
				'condition' => [
					'source' => 'images',
				],
			]
		);

			$element->add_control(
				'images',
				[
					'label' => esc_html__( 'Add Images', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::GALLERY,
					'default' => [],
					'show_label' => false,
					'dynamic' => [
						'active' => true,
					],
				]
			);

			$element->add_control(
				'limit_images',
				[
					'label' => esc_html__( 'Limit displayed images', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 100,
					'step' => 1,
				]
			);

			$element->add_control(
				'images_link',
				[
					'label' => esc_html__( 'Images links', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'media',
					'options' => [
						'media'  => esc_html__( 'Link to media', 'rey-core' ),
						'all'  => esc_html__( 'Link all', 'rey-core' ),
						''  => esc_html__( 'Disable link', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'images_link_all',
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
					'condition' => [
						'images_link' => 'all',
					],
				]
			);

			$element->add_control(
				'images_caption',
				[
					'label' => esc_html__( 'Display Caption', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$element->end_controls_section();


	}

	public function query( $element ){

		$images = $element->_settings['images'];

		if( ! (isset($element->_settings['limit_images']) && ($limit = absint($element->_settings['limit_images']))) ){
			return $images;
		}

		$offset = 0;

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			if( $the_offset = $element->get_offset() ){
				$offset = $the_offset;
			}
		}

		return array_slice($images, $offset, $limit);

	}

	public function parse_item( $element ){

		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		$args = [
			'image' => $item,
		];

		if( '' !== $element->_settings['images_link'] ){

			$args['button_url'] = [];

			if( 'media' === $element->_settings['images_link'] ){
				$args['button_url']['url'] = $item['url'];
				$args['button_url']['custom_attributes'] = [
					'data-elementor-open-lightbox' => 'yes'
				];
			}
			else if( 'all' === $element->_settings['images_link'] ){

				$image_url_control = $element->_settings['images_link_all'];

				$args['button_url']['url'] = esc_url($image_url_control['url']);

				$image_link_attributes = [];

				if ( ! empty( $image_url_control['is_external'] ) ) {
					$image_link_attributes['target'] = '_blank';
				}

				if ( ! empty( $image_url_control['nofollow'] ) ) {
					$image_link_attributes['rel'] = 'nofollow';
				}

				if ( ! empty( $image_url_control['custom_attributes'] ) ) {
					// Custom URL attributes should come as a string of comma-delimited key|value pairs
					$image_link_attributes = array_merge( $image_link_attributes, \Elementor\Utils::parse_custom_attributes( $image_url_control['custom_attributes'] ) );
				}

				$args['button_url']['custom_attributes'] = $image_link_attributes;
			}
		}

		if( '' !== $element->_settings['images_caption'] ){

			$attachment_post = get_post( $item['id'] );
			$args['captions'] = 'yes';
			$args['title'] = $attachment_post->post_excerpt ? $attachment_post->post_excerpt : $attachment_post->post_title;
			$args['subtitle'] = $attachment_post->post_content;
		}

		return $args;
	}

	public function load_more_button_per_page($element){
		return isset($element->_settings['limit_images']) && ($limit = absint($element->_settings['limit_images'])) ? $limit : false;
	}
}
