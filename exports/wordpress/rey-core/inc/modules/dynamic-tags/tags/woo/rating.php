<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Rating extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-rating',
			'title'      => esc_html__( 'Product Rating', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {

		TagDynamic::woo_product_control($this);

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Display type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'review',
				'options' => [
					'review'  => esc_html__( 'Review Count', 'rey-core' ),
					'rating'  => esc_html__( 'Rating Count', 'rey-core' ),
					'average'  => esc_html__( 'Average rating', 'rey-core' ),
					'stars'  => esc_html__( 'Stars', 'rey-core' ),
				],
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Rating}', 'rey-core' ) );
		}

		if ( ! wc_review_ratings_enabled() ) {
			return;
		}

		$settings = $this->get_settings();

		if( ! ($type = $settings['type']) ){
			return;
		}

		$data = '';

		if( 'review' === $type ){
			$data = $product->get_review_count();
		}
		elseif( 'rating' === $type ){
			$data = $product->get_rating_count();
		}
		elseif( 'average' === $type ){
			$data = $product->get_average_rating();
		}
		elseif( 'stars' === $type ){
			echo wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() );
		}

		echo wp_kses_post( $data );
	}

}
