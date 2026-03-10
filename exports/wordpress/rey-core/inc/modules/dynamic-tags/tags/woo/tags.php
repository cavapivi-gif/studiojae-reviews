<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Tags extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-tags',
			'title'      => esc_html__( 'Product Tags', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {

		TagDynamic::woo_product_control($this);

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

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Tags}', 'rey-core' ) );
		}

		$settings = $this->get_settings();

		$terms = get_the_terms( $product->get_id(), 'product_tag' );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( empty( $terms ) ) {
			return;
		}

		$links = [];

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, 'product_tag' );
			if ( ! is_wp_error( $link ) ) {
				$links[] = sprintf('<a href="%1$s">%2$s</a>', esc_url( $link ), $term->name);
			}
			if( '' !== $settings['limit'] ){
				break;
			}
		}

		echo implode( $settings['separator'], $links );
	}

}
