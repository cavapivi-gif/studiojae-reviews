<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Related {

	public function __construct() {
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'woocommerce_update_product', [$this, 'refresh_transient']);
		add_action( 'woocommerce_delete_product_transients', [$this, 'refresh_transient']);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'related_products', [$this, 'ajax__get_related'], [
			'auth'      => 3,
			'nonce'     => false,
			'assets'    => true,
			'transient' => 3 * DAY_IN_SECONDS,
		] );
		$ajax_manager->register_ajax_action( 'upsells_products', [$this, 'ajax__get_upsells'], [
			'auth'      => 3,
			'nonce'     => false,
			'assets'    => true,
			'transient' =>  3 * DAY_IN_SECONDS,
		] );
	}

	/**
	 * Refresh transient
	 *
	 * @since 2.4.0
	 **/
	public function refresh_transient( $product_id )
	{
		if( $product_id ){
			delete_transient( implode('_', [\ReyCore\Ajax::AJAX_TRANSIENT_NAME, 'related_products', $product_id] ) );
			delete_transient( implode('_', [\ReyCore\Ajax::AJAX_TRANSIENT_NAME, 'upsells_products', $product_id] ) );
		}
	}


	public function ajax__get_related( $action_data ){

		if( ! (isset($action_data['id']) && $product_id = absint($action_data['id'])) ){
			return;
		}

		if( reycore__is_multilanguage() ){
			$product_id = apply_filters('reycore/translate_ids', $product_id, 'product');
		}

		if( ! ($product = wc_get_product($product_id)) ){
			return;
		}

		$GLOBALS['product'] = $product;

		ob_start();
		$this->__run_related();
		return ob_get_clean();
	}

	public function can_lazy_load(){
		return get_theme_mod('single_product_page_related_lazy', true) && reycore__get_module('related-products');
	}

	public function run() {

		if( ! $this->can_lazy_load() ){
			$this->__run_related();
			return;
		}

		if( ( reycore__elementor_edit_mode() ) ){
			$this->__run_related();
			return;
		}

		if( ! ( ($product = wc_get_product()) && wc_get_related_products( $product->get_id() ) ) ){
			return;
		}

		// when initial rendering
		// show a placeholder
		if( ! wp_doing_ajax() ){

			echo reycore__lazy_placeholders([
				'class'        => 'related products',
				'filter_title' => 'related_products',
				'desktop'      => reycore_wc_get_columns('desktop'),
				'tablet'       => reycore_wc_get_columns('tablet'),
				'mobile'       => reycore_wc_get_columns('mobile'),
				'limit'        => reycore_wc_get_columns('desktop'),
			]);

			return;
		}

		$this->__run_related();

	}

	public function __run_related(){

		woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', [
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand', // @codingStandardsIgnoreLine.
		] ) );

	}

	public function ajax__get_upsells( $action_data ){

		if( ! (isset($action_data['id']) && $product_id = absint($action_data['id'])) ){
			return;
		}

		if( reycore__is_multilanguage() ){
			$product_id = apply_filters('reycore/translate_ids', $product_id, 'product');
		}

		if( ! ($product = wc_get_product($product_id)) ){
			return;
		}

		$GLOBALS['product'] = $product;

		ob_start();
		$this->__render_upsells();
		return ob_get_clean();
	}

	public function run_upsells(){

		if( ! $this->can_lazy_load() ){
			$this->__render_upsells();
			return;
		}

		if( ( reycore__elementor_edit_mode() ) ){
			$this->__render_upsells();
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		if ( ! ($ids = $product->get_upsell_ids()) ) {
			return;
		}

		// when initial rendering
		// show a placeholder
		if( ! wp_doing_ajax() ){

			$count = count($ids);
			$limit = (($_limit = reycore_wc_get_columns('desktop')) && $count < $_limit) ? $count : $_limit;

			echo reycore__lazy_placeholders([
				'class'        => 'up-sells upsells products',
				'filter_title' => 'upsells_products',
				'desktop'      => reycore_wc_get_columns('desktop'),
				'tablet'       => reycore_wc_get_columns('tablet'),
				'mobile'       => reycore_wc_get_columns('mobile'),
				'limit'        => $limit,
			]);

			return;
		}

		$this->__render_upsells( $ids );

	}

	public function __render_upsells( $ids = false ){

		extract([
			'limit' => '-1',
			'columns' => 4,
			'orderby' => 'rand',
			'order' => 'desc',
		]);

		if( ! $ids ){

			global $product;

			if ( ! $product ) {
				return;
			}

			$upsells = $product->get_upsell_ids();
		}

		else {
			$upsells = $ids;
		}

		// Handle the legacy filter which controlled posts per page etc.
		$args = apply_filters(
			'woocommerce_upsell_display_args',
			array(
				'posts_per_page' => $limit,
				'orderby'        => $orderby,
				'order'          => $order,
				'columns'        => $columns,
			)
		);
		wc_set_loop_prop( 'name', 'up-sells' );
		wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_upsells_columns', isset( $args['columns'] ) ? $args['columns'] : $columns ) );

		$orderby = apply_filters( 'woocommerce_upsells_orderby', isset( $args['orderby'] ) ? $args['orderby'] : $orderby );
		$order   = apply_filters( 'woocommerce_upsells_order', isset( $args['order'] ) ? $args['order'] : $order );
		$limit   = apply_filters( 'woocommerce_upsells_total', isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : $limit );

		// Get visible upsells then sort them at random, then limit result set.
		$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $upsells ), 'wc_products_array_filter_visible' ), $orderby, $order );

		$upsells = $limit > 0 ? array_slice( $upsells, 0, $limit ) : $upsells;

		wc_get_template(
			'single-product/up-sells.php',
			array(
				'upsells'        => $upsells,

				// Not used now, but used in previous version of up-sells.php.
				'posts_per_page' => $limit,
				'orderby'        => $orderby,
				'columns'        => $columns,
			)
		);
	}

	public function flush_transients(){

	}

}
