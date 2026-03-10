<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Shortcodes {

	public function __construct() {

		add_shortcode('can_ship', [$this, 'can_ship']);
		add_shortcode('rey_product_page', [$this, 'product_page']);
		add_shortcode('attribute_link', [$this, 'attribute_link']);
		add_shortcode('rey_attributes', [$this, 'attributes']);
		// add_shortcode('rey_country_selector', [$this, 'country_selector']);

		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );

	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'get_shipping_status', [$this, 'ajax__get_shipping_status'], [
			'auth'  => 3,
			'nonce' => false,
		] );
	}

	public function ajax__get_shipping_status( $data ){

		// Bail if localhost
		if( in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ){
			return esc_html__('Can\'t geolocate localhost.', 'rey-core');
		}

		$geolocation = \WC_Geolocation::geolocate_ip( apply_filters('reycore/shortcode/get_shipping_status/ip', '') );
		$country_list = WC()->countries->get_shipping_countries();

		if( ! is_null($geolocation) && !empty($geolocation) && isset($geolocation['country']) ) {

			$country_tag = '<u class="__country" style="text-decoration-color:rgba(0,0,0,0.2)">%s</u>';

			if( isset($country_list[$geolocation['country']]) && ($supported_country = $country_list[$geolocation['country']]) ) {
				if( isset($data['text']) && !empty($data['text']) ){
					$html = str_replace('%s', $country_tag, sanitize_text_field($data['text']));
					return sprintf( '<span>' . $html . '</span>', $country_list[$geolocation['country']] );
				}
			}

			else {
				if( isset($data['no_text']) && !empty($data['no_text']) ){
					$html = str_replace('%s', $country_tag, sanitize_text_field($data['no_text']));
					return sprintf( '<span>' . $html . '</span>', WC()->countries->countries[ $geolocation['country'] ] );
				}
			}
		}
	}

	/**
	 * Can Ship shortcode. Will check if shipping is supported for visitors.
	 *
	 * [can_ship text="Yes, we ship to %s!" no_text=""]
	 *
	 * @since 1.0.0
	 **/
	public function can_ship($atts) {

		if( ! (class_exists('\WooCommerce') && class_exists('\WC_Geolocation') && is_callable('WC')) ){
			return;
		}

		$attributes = [
			'style' => 'display:block;'
		];

		if( isset($atts['text']) && !empty($atts['text']) ){
			$attributes['data-text'] = esc_attr($atts['text']);
	 	}
		else {
			$attributes['data-no-text'] = esc_html__('Yes, we ship to %s!"', 'rey-core');
		}

		// Sorry, we don't ship to %s
		if( isset($atts['no_text']) && ! empty($atts['no_text']) ){
			$attributes['data-no-text'] = esc_attr($atts['no_text']);
	 	}
		else {
			$attributes['data-no-text'] = esc_html__('Sorry, we don\'t ship to %s!"', 'rey-core');
		}

		return sprintf('<span class="rey-canShip" %s></span>', reycore__implode_html_attributes($attributes));
	}


	public function product_page($atts){
		$content = '';

		if( ! (isset($atts['id']) && $id = $atts['id']) ){
			return '';
		}

		if( is_admin() ){
			return '';
		}

		// weird error in Gutenberg editor
		if( isset($_REQUEST['_locale']) && $_REQUEST['_locale'] === 'user') {
			return '';
		}

		ob_start();

		do_action('reycore/woocommerce/product_page/scripts');

		if( isset($atts['only_summary']) && 'true' === reycore__clean($atts['only_summary']) ){
			remove_all_actions( 'woocommerce_after_single_product_summary' );
		}

		echo do_shortcode(sprintf('[product_page id="%d"]', $id));

		$content = ob_get_clean();

		$search = '<div class="woocommerce">';
		$replace_with = '<div class="woocommerce single-skin--' . get_theme_mod('single_skin', 'default') . '">';

		return str_replace($search, $replace_with, $content);
	}

	/**
	 * Work in progress
	 *
	 * @return void
	 */
	public function country_selector(){

		if( ! function_exists('WC') ){
			return;
		}

		if( ! wc_shipping_enabled() ){
			if( current_user_can('administrator') ){
				echo '<p>Shipping not enabled.</p>';
			}
			return;
		}

		if( ! apply_filters( 'woocommerce_shipping_calculator_enable_country', true ) ){
			if( current_user_can('administrator') ){
				echo '<p>Shipping country selector is disabled by filter `woocommerce_shipping_calculator_enable_country`.</p>';
			}
			return;
		}

		if( ! (WC()->countries && WC()->customer) ){
			return;
		}

		$country = WC()->customer->get_shipping_country();

		$nonce_value = wc_get_var( $_REQUEST['woocommerce-shipping-calculator-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		// Update Shipping. Nonce check uses new value and old value (woocommerce-cart). @todo remove in 4.0.
		if ( ! empty( $_POST['calc_shipping'] ) && ( wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' )  ) ) { // WPCS: input var ok.

			$selected_country  = isset( $_POST['calc_shipping_country'] ) ? reycore__clean( $_POST['calc_shipping_country'] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.

			if ( $selected_country ) {
				WC()->customer->set_shipping_location( $selected_country );
				$country = $selected_country;
			} else {
				WC()->customer->set_billing_address_to_base();
				WC()->customer->set_shipping_address_to_base();
			}

			WC()->customer->set_calculated_shipping( true );
			WC()->customer->save();
			// Also calc totals before we check items so subtotals etc are up to date.
			WC()->cart->calculate_totals();

		} ?>

		<form class="rey-countrySelector" method="post" style=" display: flex; gap: 20px; ">
			<select name="calc_shipping_country">
				<option value="default"><?php esc_html_e( 'Select a country / region&hellip;', 'woocommerce' ); ?></option>
				<?php
				foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( $country, esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
				} ?>
			</select>
			<button type="submit" name="calc_shipping" value="1" class="btn btn-primary"><?php esc_html_e( 'Update', 'woocommerce' ); ?></button>
			<?php wp_nonce_field( 'woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce' ); ?>
		</form>
		<?php

	}

	/**
	 * Get URL of a taxonomy.
	 * [attribute_link taxonomy="pa_brand"]
	 *
	 * @since 1.9.7
	 **/
	public function attribute_link($atts)
	{
		$content = '';

		if( !(isset($atts['taxonomy']) && ($taxonomy = $atts['taxonomy']) && taxonomy_exists($taxonomy)) ){
			return $content;
		}

		if( !($product = wc_get_product()) ){
			return $content;
		}

		$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );

		if( ! isset($attributes[ $taxonomy ]) ){
			return $content;
		}

		if( ! ( isset($attributes[ $taxonomy ]['options']) && ($options = $attributes[ $taxonomy ]['options'] ) && !empty($options) ) ){
			return $content;
		}

		if( ($term_link = get_term_link( $options[0], $taxonomy )) && is_string($term_link) ){
			$content = $term_link;
		}
		else {
			$term_obj = get_term_by( 'term_taxonomy_id', $options[0], $taxonomy );
			if( isset($term_obj->slug) ){
				$content = sprintf( '%1$s?filter_%2$s=%3$s', get_permalink( wc_get_page_id( 'shop' ) ), wc_attribute_taxonomy_slug($taxonomy), $term_obj->slug );
			}
		}

		return esc_url( $content );
	}

	/**
	 * Get URL of a taxonomy.
	 * [rey_attributes id="pa_brand" product_id="" limit="3" separator="," link="false"]
	 *
	 * @since 2.5.0
	 **/
	public function attributes($atts){

		if( !(isset($atts['id']) && ($taxonomy = $atts['id']) && taxonomy_exists($taxonomy)) ){
			return '';
		}

		$args = [
			'taxonomy'   => $taxonomy,
			'product_id' => isset($atts['product_id']) && ! empty($atts['product_id']) ? absint($atts['product_id']) : false,
			'link'       => isset($atts['link']) && 'true' === $atts['link'],
		];

		if( isset($atts['limit']) && ($limit = absint($atts['limit'])) ){
			$args['limit'] = $limit;
		}

		if( isset($atts['separator']) && ($separator = $atts['separator']) ){
			$args['separator'] = $separator;
		}

		return reycore_wc__get_attributes($args);
	}

}
