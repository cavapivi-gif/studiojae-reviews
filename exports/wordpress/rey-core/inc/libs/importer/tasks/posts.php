<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Posts extends TaskBase
{

	private $skip = [];

	public function get_id(){
		return 'posts';
	}

	public function get_status(){
		return esc_html__('Process posts ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['posts']) && ($posts = $data['posts']) ) ){
			return $this->add_error( 'Cannot retrieve posts data.' );
		}

		foreach ([
			'post',
			'product',
			'page',
			'nav_menu_item',
			'rey-global-sections',
		] as $pt) {
			if( $this->maybe_skip($pt) ){
				$this->skip[] = $pt;
				if( 'product' === $pt ){
					$this->skip[] = 'product_variation';
				}
			}
		}

		if ( ! function_exists( 'post_exists' ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/post.php' );
		}

		$this->iterate_data($posts, function( $uid, $post_data ){
			$this->add_post($post_data, $uid);
		});

		Base::update_map($this->map);

	}

	public function add_post($post_data, $uid){

		if( ! $post_data['post_name'] ){
			return $this->add_notice('Post slug not provided. UID: ' . $uid);
		}

		$post_arr = [];

		foreach ([
			'post_name'      => '',
			'post_title'     => '',
			'post_status'    => '',
			'post_type'      => '',
			'post_content'   => '',
			'comment_status' => '',
			'post_excerpt'   => '',
			'menu_order'     => 0,
		] as $key => $value) {

			$post_data_value = $value;

			if( isset($post_data[$key]) ){
				$post_data_value = $post_data[$key];
				if( 'post_content' === $key ){
					$post_data_value = Helper::process_paths($post_data_value);
				}
			}

			$post_arr[$key] = wp_kses_post($post_data_value);
		}

		if( ! empty($this->skip) && in_array($post_arr['post_type'], $this->skip, true) ){
			return $this->add_notice( sprintf('Skipping "%s" as requested.', $post_arr['post_type']) );
		}

		if ( $post_id = $this->post_exists( $post_arr['post_title'], $post_arr['post_content'], $post_arr['post_name'], $post_arr['post_type'], $post_arr['post_status'] ) ) {
			$this->map[$uid] = absint($post_id);
			return $this->add_notice( sprintf('Post "%s" already exists.', $post_arr['post_title'] ) );
		}

		// Create a date string for the post_date
		$random_date = date('Y-m-d H:i:s', time() - rand(0, 5 * 24 * 60 * 60) ); // 5 days
		$post_arr['post_date'] = $random_date;
		$post_arr['post_date_gmt'] = get_gmt_from_date($random_date);

		$post_id = wp_insert_post($post_arr, true);

		if ( is_wp_error( $post_id ) ) {
			return $this->add_notice( sprintf('Post "%s" not created. Reason: %s ', $post_arr['post_title'], $post_id->get_error_message() ) );
		}

		$this->map[$uid] = absint($post_id);
	}

	/**
	 * Determines if a post exists based on title, content, name and type.
	 *
	 * @since 2.0.0
	 * @since 5.2.0 Added the `$type` parameter.
	 * @since 5.8.0 Added the `$status` parameter.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $title   Post title.
	 * @param string $content Optional. Post content.
	 * @param string $post_name    Optional. Post name.
	 * @param string $type    Optional. Post type.
	 * @param string $status  Optional. Post status.
	 * @return int Post ID if post exists, 0 otherwise.
	 */
	function post_exists( $title, $content = '', $post_name = '', $type = '', $status = '' ) {
		global $wpdb;

		$post_title   = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
		$post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
		$post_name    = wp_unslash( sanitize_post_field( 'post_name', $post_name, 0, 'db' ) );
		$post_type    = wp_unslash( sanitize_post_field( 'post_type', $type, 0, 'db' ) );
		$post_status  = wp_unslash( sanitize_post_field( 'post_status', $status, 0, 'db' ) );

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args  = array();

		if ( ! empty( $post_name ) ) {
			$query .= ' AND post_name = %s';
			$args[] = $post_name;
		}

		if ( ! empty( $title ) ) {
			$query .= ' AND post_title = %s';
			$args[] = $post_title;
		}

		if ( ! empty( $content ) ) {
			$query .= ' AND post_content = %s';
			$args[] = $post_content;
		}

		if ( ! empty( $type ) ) {
			$query .= ' AND post_type = %s';
			$args[] = $post_type;
		}

		if ( ! empty( $status ) ) {
			$query .= ' AND post_status = %s';
			$args[] = $post_status;
		}

		if ( ! empty( $args ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $query, $args ) );
		}

		return 0;
	}
}
