<?php
namespace ReyCore\Compatibility\Wpforms;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter('reycore/wpforms/forms', [$this, 'get_forms'], 10);
		add_filter('reycore/wpforms/control_description', [$this, 'get_notice'], 10);
	}

	/**
	 * Get forms.
	 *
	 * Retrieve an array of forms from the CF7 plugin.
	 */
	public function get_forms( $forms ) {

		if( ! function_exists('wpforms') ){
			return $forms;
		}

		$wpforms = wpforms()->form->get();

		if ( ! empty( $wpforms ) ) {
			$forms[ '' ] = esc_html__('- Select -', 'rey-core');
			foreach ( $wpforms as $form ) {
				$forms[ absint( $form->ID ) ] = esc_html( $form->post_title );
			}
		}

		return $forms;
	}

	/**
	 * Get notice.
	 *
	 */
	public function get_notice() {

		if( function_exists('wpforms') ){
			return esc_html__( 'Select the contact form you created in WP Forms.', 'rey-core' );
		}

		return __('<p>It seems <a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">WPForms</a> is not installed or active. Please activate it to be able to create a contact form to be used with this option.</p>', 'rey-core');
	}

}
