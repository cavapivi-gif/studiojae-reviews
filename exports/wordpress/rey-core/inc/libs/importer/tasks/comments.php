<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Comments extends TaskBase
{
	private $map_data;

	public function get_id(){
		return 'comments';
	}

	public function get_status(){
		return esc_html__('Process comments ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['comments']) && ($comments = $data['comments']) ) ){
			return $this->add_notice( 'Cannot retrieve comments. Most likely not added in this demo.' );
		}

		if( $this->maybe_skip('comments') ){
			return $this->add_notice( 'Skipping comments as requested.' );
		}

		$this->map_data = Base::get_map();

		wp_defer_comment_counting( true );

		$this->iterate_data($comments, function( $uid, $data ){
			$this->add_comment($data, $uid);
		});

		wp_defer_comment_counting( false );

		Base::update_map($this->map);

	}

	public function add_comment($data, $uid){

		if( ! $data['comment_post_ID'] ){
			return $this->add_notice('Comment post ID not provided. UID: ' . $uid);
		}

		if( ! (isset($this->map_data[$data['comment_post_ID']]) && ($post_id = $this->map_data[$data['comment_post_ID']])) ){
			return $this->add_notice('Post ID missing. UID: ' . $uid);
		}

		$c_data = [
			'comment_post_ID'      => $post_id,
			'comment_author'       => isset($data['comment_author']) ? $data['comment_author']            : '',
			'comment_author_email' => isset($data['comment_author_email']) ? $data['comment_author_email']: '',
			'comment_author_url'   => isset($data['comment_author_url']) ? $data['comment_author_url']    : '',
			'comment_date'         => isset($data['comment_date']) ? $data['comment_date']                : '',
			'comment_content'      => isset($data['comment_content']) ? $data['comment_content']          : '',
			'comment_approved'     => isset($data['comment_approved']) ? $data['comment_approved']        : '',
			'comment_type'         => isset($data['comment_type']) ? $data['comment_type']                : '',
		];

		if( isset($data['meta']) && ($comment_meta = (array) $data['meta']) ){
			foreach ( $comment_meta as $key => $value) {
				$c_data['comment_meta'][$key] = $value;
			}
		}

		$comment_id = wp_insert_comment($c_data);

		if ( ! $comment_id ) {
			return $this->add_notice( 'Comment not created.' );
		}

		$this->map[$uid] = absint($comment_id);
	}

}
