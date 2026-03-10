<?php
namespace ReyCore\Elementor\TemplateLibrary;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\TemplateLibrary\Base;
use \Elementor\TemplateLibrary\Source_Base;

class Source extends Source_Base {

	/**
	 * Get remote template ID.
	 *
	 * Retrieve the remote template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The remote template ID.
	 */
	public function get_id() {
		return 'rey';
	}

	/**
	 * Get remote template title.
	 *
	 * Retrieve the remote template title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The remote template title.
	 */
	public function get_title() {
		return __( 'Rey', 'rey-core' );
	}

	/**
	 * Register remote template data.
	 *
	 * Used to register custom template data like a post type, a taxonomy or any
	 * other data.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_data() {}

	/**
	 * Get remote templates.
	 *
	 * Retrieve remote templates from api.reytheme.com servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Optional. Nou used in remote source.
	 *
	 * @return array Remote templates.
	 */
	public function get_items( $args = [] ) {
		return [];
	}

	/**
	 * Get remote template.
	 *
	 * Retrieve a single remote template from api.reytheme.com servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array Remote template.
	 */
	public function get_item( $template_id ) {
		$templates = $this->get_items();

		return $templates[ $template_id ];
	}

	private function get_template_file( $remote_url ){

		$response = wp_remote_get( $remote_url, [
			'timeout' => 40
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new \WP_Error( 'no_json', 'Empty data.' );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== (int) $response_code ) {
			return new \WP_Error( $response_code, sprintf( esc_html__( '%s (HTTP Error)', 'rey-core' ), $data ) );
		}

		return $data;
	}

	private function get_template_content( $template_id ){

		if( !class_exists('\ReyTheme_API') ) {
			return new \WP_Error( 'missing_api', esc_html__('\ReyTheme_API is missing.', 'rey-core') );
		}

		if( ! reycore__get_purchase_code() ) {
			return new \WP_Error( 'not_registered', esc_html__('Purchase code is missing. Please register.', 'rey-core') );
		}

		// get temporary template url
		$template_url = \ReyTheme_API::getInstance()->get_template_data( absint($template_id) );

		if ( is_wp_error( $template_url ) ) {
			return $template_url;
		}

		if ( isset($template_url['data']) && !empty($template_url['data']))  {
			return $this->get_template_file( $template_url['data'] );
		}
	}

	/**
	 * Get remote template data.
	 *
	 * Retrieve the data of a single remote template from api.reytheme.com servers.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param array  $args    Custom template arguments.
	 * @param string $context Optional. The context. Default is `display`.
	 *
	 * @return array Remote Template data.
	 */
	public function get_data( array $args, $context = 'display' ) {

		$template_id = $args['template_id'];
		$template_slug = $args['template_slug'];
		$stored_data = (array) get_site_option(Base::LIBRARY_DB_INSTALLED, []);

		if( ! empty($stored_data) && array_key_exists( $template_slug , $stored_data ) ){
			return $stored_data[ $template_slug ];
		}

		// get template content
		$data = $this->get_template_content( $template_id );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		do_action('reycore/elementor/before_process_template', $data);

		$data['content'] = $this->replace_elements_ids( $data['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		$post_id = $args['editor_post_id'];
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( $document ) {
			$data['content'] = $document->get_elements_raw_data( $data['content'], true );
		}

		$stored_data[ $template_slug ] = $data;

		update_site_option(Base::LIBRARY_DB_INSTALLED, $stored_data, false);

		$this->mark_installed( $template_slug );

		return $data;
	}

	private function mark_installed( $template_slug ){
		// mark as installed in library
		$library_data = get_site_option(Base::LIBRARY_DB_DATA, [
			'favorites'=> [],
			'installed'=> []
		]);
		$library_data['installed'][] = $template_slug;
		$library_data['installed'] = array_unique( $library_data['installed'] );
		update_site_option( Base::LIBRARY_DB_DATA, (array) $library_data);
	}

	/**
	 * Save remote template.
	 *
	 * Remote template from api.reytheme.com servers cannot be saved on the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $template_data Remote template data.
	 *
	 * @return \WP_Error
	 */
	public function save_item( $template_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot save template to a remote source' );
	}

	/**
	 * Update remote template.
	 *
	 * Remote template from api.reytheme.com servers cannot be updated on the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_data New template data.
	 *
	 * @return \WP_Error
	 */
	public function update_item( $new_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot update template to a remote source' );
	}

	/**
	 * Delete remote template.
	 *
	 * Remote template from api.reytheme.com servers cannot be deleted from the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Error
	 */
	public function delete_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot delete template from a remote source' );
	}

	/**
	 * Export remote template.
	 *
	 * Remote template from api.reytheme.com servers cannot be exported from the
	 * database as they are retrieved from remote servers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return \WP_Error
	 */
	public function export_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot export template from a remote source' );
	}
}
