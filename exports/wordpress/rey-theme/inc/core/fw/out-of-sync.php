<?php
namespace Rey;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Handles out of sync notices to force major updates on both Core and Theme.
 */
class OutOfSync
{
	private static $items = [];

	const THEME_SLUG = 'rey';

	const CORE_SLUG = 'rey-core';

	public function __construct()
	{

		// determine which lib to load
		// and if it should load
		if( ! self::should_load() ){
			return;
		}

		define('REY_OUTDATED', true);

		// Legacy, remove old hooks
		if( class_exists('\ReyTheme_Base') && is_callable(['ReyTheme_Base', 'out_of_sync']) && function_exists('reycore__remove_filters_for_anonymous_class') ){
			reycore__remove_filters_for_anonymous_class( 'admin_notices', 'ReyTheme_Base', 'out_of_sync', 10 );
			reycore__remove_filters_for_anonymous_class( 'wp_body_open', 'ReyTheme_Base', 'out_of_sync', 10 );
		}

		add_action( 'admin_notices', [$this, 'render_notice'] );
		add_action( 'wp_body_open', [$this, 'render_notice'] );
		add_action( 'wp_ajax_rey_outdated_link', [$this, 'ajax__outdated_link'] );
		add_action( 'wp_ajax_rey_download_link', [$this, 'ajax__download_link'] );
		add_action( 'wp_ajax_rey_get_actions', [$this, 'ajax__get_actions'] );
	}

	/**
	 * Determines if the library should load
	 *
	 * @return boolean
	 */
	public static function should_load(){

		if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
			return;
		}

		self::$items = [
			self::THEME_SLUG => [
				'version_constant' => 'REY_THEME_VERSION',
				'type'             => 'theme',
				'basename'         => self::THEME_SLUG,
			],
			self::CORE_SLUG => [
				'version_constant' => 'REY_CORE_VERSION',
				'type'             => 'plugin',
				'basename'         => 'rey-core/rey-core.php',
			],
		];

		$theme_version = self::$items[self::THEME_SLUG]['version_constant'];
		$core_version = self::$items[self::CORE_SLUG]['version_constant'];

		// must have both Core and Theme active
		if( ! ( defined( $theme_version ) && defined( $core_version ) ) ){
			return false;
		}

		self::$items[self::THEME_SLUG]['major_version'] = self::get_item_major_version( constant($theme_version) );
		self::$items[self::CORE_SLUG]['major_version'] = self::get_item_major_version( constant($core_version) );

		// if both major versions are similar, no need to continue
		if( self::$items[self::THEME_SLUG]['major_version'] === self::$items[self::CORE_SLUG]['major_version'] ){
			return false;
		}

		$ns = strtolower(__NAMESPACE__);

		// we're inside Core
		if( $ns === str_replace('-', '', self::CORE_SLUG) ){
			$v1 = constant($core_version);
			$v2 = constant($theme_version);
		}
		// we're inside theme
		elseif( $ns === self::THEME_SLUG ) {
			$v1 = constant(self::$items[self::THEME_SLUG]['version_constant']);
			$v2 = constant(self::$items[self::CORE_SLUG]['version_constant']);
		}

