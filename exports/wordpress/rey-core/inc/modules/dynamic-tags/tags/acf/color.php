<?php
namespace ReyCore\Modules\DynamicTags\Tags\Acf;

use ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Color extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	const TYPES = [
		'color_picker',
	];

	public static function __config() {
		return [
			'id'         => 'acf-color',
			'title'      => esc_html__( 'ACF Color', 'rey-core' ),
			'categories' => [ 'color' ],
			'group'      => TagDynamic::GROUPS_ACF,
		];
	}

	protected function register_controls() {

		TagDynamic::acf_field_control( $this, self::TYPES );
	}

	public function render()
	{
		$settings = $this->get_settings();

		if( ! ($field = $settings['field']) ){
			return;
		}

		$output = \ReyCore\ACF\Helper::get_field_from_elementor([
			'key'   => $field,
			'provider_aware' => true,
		]);

		echo $output;

	}

	public function get_panel_template_setting_key() {
		return TagDynamic::ACF_CONTROL;
	}

}
