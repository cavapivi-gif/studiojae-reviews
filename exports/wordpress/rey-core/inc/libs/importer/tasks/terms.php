<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Terms extends TaskBase
{

	public function get_id(){
		return 'terms';
	}

	public function get_status(){
		return esc_html__('Process terms ...', 'rey-core');
	}

	public function run(){



		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['terms']) && ($terms = $data['terms']) ) ){
			return $this->add_error( 'Cannot retrieve terms data.' );
		}

		if( $this->maybe_skip('terms') ){
			return $this->add_notice( 'Skipping terms as requested.' );
		}

		$this->iterate_data($terms, function( $uid, $term_data ){
			wp_defer_term_counting( true );
			$this->add_term($term_data, $uid);
			wp_defer_term_counting( false );
		});

		Base::update_map($this->map);

	}

	public function add_term($term_data, $uid){

		if( ! $term_data['tax'] ){
			return $this->add_notice('Taxonomy property not provided. UID: ' . $uid);
		}

		if( ! $term_data['slug'] ){
			return $this->add_notice('Term slug not provided. UID: ' . $uid);
		}

		if ( ($t = term_exists( $term_data['slug'], $term_data['tax'] )) && isset($t['term_id']) ) {
			$this->map[$uid] = absint($t['term_id']);
			return $this->add_notice( sprintf('Term "%s" already exists.', $term_data['slug'] ) );
		}

		$term = wp_insert_term( $term_data['name'], $term_data['tax'], [
			'slug' => $term_data['slug'],
			'description' => isset($term_data['description']) ? $term_data['description'] : '',
		] );

		if ( is_wp_error( $term ) ) {
			return $this->add_notice( sprintf('Term "%s" not created. Reason: %s ', $term_data['slug'], $term->get_error_message() ) );
		}

		$this->map[$uid] = absint($term['term_id']);

	}

}