		return version_compare( $v1, $v2, '>' );
	}

	/**
	 * Retrieves the items.
	 *
	 * @return array
	 */
	public static function get_items(){

		if( function_exists('rey__get_props') ):
			// define only if undefined
			if( ! isset(self::$items[self::THEME_SLUG]['name']) ){
				self::$items[self::THEME_SLUG]['name'] = rey__get_props('theme_title');
			}
			if( ! isset(self::$items[self::CORE_SLUG]['name']) ){
				self::$items[self::CORE_SLUG]['name'] = rey__get_props('core_title');
			}
		endif;

		return self::$items;
	}

	/**
	 * Retrieve major version
	 *
	 * @param string $version
	 * @return string
	 */
	public static function get_item_major_version( $version ){
		$v = array_map('absint', explode( '.', $version ));
		return sprintf('%d.%d.0', $v[0], $v[1]);
	}

	public static function get_config( $slug, $items ){

		$sibling = self::CORE_SLUG === $slug ? self::THEME_SLUG : self::CORE_SLUG;

		if( ! version_compare( constant($items[ $sibling ]['version_constant']), $items[ $slug ]['major_version'], '<' ) ){
			return [];
		}

		return [
			'to_update'         => $items[ $sibling ]['name'],
			'from'              => $items[ $slug ]['name'],
			'major_version'     => $items[ $slug ]['major_version'],
			'to_update_version' => constant($items[ $sibling ]['version_constant']),
			'slug'              => $sibling,
			'type'              => $items[ $sibling ]['type']
		];
	}

	public function render_notice(){

		$items = self::get_items();

		$maybe_load_assets = [];

		foreach ( array_keys($items) as $slug ) {

			$config = self::get_config($slug, $items);

			if( empty($config) ){
				continue;
			}

			$maybe_load_assets[] = true;

			$sync_error = sprintf(
				__('<p><strong>%s</strong> is outdated and not in sync with <strong>%s</strong>.</p>', 'rey'),
				$config['to_update'],
				$config['from']
			);

			$sync_error .= sprintf(
				__('<p>The minimum %1$s version should be <strong>%2$s</strong> (but currently it\'s %3$s). If the site is not running the latest major version, there could be issues or errors because the <em>Core plugin</em> depends on the <em>Theme</em> in various aspects. Please <a href="%4$s" target="_blank"><u>follow this article</u></a> which explains more about this problem.', 'rey'),
				$config['to_update'],
				$config['major_version'],
				$config['to_update_version'],
				function_exists('rey__support_url') ? rey__support_url('kb/rey-theme-is-outdated-and-not-in-sync-with-rey-core-error/') : ''
			);

			$nonce = wp_create_nonce( 'updates' );

			$sync_error .= sprintf('<p id="outofsync-error-actions" style="display:none" data-slug="%s" data-nonce="%s"><span class="spinner"></span>', $config['slug'], $nonce);

			$sync_error .= sprintf(
				__('<strong><u><a href="#" data-slug="%2$s" data-nonce="%3$s" class="rey-genericBtn" id="js-update-link">Update %1$s now</a></u></strong>', 'rey'),
				$config['to_update'],
				$config['slug'],
				$nonce
			);

			$sync_error .= '&nbsp; or &nbsp;';

			$sync_error .= sprintf(
				__('<strong><u><a href="#" data-slug="%1$s" data-nonce="%2$s" class="rey-genericBtn" id="js-update-download-link">download</a></u></strong> and manually install it like you would install a new %3$s.', 'rey'),
				$config['slug'],
				$nonce,
				$config['type']
			);

			$sync_error .= '</p>';

			$sync_error .= '<p id="outofsync-error-spinner"></p>';

			$sync_error .= __('<p><small>This message only shows to administrators and is not public.</small></p>', 'rey');

			printf('<div class="rey-overlay --no-close" id="outofsync-error--overlay"></div><div id="outofsync-error">%s</div>', $sync_error);
		}

		if( in_array(true, $maybe_load_assets, true) ){
			$this->print_js();
			$this->print_css();
		}

	}

	public static function ajax_get_data(){

		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'updates' ) ) {
			return ['error' => 'Operation not allowed!'];
		}

		if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
			return ['error' => 'Operation not allowed!'];
		}

		if( ! function_exists('rey__clean') ){
			return ['error' => 'Missing clean function.'];
		}

		if( ! (isset($_POST['slug']) && $slug = rey__clean($_POST['slug'])) ){
			return ['error' => 'Missing type.'];
		}

		$items = self::get_items();

		if( ! isset($items[$slug]) ){
			return ['error' => 'Incorrect slug.'];
		}

		return [
			'slug' => $slug,
			'items' => $items,
		];
	}

	public function ajax__outdated_link(){

		$data = self::ajax_get_data();

		if( isset($data['error']) ){
			wp_send_json_error( $data['error'] );
		}

		if( ! class_exists('\Rey\Upgrader') ){
			require_once __DIR__ . '/upgrader.php';
		}

		$upgrader = new \Rey\Upgrader([
			'slug'     => $data['slug'],
			'basename' => $data['items'][ $data['slug'] ]['basename'],
			'hook'     => 'rey/update/outofsync',
		]);

		if( ! empty($upgrader->error) ){
			wp_send_json_error( $upgrader->error );
		}

		wp_send_json_success( $upgrader );
	}


	/**
	 * Handles downloading the lastest version
	 *
	 * @return void
	 */
	public function ajax__download_link(){

		$data = self::ajax_get_data();

		if( isset($data['error']) ){
			wp_send_json_error( $data['error'] );
		}

		if( ! ($download_link = self::get_download_url( $data['slug'] ) ) ){
			wp_send_json_error( 'Cannot retrieve download link.' );
		}

		wp_send_json_success($download_link);
	}

	public static function get_download_url( $slug ){

		if( ! class_exists('\ReyTheme_API') ){
			return false;
		}

		return \ReyTheme_API::getInstance()->get_download_url( self::THEME_SLUG === $slug ? 'theme' : $slug );
	}

	/**
	 * Handles downloading the lastest version
	 *
	 * @return void
	 */
	public function ajax__get_actions(){

		$data = self::ajax_get_data();

		if( isset($data['error']) ){
			wp_send_json_error( $data['error'] );
		}

		if( ! class_exists('\ReyTheme_API') ){
			wp_send_json_error();
		}

		$url = add_query_arg([
			'purchase_code' => \ReyTheme_Base::get_purchase_code()
			], \ReyTheme_API::$api_site_url
		);

		if( ! self::valid_url( $url ) ){
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Render CSS for handling the notice styling
	 *
	 * @return void
	 */
	public function print_css(){

		?>
		<style type="text/css">

		body:not(.wp-admin) #outofsync-error--overlay {
			visibility: visible;
			opacity: 1;
			pointer-events: auto;
			left: 0;
			cursor: auto;
		}

		#outofsync-error {
			--mleft: 2px;
			--mright: 20px;
			position: relative;
			margin: 25px var(--mright) 15px var(--mleft);
			padding: 35px 40px 30px;
			font-size: .9rem;
			background-color: HSL(var(--neutral-0));
			border: 2px solid HSL(var(--neutral-3));
			color: HSL(var(--neutral-7));
			box-shadow: var(--b-shadow-5);
			border-radius: 7px;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-weight: 400;
		}

		@media (min-width: 1025px) {
			#outofsync-error {
				--left-padding: 160px;
				padding-left: var(--left-padding);
			}
		}

		#outofsync-error a {
			color: HSL(var(--neutral-9));
		}

		#outofsync-error a:hover {
			text-decoration: none;
		}

		#outofsync-error p {
			font-size: 1em;
			margin-top: 0;
			margin-bottom: 1.2em;
		}

		#outofsync-error p:last-of-type {
			margin-bottom: 0;
		}

		#outofsync-error-spinner:after {
			content: "";
			display: inline-block;
			width: 1em;
			height: 1em;
			border: 2px solid transparent;
			border-right-color: currentColor;
			border-bottom-color: currentColor;
			border-radius: 50%;
			vertical-align: baseline;
			animation: spinner-border .75s linear infinite;
			opacity: .5;
		}

		@keyframes spinner-border {
			to {
				transform: rotate(360deg);
			}
		}

		@media (min-width: 1025px) {
			#outofsync-error:before {
				content: "\f534";
				font-family: 'dashicons';
				position: absolute;
				top: calc(50% - 0.5em);
				left: calc((var(--left-padding) / 2) - .5em);
				font-size: 80px;
				line-height: 1;
				opacity: .15;
			}
		}

		body:not(.wp-admin) #outofsync-error {
			--mleft: auto;
			--mright: auto;
			max-width: var(--container-max-width);
			width: 85%;
			position: fixed;
			z-index: 9999;
			top: 60px;
			left: 50%;
			transform: translateX(-50%);
		}

		@media (min-width: 1025px) {
			body:not(.wp-admin) #outofsync-error {
				width: 70%;
				top: 100px;
			}
		}

		</style>
		<?php
	}

	/**
	 * Render JS for handling the notice actions
	 *
	 * @return void
	 */
	public function print_js(){
		?>
		<script type="text/javascript">

		document.addEventListener("DOMContentLoaded", function() {

			var notice = document.getElementById('outofsync-error');

			if( ! notice ){
				return;
			}

			var responseHolder = notice.querySelector('#outofsync-error-actions');

			if( responseHolder && typeof jQuery !== 'undefined'){
				jQuery.ajax({
					method: "post",
					url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
					data: {
						action     : "rey_get_actions",
						slug       : responseHolder.getAttribute('data-slug'),
						_ajax_nonce: responseHolder.getAttribute('data-nonce'),
					},
					success: function (response) {

						var spinner = notice.querySelector('#outofsync-error-spinner');

						if( spinner ){
							spinner.remove();
						}

						if( response.success ){
							responseHolder.style.display = 'block';
						}
					},
				});
			}

			var updateLink = notice.querySelector('#js-update-link');

			var isUpdating = false;

			window.onbeforeunload = function () {
				if (isUpdating) {
					return "Updating is in progress! Please don't close window.";
				}
				return undefined;
			};

			updateLink && updateLink.addEventListener('click', function(e){
				e.preventDefault();

				var btn = e.currentTarget;

				isUpdating = true;

				btn.classList.add('--disabled');
				btn.classList.add('--loading');

				btn.textContent = 'Updating..';

				if( typeof jQuery === 'undefined' ){
					console.error('jQuery not defined.')
					return;
				}

				jQuery.ajax({
					method: "post",
					url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
					data: {
						action     : "rey_outdated_link",
						slug       : btn.getAttribute('data-slug'),
						_ajax_nonce: btn.getAttribute('data-nonce'),
					},
					success: function (response) {

						btn.classList.remove('--loading');
						btn.classList.remove('--disabled');

						btn.textContent = 'Reloading page..';

						isUpdating = false;

						setTimeout(function () {
							location.reload();
						}, 1000);
					},
				});

			});


			var downloadLink = notice.querySelector('#js-update-download-link');

			downloadLink && downloadLink.addEventListener('click', function(e){
				e.preventDefault();

				var btn = e.currentTarget;

				btn.classList.add('--disabled');
				btn.classList.add('--loading');

				if( typeof jQuery === 'undefined' ){
					console.error('jQuery not defined.')
					return;
				}

				jQuery.ajax({
					method: "post",
					url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
					data: {
						action     : "rey_download_link",
						slug       : btn.getAttribute('data-slug'),
						_ajax_nonce: btn.getAttribute('data-nonce'),
					},
					success: function (response) {

						btn.classList.remove('--loading');

						if( ! response ){
							console.error(response);
							return;
						}

						if( ! response.success ){
							btn.textContent = response.data;
							console.error(response.data);
							return;
						}

						btn.classList.remove('--disabled');

						var a = document.createElement('a');
							a.href = response.data;
							a.download = btn.getAttribute('data-slug');
							a.dispatchEvent(new MouseEvent('click'));
					},
				});
			});
		});

		</script>
		<?php
	}

	public static function valid_url($url)
	{
		$response = wp_safe_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}

new OutOfSync;
