<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ImageData extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-image-data',
			'title'      => esc_html__( 'Post Image Data', 'rey-core' ),
			'categories' => [ 'url', 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'data',
			[
				'label' => esc_html__( 'Data', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'src',
				'options' => [
					'src'  => esc_html__( 'Source', 'rey-core' ),
				],
			]
		);

	}

	public function render()
	{

		$settings = $this->get_settings();

		switch ($settings['data']) {
			case 'src':
				echo get_the_post_thumbnail_url();
				break;
			default:
				break;
		}

	}

}
