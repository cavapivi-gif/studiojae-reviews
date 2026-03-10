<?php
namespace ReyCore\Modules;

use ReyCore\Modules\Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class ModuleBase {

	/**
	 * Module data
	 *
	 * @var array
	 */
	private static $module_data = [];

	public function __construct(){}

	/**
	 * Get module path
	 *
	 * @return string
	 */
	public static function get_path( $path ){
		return sprintf('%sinc/modules/%s', REY_CORE_URI, $path);
	}

	/**
	 * Holds the module configuration
	 *
	 * @return array
	 */
	abstract protected static function __config();

	/**
	 * Module title
	 *
	 * @return string
	 */
	public function get_title() {
		return static::get_module_data( 'title' );
	}

	/**
	 * Module unique ID
	 *
	 * @return string
	 */
	public function get_id() {
		return static::get_module_data( 'id' );
	}

	/**
	 * Retrieve module data
	 *
	 * @return array
	 */
	public static function get_module_data( $key = '' ){

		$config = static::__config();

		if( ! ( is_array($config) && ! empty( $config ) ) ){
			return;
		}

		$module_data = wp_parse_args($config, [
			'id'              => '',
			'title'           => '',
			'icon'            => '',
			'categories'      => ['misc'],
			'keywords'        => [],
			'description'     => '',
			'help'            => '',
			'show_in_manager' =>  static::show_in_manager()
		]);

		return $key ? $module_data[$key] : $module_data;

	}

	/**
	 * Whether to show in the Modules Manager
	 *
	 * @return bool
	 */
	public static function can_run(){
		return true;
	}

	/**
	 * Whether to show in the Modules Manager
	 *
	 * @return bool
	 */
	public static function show_in_manager(){
		return true;
	}

	/**
	 * Logic to determine if the module is actually being used.
	 * For use in Modules Manager scan.
	 *
	 * @return bool
	 */
	public function module_in_use(){
		return true;
	}

	/**
	 * Retrieves the module instance
	 *
	 * @return object
	 */
	public static function instance(){
		$config = static::__config();
		return \ReyCore\Plugin::instance()->modules->get_module( $config['id'] );
	}
}
