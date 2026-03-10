<?php
namespace ReyCore\Compatibility\IndeedAffiliatePro;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter('rey/main_script_params', [$this, 'main_script_params'], 20);
	}

	public function main_script_params($params){
		$params['js_params']['select2_overrides'] = false;
		return $params;
	}

}
