<?php
namespace ReyCore\Modules\EstimatedDelivery;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-estimated-delivery';

	const VARIATIONS_KEY = '_rey_estimated_delivery_variation';

	public $args = [];
	public $settings = [];

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		new AcfFields();
		new Customizer();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'wp', [$this, 'wp']);
		add_action( 'woocommerce_product_after_variable_attributes', [$this, 'variation_settings_fields'], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [$this, 'save_variation_settings_fields'], 10, 2 );
		add_action( 'reycore/module/quickview/product', [$this, 'wp']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'woocommerce_cart_totals_before_order_total', [$this, 'cart_checkout_table_row'] );
		add_action( 'woocommerce_review_order_before_order_total', [$this, 'cart_checkout_table_row'] );
		add_action( 'woocommerce_after_cart_item_name', [$this, 'cart_custom_text'] );
		add_action( 'reycore/dynamic_tags', [$this, 'dynamic_tag']);

	}

	public static function allow_variations(){
		return get_theme_mod('estimated_delivery__variations', false);
	}


	public function set_settings(){

		$position = [
			'default' => [
				'tag' => 'reycore/woocommerce_product_meta/before',
				'priority' => 20
			],
			'custom' => []
		];

		if( ! get_theme_mod('single_product_meta_v2', true) ){
			$position['default']['tag'] = 'woocommerce_single_product_summary';
			$position['default']['priority'] = 39; // before beta (40)
		}

		$position_option = get_theme_mod('estimated_delivery__position', 'default');

		$this->settings = apply_filters('reycore/woocommerce/estimated_delivery', [
			'days_text'       => esc_html_x('days', 'Estimated delivery string', 'rey-core'),
			'date_format'     => "l, M dS",
			'exclude_dates'   => [], // array (YYYY-mm-dd) eg. array("2012-05-02","2015-08-01")
			'margin_excludes' => [], // ["Saturday", "Sunday"]
			'position'        => isset($position[ $position_option ]) ? $position[ $position_option ] : $position[ 'default' ],
			'use_locale'      => get_theme_mod('estimated_delivery__locale', false),
			'locale'          => get_locale(),
			'locale_format'   => get_theme_mod('estimated_delivery__locale_format', "%A, %b %d"),
			'variations'      => self::allow_variations(),
			'limit_days'      => 180, // 180 days maximum,
			'use_weeks_after' => 0, // use weeks after x days,
			'week_text'       => esc_html_x('weeks', 'Estimated delivery string', 'rey-core'),
			'checkout_date_format' => 'M dS',
			'checkout_locale_format' => "%B %d",
			'checkout_range_date_format'   => 'dS',
			'checkout_range_locale_format' => "%d",
		]);

	}

	function wp(){

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! reycore_wc__is_product() ){
			return;
		}

		$this->set_settings();
		$this->set_args();

		if( wp_doing_ajax() && isset($_REQUEST['id']) && $product_id = absint($_REQUEST['id']) ){
			$this->args['product'] = wc_get_product($product_id);
		}

		if( isset($this->settings['position']['tag']) ){
			add_action($this->settings['position']['tag'], [$this, 'display'], $this->settings['position']['priority']);
		}

		add_shortcode('rey_estimated_delivery', [$this, 'display_shortcode']);

		add_filter( 'woocommerce_available_variation', [$this, 'load_variation_settings_fields'] );
		add_action( 'woocommerce_single_product_summary', [$this, 'display_shipping_class'], 39);

	}

	public function set_args(){
		$this->args = [
			'product'         => wc_get_product(),
			'days'            => reycore__get_option('estimated_delivery__days', 3),
			'days_individual' => reycore__acf_get_field( 'estimated_delivery__days' ),
			'margin'          => reycore__get_option('estimated_delivery__days_margin', ''),
			'excludes'        => get_theme_mod('estimated_delivery__exclude', ["Saturday", "Sunday"]),
			'inventory'       => get_theme_mod('estimated_delivery__inventory', ['instock']),
		];
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new PdpElement );
	}

	public function dynamic_tag( $tags ){
		$tags->get_manager()->register( new DynamicTag() );
	}

	public function display_shortcode( $atts = [] ){
		return $this->display(array_merge($atts, ['return' => 1]) );
	}

	public function display( $atts = [] ){

		if( !isset($this->settings) ){
			$this->set_settings();
		}

		if( empty($this->args) ){
			$this->set_args();
		}

		if( isset($atts['id']) && $product_id = absint($atts['id']) ){
			$this->args['product'] = wc_get_product($product_id);
		}

		$this->args = wp_parse_args($this->args, $atts);

		// for shortcode
		if( ! empty($atts['return']) && 1 === absint($atts['return']) ){
			ob_start();
			$this->output($this->args);
			return ob_get_clean();
		}

		$this->output($this->args);
	}

	protected function output($args) {

		$args = wp_parse_args($args, [
			'custom_days' => '',
			'product'     => false,
			'product_id'  => 0,
			'title'       => true,
			'wrapper'     => true,
			'before'      => '',
			'after'       => '',
		]);

		if( ! $args['product'] ){
			if( $product_id = $args['product_id'] ){
				$args['product'] = wc_get_product($product_id);
			}
		}

		if( ! $args['product'] ){
			return;
		}

		if ( $args['product']->is_virtual() ) {
			return;
		}

		if( $custom_days = $args['custom_days'] ){
			$args['days'] = $custom_days;
		}

		$args['stock_status'] = $args['product']->get_stock_status();

		$days_overridden = false;

		// It's out of stock && has fallback text
		if( $args['stock_status'] === 'outofstock' && ! in_array( $args['stock_status'], $args['inventory'], true) &&
			($text = get_theme_mod('estimated_delivery__text_outofstock', '')) ){

			// Check if the text is just a number - if so, override days instead of showing text
			if( is_numeric(trim($text)) ){
				// keep per product override if it exists
				$args['days'] = $args['days_individual'] ? $args['days_individual'] : absint(trim($text));
				$days_overridden = true;
			} else {
				$this->print_wrapper( $text );
				return;
			}
		}

		// It's on backorder && has fallback text
		else if( $args['stock_status'] === 'onbackorder' && ! in_array( $args['stock_status'], $args['inventory'], true) &&
			($text = get_theme_mod('estimated_delivery__text_onbackorder', '')) ){

			// Check if the text is just a number - if so, override days instead of showing text
			if( is_numeric(trim($text)) ){
				// keep per product override if it exists
				$args['days'] = $args['days_individual'] ? $args['days_individual'] : absint(trim($text));
				$days_overridden = true;
			} else {
				$this->print_wrapper( $text );
				return;
			}
		}

		$args['date'] = $this->calculate_date([
			'days'        => absint($args['days']),
			'skipdays'    => $args['excludes'],
		]);

		if( empty($args['inventory']) && current_user_can('administrator') ){
			echo '<p class="rey-estimatedDelivery-title"><small>Estimated delivery not showing because nothing is selected in the "Show for" stock option. If you don\'t plan using Estimated Delivery option please disable the module or access Customizer > WooCommerce > Product page > Components in Summary and disable Estimated delivery option. <em>This message only shows up for Administrators</em>.</small></p>';
		}

		if( count($args['inventory']) === 1 && empty($args['inventory'][0]) ){
			$args['inventory'] = ['instock'];
		}

		// If days were overridden, allow the stock status through since we're using a custom days value
		if( ! $days_overridden && ! in_array( $args['stock_status'], $args['inventory'], true) ){
			return;
		}

		if( $custom_text = reycore__acf_get_field('estimated_delivery__custom_text') ){

			if( $args['wrapper'] ){
				$this->print_wrapper( $custom_text, $args['before'], $args['after'] );
			}
			else {
				echo $args['before'] . $custom_text . $args['after'];
			}
			return;
		}

		$display_type = get_theme_mod('estimated_delivery__display_type', 'number');

		$days = absint($args['days']);

		// no global days & no per product days
		if( ! $days && (is_null($args['days_individual']) || '' === $args['days_individual']) ){
			return;
		}

		$html = '';

		if( $args['title'] ){
			$html = sprintf('<span class="rey-estimatedDelivery-title">%s</span>&nbsp;',
				get_theme_mod('estimated_delivery__prefix',
				esc_html__('Estimated delivery:', 'rey-core'))
			);
		}

		// just mark as today
		if( ! $custom_days && $args['days_individual'] == '0' ){
			$html .= sprintf('<span class="rey-estimatedDelivery-date">%s</span>', esc_html__('Today', 'rey-core') );
		}

		// make sure it can be disabled, only to be used individually
		else {

			$the_margin = absint($args['margin']);

			$margin_date = '';

			// eg: 10th October
			if( $display_type === 'date' ){

				if( $the_margin ){
					$margin_excludes = $this->settings['margin_excludes'] ? $this->settings['margin_excludes'] : $args['excludes'];
					$margin_date = ' - ' . $this->calculate_date( [
						'days'        => $days + $the_margin,
						'skipdays'    => $margin_excludes,
					]);
				}

				$html .= sprintf('<span class="rey-estimatedDelivery-date">%s%s</span>',
					$args['date'],
					$margin_date
				);

			}

			// eg: 1-3 days
			else {

				$date_text = $this->settings['days_text'];
				$total_days = $days;

				if( $the_margin ){

					if( $days > $the_margin || $days === $the_margin ){
						$the_margin = $days + 1;
					}

					$margin_date = ' - ' . $the_margin;
					$total_days = $the_margin; // Use the higher value for weeks calculation
				}

				// Check if we should display in weeks
				$use_weeks_after = absint($this->settings['use_weeks_after']);

				if( $use_weeks_after > 0 && $total_days >= $use_weeks_after ) {

					$weeks = floor($total_days / 7);
					$remaining_days = $total_days % 7;
					$week_text = $this->settings['week_text'];

					// Determine the appropriate text based on remaining days
					if( $remaining_days === 0 ) {
						// Exact weeks (e.g., 14 days = 2 weeks)
						$weeks_display = $weeks . ' ' . $week_text;
					}
					elseif( $remaining_days <= 2 ) {
						// Almost next week (e.g., 13 days = Almost 2 weeks)
						$weeks_display = esc_html__('Almost', 'rey-core') . ' ' . ($weeks + 1) . ' ' . $week_text;
					}
					elseif( $remaining_days >= 5 ) {
						// Over current week (e.g., 15 days = Over 2 weeks)
						$weeks_display = esc_html__('Over', 'rey-core') . ' ' . $weeks . ' ' . $week_text;
					}
					else {
						// In between, show weeks with margin
						if( $the_margin ) {
							$weeks_display = $weeks . ' - ' . ($weeks + 1) . ' ' . $week_text;
						} else {
							$weeks_display = $weeks . ' ' . $week_text;
						}
					}

					$html .= sprintf('<span class="rey-estimatedDelivery-date">%s</span>', $weeks_display);
				}
				else {
					// Display in days as before
					$html .= sprintf('<span class="rey-estimatedDelivery-date">%1$s %2$s</span>',
						$days . $margin_date,
						$date_text
					);
				}
			}

		}

		if( reycore__acf_get_field('estimated_delivery__hide') ){
			return;
		}

		if( $args['wrapper'] ){
			$this->print_wrapper( $html, $args['before'], $args['after'] );
		}
		else {
			echo $args['before'] . $html . $args['after'];
		}
	}

	public function print_wrapper($html, $before = '', $after = ''){

		if( ! $html ){
			return;
		}

		echo apply_filters( 'reycore/woocommerce/estimated_delivery/output', sprintf('<div class="rey-estimatedDelivery">%s</div>', $before . $html . $after), $this, $html, $before, $after );
	}

	public function calculate_date($args = []) {

		$args = wp_parse_args($args, [
			'timestamp'   => strtotime('today'),
			'days'        => 0,
			'skipdays'    => [],
			'date_format' => $this->settings['date_format'],
			'locale_format' => $this->settings['locale_format'],
		]);

		// limit to n days
		if( $args['days'] > $this->settings['limit_days'] ){
			$args['days'] = $this->settings['limit_days'];
		}

		$i = 1;

		while ($args['days'] >= $i) {
			$args['timestamp'] = strtotime("+1 day", $args['timestamp']);
			if ( (in_array(date("l", $args['timestamp']), $args['skipdays'])) || (in_array(date("Y-m-d", $args['timestamp']), $this->settings['exclude_dates'])) )
			{
				$args['days']++;
			}
			$i++;
		}

		if( $this->settings['use_locale'] ){

			// https://php.watch/versions/8.1/strftime-gmstrftime-deprecated
			if ( version_compare(PHP_VERSION, '8.1.0', '>=') && class_exists('\IntlDateFormatter')) {
				$formatter = new \IntlDateFormatter($this->settings['locale'], \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
				$intlFormat = self::strftime_to_intl($args['locale_format']);
				$formatter->setPattern($intlFormat);
				return $formatter->format($args['timestamp']);
			}

			// deprecated since PHP 8.1
			else {
				setlocale(LC_TIME, $this->settings['locale']);
				return strftime($args['locale_format'], $args['timestamp']);
			}
		}

		return date($args['date_format'], $args['timestamp']);
	}

	public function display_shipping_class(){

		if( ! get_theme_mod('single_extras__shipping_class', false) ){
			return;
		}

		global $product;

		if( $shipping_class = $product->get_shipping_class() ) {
			$term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
			if( is_a($term, '\WP_Term') ){
				echo apply_filters('reycore/woocommerce/product_page/shipping_class', '<p class="rey-shippingClass">' . $term->name . '</p>', $term);
			}
		}
	}

	public function variation_settings_fields( $loop, $variation_data, $variation ) {

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! self::allow_variations() ){
			return;
		}

		reycore_assets()->add_styles('rey-form-row');

		woocommerce_wp_text_input([
			'id'            => self::VARIATIONS_KEY. $loop,
			'name'          => self::VARIATIONS_KEY. '[' . $loop . ']',
			'value'         => get_post_meta( $variation->ID, self::VARIATIONS_KEY, true ),
			'label'         => __( 'Estimated days delivery', 'rey-core' ),
			'desc_tip'      => true,
			'description'   => __( 'Add an estimation delivery date for this variation.', 'rey-core' ),
			'wrapper_class' => 'form-row form-row-full',
			'class' => 'input-text',
		]);
	}

	public function save_variation_settings_fields( $variation_id, $loop ) {

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! self::allow_variations() ){
			return;
		}

		if ( isset( $_POST[self::VARIATIONS_KEY][ $loop ] ) ) {
			update_post_meta( $variation_id, self::VARIATIONS_KEY, reycore__clean( $_POST[self::VARIATIONS_KEY][ $loop ] ));
		}

	}

	public function load_variation_settings_fields( $variation ) {

		if( ! reycore_wc__is_product() ){
			return $variation;
		}

		if( ! $this->settings['variations'] ){
			return $variation;
		}

		if( ! ( $variation_estimation = get_post_meta( $variation[ 'variation_id' ], self::VARIATIONS_KEY, true ) ) ){
			return $variation;
		}

		ob_start();

		$args = $this->args;
		$args['custom_days'] = $variation_estimation;
		$args['product_id'] = $variation[ 'variation_id' ];

		$this->output($args);

		$variation['estimated_delivery'] = ob_get_clean();

		return $variation;
	}

	public function cart_custom_text( $cart_item ) {

		if( ! get_theme_mod('estimated_delivery__cart_checkout', true) ){
			return;
		}

		if( empty($cart_item['product_id']) ){
			return;
		}

		$custom_text = get_field('estimated_delivery__custom_text', $cart_item['product_id']);

		if( $custom_text ){
			printf('<div class="estimated-customText">%s</div>', $custom_text);
		}

	}

	public function cart_checkout_table_row() {

		if( ! get_theme_mod('estimated_delivery__cart_checkout', true) ){
			return;
		}

		$this->set_settings();

		$display_type = get_theme_mod('estimated_delivery__display_type', 'number');
		$estimation = '';
		$global_estimation = get_theme_mod('estimated_delivery__days', 3);
		$products_estimations = [];
		$margin_estimations = [];

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$product = wc_get_product($product_id);
			$days = get_field( 'estimated_delivery__days', $product_id ); // per product

			if( isset( $cart_item['variation_id'] ) && ($variation_id = $cart_item['variation_id']) ){
				if( $var_est = get_post_meta( $variation_id, self::VARIATIONS_KEY, true ) ){ // per variation
					$product_id = $variation_id;
					$days = absint($var_est);
				}
			}

			// Check for stock status overrides
			$stock_status = $product->get_stock_status();

			// Check if out of stock and has numeric override
			if( $stock_status === 'outofstock' && ($text = get_theme_mod('estimated_delivery__text_outofstock', '')) ){
				if( is_numeric(trim($text)) ){
					// keep per product override if it exists
					$days = (! is_null($days) && '' !== $days) ? $days : absint(trim($text));
				}
			}
			// Check if on backorder and has numeric override
			else if( $stock_status === 'onbackorder' && ($text = get_theme_mod('estimated_delivery__text_onbackorder', '')) ){
				if( is_numeric(trim($text)) ){
					// keep per product override if it exists
					$days = (! is_null($days) && '' !== $days) ? $days : absint(trim($text));
				}
			}

			$products_estimations[$product_id] = (! is_null($days) && '' !== $days) ? $days : $global_estimation;
			$margin = absint(reycore__get_option('estimated_delivery__days_margin', '', false, $product_id));

			if( 'date' === $display_type ){
				$margin_estimations[$product_id] = absint($products_estimations[$product_id]) + $margin;
			}
			else {
				$margin_estimations[$product_id] = $margin;
			}

		}

		if( empty( array_filter($products_estimations) ) ){
			return;
		}

		$min = absint(min($products_estimations));
		$max = absint(max(max($products_estimations), max($margin_estimations)));
		$is_range = $min !== $max;

		// use global
		$excludes = get_theme_mod('estimated_delivery__exclude', ["Saturday", "Sunday"]);

		if( apply_filters('reycore/woocommerce/estimated_delivery/checkout_inherit_format', false) ){
			$this->settings['checkout_date_format'] = $this->settings['date_format'];
			$this->settings['checkout_locale_format'] = $this->settings['locale_format'];
		}

		// date format
		if( 'date' === $display_type ){

			// get starting point
			$estimation = $this->calculate_date([
				'days'          => $min,
				'skipdays'      => $excludes,
				'date_format'   => $this->settings['checkout_date_format'],
				'locale_format' => $this->settings['checkout_locale_format'],
			]);

			// get end point if range
			if( $is_range ){
				$estimation .= ' - ' . $this->calculate_date([
					'days'          => $max,
					'skipdays'      => $excludes,
					'date_format'   => $this->settings['checkout_range_date_format'],
					'locale_format' => $this->settings['checkout_range_locale_format'],
				]);
			}
		}

		// number of days
		else {

			// Check if we should display in weeks for checkout
			$use_weeks_after = absint($this->settings['use_weeks_after']);

			if( $use_weeks_after > 0 && $max >= $use_weeks_after ) {

				$min_weeks = floor($min / 7);
				$max_weeks = floor($max / 7);
				$min_remaining = $min % 7;
				$max_remaining = $max % 7;
				$week_text = $this->settings['week_text'];

				if( $is_range ) {
					// Handle range display in weeks
					if( $min_weeks === $max_weeks ) {
						// Same week range
						if( $min_remaining === 0 && $max_remaining === 0 ) {
							$estimation = $min_weeks . ' ' . $week_text;
						}
						elseif( $min_remaining <= 2 && $max_remaining >= 5 ) {
							$estimation = esc_html__('Almost', 'rey-core') . ' ' . ($min_weeks + 1) . ' - ' . esc_html__('Over', 'rey-core') . ' ' . $max_weeks . ' ' . $week_text;
						}
						else {
							$estimation = $min_weeks . ' - ' . ($max_weeks + 1) . ' ' . $week_text;
						}
					}
					else {
						// Different week range
						$estimation = $min_weeks . ' - ' . $max_weeks . ' ' . $week_text;
					}
				}
				else {
					// Single value in weeks
					$weeks = $min_weeks;
					$remaining = $min_remaining;

					if( $remaining === 0 ) {
						$estimation = $weeks . ' ' . $week_text;
					}
					elseif( $remaining <= 2 ) {
						$estimation = esc_html__('Almost', 'rey-core') . ' ' . ($weeks + 1) . ' ' . $week_text;
					}
					elseif( $remaining >= 5 ) {
						$estimation = esc_html__('Over', 'rey-core') . ' ' . $weeks . ' ' . $week_text;
					}
					else {
						$estimation = $weeks . ' ' . $week_text;
					}
				}
			}
			else {
				// Display in days as before
				$estimation = $min;

				// get end point if range
				if( $is_range ){
					$estimation .= ' - ' . $max;
				}

				// days text
				$estimation .= ' ' . $this->settings['days_text'];
			}
		}

		if( '' === $estimation  ){
			return;
		}

		printf('<tr class="estimated-delivery">
			<th>%1$s</th>
			<td data-title="%1$s">%2$s</td>',
			get_theme_mod('estimated_delivery__prefix', esc_html__('Estimated delivery:', 'rey-core')),
			$estimation
		);
	}

	public static function strftime_to_intl($format) {
		$map = [
			'%a' => 'eee',  // Short textual representation of the day
			'%A' => 'EEEE', // Full textual representation of the day
			'%d' => 'dd',  // Two digit representation of the day
			'%e' => 'd',   // Day of the month
			'%h' => 'MMM', // Abbreviated month name (same as %b)
			'%b' => 'MMM', // Abbreviated month name
			'%B' => 'MMMM', // Full month name
			'%m' => 'MM', // Two digit representation of the month
			'%y' => 'yy', // Two digit representation of the year
			'%Y' => 'yyyy', // Four digit representation of the year
			'%H' => 'HH', // Two digit representation of the hour in 24-hour format
			'%I' => 'hh', // Two digit representation of the hour in 12-hour format
			'%M' => 'mm', // Two digit representation of the minute
			'%S' => 'ss', // Two digit representation of the second
			'%p' => 'a',  // 'AM' or 'PM' for 12-hour format times
		];

		return str_replace(array_keys($map), array_values($map), $format);
	}


	public function is_enabled() {
		return get_theme_mod('single_extras__estimated_delivery', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Estimated Delivery', 'Module name', 'rey-core'),
			'description' => esc_html_x('This tool is useful to display a specific date or timeframe until a product will be delivered.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/estimated-delivery-text-issues-with-other-languages/'),
			'video' => true
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
