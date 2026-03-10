<?php
namespace ReyCore\Modules\CookieNotice;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'cookie-notice';
	}

	public function get_title(){
		return esc_html__('Cookie Notice', 'rey-core');
	}

	public function get_priority(){
		return 130;
	}

	public function get_icon(){
		return 'woo-store-notice';
	}

	public function help_link(){
		return reycore__support_url('kb/cookie-notice/');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cookie_notice__enable',
			'label'       => esc_html__( 'Select style', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'side-box' => esc_html__( 'Side box', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'textarea',
			'settings'    => 'cookie_notice__text',
			'label'       => esc_html__( 'Text', 'rey-core' ),
			'default'     => __('In order to provide you a personalized shopping experience, our site uses cookies. By continuing to use this site, you are agreeing to our cookie policy.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'cookie_notice__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
			'input_attrs' => [
				'data-control-class' => '--text-xl',
			],
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'cookie_notice__btn_text',
			'label'       => esc_html__( 'Button text', 'rey-core' ),
			'default'     => esc_html__('ACCEPT', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'cookie_notice__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'cookie_notice__bg_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--cookie-bg-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'cookie_notice__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'cookie_notice__text_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--cookie-text-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'cookie_notice__enable',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

	}
}
