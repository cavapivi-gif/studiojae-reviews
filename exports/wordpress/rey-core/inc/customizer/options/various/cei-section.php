<?php
namespace ReyCore\Customizer\Options\Various;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class CeiSection extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'rey-cei-section';
	}

	public static function part_of(){
		return 'cei-section';
	}

	public function get_title(){
		return esc_html__('CEI', 'rey-core');
	}

	public function get_priority(){
		return 1;
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'rey_transfer_mods_from_parent',
			'label'       => esc_html__( 'Copy settings from Parent theme', 'rey-core' ),
			'description' => esc_html__( 'In case you just switched from the Parent theme, you can easily transfer the settings. Make sure to export your current settings first.', 'rey-core' ),
			'default'     => 'parent',
			'choices'     => [
				'text' => esc_html__('Copy Settings', 'rey-core'),
				'action' => 'rey_transfer_mods',
				'class' => '--btn-full',
			],
		] );

		$this->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'rey_transfer_mods_from_child',
			'label'       => esc_html__( 'Copy settings from Child theme', 'rey-core' ),
			'description' => esc_html__( 'In case you just switched from the Child theme, you can easily transfer the settings. Make sure to export your current settings first.', 'rey-core' ),
			'default'     => 'child',
			'choices'     => [
				'text' => esc_html__('Copy Settings', 'rey-core'),
				'action' => 'rey_transfer_mods',
				'class' => '--btn-full',
			],
		] );


	}
}
