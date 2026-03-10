<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class TermsMeta extends TaskBase
{

	private $map_data;

	public function get_id(){
		return 'terms-meta';
	}

	public function get_status(){
		return esc_html__('Process terms meta data ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['terms']) && ($terms = $data['terms']) ) ){
			return $this->add_error( 'Cannot retrieve terms data.' );
		}

		if( $this->maybe_skip('terms') ){
			return $this->add_notice( 'Skipping terms meta as requested.' );
		}

		$this->map_data = Base::get_map();

		$this->iterate_data($terms, function( $uid, $term_data ){
			$this->process_term($term_data, $uid);
		});

	}

	public function process_term($term_data, $uid){

		if( ! (isset($this->map_data[$uid]) && $term_id = $this->map_data[$uid]) ){
			return;
		}

		if( isset($term_data['parent']) && isset($this->map_data[ $term_data['parent'] ]) ){
			wp_update_term( $term_id, $term_data['tax'], [
				'parent' => $this->map_data[ $term_data['parent'] ],
			] );
		}

		if( isset($term_data['meta']) ){

			foreach ( (array) $term_data['meta'] as $key => $value) {

				if( empty($value) ){
					continue;
				}

				if( isset($this->map_data[ $value ]) && ($mapped_id = $this->map_data[ $value ]) ){
					$value = $mapped_id;
				}
				else {
					$value = Helper::process_paths($value);
					$value = Helper::process_ids($value, $this->map_data);
				}

				update_term_meta( $term_id, $key, $value);

			}

		}

	}

}
