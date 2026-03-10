<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Unzip extends TaskBase
{
	public function get_id(){
		return 'unzip';
	}

	public function get_status(){
		return esc_html__('Unzipping ...', 'rey-core');
	}

	public function run(){

		if( ! ($downloaded_path = get_transient('rey_demo_dld_path')) ){
			return $this->add_error( 'Cannot retrieve downloaded path.' );
		}

		$temp_folder = '/rey/import';
		$temp_location = $this->importer->upload_dir_path . $temp_folder;
		$file_info     = pathinfo( $downloaded_path );
		$newfilename   = wp_unique_filename( $temp_location, $file_info['filename'] );
		$temp_location = sprintf( '%s/%s', $temp_location, $newfilename );

		if ( ! function_exists( 'unzip_file' ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
		}

		// extract the zip to a temp location
		$unzip = unzip_file( $downloaded_path, $temp_location );

		// throw error if the zip files cannot be extracted
		if ( is_wp_error( $unzip ) ) {

			// remove the temp directory
			Helper::fs()->delete( $temp_location, true );

			// send a wp error
			return $this->add_error( $unzip->get_error_message() );
		}

		if( ! $unzip ){
			return $this->add_error( 'Cannot unzip.' );
		}

		set_transient('rey_demo_temp_path', trailingslashit( $temp_location ), $this->importer::TRANSIENT_EXPIRATION);

	}

}
