<?php
namespace ReyCore\Modules\DiscountBadge;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-discount-badge';

	public static $product_types = ['simple', 'external', 'variation', 'price_calculator'];

	public static $prices = [];

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_loop_component']);
		add_action( 'reycore/woocommerce/pdp/init', [$this, 'register_pdp_component']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'woocommerce_delete_product_transients', [$this, 'cache_discounts_refresh']);
		add_action( 'woocommerce_update_product', [$this, 'cache_discounts_refresh']);
		add_action( 'reycore/dynamic_tags', [$this, 'dynamic_tag']);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
	}

	public function init() {

		new Customizer();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/woocommerce/compare/price', [$this, 'compare_price'], 10, 2);
		add_filter( 'reycore/woocommerce/product_loop_attributes/v2', [ $this, 'loop_attributes'], 20 );
		add_action( 'reycore/loop_inside_thumbnail/top-left', [ $this, 'mobile_badge'] );

		do_action('reycore/woocommerce/modules/discount/init', $this);
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function register_loop_component($base){
		$base->register_component( new DiscountPrice );
		$base->register_component( new DiscountTop );
	}

	public function register_pdp_component($base){
		$base->register_component( new DiscountPdp );
	}

	public function dynamic_tag( $tags ){
		$tags->get_manager()->register( new DynamicTag() );
	}

	public function register_assets($assets){

		// $assets->register_asset('styles', [
		// 	self::ASSET_HANDLE => [
		// 		'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
		// 		'deps'    => [],
		// 		'version'   => REY_CORE_VERSION,
		// 	]
		// ]);

	}

	public static function mobile_move_discount_top(){
		return apply_filters('reycore/woocommerce/discount_labels/move_top', 'top' !== reycore_wc__get_setting('loop_discount_label') );
	}

	public function loop_attributes($params) {
		$params['data-discount-mobile-top'] = self::mobile_move_discount_top() ? 'yes' : 'no';
		return $params;
	}

	public function mobile_badge() {
		if( self::mobile_move_discount_top()  ){
			echo self::get_discount_output();
		}
	}

	public static function get_prices( $product_id = null ){

		if( is_null($product_id) ){
			return [];
		}

		// product ID is already the object
		if( $product_id instanceof \WC_Product )
		{
			$product = $product_id;
			$product_id = $product->get_id();

			if( ! empty(self::$prices[$product_id]) ){
				return self::$prices[$product_id];
			}
		}

		else
		{
			$product = wc_get_product($product_id);

			if( ! $product ){
				return [];
			}

			if( ! empty(self::$prices[$product_id]) ){
				return self::$prices[$product_id];
			}

			if( ! $product->is_on_sale() ) {
				return [];
			}
		}

		return [
			'regular' => (float) $product->get_regular_price(),
			'sale' => (float) apply_filters('reycore/woocommerce/discount_labels/sale_price', $product->get_sale_price(), $product),
		];

	}

	/**
	 * Get product discount percentage
	 *
	 * @since 1.0.0
	 */
	public static function get_discount( $product = false, $percentage = true ){

		if( ! $product ){
			global $product;
		}

		if( ! $product ){
			$product = wc_get_product();
		}

		if ( ! ( $product && ($product->is_on_sale() || $product->is_type( 'grouped' )) ) ) {
			return;
		}

		$get_discount = false;

		if( $cache_discounts = apply_filters('reycore/woocommerce/cache_discounts', true) ){
			$transient_name = ($percentage ? '_rey__discount_percentage_' : '_rey__discount_save_') . $product->get_id();
			$get_discount = get_transient($transient_name);
		}

		if ( false === $get_discount ) {

			$discount = 0;

			if ( in_array($product->get_type(), self::$product_types, true) ) {

				if( $prices = self::get_prices($product) ){
					if( $percentage ){
						if ($prices['regular'] <= 0) {
							// Prevent division by zero. Thanks Stephan!
							$discount = 0;
						} else {
							$discount = ( ( $prices['regular'] - $prices['sale'] ) / $prices['regular'] ) * 100;
						}
					}
					else {
						$discount = $prices['regular'] - $prices['sale'];
					}
				}

			}

			elseif ( $product->is_type( 'grouped' ) ) {

				$perc_discount = 0;

				foreach ( $product->get_children() as $_product_id ) {

					if ( ! ($_product = wc_get_product( $_product_id )) ){
						continue;
					}

					if ( 'publish' !== $_product->get_status() ){
						continue;
					}

					if( ! $_product->is_on_sale() ){
						continue;
					}

					if( ! $percentage ){
						$discount += self::get_discount($_product, false);
					}
					else {

						$perc_discount = self::get_discount($_product, true);

						if ( $perc_discount > $discount ) {
							$discount = $perc_discount;
						}

					}

				}

			}

			elseif ( $product->is_type( 'variable' ) ) {

				foreach ( $product->get_children() as $_product_id ) {

					if ( ($_product = wc_get_product( $_product_id )) && 'publish' !== $_product->get_status() ){
						continue;
					}

					if( ! ($prices = self::get_prices($_product_id) ) ){
						continue;
					}

					if ( $prices['regular'] != 0 && ! empty( $prices['sale'] ) ) {

						if( $percentage ){
							// show the biggest
							$perc = ( $prices['regular'] - $prices['sale'] ) / $prices['regular'] * 100;
							if ( $perc > $discount ) {
								$discount = $perc;
							}
						}
						else {
							// show the biggest
							$save = $prices['regular'] - $prices['sale'];
							if ( $save > $discount ) {
								$discount = $save;
							}
						}
					}
				}

			}

			// Format price for "Sale $$"
			if( ! $percentage ){

				if( $discount ){

					$sale_discount_args = [
						'decimals' => wc_get_price_decimals(),
						'decimals_separator' => wc_get_price_decimal_separator(),
						'thousand_separator' => wc_get_price_thousand_separator(),
					];

					if( apply_filters('reycore/woocommerce/discounts/round', false) ){
						$discount = round($discount);
					}

					$get_discount = apply_filters('reycore/woocommerce/discounts/sale_price_format',
						number_format(
							$discount,
							$sale_discount_args['decimals'],
							$sale_discount_args['decimals_separator'],
							$sale_discount_args['thousand_separator']
						),
						$discount,
						$sale_discount_args
					);
				}

				else {
					$get_discount = $discount;
				}

			}

			else {
				$get_discount = absint( round($discount) );
			}

			if( $cache_discounts ){
				set_transient($transient_name, $get_discount, MONTH_IN_SECONDS);
			}
		}

		return $get_discount;
	}

	/**
	 * Get the Discount percentage HTML markup
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 */
	public static function get_discount_percentage_html($text = ''){}

	/**
	 * Get the Discount "Save difference" HTML markup
	 *
	 * @since 1.9.0
	 * @deprecated 2.4.0
	 */
	public static function get_discount_save_html(){}

	/**
	 * Shows a prefix text in discount badges of parent variable products
	 * which have variable sale prices.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function show_prefix_text($args){

		if( ! ($product = $args['pdp']) ){
			return $args;
		}

		if( ! $args['pdp'] instanceof \WC_Product ){
			return $args;
		}

		if( ! $product->is_type( 'variable' ) ){
			return $args;
		}

		$sale_prices = [];

		foreach ( $product->get_children() as $_product_id ) {

			if( ! ($prices = self::get_prices($_product_id) ) ){
				continue;
			}

			if ( $prices['regular'] != 0 && ! empty( $prices['sale'] ) ) {
				$sale_prices[] = $prices['sale'];
			}

		}

		if( ! empty($sale_prices) && count( array_unique($sale_prices) ) > 1 ){
			$args['prefix'] = $args['variables_prefix'];
		}

		return $args;
	}

	/**
	 * Get the Discount HTML markup
	 *
	 * @since 1.9.0
	 * @return string
	 */
	public static function get_discount_output( $args = [] ){

		$args = wp_parse_args($args, [
			'type'              => get_theme_mod('loop_show_sale_label', 'percentage'),
			'pdp'               => false,
			'label_start'       => '<span class="rey-discount">',
			'prefix'            => '',
			'variables_prefix'  => esc_html__('UP TO ', 'rey-core'),
			'label_end'         => '</span>',
			'discount'          => '',
			'percentage_format' => '-%d%% %s',
			'percentage_text'   => apply_filters('reycore/woocommerce/discounts/percentage_html_text', esc_html_x('OFF', 'WooCommerce single item discount percentage', 'rey-core')),
			'save_text'         => get_theme_mod('loop_sale__save_text', esc_html_x('Save', 'rey-core')),
		]);

		$args = apply_filters('reycore/woocommerce/discount_output/args', $args);

		/**
		 * In variable products, show an "UP TO " prefix before discount,
		 * for example "UP TO -60% OFF" where 60% is the larges discount.
		 *
		 * add_filter('reycore/woocommerce/discount/show_variables_prefix', '__return_true');
		 */
		if( apply_filters('reycore/woocommerce/discount/show_variables_prefix', false) ){
			$args = self::show_prefix_text($args);
		}

		$product = $args['pdp'] && $args['pdp'] instanceof \WC_Product && $args['pdp']->is_type( 'variation' ) ? $args['pdp'] : false;

		$content = '';

		if( 'save' === $args['type'] ){

			if( $args['discount'] = self::get_discount($product, false) ){

				$args['formatted_discount'] = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $args['discount']);

				$content = $args['label_start'] . $args['prefix'];
				$content .= $args['save_text'] . ' ' . $args['formatted_discount'];
				$content .= $args['label_end'];
			}
		}

		elseif( 'percentage' === $args['type'] ) {

			if( $args['discount'] = self::get_discount($product) ){
				$content = $args['label_start'] . $args['prefix'];
				$content .= sprintf( $args['percentage_format'], $args['discount'], $args['percentage_text'] );
				$content .= $args['label_end'];
			}
		}

		elseif( 'sale' === $args['type'] ){

			ob_start();
			woocommerce_show_product_loop_sale_flash();
			$content = ob_get_clean();

		}

		return apply_filters('reycore/woocommerce/discounts/output', $content, $args);
	}

	/**
	 * Refresh discounted products meta
	 *
	 * @since 1.5.0
	 **/
	public function cache_discounts_refresh( $post_id )
	{

		if( $post_id > 0 ){
			delete_transient( '_rey__discount_percentage_' . $post_id );
			delete_transient( '_rey__discount_save_' . $post_id );
			return;
		}

		if( ! $post_id ){ // only on global delete
			$products_on_sale = wc_get_product_ids_on_sale();
			foreach($products_on_sale as $product_id){
				delete_transient( '_rey__discount_percentage_' . $product_id );
				delete_transient( '_rey__discount_save_' . $product_id );
			}
		}
	}

	public function compare_price( $price, $product ){
		return $price . self::get_discount_output(['type' => 'percentage']);
	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_discount',
			[
				'label' => esc_html__( 'Discount Label', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'condition' => [
					'hide_prices!' => 'yes',
					'loop_skin!' => 'template',
				],
			]
		);

		$stack->end_injection();

	}

	public static function pdp_enabled(){
		return get_theme_mod('single_discount_badge_v2', true);
	}

	public static function loop_price_enabled(){
		return get_theme_mod('loop_show_sale_label', 'percentage') !== '' && reycore_wc__get_setting('loop_discount_label') === 'price';
	}

	public static function loop_top_enabled(){
		return get_theme_mod('loop_show_sale_label', 'percentage') !== '';
	}

	public function is_enabled() {
		return self::pdp_enabled() || self::loop_price_enabled() || self::loop_top_enabled();
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Discount Badges', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to display badges with text or numbered discounted information inside product pages and product catalog.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page', 'product catalog'],
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
