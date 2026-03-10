<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class PostsMeta extends TaskBase
{

	private $map_data;

	private $skip = [];

	public function get_id(){
		return 'posts-meta';
	}

	public function get_status(){
		return esc_html__('Process posts meta data ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['posts']) && ($posts = $data['posts']) ) ){
			return $this->add_error( 'Cannot retrieve posts data.' );
		}

		$this->map_data = Base::get_map();

		foreach ([
			'posts',
			'products',
			'page',
			'nav_menu_item',
			'rey-global-section',
		] as $pt) {
			if( $this->maybe_skip($pt) ){
				$this->skip[] = $pt;
			}
		}

		$this->iterate_data($posts, function( $uid, $post_data ){
			$this->process_post($post_data, $uid);
		});

	}

	public function process_post($post_data, $uid){

		if( ! (isset($this->map_data[$uid]) && $post_id = absint($this->map_data[$uid])) ){
			return;
		}

		if( ! empty($this->skip) && isset($post_data['post_type']) && in_array($post_data['post_type'], $this->skip, true) ){
			return ;
		}

		if( isset($post_data['post_parent']) && isset($this->map_data[ $post_data['post_parent'] ]) ){
			$update = wp_update_post([
				'ID' => $post_id,
				'post_parent' => absint($this->map_data[ $post_data['post_parent'] ]),
			], true);
		}

		if( isset($post_data['terms']) )
		{
			foreach ( (array) $post_data['terms'] as $taxonomy => $terms) {
				$post_terms = [];
				foreach ($terms as $term_uid) {
					if( isset($this->map_data[ $term_uid ]) ){
						$post_terms[] = absint($this->map_data[ $term_uid ]);
					}
				}
				if( ! empty($post_terms) ){
					wp_set_post_terms( $post_id, $post_terms, $taxonomy);
				}
			}
		}

		if( isset($post_data['meta']) )
		{
			foreach ( (array) $post_data['meta'] as $key => $value) {

				if( ! $value ){
					if( '0' != $value ){
						continue;
					}
				}

				$meta_value = $this->process_meta_item($value);

				if ( '_elementor_data' === $key ) {
					$meta_value = wp_slash( $meta_value );
					if( defined('ELEMENTOR_VERSION') ){
						update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION);
					}
				}

				update_post_meta( $post_id, $key, $meta_value);
			}
		}

	}

	public function process_meta_item($value){

		if( is_array($value) ){
			return array_map( function($item) {
				return $this->process_meta_item($item);
			}, $value );
		}

		if( isset($this->map_data[ $value ]) && ($mapped_id = $this->map_data[ $value ]) ){
			return $mapped_id;
		}

		$value = Helper::process_paths($value);
		$value = Helper::process_ids($value, $this->map_data);


		return $value;
	}

}
