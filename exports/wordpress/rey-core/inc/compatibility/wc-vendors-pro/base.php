<?php
namespace ReyCore\Compatibility\WcVendorsPro;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter('reycore/search/enable_sku', [$this, 'posts_clauses_conflict']);
	}

	function posts_clauses_conflict( $status ){

		global $post;

		if( ! $post ){
			return $status;
		}

		// check post content has vendor dashboard pro shortcode
		if( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wcv_pro_dashboard' ) ) {
			return false;
		}

		return $status;
	}

}
