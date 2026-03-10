<?php
namespace ReyCore\Modules\DiscountBadge;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DynamicTag extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-discount',
			'title'      => esc_html__( 'Product Discount', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {

		TagDynamic::woo_product_control($this);

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Label type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'percentage'  => esc_html__( 'Percentage', 'rey-core' ),
					'save'  => esc_html__( 'Save $$', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'sale_text',
			[
				'label' => esc_html__( 'Sale text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'type' => 'sale',
				],
			]
		);

		$this->add_control(
			'save_text',
			[
				'label' => esc_html__( 'Save text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'type' => 'save',
				],
			]
		);

		$this->add_control(
			'perc_text',
			[
				'label' => esc_html__( 'Percentage text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'type' => 'percentage',
				],
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return;
		}

		$settings = $this->get_settings();

		$GLOBALS['post'] = get_post( $product->get_id() );
		setup_postdata( $GLOBALS['post'] );

		$args = [
			'label_start' => '',
			'label_end' => '',
		];

		if( $type = $settings['type'] ){

			$args['type'] = $type;

			if( 'save' === $type && $settings['save_text'] ){
				$args['save_text'] = $settings['save_text'];
			}
			else if( 'percentage' === $type && $settings['perc_text'] ){
				$args['percentage_text'] = $settings['perc_text'];
			}
		}

			echo wp_kses_post( Base::get_discount_output($args) );

		wp_reset_postdata();

	}

}
