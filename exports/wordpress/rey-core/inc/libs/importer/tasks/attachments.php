<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Attachments extends TaskBase
{

	private $path;

	public function get_id(){
		return 'attachments';
	}

	public function get_status(){
		return esc_html__('Process attachments ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['attachments']) && ($att = $data['attachments']) ) ){
			return $this->add_error( 'Cannot retrieve attachments data.' );
		}

		if( ! ($this->path = get_transient('rey_demo_temp_path')) ){
			return $this->add_error( 'Cannot retrieve data path.' );
		}

		if( $this->maybe_skip('attachments') ){
			return $this->add_notice( 'Skipping attachments as requested.' );
		}

		add_filter( 'intermediate_image_sizes_advanced', '__return_null' );

		$this->iterate_data($att, function( $uid, $path ){
			$this->upload_media($path, $uid);
		});

		Base::update_map($this->map);

	}

	/**
	 * Upload media files from a specific path,
	 * and insert attachment
	 *
	 * @param string $path
	 * @param string $uid
	 *
	 * @return null|post_id
	 */
	public function upload_media( $path, $uid ) {

		// check if the image was already uploaded
		if( isset($this->map[$uid]) ){
			return $this->add_notice('Already exists ' . $path);
		}

		$file_name = basename( $path );
		$temp_path   = $this->path . 'images' . $path;
		$upload_dir = $this->importer->upload_dir_path . str_replace( $file_name, '', $path );
		$new_path   = $this->importer->upload_dir_path . $path;
		$new_url = untrailingslashit($this->importer->upload_dir_url) . $path;

		// rename if file already exists
		if( Helper::fs()->exists( $new_path ) ){
			$__new_path = sprintf('%s/%s.%s', pathinfo($path, PATHINFO_DIRNAME), uniqid(pathinfo($path, PATHINFO_FILENAME)), pathinfo($path, PATHINFO_EXTENSION));
			$new_path   = $this->importer->upload_dir_path . $__new_path;
			$new_url = untrailingslashit($this->importer->upload_dir_url) . $__new_path;
		}

		// check temp path
		if( ! Helper::fs()->exists( $temp_path ) ){
			return $this->add_notice('Temp path does not exist. ' . $temp_path );
		}

		// create folder if it doesn't exist
		if ( ! Helper::fs()->exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		Helper::fs()->copy( $temp_path, $new_path );

		// Check the type of file.
		$filetype = wp_check_filetype( $file_name, null );

		// Prepare an array of post data for the attachment.
		$attachment = [
			'guid'           => $new_url,
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		// will return a wp_error on failure
		$attachment_id = wp_insert_attachment( $attachment, $new_path, 0, true );

		// error
		if( is_wp_error($attachment_id) ){
			return $this->add_notice('Error uploading %s. Message: %s', $new_path, $attachment_id->get_error_message() );
		}

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attach_data = null;

		try {

			set_error_handler([$this, 'custom_error_handler']);

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $new_path );

			restore_error_handler();

		}
		catch (\ErrorException $e) {
			$this->add_notice('Attachment metadata warning. ' . $e->getMessage() );
		}

		if( ! is_null($attach_data) ){
			wp_update_attachment_metadata( $attachment_id, $attach_data );
		}

		$this->map[$uid] = $attachment_id;

		return $attachment_id;
	}


	function custom_error_handler($errno, $errstr, $errfile, $errline) {
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}
}
