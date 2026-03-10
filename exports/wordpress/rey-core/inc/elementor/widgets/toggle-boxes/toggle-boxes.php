<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

include __DIR__ . '/ccss.php';

class ToggleBoxes extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'toggle-boxes',
			'title' => __( 'Toggle Boxes', 'rey-core' ),
			'icon' => 'eicon-navigation-horizontal',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style.css',
				'assets/default.css',
				'assets/stacks.css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-toggle-boxes-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#toggle-boxes');
	}

	protected function register_skins() {
		foreach ([
			'SkinStacks',
		] as $skin) {
			$skin_class = __CLASS__ . '\\' . $skin;
			$this->add_skin( new $skin_class( $this ) );
		}
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$this->add_control(
			'target_type',
			[
				'label' => __( 'Select target type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'tabs'  => __( 'Tabs', 'rey-core' ),
					'reycarousel'  => __( 'Carousel', 'rey-core' ),
					'slider'  => __( 'Slider', 'rey-core' ),
					'parent'  => __( 'Parent Section Slideshow', 'rey-core' ),
					'carousel'  => __( 'Image Carousel (widget)', 'rey-core' ),
				],
				'label_block' => true
			]
		);

		$this->add_control(
			'target_type_parent_slideshow_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'To add/remove images, edit this element\'s parent section, in the background slideshow options.', 'rey-core' ),
				'content_classes' => 'rey-raw-html --em',
				'condition' => [
					'target_type' => 'parent',
				],
			]
		);

		$this->add_control(
			'target_tabs',
			[
				'label' => __( 'Tabs ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
				'placeholder' => __( 'eg: unique-id', 'rey-core' ),
				'condition' => [
					'target_type' => 'tabs',
				],
				'description' => esc_html__('Copy the unique ID, found in Section > Tabs Settings > Tabs ID, and paste it here.', 'rey-core'),
				'wpml' => false,
			]
		);

		$this->add_control(
			'target_carousel',
			[
				'label' => __( 'Carousel/Slider ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
				'placeholder' => __( 'eg: unique-id', 'rey-core' ),
				'condition' => [
					'target_type' => ['reycarousel', 'carousel', 'slider'],
				],
				'description' => esc_html__('Copy the unique ID, found in either "Image Carousel", "Carousel" or "Slider" widget ID, and paste it here.', 'rey-core'),
				'wpml' => false,
			]
		);


		$this->add_control(
			'trigger',
			[
				'label' => __( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'click',
				'options' => [
					'click'  => __( 'On Click', 'rey-core' ),
					'hover'  => __( 'On Hover', 'rey-core' ),
				],
				'condition' => [
					'target_type!' => '',
				],
			]
		);

		$items = new \Elementor\Repeater();

		$items->add_control(
			'text',
			[
				'label'       => __( 'Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [],
			]
		);

		$items->add_control(
			'link',
			[
				'label' => __( 'Link (For Hover triggers)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [],
			]
		);

		$items->add_control(
			'activate_on',
			[
				'label' => __( 'Activate item when current URL contains:', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'label_block' => true,
				'default' => '',
			]
		);

		$this->add_control(
			'items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'condition' => [
					'_skin' => '',
				],
				'default' => [
					[
						'text' => __( 'Item Text  #1', 'rey-core' ),
					],
					[
						'text' => __( 'Item Text  #2', 'rey-core' ),
					],
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'direction',
			[
				'label' => __( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				// 'default' => 'h',
				'desktop_default' => 'h',
				'tablet_default' => 'v',
				'mobile_default' => 'v',
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'options' => [
					'h'  => __( 'Horizontal', 'rey-core' ),
					'v'  => __( 'Vertical', 'rey-core' ),
				],
				'prefix_class' => '--direction-%s-'
			]
		);

		$this->add_control(
			'h_keep_inline',
			[
				'label' => esc_html__( 'Keep inline (On Mobile)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--h-inline-mobile-',
				'condition' => [
					'direction' => 'h',
				],
			]
		);

		$this->add_responsive_control(
			'horizontal_alignment',
			[
				'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes--h' => 'justify-content: {{VALUE}};',
				],
				'condition' => [
					'_skin' => '',
					'direction' => 'h',
				],
			]
		);

		$this->add_control(
			'force_stretch',
			[
				'label' => esc_html__( 'Force Stretch', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--fg-',
				'condition' => [
					'_skin' => '',
					'direction' => 'h',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'flex-grow: 1;',
				],
			]
		);

		$this->add_responsive_control(
			'distance',
			[
				'label' => __( 'Items Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes' => '--tgb-gap:{{VALUE}}px;',
				]
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Text Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'text-align: {{VALUE}}; justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_items_style',
			[
				'label' => __( 'Items Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'hover_style',
			[
				'label' => __( 'Hover/Active Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''              => __( 'Default', 'rey-core' ),
					'ulr'           => __( 'Left to Right Underline', 'rey-core' ),
					'ulr --thinner' => __( 'Left to Right Underline (thinner)', 'rey-core' ),
					'ut'            => __( 'Left to Right Thick Underline', 'rey-core' ),
					// 'ub'            => __( 'Bottom Underline', 'rey-core' ),
					// 'ut2'           => __( 'Left to Right Background', 'rey-core' ),
					// 'sc'            => __( 'Scale on hover', 'rey-core' ),
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_items_styles' );

			$this->start_controls_tab(
				'tabs_items_styles_normal',
				[
					'label' => esc_html__( 'Normal', 'rey-core' ),
				]
			);

				$this->add_control(
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' => __( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox > i' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin' => '',
						],
					]
				);

				$this->add_responsive_control(
					'border_width',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'border_color',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox' => 'border-color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'box_shadow',
						'selector' => '{{WRAPPER}} .rey-toggleBox',
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tabs_items_styles_hover',
				[
					'label' => esc_html__( 'Active', 'rey-core' ),
				]
			);

				$this->add_control(
					'text_color_active',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color_active',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'icon_color_active',
					[
						'label' => __( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active > i, {{WRAPPER}} .rey-toggleBox:hover > i' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin' => '',
						],
					]
				);

				$this->add_responsive_control(
					'border_width_active',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'border_color_active',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover' => 'border-color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'box_shadow_active',
						'selector' => '{{WRAPPER}} .rey-toggleBox.--active, {{WRAPPER}} .rey-toggleBox:hover',
					]
				);

			$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'height',
			[
				'label' => esc_html__( 'Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBox' => 'height: {{VALUE}}px;',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'main_text',
			[
			   'label' => __( 'Main Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typo',
				'selector' => '{{WRAPPER}} .rey-toggleBox-text-main'
			]
		);

		$this->add_control(
			'icon_style_title',
			[
			   'label' => __( 'Icon styles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'icons_distance',
			[
				'label' => __( 'Icon Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes' => '--tgbx-gap: {{VALUE}}px;',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'icons_size',
			[
				'label' => __( 'Icon Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-toggleBoxes' => '--toggle-boxes-icon-size: {{VALUE}}px;',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);



		$this->end_controls_section();
	}

	public function render_start( $settings ){

		$classes = [
			'rey-toggleBoxes',
			'rey-toggleBoxes--' . $settings['direction'],
			'rey-toggleBoxes--' . (! $settings['_skin'] ? 'default' : $settings['_skin'])
		];

		if( isset($settings['hover_style']) && $hov_style = $settings['hover_style'] ){
			$classes[] = '--hov-' . $hov_style;
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( $settings['target_type'] !== '' ){
			$config = [
				'target_type' => $settings['target_type'],
				'tabs_target' => esc_attr($settings['target_tabs']),
				'carousel_target' => esc_attr($settings['target_carousel']),
				'parent_trigger' => esc_attr($settings['trigger']),
			];
			$this->add_render_attribute( 'wrapper', 'data-config', wp_json_encode($config) );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->render_start($settings);

		if( !empty($settings['items']) ){

			$active_key = 0;
			$content = '';

			foreach ($settings['items'] as $key => $item) {

				$tag = 'div';
				$activate_on = isset($item['activate_on']) ? $item['activate_on'] : '';

				$item_class = 'rey-toggleBox--' . $key;
				$link_attributes = 'tabindex="0"';

				if(
					$settings['trigger'] === 'hover' &&
					isset($item['link']['url']) && !empty($item['link']['url'])
				){

					$tag = 'a';
					$link_attributes .= sprintf(' href="%s"', $item['link']['url']);
					$link_attributes .= sprintf(' target="%s"', $item['link']['is_external'] ? '_blank' : '_self');
					$link_attributes .= $item['link']['nofollow'] ? ' rel="nofollow"' : '';

					if( isset($item['link']['url']) && strpos(reycore__current_url(), $item['link']['url']) !== false ){
						$active_key = $key;
					}
				}

				if( $activate_on ){
					// check server side link
					if($key === 1 && strpos(reycore__current_url(), $activate_on) !== false ){
						$item_class .= ' --active';
					}
					// check url fragments
					else {
						$link_attributes .= sprintf(' data-activate-on="%s"', esc_attr($activate_on));
					}
				}

				$item_content = sprintf('<%2$s class="rey-toggleBox %1$s" %3$s>', $item_class, $tag, $link_attributes);

					if( $icon = $item['icon'] ){
						ob_start();
						\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
						$item_content .= ob_get_clean();
					}

					$main_text_class = '';

					if( isset($settings['hover_style']) && $hov_style = $settings['hover_style'] ){
						$class_map = [
							'ulr'           => 'btn btn-line-active',
							'ulr --thinner' => 'btn btn-line-active',
							'ut'            => 'btn btn-line-active',
						];
						if( isset($class_map[ $hov_style ]) ){
							$main_text_class = $class_map[ $hov_style ];
						}
					}

					$item_content .= sprintf('<span class="rey-toggleBox-text-main %2$s" tabindex="-1">%1$s</span>', $item['text'], $main_text_class);

				$item_content .= sprintf('</%1$s>', $tag);

				$item_content = apply_filters('reycore/elementor/toggle-boxes/item', $item_content, $item, $key );

				$content .= $item_content;
			}

			if(strpos($content, '--active') === false ){
				// add active
				echo str_replace('rey-toggleBox--' . $active_key, '--active rey-toggleBox--' . $active_key, $content);
			}
			else {
				echo $content;
			}

		}

		$this->render_end();

		reycore_assets()->add_styles([$this->get_style_name(), $this->get_style_name('default')]);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
