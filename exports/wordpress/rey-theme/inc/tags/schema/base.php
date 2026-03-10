<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('Rey_SchemaOrg') ):

	class Rey_SchemaOrg {

		public function __construct() {
			$this->includes();
			add_action( 'wp', [ $this, 'setup' ] );
		}

		public function setup() {}

		private function includes() {
			require_once REY_THEME_DIR . '/inc/tags/schema/body.php';
			require_once REY_THEME_DIR . '/inc/tags/schema/header.php';
			require_once REY_THEME_DIR . '/inc/tags/schema/footer.php';
			require_once REY_THEME_DIR . '/inc/tags/schema/sidebar.php';
			require_once REY_THEME_DIR . '/inc/tags/schema/site-navigation.php';
		}

		protected function check_plugin_support( $plugin ){

			switch($plugin):
				// Support for "Schema" WordPress plugin
				case "schema":
					return class_exists('Schema_WP') && schema_wp_get_option( 'web_page_element_enable' ) == true;
					break;
			endswitch;

			return false;
		}

		protected function is_enabled() {
			$status = true;

			if( $this->check_plugin_support('schema') ){
				$status = false;
			}

			return apply_filters( 'rey/schema/enabled', $status );
		}

	}

	new Rey_SchemaOrg();

endif;
