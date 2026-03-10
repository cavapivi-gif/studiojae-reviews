<?php
namespace ReyCore\Customizer\Options\Footer;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class General extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'footer-general';
	}

	public function get_title(){
		return esc_html__('General Settings', 'rey-core');
	}

	public function get_priority(){
		return 1;
	}

	public function get_icon(){
		return 'footer-general-settings';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-footer-settings/#general');
	}

	public function controls(){

		$this->add_control([
			'type'        => 'rey-hf-global-section',
			'label'       => esc_html__('Select Footer Layout', 'rey-core'),
			'settings'    => 'footer_layout_type',
			'default'     => 'default',
			'choices'     => [
				'type' => 'footer',
				'global_sections' => apply_filters('reycore/options/footer_layout_options', [], false),
				'gs_desc' => sprintf( esc_html__('Select a Footer Global Section. %s', 'rey-core'), reycore__header_footer_layout_desc('footer') ),
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'custom_footer_width',
			'label'       => esc_html__( 'Custom Footer Width', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'footer_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'custom_footer_width',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( array(
			'type'        		=> 'slider',
			'settings'    		=> 'footer_width',
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
					'property' 		=> '--footer-default--max-width',
					'units'    		=> 'px',
				),
			),
			'active_callback' => [
				[
					'setting'  => 'footer_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'custom_footer_width',
					'operator' => '==',
					'value'    => true,
				],
			],
		));

		$this->add_title( esc_html__('Copyright text', 'rey-core'), [
			'active_callback' => [
				[
					'setting'  => 'footer_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'footer_copyright_automatic',
			'label'       => esc_html__( 'Automated Copyright text', 'rey-core' ),
			'default'     => '1',
			'active_callback' => [
				[
					'setting'  => 'footer_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$this->add_control( array(
			'type'        => 'textarea',
			'settings'    => 'footer_copyright',
			'label'       => esc_html__('Copyright text', 'rey-core'),
			'description' => __('Enter the text that will appear in footer as copyright. <small>Note: It accepts HTML tags.</small>', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'footer_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'footer_copyright_automatic',
					'operator' => '==',
					'value'    => false,
				],
			],
			'partial_refresh'    => [
				'header_site_title' => [
					'selector'        => '.rey-siteFooter__copyright',
					'render_callback' => function() {
						return get_theme_mod('footer_copyright', '');
					},
				],
			],
		));


	}
}
