<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class General extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'header-general';
	}

	public function get_title(){
		return esc_html__('General', 'rey-core');
	}

	public function get_priority(){
		return 10;
	}

	public function get_icon(){
		return 'header-general';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-header-settings/#general');
	}

	public function help_extra_text(){
		return '<p>Or watch videos, <a href="https://youtu.be/jKRbCF8OowE" target="_blank">How to build the header</a> or <a href="https://youtu.be/XkHSB1cLIas" target="_blank">How to build a Sticky header</a>.</p>';
	}

	public function controls(){


		$this->add_control([
			'type'        => 'rey-hf-global-section',
			'label'       => esc_html__('Select Header Layout', 'rey-core'),
			'settings'    => 'header_layout_type',
			'default'     => 'default',
			'choices'     => [
				'type' => 'header',
				'global_sections' => apply_filters('reycore/options/header_layout_options', [], false),
				'gs_desc' => sprintf( esc_html__('Select a Header Global Section. %s', 'rey-core'), reycore__header_footer_layout_desc('header') ),
			],
		]);


		/* ------------------------------------ DEFAULT header options ------------------------------------ */


		$this->start_controls_group( [
			'label'    => esc_html__( 'Default Header Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
					],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'custom_header_width',
			'label'       => esc_html__( 'Custom Header Width', 'rey-core' ),
			'default'     => '',
		] );

		$this->add_control( array(
			'type'        		=> 'slider',
			'settings'    		=> 'header_width',
			'label'       		=> esc_attr__( 'Container Width', 'rey-core' ),
			'default'     		=> 1440,
			'choices'     		=> array(
				'min'  => '990',
				'max'  => '2560',
				'step' => '10',
			),
			'transport'   		=> 'auto',
			'output'      		=> array(
				array(
					'element'  		=> ':root',
					'property' 		=> '--header-default--max-width',
					'units'    		=> 'px',
				),
			),
			'active_callback' => array(
				[
					'setting'  => 'custom_header_width',
					'operator' => '==',
					'value'    => true,
				],
			),
		));


		$this->add_control( array(
			'type'        		=> 'slider',
			'settings'    		=> 'header_height',
			'label'       		=> esc_attr__( 'Header Height', 'rey-core' ),
			'default'     		=> 130,
			'choices'     		=> array(
				'min'  => '20',
				'max'  => '300',
				'step' => '1',
			),
			'transport'   		=> 'auto',
			'output'      		=> array(
				array(
					'element'  		=> ':root',
					'property' 		=> '--header-default--height',
					'units'    		=> 'px',
				),
			),
			// 'input_attrs' => array(
			// 	'data-control-class' => 'mb-3',
			// ),
		));


		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_bg_color',
			'label'       => __( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--header-bgcolor',
				],
				[
					'element'  		=> ':root',
					'property' 		=> '--header-default-fixed-shrinking-bg',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_text_color',
			'label'       => __( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--header-text-color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'custom',
			'settings'    => 'header_separator',
			'default'     => '<hr>',
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_separator_bar',
			'label'       => esc_html__( 'Separator bar', 'rey-core' ),
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_separator_bar_color',
			'label'       => esc_html__( 'Separator bar Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'active_callback' => array(
				array(
					'setting'  => 'header_separator_bar',
					'operator' => '==',
					'value'    => true,
				),
			),
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--header-bar-color',
				],
			],
		] );


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_separator_bar_mobile',
			'label'       => esc_html__( 'Separator bar - Only on mobile', 'rey-core' ),
			'description' => esc_html__( 'If enabled, the separator bar will only be shown on mobiles.', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_separator_bar',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->end_controls_group();


		/* ------------------------------------ HEADER POSITION ------------------------------------ */

		$this->add_title( esc_html__('Header Position', 'rey-core') );

		$this->add_positions_override_description();

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_position',
			'label'       => esc_html__( 'Select Position', 'rey-core' ),
			'help'        => [
				sprintf(__( 'Select how the header will position itself on top. Read more about <a href="%s" target="_blank">Header Positions and Overlapping Content</a>. Please know this option can be overriden in each page individual settings eg: <a href="%s" target="_blank">Header tab</a>. ', 'rey-core' ), reycore__support_url('kb/header-positions-and-overlapping-content/'), 'https://d.pr/i/wF5UXl'),
				// 'size'      => 290,
				'clickable' => true
			],
			'default'     => 'rel',
			'choices'     => [
				'rel'      => esc_html__( 'Relative', 'rey-core' ),
				'absolute' => esc_html__( 'Absolute ( Over Content )', 'rey-core' ),
				'fixed'    => esc_html__( 'Fixed (Sticked to top)', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'none',
				],
			],
			'separator' => 'before',
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Header Position options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'none',
				],
				[
					'setting'  => 'header_position',
					'operator' => 'in',
					'value'    => ['fixed', 'absolute'],
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_fixed_overlap', // used for absolute too
			'label'       => esc_html__( 'Overlap Content?', 'rey-core' ),
			'help' => [
				__( 'If enabled, header stays <strong>over</strong> the content, not before the content.', 'rey-core' ),
			],
			'default'     => true,
			'responsive' => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_fixed_disable_mobile',
			'label'       => esc_html__( 'Disable "Fixed" on mobiles', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'header_position',
					'operator' => '==',
					'value'    => 'fixed',
				],
			],
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'header_af__zindex',
			'label'       => esc_html__( 'Header Z-index', 'rey-core' ),
			'help' => [
				esc_html__( 'Select a z-index for header wrapper. Choose -1 to set the current z-index to auto. Please use with caution!', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'max'  => 10000000,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'none',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_preloader_animation',
			'label'       => esc_html__( 'Fade-in Header on page load', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'none',
				],
				[
					'setting'  => 'site_preloader',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


	}

	public function add_positions_override_description(){

		$page_header_positions_override = get_posts([
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'header_position',
					'value'   => '',
					'compare' => 'NOT IN'
				]
			]
		]);

		if( empty( $page_header_positions_override ) ){
			return;
		}

		$links = [];

		foreach ($page_header_positions_override as $pid) {
			$links[] = sprintf('<a href="%s" target="_blank">%s</a>', get_edit_post_link($pid), get_the_title($pid));
		}

		$notice = sprintf( wp_kses_post( '<strong>Heads up!</strong> One or more pages ( %s ) have their header position overridden, so what you\'ll choose here won\'t apply for those pages. Learn <a href="%s" target="_blank">how to change per page</a>.', 'rey-core' ), implode(', ', $links), 'https://d.pr/i/wF5UXl');

		$this->add_notice([
			'default'     => $notice,
			// 'notice_type' => 'raw'
		] );

	}
}
