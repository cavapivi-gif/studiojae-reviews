<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HeaderSearch extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'header-search',
			'title' => __( 'Search Box (Header)', 'rey-core' ),
			'icon' => 'eicon-search',
			'categories' => [ 'rey-header' ],
			'keywords' => [],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#search-box');
	}

	public function rey_get_script_depends() {
		return $this->get_skin_assets('script');
	}

	function get_skin_assets($type = ''){

		$assets = [];

		if (
			! \Elementor\Plugin::$instance->editor->is_edit_mode() &&
			! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {

			if( $settings = $this->get_settings_for_display() ){

				$search_style = isset($settings['search_style']) && !empty($settings['search_style']) ?
					$settings['search_style'] :
					$this->get_default_search_style();

				$assets = apply_filters('reycore/elementor/header-search/assets', $assets, $search_style, $type);
			}
		}
		return $assets;
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'edit_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
					'content_classes' => 'rey-raw-html --notice',
				]
			);


			$cst_link_query['autofocus[section]'] = \ReyCore\Customizer\Options\Header\Search::get_id();
			$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

			$this->add_control(
				'edit_link',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => sprintf( __( 'Search options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Search</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
					'content_classes' => 'rey-raw-html',
					'condition' => [
						'custom' => '',
					],
				]
			);

			$this->add_control(
				'custom',
				[
					'label' => __( 'Override global settings', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'global__search_style',
				[
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => get_theme_mod('header_search_style', 'wide'),
				]
			);

			$this->add_control(
				'search_style',
				[
					'label' => __( 'Search Panel Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'wide' => esc_html__( 'Wide Panel', 'rey-core' ),
						'side' => esc_html__( 'Side Panel', 'rey-core' ),
					],
					'condition' => [
						'custom!' => '',
					],
				]
			);

			$this->add_control(
				'search_complementary',
				[
					'label' => __( 'Suggestions content type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( '- Select -', 'rey-core' ),
						'menu' => esc_html__( 'Menu', 'rey-core' ),
						'keywords' => esc_html__( 'Keyword suggestions', 'rey-core' ),
					],
					'condition' => [
						'custom!' => '',
					],
				]
			);

			$this->add_control(
				'search_menu_source',
				[
					'label' => __( 'Menu Source', 'rey-core' ),
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request'   => 'get_nav_menus_options',
						'edit_link' => true,
					],
					'default' => '',
					'condition' => [
						'custom!' => '',
						'search_complementary' => 'menu',
					],
				]
			);

			$this->add_control(
				'keywords',
				[
					'label' => __( 'Keywords', 'rey-core' ),
					'description' => __( 'Add keyword suggestions, separated by comma ",".', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
					'placeholder' => __( 'eg: t-shirt, pants, trousers', 'rey-core' ),
					'condition' => [
						'custom!' => '',
						'search_complementary' => 'keywords',
					],
				]
			);

			$this->add_control(
				'search_icon_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Search', 'rey-core' ),
					'conditions' => [
						'relation' => 'and',
						'terms' => [
							['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
							['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
						]
					],
				]
			);


		$this->end_controls_section();

		$conditions = [
			'relation' => 'or',
			'terms' => [
				[
					'relation' => 'and',
					'terms' => [
						['name' => 'custom', 'operator' => '!==', 'value' => ''],
						['name' => 'search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
					],
				],
				[
					'relation' => 'and',
					'terms' => [
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
						['name' => 'custom', 'operator' => '===', 'value' => ''],
						// ['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
					],
				],
			]
		];


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'conditions' => $conditions,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch-toggle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => esc_html__( 'Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch-toggle:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch .__icon' => '--icon-size: {{VALUE}}px;',
				],
			]
		);

		$this->add_control(
			'search_icon',
			[
				'label' => esc_html__( 'Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Default', 'rey-core' ),
					'custom' => esc_html__( '- Custom Icon -', 'rey-core' ),
					'disabled' => esc_html__( '- No Icon -', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'custom_icon',
			[
				'label' => __( 'Custom Icon', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'search_icon' => 'custom',
				],

			]
		);

		$this->add_control(
			'hide_icon_desktop',
			[
				'label' => esc_html__( 'Hide Icon on Desktop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
						['name' => 'search_icon_text', 'operator' => '!=', 'value' => ''],
					]
				],
			]
		);

		$this->add_control(
			'text_title',
			[
			   'label' => esc_html__( 'TEXT', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						['name' => 'search_icon_text', 'operator' => '!=', 'value' => ''],
						['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
					]
				],
			]
		);

		$this->add_control(
			'text_position',
			[
				'label' => __( 'Text Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default', 'rey-core' ),
					'before' => esc_html__( 'Before', 'rey-core' ),
					'after' => esc_html__( 'After', 'rey-core' ),
					'under' => esc_html__( 'Under', 'rey-core' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						['name' => 'search_icon_text', 'operator' => '!=', 'value' => ''],
						['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
					]
				],
			]
		);

		$this->add_control(
			'text_distance',
			[
				'label' => esc_html__( 'Text Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--text-distance: {{VALUE}}px',
				],
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						['name' => 'search_icon_text', 'operator' => '!=', 'value' => ''],
						['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
					]
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'custom_text_typo',
				'selector' => '{{WRAPPER}} .rey-headerIcon-btnText',
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						['name' => 'search_icon_text', 'operator' => '!=', 'value' => ''],
						['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'side', 'wide']],
						['name' => 'global__search_style', 'operator' => 'in', 'value' => ['side', 'wide']],
					]
				],
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ PANEL ------------------------------------ */

		$this->start_controls_section(
			'section_panel_styles',
			[
				'label' => __( 'Panel Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'conditions' => $conditions,
			]
		);

			$this->add_control(
				'text_color',
				[
					'label' => __( 'Panel Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						':root' => '--search-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => __( 'Panel Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						':root' => '--search-bg-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();
	}

	public function get_default_search_style(){

		if( function_exists('reycore_wc__get_header_search_args') ){
			return reycore_wc__get_header_search_args('search_style');
		}

		return get_theme_mod('header_search_style', 'wide');
	}

	function set_options( $vars ){

		$settings = $this->get_settings_for_display();

		// reinforce the global setting
		$vars['search_style'] = get_theme_mod('header_search_style', 'wide');

		if( isset($settings['custom']) && $settings['custom'] ){

			$vars['search_complementary'] = $settings['search_complementary'];

			if( isset($settings['search_menu_source']) ){
				$vars['search_menu_source'] = $settings['search_menu_source'];
			}

			if( isset($settings['search_style']) && ! is_null($settings['search_style']) && '' !== $settings['search_style'] ){
				$vars['search_style'] = $settings['search_style'];
			}

			$vars['keywords'] = $settings['keywords'];
		}

		if( in_array($vars['search_style'], ['side', 'wide']) && ($search_text = $settings['search_icon_text']) ){
			$vars['search__before_content'] = $search_text;
		}

		// deprecated
		$reverse__legacy = false;
		if( isset($settings['custom_text_reverse']) ){
			$reverse__legacy = $settings['custom_text_reverse'] !== '';
		}

		if( isset($vars['classes']) ){

			$text_position = $reverse__legacy ? 'after' : 'before';

			if( isset($settings['text_position']) && $settings['text_position']  ){
				$text_position = $settings['text_position'];
			}

			$vars['classes'] .= sprintf(' --tp-%1$s --hit-%1$s', esc_attr($text_position));

			$vars['classes'] .= is_null($settings['hide_icon_desktop']) || '' === $settings['hide_icon_desktop'] ? '' : ' --hicon-lg';
		}

		return $vars;
	}

	function set_icon( $icon_html ){

		$settings = $this->get_settings_for_display();

		if( $settings['search_icon'] === '' ){
			return $icon_html;
		}

		if( $settings['search_icon'] === 'disabled' ){
			return '';
		}

		else if( $settings['search_icon'] === 'custom' ) {

			if( ($custom_icon = $settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				return \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => 'rey-icon icon-search' ] );
			}
		}

		return $icon_html;
	}

	public function maybe_show_notice(){

		$_id = 0;

		if( isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids']) ){
			$_id = end($gs_ids);
		}
		else if( get_post_type() === \ReyCore\Elementor\GlobalSections::POST_TYPE ){
			$_id = get_the_ID();
		}

		if( $_id ){
			if( 'offcanvas' === reycore__acf_get_field('gs_type', $_id, 'generic') ){
				if( current_user_can('administrator') ){
					printf('<small>%s</small>', esc_html__('Please use the "Search Form" widget instead. The "Search Box (header)" is only meant to be used inside the Header and is incompatible with this context.', 'rey-core'));
				}
				return true;
			}
		}

		return false;
	}

	protected function render() {

		if( $this->maybe_show_notice() ){
			return;
		}

		$settings = $this->get_settings_for_display();

		reycore_assets()->add_styles(['reycore-header-search-top', 'reycore-header-search', 'rey-header-icon']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );
		reycore_assets()->add_scripts(['reycore-header-search']);

		// force enable
		add_filter('theme_mod_header_enable_search', '__return_true', 10);
		add_filter('reycore/woocommerce/header/search_icon', [$this, 'set_icon']);

		$search_style = ! is_null($settings['search_style']) && '' !== $settings['search_style'] ? $settings['search_style'] : get_theme_mod('header_search_style', 'wide');

		if( 'side' === $search_style ){
			reycore_assets()->add_styles(['rey-overlay', 'reycore-side-panel']);
			reycore_assets()->add_scripts('reycore-sidepanel');
		}

		add_filter('reycore/header/search_params', [$this, 'set_options'], 10);

		// Wide & Side panels
		if( in_array($search_style, ['wide', 'side']) ){
			reycore__get_template_part('template-parts/header/search-toggle');
			add_filter('reycore/header/search_panel', '__return_true');
		}

		// Default simple form
		elseif($search_style === 'button') {
			get_template_part('template-parts/header/search-button');
			reycore_assets()->add_styles('rey-header-search');
			reycore_assets()->add_scripts('rey-searchform');
		}

		do_action('reycore/elementor/header-search/template', $settings, $search_style);

		// settings not applying on the panel
		remove_filter('reycore/woocommerce/header/search_icon', [$this, 'set_icon']);
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
