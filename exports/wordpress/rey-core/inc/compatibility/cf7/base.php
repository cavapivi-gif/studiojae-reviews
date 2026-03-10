<?php
namespace ReyCore\Compatibility\Cf7;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_filter( 'wpcf7_load_js', '__return_false' );
		add_filter( 'wpcf7_load_css', '__return_false' );
		// add_action( 'wpcf7_contact_form', [$this, 'load_scripts']);
		add_filter( 'wpcf7_form_class_attr', [$this, 'custom_load_scripts']);
		add_filter( 'reycore/cf7/forms', [$this, 'get_forms'], 10);
		add_filter( 'reycore/cf7/control_description', [$this, 'description'], 10);
	}

	/**
	 * Load assets only when the form renders
	 *
	 * @param [type] $data
	 * @return void
	 */
	function custom_load_scripts( $data ){
		$this->load_scripts();
		return $data;
	}

	function load_scripts(){
		if( function_exists('wpcf7_enqueue_scripts') ){
			wpcf7_enqueue_scripts();
		}
		if( function_exists('wpcf7_enqueue_styles') ){
			wpcf7_enqueue_styles();
		}
	}

	/**
	 * Get forms.
	 *
	 * Retrieve an array of forms from the CF7 plugin.
	 */
	public function get_forms( $forms ) {

		if( ! class_exists('\WPCF7') ){
			return $forms;
		}

		$args = [
			'post_type'   => 'wpcf7_contact_form',
			'numberposts' => -1,
			'post_status' => 'publish',
		];

		if ( $cf7 = get_posts($args) ) {
			$forms[ '' ] = esc_html__('- Select -', 'rey-core');
			foreach ( $cf7 as $cform ) {
				$forms[ $cform->ID ] = $cform->post_title;
			}
		}

		return $forms;
	}

	public function description() {

		if( class_exists('\WPCF7') ){
			return esc_html__( 'Select the contact form you created in Contact Form 7.', 'rey-core' );
		}

		return __('<p>It seems <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a> is not installed or active. Please activate it to be able to create a contact form to be used with this option.</p>', 'rey-core');
	}

}
