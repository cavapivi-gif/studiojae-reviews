<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Author extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-author',
			'title'      => esc_html__( 'Post Author Meta', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'meta',
			[
				'label' => esc_html__( 'Meta data', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'display_name',
				'options' => [
					'display_name'  => esc_html__( 'Display Name', 'rey-core' ),
					'description'  => esc_html__( 'Description', 'rey-core' ),
					'first_name'  => esc_html__( 'First Name', 'rey-core' ),
					'last_name'  => esc_html__( 'Last Name', 'rey-core' ),
					'nickname'  => esc_html__( 'Nickname', 'rey-core' ),
				],
			]
		);

	}

	public function render()
	{
		echo wp_kses_post( get_the_author_meta( $this->get_settings('meta') ) );
	}

}
