<?php
namespace ReyCore\Modules\OrderRefunds;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	private static $message = [];
	private static $order_id = '';

	const ASSET_HANDLE = 'reycore-module-order-refunds';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		$this->settings = apply_filters('reycore/woocommerce/returns', [
			'subject'         => esc_html__('Return request from %s', 'rey-core'),
			'heading'         => esc_html__('Return request', 'rey-core'),
			'email'           => get_bloginfo('admin_email'),
			'email_requester' => true,
			'order_text'      => '{{ID}} ( {{DATE}} / {{TOTAL}}{{CURRENCY}} )',
			'error_not_sent'  => esc_html__('Something went wrong and the request hasn\'t been sent. Please retry later!', 'rey-core'),
			'success_msg'     => esc_html__('Return request sent successfully.', 'rey-core'),
			'endpoint'        => 'refund-request',
			'order_args'      => ['wc-completed', 'wc-processing'], // eg: 'status' => ['wc-processing', 'wc-on-hold'],
			'fields_errors'   => [
				'observation'  => esc_html__('No reason provided!', 'rey-core'), // *
				'name'         => esc_html__('Product name is missing!', 'rey-core'), // *
				'product_id'   => esc_html__('Product ID is missing!', 'rey-core'), // *
			],
		]);

		add_rewrite_endpoint( $this->settings['endpoint'], EP_ROOT | EP_PAGES );

		add_action( 'wp', [$this, 'wp']);

	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'refund_request_order_products', [$this, 'ajax__refund_request_order_products'], 1 );
		$ajax_manager->register_ajax_action( 'refund_request_submit', [$this, 'ajax__refund_request_submit'], 1 );
	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function wp(){

		add_filter( 'query_vars', [$this, 'set_query_vars'], 0 );
		add_filter( 'woocommerce_get_query_vars', [$this, 'set_query_vars'], 0 );
		add_filter( 'woocommerce_account_menu_items', [$this, 'set_menu_item'] );
		add_action( "woocommerce_account_{$this->settings['endpoint']}_endpoint", [$this, 'add_content'] );

		if( ! is_wc_endpoint_url( $this->settings['endpoint'] ) ) {
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [$this, 'load_scripts']);
		add_action( 'wp_footer', [$this, 'product_template']);

	}

	public function register_assets($assets){

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version' => REY_CORE_VERSION,
			],
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'         => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'        => [],
				'version'     => REY_CORE_VERSION,
				'priority'    => 'low',
			]
		]);

	}

	public function add_content() {

		if( ! is_user_logged_in() ){
			printf('<p>%s</p>', esc_html__('Please login to show form.', 'rey-core') );
			return;
		}

		echo '<div class="rey-refundsPage">';

			printf('<h2 class="rey-refundsPage-title">%s</h2>', get_theme_mod('refunds__page_title', esc_html__('Request Return', 'rey-core')) );
			printf('<div class="rey-refundsPage-before">%s</div>', wpautop(do_shortcode(get_theme_mod('refunds__content', ''))) );

			$orders = $this->get_orders();

			if( ! empty($orders) ){
				$this->get_form( $orders );
			}
			else {
				printf('<p>%s</p>', esc_html__('No orders yet.', 'rey-core'));
			}

		echo '</div>';
	}

	public function get_orders(){

		$args = wp_parse_args( [
			'customer_id' => get_current_user_id(),
			// 'return' => 'ids',
		], $this->settings['order_args'] );

		return wc_get_orders($args);
	}

	public function get_form( $orders ){

		reycore_assets()->add_styles('rey-form-row'); ?>

		<div class="rey-refundsPage-orders">

			<form action="" class="woocommerce-form" method="post">

				<div class="rey-refundsPage-response --empty"></div>

				<p class="form-row">
					<label for="order_id"><?php esc_html_e('Select order', 'rey-core') ?> <span class="required">*</span></label>
					<select name="order_id" id="order_id" required>
						<option value=""><?php esc_html_e('-- Select --', 'rey-core') ?></option>
						<?php

						foreach ($orders as $order) {

							$data = sprintf('%s ( %s / %s%s )',
								sprintf( esc_html__( 'Order #%d', 'rey-core' ), $order->get_id() ),
								wc_format_datetime($order->get_date_created()),
								$order->get_total(),
								get_woocommerce_currency_symbol()
							);

							$data = $this->settings['order_text'];

							$data = str_replace( '{{ID}}', sprintf( esc_html__( 'Order #%d', 'rey-core' ), $order->get_id() ), $data);
							$data = str_replace( '{{DATE}}', wc_format_datetime($order->get_date_created()), $data);
							$data = str_replace( '{{TOTAL}}', $order->get_total(), $data);
							$data = str_replace( '{{CURRENCY}}', get_woocommerce_currency_symbol(), $data);

							printf('<option value="%d">%s</option>', $order->get_id(), $data);
						} ?>
					</select>
				</p>

				<div class="form-row __items"></div>

				<p class="form-row">
					<button type="submit" class="btn btn-primary"><?php esc_html_e('Send Request', 'rey-core') ?></button>
				</p>

			</form>
		</div>

		<?php
		reycore_assets()->add_styles([self::ASSET_HANDLE, 'rey-buttons']);
	}

	public function product_template(){
		?>
		<script type="text/html" id="tmpl-rey-order-refund-template">
			<# Object.keys(data.items).forEach( function(i) { #>
				<# var dataString = JSON.stringify(data);
				var itemData = data.items[i];
				var qtyDisabledAttr = itemData.quantity <= 1 ? 'disabled' : '';
				var itemPrice = itemData.total/itemData.quantity; #>
				<div class="__item" data-id="{{i}}" data-send="{{dataString}}" data-click-sel="<?php esc_html_e('CLICK TO SELECT', 'rey-core') ?>">
					<h4 class="__product-title">{{itemData.name}} ( {{itemData.quantity}} x {{{itemData.currency}}} {{itemPrice}} )</h4>
					<div class="__item-data">
						<div class="__qty-box">
							<label for="qty_{{i}}"><?php esc_html_e('Quantity', 'rey-core') ?></label>
							<input type="number" name="qty" id="qty_{{i}}" class="__qty" {{qtyDisabledAttr}} min="1" max="{{itemData.quantity}}" step="1" value="1">
						</div>
						<div class="__reasons-box">
							<label for="observation_{{i}}"><?php esc_html_e('Reason and observations', 'rey-core') ?> <span class="required">*</span></label>
							<input type="text" name="observation" id="observation_{{i}}" class="__reasons" data-required />
						</div>
					</div>
					<input type="hidden" name="name" value="{{itemData.name}}">
					<input type="hidden" name="product_id" value="{{itemData.product_id}}">
					<input type="hidden" name="variation_id" value="{{itemData.variation_id}}">
					<input type="hidden" name="tax" value="{{itemData.tax}}">
					<input type="hidden" name="price" value="{{{itemData.currency}}} {{itemPrice}}">
				</div>
			<# }); #>
		</script>
		<?php

	}

	function ajax__refund_request_order_products( $action_data ){

		if( !(isset($action_data['order']) && $order_id = absint($action_data['order'])) ){
			return ['errors' => esc_html__('Order id not provided!', 'rey-core')];
		}

		$order = wc_get_order($order_id);

		$data = [];

		foreach ( $order->get_items() as $item_id => $item ) {

			$total      = $item->get_total();
			$total_tax  = $item->get_subtotal_tax();

			$data[$item_id] = [
				'product_id'     => $item->get_product_id(),
				'variation_id'   => $item->get_variation_id(),
				'name'           => $item->get_name(),
				'total'          => $total,
				'tax'            => $total_tax,
				'quantity'       => $item->get_quantity(),
				'currency'       => get_woocommerce_currency_symbol(),
				'total_with_tax' => wp_strip_all_tags(
					wc_price($total + $total_tax, [
						'ex_tax_label' => true,
					])
				),
			];
		}

		return $data;
	}

	public function ajax__refund_request_submit( $action_data ){

		if( ! (isset($action_data['order']) && (self::$order_id = absint($action_data['order']))) ){
			return ['error' => esc_html__('Order id not provided!', 'rey-core')];
		}

		if( ! (isset($action_data['products']) && ($products = reycore__clean($action_data['products']))) ){
			return ['error' => esc_html__('Products missing!', 'rey-core')];
		}

		$fields = [
			'name'         => esc_html__('Name', 'rey-core'), // *
			'qty'          => esc_html__('Quantity', 'rey-core'),
			'observation'  => esc_html__('Reason', 'rey-core'), // *
			'product_id'   => esc_html__('Product ID', 'rey-core'), // *
			'variation_id' => esc_html__('Variation ID', 'rey-core'),
			'tax'          => esc_html__('Tax', 'rey-core'),
			'price'        => esc_html__('Price', 'rey-core'),
		];

		$errors = $products_message = [];

		foreach ($products as $i => $product_fields) {

			$product_data = [];
			$product_id = 0;

			foreach ($fields as $key => $title) {

				if( ! isset($product_fields[$key]) && isset($this->settings['fields_errors'][ $key ]) ){
					$errors[] = $this->make_notice($this->settings['fields_errors'][ $key ]);
					continue;
				}

				if( 'product_id' === $key ){
					$product_id = $product_fields[$key];
				}

				$product_data[$key] = sprintf('%s: %s;<br>', $title, $product_fields[$key]);
			}

			if( $product = wc_get_product($product_id) ){
				$product_data['link'] = sprintf('<a href="%s">%s</a><br>', $product->get_permalink(), esc_html__('Product URL', 'rey-core'));
			}

			$products_message[] = implode('', $product_data);
		}

		if( empty($product_data) ){
			$errors[] = $this->make_notice('No products!');
		}

		if( ! empty($errors) ){
			do_action('reycore/woocommerce/returns/submit=fail', $errors, $this);
			return [ 'errors' => $errors ];
		}

		$message['start'] = sprintf( '<strong>%s:</strong> <a href="{{URL}}">#%s</a>',
			esc_html_x('Order', 'Refunds form mail title', 'rey-core'),
			self::$order_id
		);

		$message['title'] = esc_html_x('Products:', 'Refunds form mail title', 'rey-core');

		$message['products_content'] = implode('----<br>', $products_message);

		if( ($msg = implode('<br>', $message)) && $this->send_email_woocommerce_style( $msg ) ){

			do_action('reycore/woocommerce/returns/submit=success', $this);

			return $this->make_notice($this->settings['success_msg'], 'check');
		}
		else {
			return [
				'errors' => $this->make_notice($this->settings['error_not_sent'])
			];
		}
	}

	public function send_email_woocommerce_style($message) {

		$user = wp_get_current_user();

		$name = $user->user_login;
		if( $user->first_name && $user->last_name ) {
			$name = " {$user->first_name} {$user->last_name}";
		}
		elseif( $user->first_name ) {
			$name = " {$user->first_name}";
		}

		// @email - Email address of the reciever
		$email = $this->settings['email'];

		// @subject - Subject of the email
		$subject = sprintf($this->settings['subject'], $name);

		// @heading - Heading to place inside of the woocommerce template
		$heading = $this->settings['heading'];

		// Get woocommerce mailer from instance
		$mailer = WC()->mailer();

		// Wrap message using woocommerce html email template
		$wrapped_message = $mailer->wrap_message($heading, $message);

		// Create new WC_Email instance
		$wc_email = new \WC_Email;

		// Style the wrapped message with woocommerce inline styles
		$html_message = $wc_email->style_inline($wrapped_message);

		$headers = [
			"Content-Type: text/html; charset=UTF-8",
		];

		if( $this->settings['email_requester'] ){
			$store_name = get_bloginfo('name');
			wp_mail(
				$user->user_email,
				$subject,
				str_replace('{{URL}}', wc_get_endpoint_url( 'view-order', self::$order_id, wc_get_page_permalink( 'myaccount' ) ), $html_message ),
				array_merge($headers, [
					"Reply-to: {$store_name} <{$email}>"
				])
			);
		}

		// Send the email using wordpress mail function
		return wp_mail(
			$email,
			$subject,
			str_replace('{{URL}}', admin_url( sprintf('post.php?post=%d&action=edit', self::$order_id) ), $html_message ),
			array_merge($headers, [
				"Reply-to: {$name} <{$user->user_email}>"
			])
		);
	}

	public function make_notice($message, $icon = 'close'){
		return '<p class="__msg">' . reycore__get_svg_icon(['id' => $icon]) . '<span>' . $message . '</span></p>';
	}

	public function set_query_vars( $vars ) {

		$vars[ $this->settings['endpoint'] ] = $this->settings['endpoint'];

		return $vars;
	}

	public function set_menu_item( $items ) {

		$afterIndex = 4;

		$rr = [
			$this->settings['endpoint'] => get_theme_mod('refunds__menu_text', esc_html__('Request Return', 'rey-core'))
		];

		return array_merge( array_slice( $items, 0, $afterIndex + 1 ), $rr, array_slice( $items, $afterIndex + 1 ));
	}

	public function load_scripts(){

		global $wp_query;

		if( ! isset($wp_query->query[$this->settings['endpoint']]) ){
			return;
		}

		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function is_enabled(){
		return get_theme_mod('refunds__enable', false);
	}

	public static function __config(){
		return [
			'id'          => basename(__DIR__),
			'title'       => esc_html_x('Order Refunds Form', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a Refund/Returns form inside the customer account page.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/refund-form/'),
			'video'       => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
