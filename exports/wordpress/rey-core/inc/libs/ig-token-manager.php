<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class IgTokenManager {

	const ERROR_DB_KEY = 'rey_ig_token_cron_error_message';
	const OPTION_KEY = 'ig_access_token';

	const CRON_NAME = 'rey_refresh_instagram_token';
	const CRON_KEY = 'monthly';
	const CRON_DISPLAY = 'Monthly';
	const CRON_INTERVAL = MONTH_IN_SECONDS;

	public function __construct() {

		// add_action( 'init', [ $this, 'set_schedule' ] );
		add_action( 'reycore/elementor/instagram/before_render', [ $this, 'set_schedule' ] );
		add_action( self::CRON_NAME, [ $this, 'refresh_token' ] );
		add_filter( 'cron_schedules', [ $this , 'cron_schedules' ] );

		add_action( 'update_option', [ $this, 'update_option' ], 10, 3 );
		add_action( 'admin_notices', [ $this, 'print_admin_error_notices' ] );

		add_filter( 'acf/prepare_field/name=' . self::OPTION_KEY, [$this, 'add_refresh_button'] );
		add_action( 'wp_ajax_rey_refresh_ig_token', [$this, 'ajax_refresh_token'] );

	}

	/**
	 * Retrieve the token option key (name)
	 *
	 * @return string
	 */
	public static function get_token_option_key(){
		return REY_CORE_THEME_NAME . '_' . self::OPTION_KEY;
	}

	/**
	 * Retrieve the Instagram Access Token
	 *
	 * @return string
	 */
	public static function get_token(){
		return get_option( self::get_token_option_key() );
	}

	/**
	 * Add schedule
	 * https://developers.facebook.com/docs/instagram-basic-display-api/guides/long-lived-access-tokens/
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function cron_schedules( $schedules ) {

		if ( ! isset($schedules[self::CRON_KEY]) ) {
			$schedules[ self::CRON_KEY ] = [
				'interval' => self::CRON_INTERVAL,
				'display'  => self::CRON_DISPLAY
			];
		}

		return $schedules;
	}

	/**
	 * Set Schedule
	 * Make sure token is set
	 *
	 * @return void
	 */
	public function set_schedule(){
		if( ! empty( $this->get_token() ) && ! wp_doing_ajax() ){
			$this->schedule_token_refresh_event();
		}
	}

	/**
	 * Schedule event
	 *
	 * @return void
	 */
	public function schedule_token_refresh_event() {
		if ( ! wp_next_scheduled( self::CRON_NAME ) ) {
			wp_schedule_event( time(), self::CRON_KEY, self::CRON_NAME );
		}
	}

	/**
	 * Delete errors and clear cron
	 * when IG Token option is updated
	 *
	 * @param string $option_name
	 * @param string $old_value
	 * @param string $value
	 * @return void
	 */
	public function update_option( $option_name, $old_value, $value ) {

		if ( $option_name !== self::get_token_option_key() ) {
			return;
		}

		delete_option( self::ERROR_DB_KEY );

		if ( $value === '' ) {
			$this->clear_scheduled_event();
		}

	}

	public function clear_scheduled_event() {

		if ( $timestamp = wp_next_scheduled( self::CRON_NAME ) ) {
			wp_unschedule_event( $timestamp, self::CRON_NAME );
		}

	}

	public function refresh_token() {

		if ( ! ($ig_token = self::get_token()) ) {
			return;
		}

		if( defined('WP_DEBUG') && WP_DEBUG ){
			error_log(var_export( 'Refresh Instagram TOKEN.', true));
		}

		$api_req  = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $ig_token;
		$response = wp_safe_remote_get( $api_req );

		if ( is_wp_error( $response ) ) {
			update_option( self::ERROR_DB_KEY, $response->get_error_message() );
		}

		else {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), false );

			if ( $response_body && json_last_error() === JSON_ERROR_NONE ) {

				if ( isset( $response_body->error ) ) {
					update_option( self::ERROR_DB_KEY, $response_body->error->message );
				}

				else {

					delete_option( self::ERROR_DB_KEY );

					if ( ! empty( $response_body->access_token ) ) {

						// Update token
						update_option( self::get_token_option_key(), $response_body->access_token );

						// Delete cache data
						$this->clear_feeds_cache();

						// Schedule token refresh
						$this->schedule_token_refresh_event();
					}

				}
			}

			else {
				update_option( self::ERROR_DB_KEY, __( 'Error in json_decode.', 'rey-core' ) );
			}

		}
	}

	public function clear_feeds_cache(){
		return \ReyCore\Helper::clean_db_transient( 'rey_insta_' );
	}

	public function add_refresh_button( $field ) {

		if ( self::get_token() ) {
			$field['instructions'] .= __(' Click to <a href="#" class="js-ig-refresh-token">Refresh Access Token</a>.', 'rey-core');
		}

		return $field;
	}

	public function ajax_refresh_token(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		$this->refresh_token();

		wp_send_json_success();
	}

	public function print_admin_error_notices() {

		if( ! (isset( $_REQUEST['page'] ) && REY_CORE_THEME_NAME . '-settings' === reycore__clean($_REQUEST['page'])) ){
			return;
		}

    	global $pagenow;

		if( 'admin.php' !== $pagenow ){
			return;
		}

		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		if ( ! ($errors = get_option( self::ERROR_DB_KEY )) ) {
			return;
		}

		if ( ! empty( $errors ) ) {
		?>
			<div class="notice notice-error">
				<p>
					<?php
						echo esc_html( sprintf(
							//translators: %s is an error message
							__( 'An error occurred while updating Instagram access token: %s', 'rey-core' ),
							$errors
						) );
					?>
				</p>
			</div>
		<?php
		}
    }

}
