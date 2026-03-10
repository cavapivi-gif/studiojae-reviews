<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class GetData extends TaskBase
{

	public function get_id(){
		return 'get-data';
	}

	public function get_status(){
		return esc_html__('Retrieve data ...', 'rey-core');
	}

	public function run(){

		if( ! ($path = get_transient('rey_demo_temp_path')) ){
			return $this->add_error( 'Cannot retrieve data path.' );
		}

		$content = ( Helper::fs()->exists( $path . 'data.json' ) ) ? Helper::fs()->get_contents( $path . 'data.json' ) : false;

		if ( false === $content ) {
			Helper::fs()->delete( $path, true );
			return $this->add_error( 'Import failed. No configuration file found.' );
		}

		// check for template data
		$content_data = json_decode( $content, true );

		if ( empty( $content_data ) ) {
			Helper::fs()->delete( $path, true );
			return $this->add_error( 'Import failed. Empty data.' );
		}

		set_transient('rey_demo_data', $content_data, $this->importer::TRANSIENT_EXPIRATION);

	}

}
