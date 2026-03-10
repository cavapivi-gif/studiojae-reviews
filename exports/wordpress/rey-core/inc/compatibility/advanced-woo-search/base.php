<?php
namespace ReyCore\Compatibility\AdvancedWooSearch;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	/**
	 * Advanced Woo Search
	 * https://wordpress.org/plugins/advanced-woo-search/
	 */

	public function __construct()
	{
		add_action( 'rey/search_form', [ $this, 'search_form' ], 10);
		add_action( 'reycore/woocommerce/search/pre_request', [ $this, 'before_request' ], 10, 3);
		// add_filter( 'aws_js_seamless_selectors', [$this, 'js_seamless_selectors'] );
	}

	function search_form(){
		echo '<input type="hidden" name="type_aws" value="true">';
	}

	function before_request( $results, $search_string, $rey_search ){

		$aws_results = aws_search($search_string);

		if( ! (isset($aws_results['products']) && ! empty($aws_results['products'])) ){
			return $results;
		}

		$products_ids = array_slice( wp_list_pluck($aws_results['products'], 'id'), 0, 5, true);

		$query = new \WP_Query( [
			'post_type' => 'product',
			'post__in' => $products_ids,
			'suppress_filters' => true,
		] );

		return $rey_search->json_results( $query );
	}

	function js_seamless_selectors($selectors){
		$selectors[] = '.rey-searchForm form';
		return $selectors;
	}

}
