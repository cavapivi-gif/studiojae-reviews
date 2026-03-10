<?php
namespace ReyCore\Compatibility\WooAdvancedProductSizeChart;

if ( ! defined( 'ABSPATH' ) ) exit;

// Advanced Product Size Charts for WooCommerce
// https://wordpress.org/plugins/woo-advanced-product-size-chart/

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $data = [];

	const ASSET_HANDLE = 'reycore-wapsc-styles';

	public static $prefix_func = null;
	public static $prefix_upper = null;

	private $__inline_attribute;

	public function __construct()
	{

		$prefix = class_exists('\SCFW_Size_Chart_For_Woocommerce') ? '\scfw_' : '';
		self::$prefix_func = str_replace('\\', '', $prefix);
		self::$prefix_upper = strtoupper($prefix);

		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_action( 'wp', [ $this, 'init' ] );
		add_action( 'reycore/templates/elements/woo_size_charts', [ $this, 'button_html' ] );
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'admin_head', [$this, 'admin_head']);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function init(){

		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		$this->data = [
			'btn_text' => '',
			'modal_content' => ''
		];

		$this->advanced_size_chart_for_woocommerce();

		add_action('rey/after_site_wrapper', [$this, 'modal_html'], 50);
		add_filter( 'reycore/modal_template/show', '__return_true' );

		$btn_position = get_theme_mod('wapsc_button_position', 'before_atc');
		$product_type = 'simple';

		$button_positions = [
			'before_atc' => [
				'simple' => [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 29
				],
				'variable' => [
					'hook' => 'woocommerce_before_single_variation',
					'priority' => 10
				]
			],
			'after_atc' => [
				'simple' => [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 31
				],
				'variable' => [
					'hook' => 'woocommerce_after_single_variation',
					'priority' => 10
				]
			],
			'inline_atc' => [
				'simple' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 0
				],
				'variable' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 0
				],
			],
		];

		// patch when catalog mode
		if( get_theme_mod('shop_catalog', false) ){
			$button_positions['before_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
			$button_positions['before_atc']['variable']['priority'] = 30;
			$button_positions['after_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
			$button_positions['after_atc']['variable']['priority'] = 30;
		}

		if( ($product = wc_get_product()) && $product->is_type( 'variable' ) ){
			$product_type = 'variable';
		}

		if( isset($button_positions[$btn_position]) ){
			add_action($button_positions[$btn_position][$product_type]['hook'], [$this, 'button_html'], $button_positions[$btn_position][$product_type]['priority']);
		}

		else if( 'inline_attribute' === $btn_position ){

			if( 'variable' === $product_type ){
				add_action('woocommerce_before_variations_form', [$this, 'add_inline_button'], 0);
				add_action('woocommerce_after_variations_form', [$this, 'remove_inline_button'], 0);
			}
			else {
				add_action('woocommerce_single_product_summary', [$this, 'button_html'], 31);
			}
		}

		add_shortcode('rey_woo_advanced_size_chart', [$this, 'button_html']);
	}

	public function button_html( $args = [] ){

		if( isset($args['btn_text']) && ! empty($args['btn_text']) ){
			$this->data['btn_text'] = $args['btn_text'];
		}

		if( !($btn_text = $this->data['btn_text']) ){
			return;
		}

		$btn_classes = $wrapper_classes = '';

		$btn_position = get_theme_mod('wapsc_button_position', 'before_atc');

		if(
			'inline_attribute' === $btn_position &&
			$this->__inline_attribute
		){
			$wrapper_classes = 'rey-swatchList-item--dummy';
		}
		else {

			$btn_style = get_theme_mod('wapsc_button_style', 'line-active');

			if( isset($args['btn_style']) && ! empty($args['btn_style']) ){
				$btn_style = $args['btn_style'];
			}
			reycore_assets()->add_styles('rey-buttons');
			$btn_classes = 'btn btn-' . $btn_style;
		}

		printf('<div class="rey-sizeChart-btnWrapper %3$s"><a href="#" class="%4$s rey-sizeChart-btn" data-reymodal=\'%2$s\'>%1$s</a></div>',
			$btn_text,
			wp_json_encode([
				'content' => '.rey-sizeChart-modal',
				'width' => 700,
				'id' => 'wapsc-' . get_the_ID(),
			]),
			esc_attr($wrapper_classes),
			esc_attr($btn_classes),
		);

		add_filter( 'reycore/modals/always_load', '__return_true');

	}

	function swatches_inline_button($item_output, $term, $swatch_base, $params){

		if( ! $this->__inline_attribute ){
			return $item_output;
		}

		if( $params['is_last'] && wc_attribute_taxonomy_name($this->__inline_attribute) === $term->taxonomy ){
			ob_start();
			$this->button_html();
			$item_output .= ob_get_clean();
		}

		return $item_output;
	}

	public function add_inline_button(){

		if( ! ($this->__inline_attribute = get_theme_mod('wapsc_button_attribute')) ){
			return;
		}

		add_filter('reycore/variation_swatches/render_item', [$this, 'swatches_inline_button'], 10, 4 );
	}

	public function remove_inline_button(){

		$this->__inline_attribute = null;

		remove_filter('reycore/variation_swatches/render_item', [$this, 'swatches_inline_button']);

	}

	public function modal_html(){

		if( !($modal_content = $this->data['modal_content']) ){
			return;
		}

		printf('<div class="rey-sizeChart-modal --hidden">%s</div>', $modal_content);
	}

	function advanced_size_chart_for_woocommerce() {

		if( apply_filters('reycore/woo-advanced-product-size-chart/override', true) === false ){
			return;
		}

		$handle = 'advanced-product-size-charts-for-woocommerce';

		add_action( 'wp_print_scripts', function() use ($handle){
			wp_dequeue_script( $handle );
		});

		add_action( 'wp_enqueue_scripts', function() use ($handle){
			wp_dequeue_style( $handle );
			wp_dequeue_style( $handle . '-jquery-modal-default-theme' );
		}, 999);

		$class__Size_Chart_For_Woocommerce_Public = self::$prefix_upper . 'Size_Chart_For_Woocommerce_Public';

		reycore__remove_filters_for_anonymous_class(
			'woocommerce_before_single_product',
			str_replace('\\', '', $class__Size_Chart_For_Woocommerce_Public),
			self::$prefix_func . 'size_chart_popup_button_position_callback',
			10
		);

		$product = wc_get_product();

		if( ! ($product && ($product_id = $product->get_id())) ){
			return;
		}

		$func__size_chart_get_product_chart_id = self::$prefix_func . 'size_chart_get_product';

		if( ! function_exists($func__size_chart_get_product_chart_id) ){
			return;
		}


		$prod_chart_id = $func__size_chart_get_product_chart_id( $product_id );
		$Size_Chart_For_Woocommerce_Public = null;

		$chart_id = null;

		if ( isset( $prod_chart_id ) && !empty($prod_chart_id) ) {

			if( is_array($prod_chart_id) ){

				foreach ($prod_chart_id as $p_chart_id) {

					if( ! $chart_id && 'publish' === get_post_status( $p_chart_id ) ) {
						$chart_id = $p_chart_id;
					}
				}
			}

		}
		else {
			$Size_Chart_For_Woocommerce_Public = new $class__Size_Chart_For_Woocommerce_Public('', '', '');
			$func__size_chart_id_by_category = self::$prefix_func . 'size_chart_id_by_category';
			$chart_id = $Size_Chart_For_Woocommerce_Public->$func__size_chart_id_by_category( $product_id );
		}

		if( is_array($chart_id) && ! empty($chart_id)){
			$chart_id = $chart_id[0];
		}

		// Check if product is belongs to tag

		if ( 0 == $chart_id || ! $chart_id ) {
			if( ! $Size_Chart_For_Woocommerce_Public ){
				$Size_Chart_For_Woocommerce_Public = new $class__Size_Chart_For_Woocommerce_Public('', '', '');
			}

			$func__size_chart_id_by_tag = self::$prefix_func . 'size_chart_id_by_tag';
			$chart_id = $Size_Chart_For_Woocommerce_Public->$func__size_chart_id_by_tag( $product_id );

			// Check if product is belongs to attribute
			if ( 0 == $chart_id || !$chart_id ) {
				$func__size_chart_id_by_attributes = self::$prefix_func . 'size_chart_id_by_attributes';
				$chart_id = $Size_Chart_For_Woocommerce_Public->$func__size_chart_id_by_attributes( $product_id );
			}
		}

		if( is_array($chart_id) && ! empty($chart_id)){
			$chart_id = $chart_id[0];
		}

		if( ! $chart_id ){
			return;
		}


		$func__size_chart_get_label_by_chart_id = self::$prefix_func . 'size_chart_get_label_by_chart_id';
		$func__size_chart_get_position_by_chart_id = self::$prefix_func . 'size_chart_get_position_by_chart_id';
		$func__size_chart_get_popup_label = self::$prefix_func . 'size_chart_get_popup_label';

		$chart_position = $func__size_chart_get_position_by_chart_id( $chart_id );

		if ( 0 !== $chart_id && ('tab' !== $chart_position) ) {

			$size_chart_popup_label = $func__size_chart_get_popup_label( $chart_id );

			if ( isset( $size_chart_popup_label ) && !empty($size_chart_popup_label) ) {
				$popup_label = $size_chart_popup_label;
			} else {
				$popup_label = $func__size_chart_get_label_by_chart_id( $chart_id );
			}

			$chart_popup_icon = scfw_size_chart_get_popup_icon_by_chart_id( $chart_id );
			$icon_html = '';
			if ( ! empty($chart_popup_icon) ) {
				$icon_html = sprintf( '<img src="%1$s" alt="%2$s" class="rey-icon" />', esc_url( SCFW_PLUGIN_URL . 'includes/chart-icons/' . $chart_popup_icon . '.svg' ), $chart_popup_icon );
			}

			$this->data['btn_text'] = $popup_label . $icon_html;

			$file_dir_path = WP_PLUGIN_DIR . '/woo-advanced-product-size-chart/includes/common-files/size-chart-contents.php';

			if ( file_exists( $file_dir_path ) ) {
				ob_start();
				include_once $file_dir_path;
				$this->data['modal_content'] = ob_get_clean();
			}
		}

	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function admin_head(){
		?><style>body .fs-notice[data-id="trial_promotion"],.fs-notice.fs-slug-size-chart-get-started, .editorsSizeChart-notice{display: none !important;}</style><?php
	}

	public function enqueue_scripts(){
		if( ! is_product() ){
			return;
		}
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){
		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
		]);
	}

}
