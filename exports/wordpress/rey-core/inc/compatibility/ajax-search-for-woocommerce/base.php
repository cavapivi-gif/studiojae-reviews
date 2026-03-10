<?php
namespace ReyCore\Compatibility\AjaxSearchForWoocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	/**
	 * FiboSearch
	 * https://wordpress.org/plugins/ajax-search-for-woocommerce/
	 */

	public function __construct()
	{
		add_filter( 'reycore/ajaxfilters/search_query', [$this, 'search_query']);
		add_filter( 'reycore/ajaxfilters/active_filters/link', [$this, 'reset_link']);
		add_filter( 'reycore/woocommerce/reset_filters_link', [$this, 'reset_link']);
		add_filter( 'woocommerce_price_filter_sql', [$this, 'filter_price_sql']);
	}

	function get_ids_sql(){

		global $wpdb;

		if( $result_post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] ) ) {
			return esc_sql( sprintf(" {$wpdb->posts}.ID IN (%s) ", implode(',', $result_post_ids)) );
		}

		return '';
	}

	function search_query( $search ){

		if( $result_sql = $this->get_ids_sql() ) {
			$search[] = $result_sql;
		}

		return $search;
	}

	function filter_price_sql($sql){

		if( ! class_exists('\DgoraWcas\Helpers') ){
			return $sql;
		}

		if ( ! \DgoraWcas\Helpers::isProductSearchPage() ) {
			return $sql;
		}

		if ( ! \DgoraWcas\Helpers::is_running_inside_class( 'REYAJAXFILTERS_Price_Filter_Widget' ) ) {
			return $sql;
		}

		if( $result_sql = $this->get_ids_sql() ) {
			$sql .= $result_sql;
		}

		return $sql;

	}

	function reset_link($link){

		if ( isset( $_GET['s'] ) ) {
			$link = add_query_arg( ['dgwt_wcas' => '1'], $link );
		}

		return $link;
	}
}
