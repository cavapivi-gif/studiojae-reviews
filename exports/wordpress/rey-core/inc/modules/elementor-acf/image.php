<?php
namespace ReyCore\Modules\ElementorAcf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Image extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-acf-image';
	}

	public function get_title() {
		return __( 'Image', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-editor-icons --acf-image';
	}

	public function get_categories() {
		return [ 'rey-acf' ];
	}

	public function show_in_panel() {
		return class_exists('\ACF') && (bool) reycore__get_purchase_code();
	}

	public function get_keywords() {
		return [ 'acf', 'image', 'photo', 'visual' ];
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	protected function register_controls() {

		$this->start_controls_section(
			'section_image',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select Image Field', 'rey-core' ),
					'description' => esc_html__( 'Choose the image field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'image',
						],
					],
				]
			);

			$this->add_control(
				'fallback',
				[
					'label' => esc_html__( 'Add fallback image?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'fallback_image',
				[
					'label' => __( 'Choose Image', 'elementor' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'dynamic' => [
						'active' => true,
					],
					'default' => [
						'url' => '',
					],
					'condition' => [
						'fallback!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					'separator' => 'none',
				]
			);

			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'elementor' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
							'title' => __( 'Left', 'elementor' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'elementor' ),
							'icon' => 'eicon-text-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'elementor' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'link_to',
				[
					'label' => __( 'Link', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => [
						'none' => __( 'None', 'elementor' ),
						'file' => __( 'Media File', 'elementor' ),
						'custom' => __( 'Custom URL', 'elementor' ),
					],
				]
			);

			$this->add_control(
				'link',
				[
					'label' => __( 'Link', 'elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'elementor' ),
					'condition' => [
						'link_to' => 'custom',
					],
					'show_label' => false,
				]
			);

			$this->add_control(
				'link_acf_field',
				[
					'label' => esc_html__( 'ACF Link', 'rey-core' ),
					'description' => esc_html__( 'Choose the link field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'url',
						],
					],
					'condition' => [
						'link_to' => 'custom',
					],
				]
			);

			$this->add_control(
				'open_lightbox',
				[
					'label' => __( 'Lightbox', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default' => __( 'Default', 'elementor' ),
						'yes' => __( 'Yes', 'elementor' ),
						'no' => __( 'No', 'elementor' ),
					],
					'condition' => [
						'link_to' => 'file',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$this->add_responsive_control(
				'width',
				[
					'label' => __( 'Width', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'unit' => '%',
					],
					'tablet_default' => [
						'unit' => '%',
					],
					'mobile_default' => [
						'unit' => '%',
					],
					'size_units' => [ '%', 'px', 'vw' ],
					'range' => [
						'%' => [
							'min' => 1,
							'max' => 100,
						],
						'px' => [
							'min' => 1,
							'max' => 1000,
						],
						'vw' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'space',
				[
					'label' => __( 'Max Width', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'unit' => '%',
					],
					'tablet_default' => [
						'unit' => '%',
					],
					'mobile_default' => [
						'unit' => '%',
					],
					'size_units' => [ '%', 'px', 'vw' ],
					'range' => [
						'%' => [
							'min' => 1,
							'max' => 100,
						],
						'px' => [
							'min' => 1,
							'max' => 1000,
						],
						'vw' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} img' => 'max-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'height',
				[
					'label' => __( 'Height', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'unit' => 'px',
					],
					'tablet_default' => [
						'unit' => 'px',
					],
					'mobile_default' => [
						'unit' => 'px',
					],
					'size_units' => [ 'px', 'vh' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 500,
						],
						'vh' => [
							'min' => 1,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} img' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'object-fit',
				[
					'label' => __( 'Object Fit', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'condition' => [
						'height[size]!' => '',
					],
					'options' => [
						'' => __( 'Default', 'elementor' ),
						'fill' => __( 'Fill', 'elementor' ),
						'cover' => __( 'Cover', 'elementor' ),
						'contain' => __( 'Contain', 'elementor' ),
					],
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} img' => 'object-fit: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'separator_panel_style',
				[
					'type' => \Elementor\Controls_Manager::DIVIDER,
					'style' => 'thick',
				]
			);

			$this->start_controls_tabs( 'image_effects' );

			$this->start_controls_tab( 'normal',
				[
					'label' => __( 'Normal', 'elementor' ),
				]
			);

			$this->add_control(
				'opacity',
				[
					'label' => __( 'Opacity', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 1,
							'min' => 0.10,
							'step' => 0.01,
						],
					],
					'selectors' => [
						'{{WRAPPER}} img' => 'opacity: {{SIZE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Css_Filter::get_type(),
				[
					'name' => 'css_filters',
					'selector' => '{{WRAPPER}} img',
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab( 'hover',
				[
					'label' => __( 'Hover', 'elementor' ),
				]
			);

			$this->add_control(
				'opacity_hover',
				[
					'label' => __( 'Opacity', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 1,
							'min' => 0.10,
							'step' => 0.01,
						],
					],
					'selectors' => [
						'{{WRAPPER}}:hover img' => 'opacity: {{SIZE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Css_Filter::get_type(),
				[
					'name' => 'css_filters_hover',
					'selector' => '{{WRAPPER}}:hover img',
				]
			);

			$this->add_control(
				'background_hover_transition',
				[
					'label' => __( 'Transition Duration', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 3,
							'step' => 0.1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} img' => 'transition-duration: {{SIZE}}s',
					],
				]
			);

			$this->add_control(
				'hover_animation',
				[
					'label' => __( 'Hover Animation', 'elementor' ),
					'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'image_border',
					'selector' => '{{WRAPPER}} img',
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'image_border_radius',
				[
					'label' => __( 'Border Radius', 'elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'image_box_shadow',
					'exclude' => [
						'box_shadow_position',
					],
					'selector' => '{{WRAPPER}} img',
				]
			);

		$this->end_controls_section();

	}

	/**
	 * Retrieve image widget link URL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $settings
	 *
	 * @return array|string|false An array/string containing the link URL, or false if no link.
	 */
	private function get_link_url( $settings ) {

		if( $link_acf_field = $settings['link_acf_field'] ){
			$settings['link']['url'] = Base::get_field($link_acf_field);
		}

		if ( 'none' === $settings['link_to'] ) {
			return false;
		}

		if ( 'custom' === $settings['link_to'] ) {
			if ( empty( $settings['link']['url'] ) ) {
				return false;
			}

			return $settings['link'];
		}

		return [
			'url' => $settings['image']['url'],
		];
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$image = [];

		if( $settings['fallback'] !== '' && isset($settings['fallback_image']['url']) && !empty($settings['fallback_image']['url']) ){
			$image = $settings['fallback_image'];
		}

		if( $acf_field = $settings['acf_field'] ){
			if( ($acf_image = Base::get_field($acf_field))){
				if( isset($acf_image['url']) && !empty($acf_image['url']) ){
					$image['id'] = $acf_image['id'];
					$image['url'] = $acf_image['url'];
				}
				else {
					if( is_string($acf_image) && ! is_numeric($acf_image) ){
						$image['id'] = attachment_url_to_postid($acf_image);
						$image['url'] = $acf_image;
					}
					else if( is_numeric($acf_image) ){
						$image['id'] = $acf_image;
						$image['url'] = wp_get_attachment_url($acf_image);
					}
				}
			}
		}

		$settings['image'] = $image;

		if ( empty( $settings['image']['url'] ) ) {
			return;
		}

		$link = $this->get_link_url( $settings );

		if ( $link ) {
			$this->add_link_attributes( 'link', $link );

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$this->add_render_attribute( 'link', [
					'class' => 'elementor-clickable',
				] );
			}

			if ( 'custom' !== $settings['link_to'] ) {
				$this->add_lightbox_data_attributes( 'link', $settings['image']['id'], $settings['open_lightbox'] );
			}
		} ?>

			<?php if ( $link ) : ?>
					<a <?php echo $this->get_render_attribute_string( 'link' ); ?>>
			<?php endif; ?>

				<?php echo \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings ); ?>

			<?php if ( $link ) : ?>
					</a>
			<?php endif; ?>

		<?php
	}

}
