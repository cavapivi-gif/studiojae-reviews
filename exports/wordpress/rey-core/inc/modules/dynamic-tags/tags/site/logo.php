<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Logo extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'site-logo',
			'title'      => esc_html__( 'Site Logo Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Logo type (Customizer > Header > Logo)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'custom_logo',
				'options' => [
					'custom_logo' => esc_html__('Default logo', 'rey-core'),
					'logo_mobile' => esc_html__('Mobile custom logo', 'rey-core')
				],
			]
		);

	}

	public function get_value( $options = [] )
	{
		if( ! ($key = $this->get_settings('type')) ){
			return [];
		}

		if( ! ($image_id = absint(rey__get_option($key, ''))) ){
			return [];
		}

		return [
			'id' => $image_id,
			'url' => wp_get_attachment_image_src($image_id, 'full'),
		];
	}

}
