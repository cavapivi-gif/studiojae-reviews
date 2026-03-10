<?php
namespace ReyCore\Modules\ProductQuantity;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const VARIATIONS_KEY_MIN = '_rey_qty_per_variation_min';
	const VARIATIONS_KEY_MAX = '_rey_qty_per_variation_max';
	const VARIATIONS_KEY_STEP = '_rey_qty_per_variation_step';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'woo_init']);
	}

	public function woo_init(){

		add_filter( 'woocommerce_quantity_input_min', [$this, 'quantity_min'], 100, 2);
		add_filter( 'woocommerce_quantity_input_max', [$this, 'quantity_max'], 100, 2);
		add_filter( 'woocommerce_quantity_input_step', [$this, 'quantity_step'], 100, 2);
		add_filter( 'woocommerce_available_variation', [$this, 'quantity_variations'], 100, 3);
		add_action( 'woocommerce_product_after_variable_attributes', [$this, 'variation_settings_fields'], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [$this, 'save_variation_settings_fields'], 10, 2 );

		add_filter('woocommerce_add_to_cart_quantity', [$this, 'quantity_correction'], 20, 2);
		add_filter('woocommerce_update_cart_quantity', [$this, 'cart_quantity_correction'], 20, 2);
		add_filter('woocommerce_stock_amount_cart_item', [$this, 'cart_quantity_correction'], 20, 2);

		new AcfFields();

	}

	public static function get_qty_value( $product_id, $type = 'minimum', $variation = false ){

		$variation_map_key = [
			'minimum'  => self::VARIATIONS_KEY_MIN,
			'maximum'  => self::VARIATIONS_KEY_MAX,
			'step' => self::VARIATIONS_KEY_STEP,
		];

		$qty_data = [];

		if( $variation && isset($variation_map_key[$type]) )
		{
			if( $variation_value = get_post_meta( $product_id, $variation_map_key[$type], true ) ){
				$qty_data[$type] = $variation_value;
			}
			else {
				return;
			}
		}
		else {
			if( ! ($qty_data = get_field( 'quantity_options', $product_id )) ){
				return;
			}
		}

		if( isset($qty_data[$type]) ){
			$val = filter_var( $qty_data[$type], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			if( $val !== false && $val !== '' ){
				return $val;
			}
		}

	}

	public function quantity_variations( $available_variation, $_product_variable, $variation){

		if( ! ($available_variation['variation_is_active'] && $available_variation['variation_is_visible'] && $available_variation['is_purchasable']) ){
			return $available_variation;
		}

		if( ! ($product_id = $variation->get_parent_id()) ){
			return $available_variation;
		}

		if( $min = self::get_qty_value($product_id, 'minimum') ){
			$available_variation['min_qty'] = $min;
		}

		if( $max = self::get_qty_value($product_id, 'maximum') ){
			$available_variation['max_qty'] = $max;
		}

		$available_variation['step_qty'] = 1;

		if( $custom = self::get_qty_value($product_id, 'step') ){
			$available_variation['step_qty'] = $custom;
		}

		foreach ([
			'min_qty'  => self::VARIATIONS_KEY_MIN,
			'max_qty'  => self::VARIATIONS_KEY_MAX,
			'step_qty' => self::VARIATIONS_KEY_STEP,
		] as $key => $value)
		{
			if( $variation_opt = get_post_meta( $available_variation[ 'variation_id' ], $value, true ) ){
				$available_variation[$key] = $variation_opt;
			}
		}

		return $available_variation;
	}

	public function quantity_min($val, $product){

		if( ! $product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){

			// variation might have its own QTY values
			$custom = self::get_qty_value($product_id, 'minimum', true);
			if( $custom !== false && $custom !== '' && $custom !== null ){
				return $custom;
			}

			$product_id = $product->get_parent_id();
		}

		$custom = self::get_qty_value($product_id, 'minimum');
		if( $custom !== false && $custom !== '' && $custom !== null ){
			return $custom;
		}

		// if no custom minimum but STEP is set, use it as minimum
		// if( is_null($custom) && $custom_step = self::get_qty_value($product_id, 'step') ){
		// 	return $custom_step;
		// }

		return $val;
	}

	public function quantity_max($val, $product){

		if( !$product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){

			// variation might have its own QTY values
			if( $custom = self::get_qty_value($product_id, 'maximum', true) ){
				return $custom;
			}

			$product_id = $product->get_parent_id();
		}

		if( $custom = self::get_qty_value($product_id, 'maximum') ){
			return $custom;
		}

		return $val;
	}

	public function quantity_step($val, $product){

		if( !$product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){

			// variation might have its own QTY values
			if( $custom = self::get_qty_value($product_id, 'step', true) ){
				return $custom;
			}

			$product_id = $product->get_parent_id();
		}

		if( $custom = self::get_qty_value($product_id, 'step') ){
			return $custom;
		}

		return $val;
	}

	public static function fix_quantity($quantity, $product){

		$min = floatval(apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ));
		$step = floatval(apply_filters( 'woocommerce_quantity_input_step', 1, $product ));

		if( $min && $min > $quantity ){
			$quantity = $min;
		}

		elseif( $step && $quantity % $step !== 0 ){
			$_qty = floor($quantity / $step) * $step;
			if( $min ){
				$_qty = max($min, $_qty);
			}
			$quantity = $_qty;
		}

		return $quantity;
	}

	/**
	 * Correct quantity when adding a product to cart.
	 * For example when having a minimum or step
	 *
	 * @param int $quantity
	 * @param int $product_id
	 * @return int
	 */
	public function quantity_correction($quantity, $product_id){

		if( $product = wc_get_product($product_id) ){
			return self::fix_quantity($quantity, $product);
		}

		return $quantity;
	}

	/**
	 * Correct quantity when updating the cart with custom quantity values
	 *
	 * @param string $quantity
	 * @param string $cart_item_key
	 * @return int
	 */
	public function cart_quantity_correction($quantity, $cart_item_key){

		$cart_data =  WC()->cart->get_cart();

		if( ! isset($cart_data[ $cart_item_key ]['data']) ){
			return $quantity;
		}

		$product = $cart_data[ $cart_item_key ]['data'];

		return self::fix_quantity($quantity, $product);;
	}

	public function variation_settings_fields( $loop, $variation_data, $variation ) {

		if( ! $this->is_enabled() ){
			return;
		}

		reycore_assets()->add_styles('rey-form-row');

		printf('<div class="form-row form-row-3x"><h4 style="margin-bottom:0;margin-top:0.5em;flex-basis:100%%;">%s</h4>', esc_html__('Quantity Options:', 'rey-core'));

			foreach ([
				[
					'key'         => self::VARIATIONS_KEY_MIN,
					'label'       => __( 'Minimum', 'rey-core' ),
					'description' => __( 'Set a minimum quantity.', 'rey-core' ),
				],
				[
					'key'         => self::VARIATIONS_KEY_MAX,
					'label'       => __( 'Maximum', 'rey-core' ),
					'description' => __( 'Set a maximum quantity.', 'rey-core' ),
				],
				[
					'key'         => self::VARIATIONS_KEY_STEP,
					'label'       => __( 'Step', 'rey-core' ),
					'description' => __( 'Set the stepping point.', 'rey-core' ),
				],
			] as $value) {

				woocommerce_wp_text_input( [
					'id'                => $value['key'] . $loop,
					'name'              => $value['key'] . '[' . $loop . ']',
					'value'             => get_post_meta( $variation->ID, $value['key'], true ),
					'label'             => $value['label'],
					'description'       => $value['description'],
					'desc_tip'          => 'true',
					'type'              => 'number',
					'custom_attributes' => [
						'step' => 'any',
						'min'  => '0'
					],
				]);
			}

		echo '</div>';

	}

	public function save_variation_settings_fields( $variation_id, $loop ) {

		if( ! $this->is_enabled() ){
			return;
		}

		foreach ([
			self::VARIATIONS_KEY_MIN,
			self::VARIATIONS_KEY_MAX,
			self::VARIATIONS_KEY_STEP,
		] as $value) {
			if ( isset( $_POST[$value][ $loop ] ) ) {
				update_post_meta( $variation_id, $value, reycore__clean( $_POST[$value][ $loop ] ));
			}
		}
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Quantity Min/Max', 'Module name', 'rey-core'),
			'description' => esc_html_x('Limit a product\'s minimum and maximum number of items the can be bought.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/product-settings-options/#quantity-min-max-limits'),
			'video' => true,
		];
	}

	public function module_in_use(){

		$post_ids = get_posts([
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => 'quantity_options_minimum',
					'value'   => '',
					'compare' => 'NOT IN'
				],
				[
					'key' => 'quantity_options_maximum',
					'value'   => '',
					'compare' => 'NOT IN'
				],
			]
		]);

		return ! empty($post_ids);

	}
}
