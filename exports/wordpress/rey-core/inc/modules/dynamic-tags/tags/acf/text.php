<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Text extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	const TYPES = [
		'text',
		'textarea',
		'number',
		'email',
		'wysiwyg',
		'url',
		// might return array
		'select',
		'checkbox',
		'radio',
		// bool
		'true_false',
		// other
		// 'color_picker',
	];

	public static function __config() {
		return [
			'id'         => 'acf-text',
			'title'      => esc_html__( 'ACF Text', 'rey-core' ),
			'categories' => [ 'url', 'text', 'number', 'color' ],
			'group'      => TagDynamic::GROUPS_ACF,
		];
	}

	public function parse_control(){

		$this->add_control(
			'parse',
			[
				'label' => esc_html__( 'Parse Shorcodes & Embed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => false,
			]
		);

	}

	protected function register_controls() {

		TagDynamic::acf_field_control( $this, self::TYPES );

		$this->parse_control();
	}

	public function render()
	{
		$settings = $this->get_settings();

		$config = [
			'key'   => '',
			'parse' => $settings['parse'],
		];

		$config['key'] = $settings['field'];

		if( empty($config['key']) ){

			$config['key'] = $settings['field_custom_key'];

			if( empty($config['key']) ){
				return;
			}

		}

		if( isset($settings['index']) ){
			$config['index'] = ! empty($settings['index']) ? absint($settings['index']) : 1;
		}

		$output = \ReyCore\ACF\Helper::get_field_from_elementor($config);

		if( is_array($output) && ! empty($output) ){
			foreach ($output as $item) {
				if( isset($item['label']) && isset($item['value']) ){ // Both Value & Label
					printf( TagDynamic::get_settings('acf_both_markup'), $item['label'], $item['value']);
				}
				else if (is_string($item)) { // Either Label or Value
					printf( TagDynamic::get_settings('acf_single_markup'), $item);
				}
			}
		}
		else if( is_bool($output) ){
			echo $output ? TagDynamic::get_settings('acf_yes_text') : TagDynamic::get_settings('acf_no_text');
		}
		// likely string
		else {
			echo $output;
		}
	}

	public function get_panel_template_setting_key() {
		return TagDynamic::ACF_CONTROL;
	}

}
