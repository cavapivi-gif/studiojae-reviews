<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Navigation extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'header-navigation';
	}

	public function get_title(){
		return esc_html__('Navigation', 'rey-core');
	}

	public function get_priority(){
		return 40;
	}

	public function get_icon(){
		return 'navigation';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-header-settings/#navigation');
	}

	public function controls(){

		$this->add_title( esc_html__('Desktop Navigation', 'rey-core'), [
			'separator' => 'none',
		]);

		$this->add_title( '', [
			'separator' => 'none',
			'description' => sprintf(_x('For more customisation options, please edit the <strong>Header Navigation</strong> widget in the <a href="%s" target="_blank">Header (Global Section)</a>.', 'Customizer control description', 'rey-core'), admin_url( sprintf('post.php?post=%d&action=elementor', get_theme_mod('header_layout_type', 'default')) ) ),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!contains',
					'value'    => ['none', 'default'],
				],
			]
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_nav_cache',
			'label'       => esc_html__( 'Cache Navigation', 'rey-core' ),
			'help' => [
				__( 'This option will improve the page rendering by caching the menu output.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_nav_overlay',
			'label'       => esc_html__( 'Hover Overlay', 'rey-core' ),
			'help' => [
				__( 'This option enables the overlay to be shown behind submenus when hovering menu items. "Show & Header Over" will put the header in front of the overlay', 'rey-core' )
			],
			'default'     => 'show',
			'choices'     => [
				'show'  => esc_html__( 'Show', 'rey-core' ),
				'show_header_top'  => esc_html__( 'Show & Header Over', 'rey-core' ),
				'hide'  => esc_html__( 'Disabled', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_nav_overlay_top_color',
			'label'       => esc_html__( 'Header\'s Text Color (default)', 'rey-core' ),
			'help' => [
				__( 'Set a default text color of the header. Useful when the site overlay is visible on hovering the animation. Please know it can be overridden by any widget in the Header (Global section).', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element'  => 'body.header-top-overlay--is-opened.--o-src-menu',
					'property' => '--header-text-color',
				],
				[
					'element'  => 'body.header-overlay--is-opened.--o-src-menu .rey-mainNavigation--desktop .menu-item.depth--0',
					'property' => 'color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'header_nav_overlay',
					'operator' => 'in',
					'value'    => ['show', 'show_header_top'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_nav_overlay_top_bg_color',
			'label'       => esc_html__( 'Header Background Color (default)', 'rey-core' ),
			'help' => [
				__( 'Set a default background color of the header. Useful when the site overlay is visible on hovering the animation.', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element'  => 'body.header-top-overlay--is-opened.--o-src-menu',
					'property' => '--header-bgcolor',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'header_nav_overlay',
					'operator' => '==',
					'value'    => 'show_header_top',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_nav_hover_delays',
			'label'       => esc_html__( 'Hover Delays', 'rey-core' ),
			'help' => [
				__( 'This option enables a subtle delay for submenus effects when hovering menu items.', 'rey-core' )
			],
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_nav_submenus_shadow',
			'label'       => esc_html__( 'Sub-menu Shadow', 'rey-core' ),
			'default'     => '1',
			'choices'     => [
				'0' => esc_html__( 'Disabled', 'rey-core' ),
				'1' => esc_html__( 'Level 1', 'rey-core' ),
				'2' => esc_html__( 'Level 2', 'rey-core' ),
				'3' => esc_html__( 'Level 3', 'rey-core' ),
				'4' => esc_html__( 'Level 4', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'header_nav_items_spacing',
			'label'       => esc_html__( 'Horizontal Spacing (rem)', 'rey-core' ),
			'default'     => 1,
			'transport'   => 'auto',
			'choices'     => [
				'min'  => 0.1,
				'max'  => 100,
				'step' => 0.1,
			],
			'output'      		=> [
				[
					'media_query'	=> '@media (min-width: 1025px)',
					'element'  		=> ':root',
					'property' 		=> '--header-nav-x-spacing',
					'units'    		=> 'rem',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			]
		] );

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_menu_lvl_1',
			'label'       => esc_html__('Typography (1st level)', 'rey-core'),
			'help' => [
				__( 'Control the typography settings of the desktop menu 1st level.', 'rey-core' )
			],
			'default'     => [
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			],
			'output' => [
				[
					'element' => '.rey-mainMenu.rey-mainMenu--desktop > .menu-item.depth--0 > a',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
			'load_choices' => true,
		));

		$this->add_title( esc_html__('Mobile Navigation', 'rey-core'), [ ]);

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'nav_breakpoint',
			'label'       => esc_html__( 'Breakpoint for mobile navigation', 'rey-core' ),
			'default'     => 1024,
			'choices'     => [
				'min'  => 768,
				'max'  => 5000,
				'step' => 1,
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'mobile_menu',
			'label'       => esc_html__( 'Mobile menu source', 'rey-core' ),
			'description' => esc_html__( 'By default, the main menu is used for mobile menu as well, however if you want to have different structure, please select another navigation', 'rey-core' ),
			'default'     => '',
			'choices'     => ['' => esc_html__('- Select -', 'rey-core')],
			'ajax_choices' => 'get_menus_list',
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'mobile_panel_bg_color',
			'label'       => esc_html__( 'Mobile Panel Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--header-nav-mobile-panel-bg-color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'mobile_panel_text_color',
			'label'       => esc_html__( 'Mobile Panel Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--header-nav-mobile-panel-text-color',
				],
			],
		] );

		$this->add_notice([
			'default'     => __('In case these options doesn\'t seem to work, please check if you\'re using a Header Global Section and make sure the "Header - Navigation" element doesn\'t have the Override settings option enabled eg: <a href="https://d.pr/i/cjQx1X" target="_blank">https://d.pr/i/cjQx1X</a>.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'default',
				],
			]
		] );

	}
}
