<?php
namespace ReyCore\Libs\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Api {

	const DEMOS_LIST_OPTION = 'reycore_demos';

	public function __construct(){
	}

	public static function get_demos(){

		$demos = [];

		if( ! (class_exists('\ReyTheme_API') && reycore__get_purchase_code()) ) {
			return $demos;
		}

		if( false !== ($demos = get_option( self::DEMOS_LIST_OPTION )) ) {
			return $demos;
		}

		add_filter('rey/api/remote_get/args', [__CLASS__, 'api_append_version'], 10, 2);

		$request = \ReyTheme_API::getInstance()->get_demos();

		remove_filter('rey/api/remote_get/args', [__CLASS__, 'api_append_version'], 10, 2);

		if ( is_wp_error( $request ) ) {
			$demos = $request;
		}
		else {
			if ( isset($request['data']) && is_array($request['data']) && ! empty($request['data']))  {
				$demos = array_map('reycore__clean', $request['data'] );
			}
		}

		update_option( self::DEMOS_LIST_OPTION, $demos, false );

		return $demos;
	}

	/**
	 * Add presigned demo download url.
	 * This will get the expiring demo data link url.
	 *
	 * @param string $slug
	 * @since 1.0.0
	 * @return mixed
	 */
	public static function get_download_url( $slug ){

		if( $slug && reycore__get_purchase_code() ) {

			add_filter('rey/api/remote_get/args', [__CLASS__, 'api_append_version'], 10, 2);

			$request = \ReyTheme_API::getInstance()->get_demo_data( $slug );

			remove_filter('rey/api/remote_get/args', [__CLASS__, 'api_append_version'], 10, 2);

			if ( ! is_wp_error( $request ) ) {
				if ( isset($request['data']) && ! empty($request['data']))  {
					return $request['data'];
				}
			}
		}

		return false;
	}

	public static function api_append_version( $args, $endpoint ){
		$args['body']['version'] = 2;
		return $args;
	}

}
