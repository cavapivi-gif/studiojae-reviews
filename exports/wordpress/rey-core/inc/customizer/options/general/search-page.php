<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class SearchPage extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-search-page';
	}

	public function get_title(){
		return esc_html__('Search Page', 'rey-core');
	}

	public function get_priority(){
		return 65;
	}

	public function get_icon(){
		return 'search-page';
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__search_page',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no'  => esc_attr__( 'Disabled', 'rey-core' )
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'search__header_position',
			'label'       => esc_html__( 'Header Position', 'rey-core' ),
			'default'     => 'rel',
			'choices'     => [
				'' => esc_html__( 'Inherit', 'rey-core' ),
				'rel' => esc_html__( 'Relative', 'rey-core' ),
				'absolute' => esc_html__( 'Absolute ( Over Content )', 'rey-core' ),
				'fixed' => esc_html__( 'Fixed (Sticked to top)', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'search__header_text_color',
			'label'       => esc_html__( 'Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> 'body.search-results',
					'property' 		=> '--header-text-color',
				]
			],
		] );

	}
}
