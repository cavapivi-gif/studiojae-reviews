<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReyTheme_Base
{
	const DASHBOARD_PAGE_ID = REY_THEME_NAME . '-dashboard';
	const REGISTRATION_OPTION_ID = 'rey_purchase_code';
	const SUBSCRIBE_NEWSLETTER_OPTION_ID = 'rey_subscribed_to_newsletter';

	// Statuses
	const STATUS_VALID = 'valid';
	const STATUS_NOT_FOUND = 'not_found';
	const STATUS_ALREADY_EXISTS = 'already_exists';

	/**
	 * ReyTheme_Base constructor.
	 */
	public function __construct()
	{
		add_action( 'admin_notices', [$this, 'add_registration_notice'] );
		add_action( 'admin_init', [$this, 'init']);
		add_action( 'rey/api/parse_remote', [$this, 'parse_response']);
		add_action( 'wp_ajax_rey_dashbox_test_connection', [ $this, 'ajax__dashbox_test_connection' ] );
	}

	public function init(){

		if( is_admin() ){
			if(
				isset($_REQUEST['deregister'])
				&& 1 === absint($_REQUEST['deregister'])
				&& isset($_REQUEST['page'])
				&& rey__clean($_REQUEST['page']) === 'rey-dashboard'
				&& current_user_can('administrator')
			){
				$this->delete_purchase_code();
				wp_safe_redirect( admin_url('admin.php?page=rey-dashboard') );
			}
		}

		global $pagenow;

		if( 'update-core.php' !== $pagenow && is_admin() && isset($_REQUEST['force-check']) && 1 === absint($_REQUEST['force-check']) && current_user_can('administrator') ){
			\Rey\Plugins::refresh();
		}
	}

	function theme_api(){
		return ReyTheme_API::getInstance();
	}

	/**
	 * Register purchase code method
	 *
	 * @since 1.0.0
	 */
	protected function register(){

		// check if empty
		if ( empty( $_POST['rey_purchase_code'] ) ) {
			wp_send_json_error( esc_html__( 'Please enter your purchase code.', 'rey' ) );
		}

		$purchase_code = trim( $_POST['rey_purchase_code'] );

		// check if valid UUID
		if ( ! preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $purchase_code ) ) {
			wp_send_json_error( esc_html__( 'Please enter the correct purchase code format, eg: 00000000-0000-0000-0000-000000000000.', 'rey' ) );
		}

		$args = [
			'purchase_code' => $purchase_code,
			'email_address' => sanitize_email( $_POST['rey_email_address'] ),
		];

		// check if newsletter is checked
		if( !empty($args['email_address']) && isset($_POST['rey_subscribe_newsletter']) && absint($_POST['rey_subscribe_newsletter']) === 1 ){
			$this->set_subscribed_newsletter();
		}

		// send registration request
		$request = $this->theme_api()->register( $args );

		// check for errors
		if ( is_wp_error( $request ) ) {
			wp_send_json_error( $request->get_error_message() );
		}

		// check if status is invalid
		if ( isset($request['success']) && !$request['success'] ) {
			$error_msg = $this->theme_api()->get_error_message( esc_html( $request['data'] ) );
			wp_send_json_error( $error_msg );
		}

		if( isset($request['data']['status']) ) {
			if( $request['data']['status'] === self::STATUS_VALID){
				$this->__store_purchase_code( $purchase_code );
				wp_send_json_success();
			}
			elseif( $request['data']['status'] === self::STATUS_ALREADY_EXISTS){
				$error_msg = $this->theme_api()->get_error_message( esc_html( $request['data']['status'] ) );
				wp_send_json_error( $error_msg );
			}
			else {
				wp_send_json_error( esc_html__('Purchase code seems to be invalid.', 'rey') );
			}
		}

		return false;
	}

	/**
	 * Add registration notice in admin pages, if unregistered.
	 *
	 * @since 1.0.0
	 */
	function add_registration_notice()
	{
		if( self::get_purchase_code() ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php printf( wp_kses( __('Please <a href="%s">register</a> %s to enable its features, importing pre-made designs and templates, and install the latest updates and premium plugins.', 'rey'), ['a' => ['href' => []]] ), esc_url( add_query_arg( ['page'=>self::DASHBOARD_PAGE_ID ], admin_url('admin.php'))), ucfirst(REY_THEME_NAME) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Install Rey Child theme
	 *
	 * @since 1.0.0
	 */
	public static function install_child_theme() {

		$url    = REY_THEME_DIR . '/inc/files/rey-child.zip';

		if ( ! current_user_can( 'install_themes' ) ) {
			rey__log_error( 'err024', __('Forbidden to install child themes.', 'rey') );
			return false;
		}

		if ( ! class_exists( 'Theme_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$skin = new Automatic_Upgrader_Skin();

		$upgrader = new Theme_Upgrader( $skin, array( 'clear_destination' => true ) );
		$result   = $upgrader->install( $url );

		// There is a bug in WP where the install method can return null in case the folder already exists
		// see https://core.trac.wordpress.org/ticket/27365
		if ( $result === null && ! empty( $skin->result ) ) {
			$result = $skin->result;
		}

		if ( is_wp_error( $skin->result ) ) {
			rey__log_error( 'err023', $result->get_error_message() );
			return false;
		}

		return true;
	}

	/**
	 * Method used to deregister current purchase code
	 *
	 * @since 1.0.0
	 */
	protected function deregister(){

		$purchase_code = self::get_purchase_code();

		if( ! $purchase_code ){
			return false;
		}

		if( ! preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $purchase_code ) ){
			$this->delete_purchase_code();
			return false;
		}

		$request = $this->theme_api()->deregister([
			'purchase_code' => $purchase_code
		]);

		if( is_wp_error( $request ) ){
			return ['error' => $request->get_error_message()];
		}

		do_action('rey/deregistered', $purchase_code);

		return $this->delete_purchase_code();
	}

	/**
	 * Method to enable child theme
	 * @since 1.0.0
	 */
	public static function enable_child_theme() {

		$child_theme = self::get_child_theme();

		if ( $child_theme !== false ) {
			switch_theme( $child_theme->get_stylesheet() );
		}

		wp_send_json_success();
	}

	/**
	 * Check for child theme
	 * @since 1.0.0
	 */
	public static function get_child_theme()
	{
		$child_theme = false;
		$current_installed_themes = wp_get_themes();
		$active_theme      = wp_get_theme();
		$theme_folder_name = $active_theme->get_template();

		if ( is_array( $current_installed_themes ) ) {
			foreach ( $current_installed_themes as $key => $theme_obj ) {
				if ( $theme_obj->get( 'Template' ) === $theme_folder_name ) {
					$child_theme = $theme_obj;
				}
			}
		}

		return $child_theme;
	}

	/**
	 * Store purchase code and load plugins from API
	 *
	 * @since 1.0.0
	 */
	private function __store_purchase_code( $code )
	{
		// store the purchase code
		// check if registered with `get_purchase_code()`
		$this->set_purchase_code( $code );

		// generate state nonce
		update_option( 'rey_state_notice', false);

		// set mandatory plugins
		\Rey\Plugins::refresh();

	}

	public static function get_ips_raw(){
		return [
			'public' => (($public_ip = wp_safe_remote_get('http://ipecho.net/plain')) && ! is_wp_error($public_ip)) ? wp_remote_retrieve_body($public_ip) : '',
			'local' => gethostbyname(gethostname())
		];
	}

	public static function get_ips_data( $object = false){

		/**
		 * Use ?rey_show_server_ip in Dashboard to show the Public IP address.
		 */
		$raw_data = self::get_ips_raw();

		$data = '<div class="rey-connectionIps" data-copy-contents data-corner-label="Click to copy">';

		// Usually the one that gets blocked in firewalls
		$data .= sprintf('Public IP: <strong>%s</strong> <br>', $raw_data['public']);
		$data .= sprintf('Local IP: <strong>%s</strong> <br>', $raw_data['local']);

		$data .= '</div>';

		if( $object ){
			return [
				'data' => $data,
				'public' => $raw_data['public'],
				'local' => $raw_data['local']
			];
		}

		return $data;
	}

	public static function get_failed_connection_message( $error = null ){

		$message = '<div class="failed-connection-message">';

		if( $error ){
			$message .= sprintf( '<p class="__error"><strong>Error:</strong> %s</p>', $error );
		}

		$message .= '<p>Requests to the Rey API are currently experiencing issues, preventing you from registering, importing demos, downloading templates, or installing updates for Rey. These problems can occur for various reasons, including:</p>';

		$message .= '<ul>';

			// Check if HTTPS is on or if the server is using the standard HTTPS port (443)
			if (
				( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) &&
				( strpos(get_site_url(), 'https://') !== 0 || strpos(get_home_url(), 'https://') !== 0 )
			) {
				$message .= sprintf('<li><span class="__icon-count">1</span>WordPress Address (URL) and Site Address (URL) are incorrectly setup and they\'re missing the "<strong><em>https</em></strong>" protocol. Please visit the <a href="%s">Settings page</a> and update them now.</li>', admin_url('options-general.php'));
			}
			else {
				$message .= '<li><span class="__icon-count">1</span>The SSL certificate is not properly setup. Plugins such as <a href="https://wordpress.org/plugins/really-simple-ssl/" target="_blank">Really Simple SSL</a> may fix this problem.</li>';
			}

			$ip_data = self::get_ips_raw();

			$support_url = rey__support_url('new') . '?' . http_build_query([
				'api-connection-fail' => 1,
				'subject'             => 'API Connection fail',
				'public'              => $ip_data['public'],
				'local'               => $ip_data['local'],
				'url'                 => get_site_url(),
			]);

			$message .= '<li>';
				$message .= '<p><span class="__icon-count">2</span>Or, there may be issues with your server accessing our API.</p>';
				$message .= sprintf('<p>This can occur if our hosting blocked your server\'s IP address. Often, this situation arises with shared hosting environments, where multiple websites share the same IP address. If any of these sites engage in suspicious activities, it can lead to the entire IP range being compromised and potentially blocked by our systems.</p>
				If you believe this has happened, please <a href="%s" target="_blank">contact our support team</a> for assistance. We\'re here to help resolve the issue and restore your access as quickly as possible.</p>', $support_url);
				$message .= '<p><code>IP addresses: ' . $ip_data['public'] . ' (Public), ' . $ip_data['local'] . ' (Local).</code></p>';
			$message .= '</li>';

		$message .= '</ul>';
		$message .= '</div>';

		return $message;
	}

	/**
	 * Store purchase code
	 *
	 * @since 1.0.0
	 */
	private function set_purchase_code($purchase_code){
		return update_site_option( self::REGISTRATION_OPTION_ID, $purchase_code );
	}

	/**
	 * Get purchase code
	 *
	 * @since 1.0.0
	 */
	public static function get_purchase_code() {

		static $opt;

		if( is_null($opt) ){

			$opt = trim( get_site_option( self::REGISTRATION_OPTION_ID ) );

			if( self::REGISTRATION_OPTION_ID === $opt || ! preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $opt ) ){
				delete_site_option( self::REGISTRATION_OPTION_ID );
				$opt = false;
			}
		}

		return $opt;
	}

	/**
	 * Remove purchase code option
	 *
	 * @since 1.0.0
	 */
	private function delete_purchase_code() {
		return delete_site_option( self::REGISTRATION_OPTION_ID );
	}

	/**
	 * Set subscribed option
	 *
	 * @since 1.0.0
	 */
	protected function set_subscribed_newsletter(){
		return update_site_option( self::SUBSCRIBE_NEWSLETTER_OPTION_ID, 'yes' );
	}

	/**
	 * Check if subscribed to newsletter
	 *
	 * @since 1.0.0
	 */
	public function is_subscribed_to_newsletter() {
		return trim( get_site_option( self::SUBSCRIBE_NEWSLETTER_OPTION_ID ) ) === 'yes';
	}

	/**
	 * Get the purchase code, with only a few characters shown.
	 *
	 * @since 1.0.0
	 */
	public static function get_hidden_purchase_code() {
		$input_string = self::get_purchase_code();

		$start = 5;
		$length = mb_strlen( $input_string ) - $start - 5;

		$mask_string = preg_replace( '/\S/', 'x', $input_string );
		$mask_string = mb_substr( $mask_string, $start, $length );
		$input_string = substr_replace( $input_string, $mask_string, $start, $length );

		return $input_string;
	}

	public function parse_response($data){
		if( ! empty($data['rstatus']) ){
			$this->deregister();
			update_option( 'rey_state_notice', rey__wp_kses($data['rstatus']));
		}
	}

	/**
	 * Tests connection to Rey's API
	 *
	 * @return void
	 */
	public function ajax__dashbox_test_connection(){

		if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
			wp_send_json_error('Operation not allowed!');
		}

		$data_response = [
			'available' => true,
			'status_table' => [
				'flag' => '',
				'content' => '',
			],
			'registration_form' => [
				'content' => '',
			],
		];

		$is_registered = self::get_purchase_code();

		$request = wp_safe_remote_get( \ReyTheme_API::$api_site_url );

		if ( is_wp_error( $request ) ) {
			$data_response['available'] = false;
			$data_response['status_table']['flag'] = '<code class="ssFlag ssFlag--danger">Unavailable</code>';
			$fail_message = self::get_failed_connection_message( $request->get_error_message() );
			if( $is_registered ){
				$data_response['status_table']['content'] = $fail_message;
			}
			else {
				$data_response['registration_form']['content'] = $fail_message;
			}
			wp_send_json_success($data_response);
		}

		$status_code = wp_remote_retrieve_response_code( $request );

		if( 429 === $status_code ){
			$data_response['available'] = false;
			$data_response['status_table']['flag'] = '<code class="ssFlag ssFlag--warning">Temporarily blocked</code>';
			$data_response['status_table']['content'] = '<p class="__text"><small>Access to the Rey API is temporarily blocked for an hour because it has repeatedly reached the server\'s rate limit. If the block persists, please contact support and attach the IPs below.</small></p>' . self::get_ips_data();
			wp_send_json_success($data_response);
		}

		else if( 200 !== $status_code ){
			$data_response['available'] = false;
			$data_response['status_table']['flag'] = '<code class="ssFlag ssFlag--danger">Unavailable</code>';
			$fail_message = self::get_failed_connection_message( $status_code );
			if( $is_registered ){
				$data_response['status_table']['content'] = $fail_message;
			}
			else {
				$data_response['registration_form']['content'] = $fail_message;
			}
			wp_send_json_success($data_response);
		}

		$data_response['status_table']['flag'] = '<code class="ssFlag ssFlag--success">Available</code>';

		wp_send_json_success($data_response);
	}
}

new ReyTheme_Base;
