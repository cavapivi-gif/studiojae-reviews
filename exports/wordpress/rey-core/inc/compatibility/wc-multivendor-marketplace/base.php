<?php
namespace ReyCore\Compatibility\WcMultivendorMarketplace;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter( 'reycore/load_more_pagination_args', [$this, 'handle_pagination'], 20);
	}

	public function handle_pagination($params){

		if( isset($params['target']) ){
			$params['target'] .= ', #products-wrapper ul.products';
		}

		return $params;
	}

}
