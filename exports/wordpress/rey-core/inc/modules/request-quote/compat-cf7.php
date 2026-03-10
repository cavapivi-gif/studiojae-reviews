<?php
namespace ReyCore\Modules\RequestQuote;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompatCf7
{

	public $cf7_form;

	public function __construct()
	{

		if( ! class_exists('WPCF7') ){
			return;
		}

		if( ! Base::instance()->is_enabled() ) {
			return;
		}

		if( get_theme_mod('request_quote__form_type', 'cf7') !== 'cf7' ){
			return;
		}

		if( ! ($this->cf7_form = get_theme_mod('request_quote__cf7', '')) ){
			return;
		}

		add_filter( 'reycore/woocommerce/request_quote/output', [$this, 'request_quote_output'], 10, 2);
		add_action( 'wpcf7_before_send_mail', [$this, 'before_send_mail']);
	}

	function request_quote_output( $html, $args ){

		$args = wp_parse_args($args, [
			'class' => ''
		]);

		if ( $contact_form = wpcf7_contact_form($this->cf7_form) ) {
			$html = $contact_form->form_html([
				'html_class' => $args['class']
			]);
		}

		return $html;
	}


	public function before_send_mail( $WPCF7_ContactForm )
	{

		//Get current form
		$wpcf7 = \WPCF7_ContactForm::get_current();

		// get current SUBMISSION instance
		$submission = \WPCF7_Submission::get_instance();

		// Ok go forward
		if ( ! $submission ) {
			return;
		}

		// get submission data
		$data = $submission->get_posted_data();

		if ( empty( $data ) ) {
			return;
		}

		$mail = $wpcf7->prop( 'mail' );

		if( ! (isset($data['rey-request-quote-product-id']) && $product_id = absint($data['rey-request-quote-product-id'])) ){
			return;
		}

		$extra = 'Product ID: <strong>'. $product_id .'</strong>.<br>';

		$product = wc_get_product($product_id);
		$product_title = $product->get_title();

		if( $product->get_type() === 'variation' ){
			$product_title = $product->get_name();
		}

		if( $product && $psku = $product->get_sku() ){
			$extra .= 'Product SKU: <strong>'. $psku .'</strong>.<br>';
		}

		$extra .= 'Product: <a href="'. esc_url( get_the_permalink( $product_id ) ) .'"><strong>' . $product_title . '</strong></a>.<br>';

		if( isset($data['rey-request-quote-variation-data']) && $variation_attributes = reycore__clean($data['rey-request-quote-variation-data']) ){
			foreach ( (array) json_decode($variation_attributes) as $name => $value) {
				if( ! $value ) continue;
				$extra .= sprintf('%s: <strong>%s</strong>.<br>', strtoupper( $name ), strtoupper( $value ));
			}
		}

		if( strpos($mail['subject'], '[your-subject]') !== false ){
			$mail['subject'] = str_replace( '[your-subject]', str_replace('&#8211;', '-', $product_title), $mail['subject']);
		}
		else {
			$mail['subject'] = $mail['subject'] . ' - ' . $product_title;
		}

		$extra = apply_filters('reycore/woocommerce/request_quote_mail', $extra, $product_id);

		$mail['body'] = $extra . '<br><br>' . $mail['body'];
		$mail['use_html'] = true;

		// Save the email body
		$wpcf7->set_properties( [
			"mail" => $mail,
		]);

		return $wpcf7;
	}

}
