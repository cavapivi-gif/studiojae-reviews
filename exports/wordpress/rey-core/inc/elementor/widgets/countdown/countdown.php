<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Countdown extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static $link = [
		'start' => '',
		'end'   => '',
	];

	public static function get_rey_config(){
		return [
			'id' => 'countdown',
			'title' => __( 'Countdown [rey]', 'rey-core' ),
			'icon' => 'eicon-countdown',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['timer', 'limited', 'sale', 'promo'],
		];
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
				'label' => __( 'Countdown', 'rey-core' ),
			]
		);

			$this->add_control(
				'type',
				[
					'label' => esc_html__( 'Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'due',
					'options' => [
						'due'  => esc_html__( 'Due Date', 'rey-core' ),
						'evergreen'  => esc_html__( 'Evergreen', 'rey-core' ),
					],
				]
			);

			$this->add_control( // ok
				'due_date',
				[
					'label'     => esc_html__( 'Due Date', 'rey-core' ),
					'type'      => \Elementor\Controls_Manager::DATE_TIME,
					'default'   => '',
					'condition' => [
						'type' => 'due',
					],
				]
			);

			$this->add_control( // ok
				'starting_from',
				[
					'label'     => esc_html__( 'Starting From', 'rey-core' ),
					'type'      => \Elementor\Controls_Manager::DATE_TIME,
					'default'   => '',
					'condition' => [
						'type' => 'evergreen',
					],
				]
			);

			$this->add_control(
				'duration',
				[
					'label' => esc_html__( 'Duration (days)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 3,
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'condition' => [
						'type' => 'evergreen',
					],
				]
			);

			$this->add_control(
				'repeat_count',
				[
					'label' => esc_html__( 'Repeat Cycles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'condition' => [
						'type' => 'evergreen',
					],
				]
			);

			$this->add_control( // ok
				'hide',
				[
					'label' => esc_html__( 'Hide from Countdown', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => '',
					'multiple' => true,
					'options' => [
						'd' => esc_html__( 'Days', 'rey-core' ),
						'h' => esc_html__( 'Hours', 'rey-core' ),
						'i' => esc_html__( 'Minutes', 'rey-core' ),
						's' => esc_html__( 'Seconds', 'rey-core' ),
					],
				]
			);

			$this->add_control( // ok
				'labels',
				[
					'label' => esc_html__( 'Show Labels', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control( // ok
				'short',
				[
					'label' => esc_html__( 'Abbreviated Labels', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'labels!' => '',
					],
				]
			);

			$this->add_control( // ok
				'link',
				[
					'label' => __( 'Wrap in link', 'rey-core' ),
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_labels',
			[
				'label' => __( 'Labels', 'rey-core' ),
				'condition' => [
					'labels!' => '',
				],
			]
		);

			$this->add_control(
				'labels_text_days',
				[
				   'label' => esc_html__( 'Days', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

				$this->add_control(
					'd_singular',
					[
						'label' => esc_html__( 'Singular', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: day', 'rey-core' ),
					]
				);

				$this->add_control(
					'd_plural',
					[
						'label' => esc_html__( 'Plural', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: days', 'rey-core' ),
					]
				);

				$this->add_control(
					'd_abbr',
					[
						'label' => esc_html__( 'Abbreviated', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: d.', 'rey-core' ),
					]
				);

			$this->add_control(
				'labels_text_hours',
				[
				   'label' => esc_html__( 'Hours', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_control(
					'h_singular',
					[
						'label' => esc_html__( 'Singular', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: hour', 'rey-core' ),
					]
				);

				$this->add_control(
					'h_plural',
					[
						'label' => esc_html__( 'Plural', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: hours', 'rey-core' ),
					]
				);

				$this->add_control(
					'h_abbr',
					[
						'label' => esc_html__( 'Abbreviated', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: h.', 'rey-core' ),
					]
				);

			$this->add_control(
				'labels_text_minutes',
				[
				   'label' => esc_html__( 'Minutes', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_control(
					'i_singular',
					[
						'label' => esc_html__( 'Singular', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: minute', 'rey-core' ),
					]
				);

				$this->add_control(
					'i_plural',
					[
						'label' => esc_html__( 'Plural', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: minutes', 'rey-core' ),
					]
				);

				$this->add_control(
					'i_abbr',
					[
						'label' => esc_html__( 'Abbreviated', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: min.', 'rey-core' ),
					]
				);

			$this->add_control(
				'labels_text_seconds',
				[
				   'label' => esc_html__( 'Seconds', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_control(
					's_singular',
					[
						'label' => esc_html__( 'Singular', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: seconds', 'rey-core' ),
					]
				);

				$this->add_control(
					's_plural',
					[
						'label' => esc_html__( 'Plural', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: seconds', 'rey-core' ),
					]
				);

				$this->add_control(
					's_abbr',
					[
						'label' => esc_html__( 'Abbreviated', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => esc_html__( 'eg: sec.', 'rey-core' ),
					]
				);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'blocks',
					'options' => [
						'blocks'  => esc_html__( 'Blocks', 'rey-core' ),
						'inline'  => esc_html__( 'Inline', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'spacing',
				[
					'label' => esc_html__( 'Blocks Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-gap: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'inner_spacing',
				[
					'label' => esc_html__( 'Inner Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-i-gap: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'stretch',
				[
					'label' => esc_html__( 'Stretch', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--',
					'return_value' => 'stretch',
				]
			);

			$this->add_control(
				'text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'padding',
				[
					'label' => esc_html__( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'border',
					'selector' => '{{WRAPPER}} .__item',
				]
			);

			$this->add_control(
				'border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}}' => '--cdown-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => __( 'Content Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'number_title',
				[
				   'label' => esc_html__( 'Number', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

			$this->add_control(
				'number_color',
				[
					'label' => esc_html__( 'Number Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--item-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'number_typo',
					'selector' => '{{WRAPPER}} .__number',
				]
			);

			$this->add_control(
				'label_title',
				[
				   'label' => esc_html__( 'Label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'labels!' => '',
					],
				]
			);

			$this->add_control(
				'label_color',
				[
					'label' => esc_html__( 'Label Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--text-color: {{VALUE}}',
					],
					'condition' => [
						'labels!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'selector' => '{{WRAPPER}} .__label',
					'condition' => [
						'labels!' => '',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_inline_style',
			[
				'label' => __( 'Inline Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'inline',
				],
			]
		);

			$this->add_control(
				'show_icon',
				[
					'label' => esc_html__( 'Show Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

		$this->end_controls_section();

	}

	public function render_link_start() {

		if( ! ($url = $this->_settings['link']['url']) ){
			return;
		}

		if( $this->_settings['link']['is_external'] ){
			$this->add_render_attribute( 'link_wrapper' , 'target', '_blank' );
		}

		if( $this->_settings['link']['nofollow'] ){
			$this->add_render_attribute( 'link_wrapper' , 'rel', 'nofollow' );
		}

		$this->add_render_attribute( 'link_wrapper' , [
			'href' => $url,
			'class' => 'rey-cdownLink',
		] );

		printf( '<a %s>', $this->get_render_attribute_string('link_wrapper'));

		self::$link['end'] = '</a>';
	}

	public function render_link_end() {
		echo self::$link['end'];
	}

	public function render_the_content(){

		// determine now => to
		// test all options
		// evergreen


		$config = [
			'use_short'     => $this->_settings['short'] !== '',
			'inline'        => $this->_settings['layout'] === 'inline',
			'labels'        => $this->_settings['labels'] !== '',
			'hidden_labels' => array_filter((array) $this->_settings['hide']),
			'classes'       => [
				'rey-cdown-el',
			],
			'use_icon'  => $this->_settings['show_icon'] !== '',
			// 'icon'      => $this->_settings['badge_icon'],
		];

		// override labels
		if( '' !== $this->_settings['labels'] ){
			foreach (['d', 'h', 'i', 's'] as $item) {
				foreach (['singular', 'plural', 'abbr'] as $type) {
					if( isset($this->_settings[ "{$item}_{$type}"]) && ($a = $this->_settings[ "{$item}_{$type}"]) ){
						$config['strings'][$item][$type] = $a;
					}
				}
			}
		}

		$countdown = new \ReyCore\Libs\Countdown($config);

		if( 'due' === $this->_settings['type'] && $date = $this->_settings['due_date'] ){
			$countdown->set_to($date, true);
		}

		else if( 'evergreen' === $this->_settings['type'] ){
			$evergreen = $countdown::get_evergreen([
				'starting_from' => $this->_settings['starting_from'],
				'duration'      => $this->_settings['duration'],
				'repeat_count'  => $this->_settings['repeat_count'],
			]);
			if( $evergreen ){
				$countdown->set_to( $evergreen );
			}
		}

		echo $countdown->render();
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		$this->render_link_start();
		$this->render_the_content();
		$this->render_link_end();

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
