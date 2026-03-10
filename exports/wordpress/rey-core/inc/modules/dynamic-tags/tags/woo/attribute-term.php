<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AttributeTerm extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-attribute-term',
			'title'      => esc_html__( 'Product Attribute Term', 'rey-core' ),
			'categories' => [ 'text', 'url', 'color', 'number', 'datetime' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {

		TagDynamic::woo_product_control($this);

		$this->add_control(
			'attribute',
			[
				'label'       => esc_html__( 'Attribute Item', 'rey-core' ),
				'default'     => '',
				'type'        => 'rey-query',
				'label_block' => true,
				'query_args'  => [
					'type'     => 'terms',
					'taxonomy' => 'product_taxonomies',
				],
			]
		);

		$this->add_control(
			'display',
			[
				'label' => esc_html__( 'Display', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''     => esc_html__( 'Title', 'rey-core' ),
					'desc' => esc_html__( 'Description', 'rey-core' ),
					'count' => esc_html__( 'Count', 'rey-core' ),
					'url'  => esc_html__( 'URL', 'rey-core' ),
					'meta' => esc_html__( 'Meta', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'meta_key',
			[
				'label' => esc_html__( 'Meta Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'display' => 'meta',
				],
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Attribute Term}', 'rey-core' ) );
		}

		$settings = $this->get_settings();

		if( ! ($term_id = $settings['attribute']) ){
			return;
		}

		$term_obj = get_term_by( 'term_taxonomy_id', $term_id );

		if( ! isset($term_obj->name) ){
			return;
		}

		// product must have this term
		if( ! has_term( $term_id, $term_obj->taxonomy, $product->get_id() ) ){
			return;
		}

		$data = $term_obj->name;

		if( $display = $settings['display'] ){

			if( 'desc' === $display ){
				$data = $term_obj->description;
			}

			if( 'count' === $display ){
				$data = $term_obj->count;
			}

			else if( 'url' === $display ){
				if( ($term_link = get_term_link( $term_id, $term_obj->taxonomy )) && is_string($term_link) ){
					$data = $term_link;
				}
			}

			else if( 'meta' === $display ){

				if( (($meta_key = $settings['meta_key']) && ($term_meta = get_term_meta($term_id, $meta_key, true))) ){
					if( is_string($term_meta) ){
						$data = $term_meta;
					}
					else if( is_array($term_meta) ){
						$data = implode(', ', $term_meta);
					}
				}

			}
		}

		echo wp_kses_post($data);

	}

}
