<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Terms extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-terms',
			'title'      => esc_html__( 'Post Terms', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	/**
	 * Get an array of publicly-querable taxonomies.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_taxonomies() {

		$items = [];

		foreach ( get_post_taxonomies() as $taxonomy ) {
			$id           = $taxonomy;
			$taxonomy     = get_taxonomy( $id );
			if( ! is_wp_error($taxonomy) ){
				$items[ $id ] = $taxonomy->labels->name;
			}
		}

		return $items;
	}

	protected function register_controls() {

		$this->add_control(
			'tax',
			[
				'label' => esc_html__( 'Taxonomy', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'category',
				'options' => self::get_taxonomies(),
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => esc_html__( 'Limit to first?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

	}

	public function render()
	{
		$settings = $this->get_settings();

		$terms = get_the_terms( get_the_ID(), $settings['tax'] );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( empty( $terms ) ) {
			return;
		}

		$links = [];

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $settings['tax'] );
			if ( ! is_wp_error( $link ) ) {
				$links[] = sprintf('<a href="%1$s" rel="%3$s">%2$s</a>', esc_url( $link ), $term->name, esc_attr($settings['tax']));
			}
			if( '' !== $settings['limit'] ){
				break;
			}
		}

		echo implode( $settings['separator'], $links );
	}
}
