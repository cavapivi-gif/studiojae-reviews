<?php
namespace ReyCore\Modules\RequestQuote;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompatWpforms {

	public $wpform;

	public function __construct()
	{

		if( ! function_exists('wpforms') ){
			return;
		}

		if( ! Base::instance()->is_enabled() ) {
			return;
		}

		if( get_theme_mod('request_quote__form_type', 'cf7') !== 'wpforms' ){
			return;
		}

		if( ! ($this->wpform = get_theme_mod('request_quote__wpforms', '')) ){
			return;
		}

		add_filter('reycore/woocommerce/request_quote/output', [$this, 'request_quote_output'], 10, 2);
		add_filter('wpforms_emails_send_email_data', [$this, 'add_custom_content'], 10, 2);

	}


	public function request_quote_output( $html, $args ){

		$args = wp_parse_args($args, [
			'class' => ''
		]);

		$shortcode = sprintf( '[wpforms id="%d"]', $this->wpform );

		return sprintf( '<div class="rey-wpforms-form %s">%s</div>', $args['class'], do_shortcode( $shortcode ) );
	}

	public function add_custom_content( $data, $email ) {

		if ( ! ( isset($email->form_data) && absint( $email->form_data['id'] ) === absint($this->wpform) ) ) {
			return $data;
		}

		if( ! (isset($_REQUEST['rey-request-quote-product-id']) && $product_id = absint($_REQUEST['rey-request-quote-product-id'])) ){
			return $data;
		}

		$extra = esc_html_x('Product data:', 'WPForms product data title in email.', 'rey-core') . "<br>";
		$extra .= 'Product ID: <strong>'. $product_id .'</strong>.<br>';

		$product = wc_get_product($product_id);
		$product_title = $product->get_title();

		if( $product->get_type() === 'variation' ){
			$product_title = $product->get_name();
		}

		if( $product && $psku = $product->get_sku() ){
			$extra .= 'Product SKU: <strong>'. $psku .'</strong>.<br>';
		}

		$extra .= 'Product: <a href="'. esc_url( get_the_permalink( $product_id ) ) .'"><strong>' . $product_title . '</strong></a>.<br>';

		if( isset($_REQUEST['rey-request-quote-variation-data']) && $variation_attributes = reycore__clean($_REQUEST['rey-request-quote-variation-data']) ){
			foreach ( (array) json_decode(wp_unslash($variation_attributes)) as $name => $value) {
				if( ! $value ) continue;
				$extra .= sprintf('%s: <strong>%s</strong>.<br>', strtoupper( $name ), strtoupper( $value ));
			}
		}

		$data['message'] .= "\n\n" . apply_filters('reycore/woocommerce/request_quote_mail', $extra, $product_id);

		return $data;
	}
}
