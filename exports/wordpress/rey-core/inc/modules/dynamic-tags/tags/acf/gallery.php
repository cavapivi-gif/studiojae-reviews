<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Gallery extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	const TYPES = [
		'gallery',
	];

	public static function __config() {
		return [
			'id'         => 'acf-gallery',
			'title'      => esc_html__( 'ACF Gallery', 'rey-core' ),
			'categories' => [ 'gallery' ],
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
				return [];
			}

		}

		if( isset($settings['index']) ){
			$config['index'] = ! empty($settings['index']) ? absint($settings['index']) : 1;
		}

		$data = \ReyCore\ACF\Helper::get_field_from_elementor($config);

		if( empty($data) ){
			return [];
		}

		$set = [];

		foreach ($data as $item) {

			if( is_array( $item ) ){
				if( ! empty($item['url']) ){
					$set[] = [
						'id' => $item['id'],
						'url' => $item['url'],
					];
				}
			}

			else if( is_numeric($item) ){ // ID
				$set[] = [
					'id'  => $item,
					'url' => wp_get_attachment_image_src($item, 'full'),
				];
			}

			else {
				$set[] = [
					'id'  => attachment_url_to_postid($item),
					'url' => $item,
				];
			}

		}

		return $set;
	}

	public function get_panel_template_setting_key() {
		return TagDynamic::ACF_CONTROL;
	}

}
