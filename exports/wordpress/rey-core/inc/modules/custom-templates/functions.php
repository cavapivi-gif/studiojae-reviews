<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore_rt__get_post_types')):
	/**
	 * Get post types list
	 *
	 * @since 2.1.0
	 **/
	function reycore_rt__get_post_types()
	{
		if( ! ($new_choices = reycore__get_post_types_list()) ){
			return [];
		}

		unset($new_choices['product']);
		unset($new_choices['page']);

		return $new_choices;
	}
endif;


if(!function_exists('reycore_rt__get_cpt')):
/**
 * Get custom post types
 *
 * @since 2.1.0
 **/
function reycore_rt__get_cpt()
{
	$ctps = [];

	$post_types = reycore_rt__get_post_types();

	if( empty($post_types) ){
		return $ctps;
	}

	// get rid of posts (only custom)
	unset($post_types['post']);

	foreach ($post_types as $post_type => $post_type_title) {

		$ctps[$post_type] = [
			'post_field_name' => $post_type . '_posts',
			'post_type' => $post_type,
			'post_type_title' => $post_type_title,
			'post_choices' => [],
			'taxonomies' => [],
		];

		$posts = get_posts([
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'post_type'        => $post_type,
			'post_status'      => 'publish',
		]);

		foreach ($posts as $post) {
			$ctps[$post_type]['post_choices'][$post->ID] = $post->post_title;
		}

		// taxonomies
		$taxes = get_object_taxonomies($post_type, 'objects');

		foreach ($taxes as $tax) {

			$terms = get_terms( [
				'taxonomy' => $tax->name,
				'hide_empty' => false,
			]);

			if( empty($terms) ){
				continue;
			}

			$tax_data = [
				'tax_name' => $tax->name,
				'tax_label' => $tax->label,
				'tax_field_name' => $tax->name . '_taxes',
				'tax_choices' => [],
			];

			foreach ($terms as $term) {
				$tax_data['tax_choices'][$term->term_id] = $term->name;
			}

			$ctps[$post_type]['taxonomies'][] = $tax_data;
		}

	}

	return $ctps;
}
endif;


add_filter( 'reycore/ajaxfilters/js_params', function ($params){

	if( isset($params['shop_loop_container']) ){
		$params['shop_loop_container'] .= ', .elementor-widget-reycore-woo-loop-products .reyajfilter-before-products';
	}

	if( isset($params['not_found_container']) ){
		$params['not_found_container'] .= ', .elementor-widget-reycore-woo-loop-products .reyajfilter-before-products';
	}

	return $params;
}, 20);

add_filter( 'reycore/load_more_pagination_args', function ($params){

	if( isset($params['target']) ){
		$params['target'] = $params['target'] . ', .elementor-widget-reycore-woo-loop-products ul.products';
	}

	return $params;
}, 20);
