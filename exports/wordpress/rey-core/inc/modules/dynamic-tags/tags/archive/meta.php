<?php
namespace ReyCore\Modules\DynamicTags\Tags\Archive;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Meta extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'archive-meta',
			'title'      => esc_html__( 'Archive Meta', 'rey-core' ),
			'categories' => [ 'text', 'url', 'media', 'post_meta', 'gallery', 'number', 'color', 'datetime', ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_ARCHIVE,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'custom_key',
			[
				'label' => esc_html__( 'Meta Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);

	}

	public function render() {

		if( ! ($key = $this->get_settings('custom_key')) ){
			return;
		}

		echo get_term_meta(get_queried_object_id(), $key, true);
	}

}
