<?php
namespace ReyCore\Customizer\Options\Cover;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Frontpage extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'cover-frontpage';
	}

	public function get_title(){
		return esc_html__('Frontpage', 'rey-core');
	}

	public function get_priority(){
		return 10;
	}

	public function get_icon(){
		return 'cover-frontpage';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-page-cover/#frontpage');
	}

	public function controls(){

		$this->add_title( esc_html__('Frontpage', 'rey-core'), [
			'description' => esc_html__('These settings will apply on your website\'s Frontpage, assigned in Customizer > General Settings > Homepage Settings.', 'rey-core') . '<br><br>' . \ReyCore\Customizer\Options\Cover::get_main_desc(),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__frontpage',
			'label'       => esc_html__( 'Select a Page Cover layout', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no' => 'Disabled',
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );


	}
}
