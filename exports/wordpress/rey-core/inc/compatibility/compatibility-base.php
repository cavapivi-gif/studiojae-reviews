<?php
namespace ReyCore\Compatibility;

use ReyCore\Compatibility\Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class CompatibilityBase {

	public $path;

	public function __construct(){}

	/**
	 * Get module path
	 *
	 * @return string
	 */
	public static function get_path( $path ){
		return sprintf('%sinc/compatibility/%s', REY_CORE_URI, $path);
	}

}
