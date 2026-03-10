<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ImageGallery {

	function __construct(){

		add_action( 'elementor/element/image-gallery/section_gallery/before_section_end', [$this, 'columns_settings']);
		add_action( 'elementor/element/image-gallery/section_gallery_images/before_section_end', [$this, 'layout_settings']);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);

	}

	function before_render( $element )
	{

		if( $element->get_unique_name() !== 'image-gallery' ){
			return;
		}

		reycore_assets()->add_styles('reycore-elementor-el-image-gallery');
	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function layout_settings( $element )
	{

		$element->add_control(
			'rey_content_position',
			[
				'label' => __( 'Vertical Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'top' => __( 'Top', 'rey-core' ),
					'center' => __( 'Middle', 'rey-core' ),
					'bottom' => __( 'Bottom', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .gallery' => 'align-items: {{VALUE}}',
					// '{{WRAPPER}} .gallery' => 'align-content: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'rey_opacity',
			[
				'label' => __( 'Opacity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .gallery-item img' => 'opacity: {{SIZE}};',
				],
			]
		);

		// Update padding
		$el_spacing = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'image_spacing_custom' );
		if( $el_spacing && ! is_wp_error($el_spacing) ){
			$el_spacing['selectors']['{{WRAPPER}} .gallery'] = '--gallery-spacing:{{SIZE}}{{UNIT}}';
			$element->update_control( 'image_spacing_custom', $el_spacing );
		}

	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function columns_settings( $element )
	{

		$element->remove_control('gallery_columns');

		$gallery_columns = range( 1, 10 );
		$gallery_columns = array_combine( $gallery_columns, $gallery_columns );

		$element->add_responsive_control(
			'gallery_columns',
			[
				'label' => __( 'Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 4,
				'options' => $gallery_columns,
				'prefix_class' => 'gallery-cols-%s-',
				'render_type' => 'template',
			]
		);

	}

}
