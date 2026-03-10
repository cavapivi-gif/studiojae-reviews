<?php
namespace ReyCore\Compatibility\WpHide;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_action('reycore/ajax/before_send_success', [$this, 'ajax_compatibility']);
	}

	function ajax_compatibility($ajax){

		global $wph;

		if ( ! isset($wph) || is_null($wph)) {
			return;
		}

		if( ! method_exists($wph, 'proces_html_buffer') ){
			return;
		}

		$response = [
		  'success' => true,
		  'data' => $wph->proces_html_buffer( $ajax->get_response_data() ),
		];

		$json = wp_json_encode( $response );

		$accept_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

		if ( function_exists( 'gzencode' ) && strpos( $accept_encoding, 'gzip' ) !== false ) {
		  $response = gzencode( $json );

		  header( 'Content-Type: application/json; charset=utf-8' );
		  header( 'Content-Encoding: gzip' );
		  header( 'Content-Length: ' . strlen( $response ) );

		  echo $response; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
		  header( 'Content-Type: application/json; charset=utf-8' );
		  echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		wp_die( '', '', [ 'response' => null ] );

	  }

}
