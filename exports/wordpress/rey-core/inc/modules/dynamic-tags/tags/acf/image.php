<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Image extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	const TYPES = [
		'image',
	];

	public static function __config() {
		return [
			'id'         => 'acf-image',
			'title'      => esc_html__( 'ACF Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => TagDynamic::GROUPS_ACF,
		];
	}

	protected function register_controls() {
		TagDynamic::acf_field_control( $this, self::TYPES);

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
			]
		);
	}

	public function get_value( $options = [] )
	{
		$settings = $this->get_settings();

		$config = [
			'key'   => '',
		];

		$config['key'] = $settings['field'];

		if( empty($config['key']) ){

			$config['key'] = $settings['field_custom_key'];

			if( empty($config['key']) ){
				return [];
			}

		}

		if( isset($settings['index']) ){
			$config['index'] = ! empty($settings['index']) ? absint($settings['index']) : 1;
		}

		$data = \ReyCore\ACF\Helper::get_field_from_elementor($config);

		if( ! $data ){

			if( !empty($settings['fallback']['id']) ){
				return [
					'id' => $settings['fallback']['id'],
					'url' => $settings['fallback']['url'],
				];
			}

			return [];
		}

		if( is_array( $data ) && isset($data['url']) ){ // Array
			return [
				'id' => $data['id'],
				'url' => $data['url'],
			];
		}
		else if( is_numeric($data) ){ // ID
			return [
				'id'  => $data,
				'url' => wp_get_attachment_image_src($data, 'full'),
			];
		}

		return [
			'id'  => attachment_url_to_postid($data),
			'url' => $data,
		];

	}

	public function get_panel_template_setting_key() {
		return TagDynamic::ACF_CONTROL;
	}

}
