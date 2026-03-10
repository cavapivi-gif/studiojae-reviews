<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('ReyTheme_Plugins') ):
	/**
	 * Manager for Rey's plugins
	 * and wrapper for TGMPA Plugin installer
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Plugins
	{
		private static $_instance = null;

		private $plugins = [];

		/**
		 * TGMPA instance.
		 *
		 * @var    object
		 */
		public $tgmpa;

		private function __construct()
		{

			if( \Rey\Plugins::version_swap() ){
				return;
			}

			require_once REY_THEME_DIR . '/inc/libs/class-tgm-plugin-activation.php';

			add_action( 'admin_notices', [$this, 'check_plugins_notice']);
			add_action( 'admin_init', [$this, 'tgmpa_refresh_plugins_list']);
			add_filter( 'rey/admin_script_params', [$this, 'add_admin_script_params'] );
			add_filter( 'reybase_tgmpa_load', [$this, 'load_tgmpa'] );
			add_filter( 'reybase_tgmpa_admin_menu_args', [$this, 'tgmpa_menu_item'] );
			add_action( 'reybase_tgmpa_register', [$this, 'register_plugins'] );
			add_filter( 'reybase_tgmpa_table_data_item', [$this, 'tgmpa_table_data_item'], 10, 2 );
			add_filter( 'reybase_tgmpa_table_data_items', [$this, 'tgmpa_table_data_items'], 20);
			add_filter( 'reybase_tgmpa_plugin_action_links', [$this, 'filter_reybase_tgmpa_plugin_action_links'], 10, 3 );
			add_action( 'reybase_tgmpa_page_content', [$this, 'page_content']);
		}

		/**
		 * Conditionally load TGMPA
		 *
		 * @since 1.0.0
		 */
		public function load_tgmpa( $status )
		{

			// is admin but not Ajax
			// $status = ( is_admin() && ! defined( 'DOING_AJAX' ) );

			if( is_admin() && current_user_can( 'install_plugins' ) && rey__get_props('plugins_manager') ){
				return true;
			}

			return $status;
		}

		/**
		 * Get TGMPA url
		 *
		 * @since 1.0.0
		 */
		public function tgmpa_url() {
			return ReyBase__TGM_Plugin_Activation::get_instance()->get_tgmpa_url();
		}

		function get_essential_plugins(){
			return [
				'elementor',
				'woocommerce',
				'one-click-demo-import'
			];
		}

		/**
		 * Filter TGMPA's table and append a "need registration" flag
		 *
		 * @since 1.0.0
		 */
		function tgmpa_table_data_item( $table_item, $plugin ){

			$table_item['type'] = 'Optional';
			$table_item['sort'] = 3;

			if( in_array($table_item['slug'], $this->get_essential_plugins(), true) ){
				$table_item['type'] = 'Essential';
				$table_item['sort'] = 2;
			}

			if( REY_THEME_CORE_SLUG === $table_item['slug'] ){
				$table_item['type'] = 'Required';
				$table_item['sort'] = 1;
			}

			if( isset($plugin['type']) && $plugin['type'] === REY_THEME_NAME && ! ReyTheme_Base::get_purchase_code() ){
				$table_item['needs_registration_for_action'] = true;
			}
			return $table_item;
		}

		function tgmpa_table_data_items($items){

			$sort = [];
			$name = [];

			foreach ( $items as $i => $plugin ) {
				$sort[ $i ] = $plugin['sort'];
				$name[ $i ] = $plugin['sanitized_plugin'];
			}

			array_multisort( $sort, SORT_ASC, $name, SORT_ASC, $items );

			return $items;
		}

		/**
		 * Message to show in TGMPA's plugins that
		 * are unregistered.
		 *
		 * @since 1.0.0
		 */
		function reybase_tgmpa_plugin_action_links_message( $plugin_name, $action = '' ){
			return rey__wp_kses( sprintf( __('<span class="rey-tgmpaNeedReg">Please <a href="%1$s">register %2$s</a>, to be able to %3$s %4$s, or %3$s it <a href="%5$s" target="_blank">manually</a>.</span>', 'rey'),
				esc_url(add_query_arg( ['page' => ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
				REY_THEME_NAME,
				$action,
				$plugin_name,
				rey__support_url('kb/installing-rey-plugins/#installing-plugins-manually')
			) );
		}


		/**
		 * Filter TGMPA's action links and depending on the "need registration" flag,
		 * show a notice to manually install if unregistered.
		 *
		 * @since 1.0.0
		 */
		function filter_reybase_tgmpa_plugin_action_links( $action_links, $item_slug, $item )
		{
			if( isset($item['needs_registration_for_action']) )
			{
				if( isset($action_links['install']) ){
					$action_links['install'] = $this->reybase_tgmpa_plugin_action_links_message( $item['plugin'], esc_html__('install', 'rey') );
				}

				if( isset($action_links['update']) ){
					$action_links['update'] = $this->reybase_tgmpa_plugin_action_links_message( $item['plugin'], esc_html__('update', 'rey') );
				}
			}

			return $action_links;
		}

		/**
		 * Show notice if required plugins are not installed or inactive
		 *
		 * @since 1.0.0
		 */
		public function check_plugins_notice()
		{
			$required_plugins = $this->get_required_plugins();

			if( empty($required_plugins) ){
				return;
			}

			$notice_plugins = [];

			foreach( $required_plugins as $plugin ){
				if( ! $plugin['active'] && $plugin['slug'] !== 'one-click-demo-import' ){
					$notice_plugins[] = esc_html($plugin['name']);
				}
			}

			if( empty($notice_plugins) ){
				return;
			} ?>

			<div class="notice notice-warning js-storeNotice" data-from="required-plugins">
				<p><?php echo rey__wp_kses( sprintf( __('<strong>%1$s</strong> requires a few plugins (<strong>%3$s</strong>) to be installed & activated to work properly. <a href="%2$s">Install & Activate plugins</a>', 'rey'),
					rey__get_props('theme_title'),
					esc_url(add_query_arg([ 'page' => ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
					implode(', ', $notice_plugins)
				) ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rey') ?></span>
				</button>
			</div>
			<?php
		}

		/**
		 * Get plugins
		 *
		 * @since 1.0.0
		 */
		public function get_plugins( $type = 'all' ){
			return \Rey\Plugins::get_plugins($type);
		}

		/**
		 *
		 *
		 * @since 1.0.0
		 */
		public function get_rey_plugins() {
			return \Rey\Plugins::get_rey_plugins();
		}

		/**
		 * Get Rey's plugins marked as required.
		 *
		 * @since 1.0.0
		 */
		public function get_required_plugins(){
			return \Rey\Plugins::get_required_plugins();
		}

		/**
		 * Set plugin list.
		 *
		 * @param $force (bool). Force check with API server.
		 *
		 * @since 1.0.0
		 */
		public function set_plugins() {}

		public function refresh_plugins(){
			return \Rey\Plugins::refresh();
		}

		/**
		 * Add TGMPA configuration
		 *
		 * @since 1.0.0
		 */
		private function tgmpa_config( $location = '' ){

			if( $location ){
				$menu = $location;
			}
			else {
				$menu = REY_THEME_NAME . '-install-required-plugins';
			}

			return [
				'id'           => 'rey-tgmpa',
				'menu'         => $menu,
				'has_notices'  => false,
				'is_automatic' => true,
			];

		}

		function tgmpa_menu_item($args){

			if( $dashboard_id = ReyTheme_Base::DASHBOARD_PAGE_ID ){
				$args['parent_slug'] = $dashboard_id;
				$args['page_title'] = esc_html__('Plugins Manager', 'rey');
				$args['menu_title'] = esc_html__('Plugins Manager', 'rey');
			}

			return $args;
		}

		function page_content(){
			?>
			<div class="rey-ocdi__intro-text">
				<h3><?php esc_html_e('Notes:', 'rey') ?></h3>
				<ul>
					<li>
						<p>These plugins are <strong>not required</strong> and you don't need to install all of them. They're just used throughout the demos. If you import a demo, you'll be prompted to choose which plugins to install.</p>
					</li>
					<li>
						<p>If you don't or can't import a demo and instead want to start from scratch installing plugins, you can head over to <a href="<?php echo rey__support_url('kb/installing-rey-plugins/#plugins-that-are-used-in-the-demos'); ?>" target="_blank">this KB article</a> and see which plugins are used in a particular demo.</p>
					</li>
				</ul>
			</div>
			<?php
		}

		/**
		 * Register plugin configuration for TGMPA
		 *
		 * @since 1.0.0
		 */
		public function register_plugins()
		{
			reybase_tgmpa( $this->get_plugins(), $this->tgmpa_config() );
		}

		public function activate_plugin( $file_path ){
			// Include the plugin.php file so you have access to the activate_plugin() function
			require_once(ABSPATH .'/wp-admin/includes/plugin.php');
			$activate = activate_plugin( $file_path );
			if ( is_wp_error( $activate ) ) {
				return false; // End it here if there is an error with activation.
			}
			return true;
		}

		/**
		 * Parse list of plugins and return the first one
		 * under certain conditions.
		 *
		 * @since 1.0.0
		 */
		function get_plugin_for_action( $plugins = [] )
		{
			if( ! empty($plugins) ){
				return current( array_filter( $plugins, function($v, $k){
					// not installed (or installed and inactive) and premium (but registered)
					return (
						! $v['installed'] || ($v['installed'] && ! $v['active'])) &&
						! ( $v['type'] === REY_THEME_NAME && ! ReyTheme_Base::get_purchase_code()
					) ;
				}, ARRAY_FILTER_USE_BOTH) );
			}
			return false;
		}

		/**
		 * Add script params
		 *
		 * @since 1.0.0
		 */
		function add_admin_script_params($params){

			$params['refresh_plugins_text'] = esc_html__('SYNC PLUGINS LIST', 'rey');
			$params['refresh_plugins_url'] = wp_nonce_url( admin_url( sprintf('admin.php?page=%s', REY_THEME_NAME . '-install-required-plugins') ), 'rey_refresh_plugins', 'refresh_plugins_nonce');

			return $params;
		}

		function tgmpa_refresh_plugins_list(){
			if ( ! (isset($_GET['refresh_plugins_nonce']) && wp_verify_nonce($_GET['refresh_plugins_nonce'], 'rey_refresh_plugins')) ) {
				return;
			}
			$this->refresh_plugins();
			wp_safe_redirect( admin_url( sprintf('admin.php?page=%s', REY_THEME_NAME . '-install-required-plugins') ) );
			exit();
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyTheme_Plugins
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyTheme_Plugins::getInstance();

endif;
