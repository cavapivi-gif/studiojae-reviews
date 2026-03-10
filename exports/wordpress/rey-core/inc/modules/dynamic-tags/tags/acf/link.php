<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Link extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	const TYPES = [
		'link',
		'page_link',
		'file',
	];

	public static function __config() {
		return [
			'id'         => 'acf-link',
			'title'      => esc_html__( 'ACF Link', 'rey-core' ),
			'categories' => [ 'url' ],
			'group'      => TagDynamic::GROUPS_ACF,
		];
	}

	protected function register_controls() {

		TagDynamic::acf_field_control( $this, self::TYPES);

	}

	public function get_value( $options = [] )
	{
		$settings = $this->get_settings();

		if( ! ($field = $settings['field']) ){
			return;
		}

		$config = [
			'key'   => '',
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

		$data = \ReyCore\ACF\Helper::get_field_from_elementor($config);

		if( ! $data ){
			return;
		}

		if( is_array( $data ) && isset($data['url']) ){
			return $data['url'];
		}

		return $data;

	}

	public function get_panel_template_setting_key() {
		return TagDynamic::ACF_CONTROL;
	}

}
