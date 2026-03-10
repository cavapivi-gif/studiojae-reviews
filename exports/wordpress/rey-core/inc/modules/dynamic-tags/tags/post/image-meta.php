<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ImageMeta extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'post-image-meta',
			'title'      => esc_html__( 'Image from Post Meta-key', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$post_custom_keys = get_post_custom_keys();

		if( is_null($post_custom_keys) ){
			return;
		}

		if( ! is_array($post_custom_keys) ){
			$post_custom_keys = [];
		}

		$keys = array_filter($post_custom_keys, function($item){
			return strpos($item, '_') !== 0;
		});

		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Meta Keys', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array_merge(array_combine($keys, $keys), [
					''  => esc_html__( '- Select -', 'rey-core' ),
					'custom'  => esc_html__( '- Custom -', 'rey-core' ),
				]),
			]
		);

		$this->add_control(
			'custom_key',
			[
				'label' => esc_html__( 'Custom Meta Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'key' => 'custom',
				],
			]
		);

	}

    public function get_value( $options = [] ){

		$settings = $this->get_settings();

		$key = $settings['key'];

		if( 'custom' === $key && ($custom_key = $settings['custom_key']) ){
			$key = $custom_key;
		}

		if( ! $key ){
			return [];
		}

		$att_id = get_post_meta(get_the_ID(), $key, true);

		if( ! is_numeric($att_id) ){
			return [];
		}

		return [
			'id' => absint($att_id),
			'url' => wp_get_attachment_image_src($att_id, 'full'),
		];

	}

}
