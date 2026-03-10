<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AuthorLink extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-author-link',
			'title'      => esc_html__( 'Post Author URL', 'rey-core' ),
			'categories' => [ 'url' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Link type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'website',
				'options' => [
					'website'  => esc_html__( 'Website', 'rey-core' ),
					'posts'  => esc_html__( 'Posts URL', 'rey-core' ),
				],
			]
		);

	}

	public function render()
	{
		$type = $this->get_settings('type');

		if ( 'website' === $type ) {
			$author_url = get_the_author_meta( 'url' );
		}
		elseif ( 'posts' === $type ) {
			global $authordata;
			$author_url = get_author_posts_url( isset( $authordata->ID ) ? $authordata->ID : 0 );
		}

		echo esc_url( $author_url );
	}

}
