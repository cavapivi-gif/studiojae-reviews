<?php
namespace ReyCore\Libs\PluginsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {

	public static function fs(){

		static $fs;

		if( is_null($fs) ){
			$fs = reycore__wp_filesystem();
		}

		return $fs;
	}

}
