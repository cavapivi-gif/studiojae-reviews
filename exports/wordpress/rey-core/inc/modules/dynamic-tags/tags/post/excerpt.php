<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Excerpt extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-excerpt',
			'title'      => esc_html__( 'Post Excerpt', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'excerpt_length',
			[
				'label' => __( 'Length (word count)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 200,
				'step' => 1,
			]
		);
	}

	public function render()
	{

		$excerpt = wp_kses_post( get_the_excerpt() );
		$settings = $this->get_settings();

		if( $len = $settings['excerpt_length'] ){

			$excerpt = explode(' ', $excerpt, $len);

			if ( count( $excerpt ) >= $len ) {
				array_pop($excerpt);
				$excerpt[] = '&hellip;';
			}

			$excerpt = preg_replace('`\[[^\]]*\]`','', implode(' ',$excerpt));
		}

		echo $excerpt;
	}

}
