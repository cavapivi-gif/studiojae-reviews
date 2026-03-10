<?php
namespace ReyCore\Modules\ProductGridCustomTax;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const KEY = 'custom_tax__';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'elementor/element/reycore-product-grid/section_query/before_section_end', [ $this, 'add_controls' ] );
		add_filter( 'reycore/elementor/product_grid/query_args', [ $this, 'query_args' ], 20, 3 );

	}

	public function get_product_taxonomies(){

		$custom_taxs = array_diff(
			array_filter( get_object_taxonomies( 'product' ), function($el){
				return strpos($el, 'pa_') === false;
			}),
			[
				'product_type',
				'product_visibility',
				'product_shipping_class',
				'product_cat', // already has control
				'product_tag', // already has control
			]
		);

		if( ! empty($custom_taxs) ){
			return $custom_taxs;
		}

		return [];
	}

	public function add_controls( $element ){

		foreach ($this->get_product_taxonomies() as $custom_tax) {

			$custom_tax_taxonomy = get_taxonomy($custom_tax);

			if( ! is_wp_error($custom_tax_taxonomy) && $custom_tax_taxonomy instanceOf \WP_Taxonomy ){

				$element->start_injection( [
					'of' => 'orderby',
				] );

				$element->add_control(
					self::KEY . $custom_tax,
					[
						'label' => sprintf( esc_html__( 'Select one or more %s terms', 'rey-core' ), $custom_tax_taxonomy->label ),
						'placeholder' => esc_html__('- Select -', 'rey-core'),
						'type' => 'rey-query',
						'query_args' => [
							'type' => 'terms',
							'taxonomy' => $custom_tax,
							'field' => 'id'
						],
						'multiple' => true,
						'default' => [],
						'label_block' => true,
						'condition' => [
							'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
						],
					]
				);

				$element->end_injection();

			}

		}

	}

	public function query_args( $query_args, $element_id, $settings){

		foreach ($this->get_product_taxonomies() as $custom_tax) {

			if( isset($settings[self::KEY . $custom_tax]) && ($terms = $settings[self::KEY . $custom_tax]) ){

				if( ! taxonomy_exists($custom_tax) ){
					continue;
				}

				$query_args['tax_query'][] = [
					'taxonomy' => $custom_tax,
					'field'    => 'term_id',
					'terms'    => $terms,
					'operator' => 'IN',
				];
			}

		}

		return $query_args;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Grid - Custom Taxonomies', 'Module name', 'rey-core'),
			'description' => esc_html_x('Products Grid element, adds support for querying custom taxomomies.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', self::KEY );

		return ! empty( $results );

	}
}
