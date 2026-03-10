<?php
namespace ReyCore\Compatibility\PageOptimize;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {

		if( ! is_admin() ) {
			return;
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		deactivate_plugins( 'page-optimize/page-optimize.php');

		unset( $_REQUEST['activate'], $_REQUEST['plugin_status'], $_REQUEST['activate-multi'] );

		// wp_redirect(admin_url('plugins.php'));
		// exit;

	}

}
