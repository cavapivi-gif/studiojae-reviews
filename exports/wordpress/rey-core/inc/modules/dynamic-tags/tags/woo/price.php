<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Price extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-price',
			'title'      => esc_html__( 'Product Price', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Price type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'active',
				'options' => [
					'active'  => esc_html__( 'Product Active price', 'rey-core' ),
					'regular'  => esc_html__( 'Product Regular price', 'rey-core' ),
					'sale'  => esc_html__( 'Product Sale price', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'currency',
			[
				'label' => esc_html__( 'Show Currency', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Price}', 'rey-core' ) );
		}

		$settings = $this->get_settings();

		$price = '';

		if( $type = $settings['type'] ){

			if( 'active' === $type ){
				$price = $product->get_price();
			}
			if( 'regular' === $type ){
				$price = $product->get_regular_price();
			}
			if( 'sale' === $type ){
				$price = $product->get_sale_price();
			}

		}

		if( $price ){

			if( '' !== $settings['currency'] ){
				$price = wc_price($price, [ 'ex_tax_label' => false ]);
			}

			echo wp_strip_all_tags( $price );
		}
	}

}
