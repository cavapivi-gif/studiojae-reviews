<?php
namespace ReyCore\Compatibility\WcAjaxProductFilter;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action( 'admin_notices', [$this, 'show_notices'] );
	}

	/**
	 * Display notices
	 *
	 * @since 1.0.0
	 */
	function show_notices()
	{

		echo '<div class="notice notice-error">';
		echo __( '<p>Since Rey 1.5.0 update, <strong>"WC Ajax Filters" plugin is deprecated</strong> and all support for it has been removed from Rey. Please use Rey\'s own filtering widgets, or downgrade to an older Rey Core version and look for the migration notice in the backend.</p>', 'rey-core' );
		echo '</div>';

		$this->deactivate();

	}

	public function deactivate(){
		if( ! is_admin() ) return;
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( 'wc-ajax-product-filter/wcapf.php');
		unset( $_GET['activate'], $_GET['plugin_status'], $_GET['activate-multi'] );
	}

}
