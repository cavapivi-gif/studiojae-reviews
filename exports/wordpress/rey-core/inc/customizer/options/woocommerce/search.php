<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Search extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-search-page';
	}

	public static function part_of(){
		return 'general-search-page';
	}

	public function get_title(){
		return esc_html__('Search Page (Woo)', 'rey-core');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'multicheck',
			'settings'    => 'search__include',
			'label'       => esc_html__( 'Include taxonomies in search', 'rey-core' ),
			'default'     => [],
			'choices'     => \ReyCore\Customizer\Helper::wc_taxonomies(),
			'help' => [
				esc_html__( 'Please use with attention because it might return too many generic results and will slow down the search query.', 'rey-core' )
			],
			'separator' => 'before',
			'priority' => 60,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'search__sku',
			'label'       => esc_html__( 'Search Product SKU', 'rey-core' ),
			'default'     => 'yes',
			'choices'     => [
				'yes' => esc_html__( 'Yes - in Parent products only', 'rey-core' ),
				'yes-variations' => esc_html__( 'Yes - in Parents & Variations', 'rey-core' ),
				'yes-variations-only' => esc_html__( 'Yes - Only in Variations', 'rey-core' ),
				'no' => esc_html__( 'No', 'rey-core' ),
			],
			'priority'    => 70,
			'help' => [
				esc_html__( 'Select where to search for the SKU code in the products. May slow down the search.', 'rey-core' )
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'search__sku_show',
			'label'       => esc_html__( 'Product types to show', 'rey-core' ),
			'default'     => 'parents',
			'choices'     => [
				'parents' => esc_html__( 'Parents', 'rey-core' ),
				'variations' => esc_html__( 'Variations', 'rey-core' ),
				'both' => esc_html__( 'Both Parents and Variations', 'rey-core' ),
			],
			'priority'    => 70,
			'help' => [
				esc_html__( 'This option will determine what type of products results to show, whether Parent products, their Variations, or both.', 'rey-core' )
			],
			'active_callback' => [
				[
					'setting'  => 'search__sku',
					'operator' => 'in',
					'value'    => ['yes-variations', 'yes-variations-only'],
				],
			],
		] );

		$this->add_title( esc_html__('EMPTY RESULTS PAGE', 'rey-core'), [
			'priority' => 80,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_search_empty__gs',
			'label'       => esc_html__( 'Show Global Section', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => '- None -'
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
			'priority' => 90,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_search_empty__mode',
			'label'       => esc_html__( 'Mode', 'rey-core' ),
			'default'     => 'overwrite',
			'choices'     => [
				'overwrite' => esc_html__( 'Overwrite Content', 'rey-core' ),
				'before' => esc_html__( 'Add Before', 'rey-core' ),
				'after' => esc_html__( 'Add After', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'loop_search_empty__gs',
					'operator' => '!=',
					'value'    => '',
				],
			],
			'priority' => 100,
		] );

	}
}
