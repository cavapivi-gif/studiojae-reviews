<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Api;

class Download extends TaskBase
{
	public function get_id(){
		return 'download';
	}

	public function get_status(){
		return esc_html__('Downloading...', 'rey-core');
	}

	public function run(){

		if( ! ( ($config = get_transient('rey_demo_config')) && ! empty($config['demo']) ) ){
			return $this->add_error( 'Cannot retrieve configuration.' );
		}

		if(  ! ($filepath = Api::get_download_url( $config['demo'] )) ){
			return $this->add_error( 'Cannot retrieve download url.' );
		}

		if ( ! function_exists( 'download_url' ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
		}

		// Download file to temporary location.
		$downloaded_path = download_url( $filepath );

		// Make sure there were no errors.
		if ( is_wp_error( $downloaded_path ) ) {
			return $this->add_error( sprintf('Cannot download demo file from URL. %s', $downloaded_path->get_error_message() ) );
		}

		set_transient('rey_demo_dld_path', $downloaded_path, $this->importer::TRANSIENT_EXPIRATION);

	}
}
