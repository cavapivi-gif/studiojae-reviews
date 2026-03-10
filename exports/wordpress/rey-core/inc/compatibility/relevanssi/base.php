<?php
namespace ReyCore\Compatibility\Relevanssi;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action('reycore/woocommerce/search/search_products_query', [$this, 'search_query']);
		add_filter('reycore/woocommerce/components_add_remove/priority', [$this, 'change_loop_components_priority']);
		add_filter('reycore/woocommerce_get_filtered_term_product_counts_query', 'relevanssi_filtered_term_product_counts_query', 100 );
	}

	public function search_query( $the_query ) {
		relevanssi_do_query( $the_query );
	}

	function change_loop_components_priority(){
		return 9;
	}
}
