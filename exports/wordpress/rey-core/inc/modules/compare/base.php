<?php
namespace ReyCore\Modules\Compare;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public $is_enabled = false;
	public static $page_id = 0;

	public $load_markup = false;

	const COOKIE_KEY = 'rey_compare_ids';

	const ASSET_HANDLE = 'reycore-compare';

	public function __construct()
	{

		parent::__construct();

		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
	}

	public function init(){

		self::$page_id = self::get_page_id();

		$this->is_enabled = get_theme_mod('compare__enable', false) && ! is_null(self::$page_id);

		if( ! $this->is_enabled ){
			return;
		}

		add_action( 'reycore/loop_inside_thumbnail/top-right', [$this, 'catalog_button_html']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		add_action( 'reycore/elementor/product_grid/lazy_load_assets', [$this, 'lazy_load_markup']);

		add_filter('rey/site_content_classes', [$this, 'add_loading'], 10);

		add_action( 'reycore/woocommerce/wishlist/page', [ $this, 'add_assets' ] );

		add_filter( 'body_class', [$this, 'append_compare_page_class']);

		add_action( 'rey/before_site_container', [$this, 'apply_filter_content']);
		add_action( 'rey/after_site_container', [$this, 'remove_filter_content']);

		add_filter( 'rey/main_script_params', [$this, 'script_params']);

		add_filter( 'reycore/woocommerce/compare/ids', [$this, 'get_compare_ids']);
		add_filter( 'reycore/woocommerce/compare/counter_html', [$this, 'compare_counter_html']);
		add_filter( 'reycore/woocommerce/compare/title', [$this, 'compare_title']);

		add_filter( 'reycore/woocommerce/account_menu_items/before_logout', [$this, 'add_compare_page_to_account_menu']);
		add_filter( 'woocommerce_get_endpoint_url', [$this, 'add_compare_url_endpoint'], 20, 4);

		add_action( 'woocommerce_before_single_product', [$this, 'pdp_button']);

		add_action( 'wp_login', [$this, 'update_ids_after_login'], 10, 2);

		add_action( 'template_redirect', [$this, 'track_products'], 20 );
		add_action( 'reycore/woocommerce/quickview/before_render', [$this, 'track_products'], 20 );

		add_action( 'wp_footer', [$this, 'after_add_markup']);

		new CompatStickyAtc();
	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function add_assets(){

		static $assets_loaded;

		if( is_null($assets_loaded) ){

			$this->load_markup = true;

			reycore_assets()->add_scripts([self::ASSET_HANDLE]);
			reycore_assets()->add_styles([self::ASSET_HANDLE, 'rey-simple-scrollbar', 'reycore-tooltips', 'rey-wc-star-rating']);

			$assets_loaded = true;
		}
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority'  => 'low',
			]
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-page' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style-page.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce', 'wc-js-cookie', 'rey-simple-scrollbar', 'reycore-tooltips'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public static function get_cookie_key( $custom = '' ){
		return self::COOKIE_KEY . '_' . (is_multisite() ? get_current_blog_id() : 0) . ($custom ? '_' . $custom : '');
	}

	public function script_params($params){

		$params['compare_after_add'] = get_theme_mod('compare__after_add', 'notice');
		$params['compare_text_add'] = self::get_texts('add');
		$params['compare_text_rm'] = self::get_texts('rm');

		return $params;
	}

	public static function get_texts( $text = '' ){

		$texts = apply_filters('reycore/woocommerce/compare/texts',  [
			'compare__text' => __('Compare products', 'rey-core'),
			'add' => esc_html__('Compare product', 'rey-core'),
			'rm' => esc_html__('Remove from list', 'rey-core'),
			'btn' => esc_html__('COMPARE NOW', 'rey-core'),
			'page_title' => __('Compare list is empty.', 'rey-core'),
			'page_text' => __('You don\'t have any products added in your list. Search and choose items to your liking!', 'rey-core'),
			'page_btn_text' => __('SHOP NOW', 'rey-core'),
			'close' => esc_html__('CLOSE', 'rey-core'),
			'products' => esc_html__('PRODUCT(s)', 'rey-core'),
			'recently_viewed' => esc_html__('RECENTLY VIEWED PRODUCTS', 'rey-core'),
			'recently_viewed_add' => esc_html__('Add to list', 'rey-core'),
			'reset_list' => esc_html__('RESET LIST', 'rey-core'),
			'reset_list_mobile' => esc_html__('RESET', 'rey-core'),
			'no_products' => esc_html__('No recently viewed products yet.', 'rey-core'),
			'mobile_tip' => esc_html__('Hold and drag the table!', 'rey-core'),
		]);

		if( !empty($text) && isset($texts[$text]) ){
			return $texts[$text];
		}

		return $texts;
	}

	public static function get_cookie_products_ids(){
		$products = [];

		if ( ! empty( $_COOKIE[self::get_cookie_key()] ) ) { // @codingStandardsIgnoreLine.
			$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key()] ) ) ); // @codingStandardsIgnoreLine.
		}

		return $products;
	}

	public static function get_ids(){

		$products = [];

		if( is_user_logged_in() ){
			$user = wp_get_current_user();
			$products = get_user_meta($user->ID, self::get_cookie_key(), true);
		}
		else {
			$products = self::get_cookie_products_ids();
		}

		return $products;
	}

	function add_loading($classes){

		if( self::is_compare_page() ){
			if( $this->get_ids() ){
				$classes[] = '--loading';
			}
		}

		return $classes;
	}

	public function get_product_id(){

		if( ! $this->is_enabled ){
			return;
		}

		if( ! get_theme_mod('compare__loop_enable', true) ){
			return;
		}

		$product = wc_get_product();

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		return $id;
	}

	public function get_button_attributes(){

		static $button_attributes;

		if( is_null($button_attributes) ){

			$button_class = [];
			$button_text = self::get_texts('add');

			if( is_user_logged_in() ){
				$button_class[] = '--supports-ajax';
			}

			$button_content = self::get_compare_icon();

			$button_attributes = [
				'class'   => $button_class,
				'text'    => $button_text,
				'content' => $button_content,
				'tooltip' => $button_text,
			];

		}

		return $button_attributes;
	}

	function catalog_button_html(){

		if( ! ($id = $this->get_product_id()) ){
			return;
		}

		$this->add_assets();

		$button_attributes = $this->get_button_attributes();

		if( ( $active_products = self::get_ids() ) && in_array($id, $active_products, true) ){
			$button_attributes['class'][] = '--in-compare';
			$button_attributes['text'] = self::get_texts('rm');
		}

		printf(
			'<a href="%5$s" class="%1$s rey-compareBtn rey-compareBtn-link" data-lazy-hidden data-id="%2$s" title="%3$s" aria-label="%3$s" data-rey-tooltip="%6$s">%4$s</a>',
			esc_attr( implode(' ', $button_attributes['class']) ),
			esc_attr($id),
			$button_attributes['text'],
			$button_attributes['content'],
			esc_url( get_permalink($id) ),
			$button_attributes['tooltip']
		);
	}

	public function lazy_load_markup(){
		$this->load_markup = true;
	}

	function pdp_button(){

		if( !get_theme_mod('compare__pdp_enable', true) ){
			return;
		}

		$position = get_theme_mod('compare__pdp_position', 'after');

		$hooks = [
			'before' => [
				'hook' => 'woocommerce_before_add_to_cart_form',
				'priority' => 10
			],
			'inline' => [
				'hook' => 'woocommerce_after_add_to_cart_button',
				'priority' => 2
			],
			'after' => [
				'hook' => 'reycore/woocommerce/single/after_add_to_cart_form',
				'priority' => 0
			],
			'not_purchasable' => [
				'hook' => 'woocommerce_single_product_summary',
				'priority' => 25
			],
		];

		if ( ($product = wc_get_product()) && ! $product->is_purchasable() && $product->get_type() !== 'external' ) {
			$position = 'not_purchasable';
		}

		add_action( $hooks[$position]['hook'], [$this, 'output_pdp_button'], $hooks[$position]['priority'] );

	}

	public function maybe_render(){

		if( ! apply_filters( 'reycore/woocommerce/pdp/render/compare', true ) ){
			return;
		}

		return true;
	}

	function output_pdp_button(){

		if( ! $this->maybe_render() ){
			return;
		}

		$product = wc_get_product();

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$this->add_assets();

		$button_class = $text_class = [];
		$active_products = self::get_ids();

		$button_text = self::get_texts('add');

		if( !empty($active_products) && in_array($id, $active_products, true) ){
			$button_class[] = '--in-compare';
			$button_text = self::get_texts('rm');
		}

		if( is_user_logged_in() ){
			$button_class[] = '--supports-ajax';
		}

		$button_content = self::get_compare_icon();
		$is_block = false;

		if( ($btn_style = get_theme_mod('compare__pdp_btn_style', 'btn-line')) && $btn_style !== 'none' ){

			reycore_assets()->add_styles('rey-buttons');

			if( strpos($btn_style, 'btn--block') !== false ){
				$is_block = true;
			}

			$button_class['btn_style'] = 'btn ' . $btn_style;

			// disable line buttons
			if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
				$text_class['text_style'] = 'btn ' . $btn_style;
				$button_class['btn_style'] = 'btn --btn-text';
			}
		}

		$text_visibility = get_theme_mod('compare__pdp_wtext', 'show_desktop');

		if( $text_visibility && $button_text ){

			$button_class[] = '--text-' . esc_attr($text_visibility);

			if( $text_visibility === 'show_desktop' ){
				$text_class[] = '--dnone-sm --dnone-md';
			}

			$button_content .= sprintf('<span class="rey-compareBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);

		}

		$attributes = [
			'aria-label' => $button_text
		];

		// only when text is hidden
		if( $text_visibility === '' && get_theme_mod('compare__pdp_tooltip', false) ){
			$attributes['data-rey-tooltip'] = $button_text;
		}

		$btn_html = sprintf(
			'<div class="rey-compareBtn-wrapper %7$s" data-transparent><a href="%5$s" class="%1$s rey-compareBtn" data-id="%2$s" title="%3$s" %6$s>%4$s</a></div>',
			esc_attr(implode(' ', $button_class)),
			esc_attr($id),
			$button_text,
			$button_content,
			esc_url( get_permalink($id) ),
			reycore__implode_html_attributes($attributes),
			($is_block ? '--block' : '')
		);

		echo $btn_html;


	}

	public static function get_compare_icon( $class = '' ){
		return reycore__get_svg_icon([
			'id' => 'compare',
			'class' => 'rey-compareBtn-icon ' . $class
		]);
	}


	public static function is_compare_page(){

		if( ! ($page_id = self::$page_id) ){
			return;
		}

		static $is;

		if( is_null($is) ){

			if( reycore__is_multilanguage() ){
				$page_id = absint( apply_filters('reycore/translate_ids', $page_id, 'post') );
			}

			$is = is_page($page_id);
		}

		return $is;
	}

	public static function get_page_id(){

		static $id;

		if( is_null($id) ){
			if( $page_id = get_theme_mod('compare__default_url', '') ){
				$id = absint($page_id);
			}
		}

		return $id;
	}

	public static function get_page_url( $url = '' ){

		if( $page_id = self::$page_id ){
			return esc_url( get_permalink($page_id) );
		}

		return $url;
	}

	function append_compare_page_class($classes){

		if( self::is_compare_page() ){
			$classes[] = 'woocommerce';
			$classes[] = 'rey-compare-page';
		}

		return $classes;
	}

	function apply_filter_content(){

		if( ! self::is_compare_page() ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE . '-page');

		add_filter( 'the_content', [$this, 'append_page_content']);
		remove_all_actions('rey/content/title');
	}

	function remove_filter_content(){
		remove_filter( 'the_content', [$this, 'append_page_content']);
	}

	function append_page_content( $content ){

		if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
			return $content;
		}

		if( !is_main_query() ){
			return $content;
		}

		$this->add_assets();

		add_filter('comments_open', '__return_false', 20, 2);
		add_filter('pings_open', '__return_false', 20, 2);
		add_filter('comments_array', '__return_empty_array', 10, 2);

		reycore_assets()->add_styles('rey-buttons');

		ob_start(); ?>

			<div class="rey-compareWrapper --empty"></div>

			<div class="rey-compare-emptyPage">

				<div class="rey-compare-emptyPage-icon">
					<?php echo self::get_compare_icon(); ?>
				</div>

				<div class="rey-compare-emptyPage-title">
					<h2><?php echo self::get_texts('page_title'); ?></h2>
				</div>

				<div class="rey-compare-emptyPage-content">
					<p><?php echo self::get_texts('page_text'); ?></p>
					<a href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ) ?>" class="btn btn-primary">
						<?php echo self::get_texts('page_btn_text') ?>
					</a>
				</div>
			</div>

			<div class="rey-lineLoader rey-compareLoader"></div>

		<?php
		$w_content = ob_get_clean();

		if( apply_filters('reycore/woocommerce/compare/empty_page', true) ){
			return $w_content;
		}
		else {
			return $content . $w_content;
		}

	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'compare_get_page_content', [$this, 'ajax__get_page_content'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
		$ajax_manager->register_ajax_action( 'compare_add_to_user', [$this, 'ajax__add_to_user_meta'] );
		$ajax_manager->register_ajax_action( 'compare_get_viewed_products', [$this, 'ajax__get_viewed_products'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	public function ajax__get_page_content(){

		if( ! $this->is_enabled ){
			return;
		}

		ob_start();
		reycore__get_template_part('inc/modules/compare/compare-page');
		return ob_get_clean();
	}

	function after_add_markup(){

		if( ! $this->load_markup ){
			return;
		}

		if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
			return;
		}

		// anywhere but compare page
		if( self::is_compare_page() ){
			return;
		}

		$type = get_theme_mod('compare__after_add', 'notice');

		if( $type === 'notice' ){

			reycore_assets()->add_styles('rey-buttons'); ?>

			<div class="rey-compareNotice-wrapper" data-lazy-hidden>
				<div class="rey-compareNotice">
					<div class="rey-compareNotice-inner">
						<div class="rey-compareIcon">
							<?php echo self::get_compare_icon(); ?>
							<a href="#" class="rey-compareClose" data-tooltip-text="<?php echo self::get_texts('close') ?>"><?php echo reycore__get_svg_icon(['id' => 'close']) ?></a>
						</div>
						<div class="rey-compareTitle">
							<h4><?php echo self::get_texts('compare__text'); ?></h4>
							<div class="rey-compareTitle-count">
								<?php echo $this->compare_counter_html(); ?> <?php echo self::get_texts('products') ?>
							</div>
							<div class="rey-lineLoader"></div>
						</div>
						<a href="#" class="btn btn-line rey-compare-recentBtn">
							<span class="--dnone-md --dnone-sm"><?php echo self::get_texts('recently_viewed') ?></span>
							<?php echo reycore__get_svg_icon(['id' => 'grid', 'class' => '__mobile --dnone-lg']) ?>
							<?php echo reycore__get_svg_icon(['id' => 'arrow', 'class' => '__inactive']) ?>
							<?php echo reycore__get_svg_icon(['id' => 'close', 'class' => '__active']) ?>
						</a>
						<a href="#" class="btn btn-line rey-compare-resetBtn">
							<?php
								printf('<span class="--dnone-md --dnone-sm">%s</span>', self::get_texts('reset_list'));
								printf('<span class="--dnone-lg">%s</span>', self::get_texts('reset_list_mobile'));
							?>
						</a>
						<?php if( $compare_url = self::get_page_url() ){
							$compare_text = sprintf('<span class="--dnone-md --dnone-sm">%s</span>', self::get_texts('btn'));
							printf('<a href="%1$s" class="btn btn-primary rey-compare-compareBtn">%2$s</a>',
								$compare_url,
								$compare_text . self::get_compare_icon('--dnone-lg')
							);
						} ?>
					</div>
					<div class="rey-compareNotice-recentProducts">
						<div class="rey-compareNotice-recentProducts-inner"></div>
						<div class="rey-lineLoader"></div>
					</div>
				</div>
			</div>

			<?php
		}
	}

	function add_compare_page_to_account_menu($items){

		if( self::$page_id ){
			$sup = sprintf(' <sup>%s</sup>', $this->compare_counter_html() );
			$items['rey_compare'] = ! is_rtl() ? $this->compare_title() . $sup : $sup . $this->compare_title();
		}

		return $items;
	}

	function add_compare_url_endpoint($url, $endpoint, $value, $permalink){

		if( $endpoint === 'rey_compare') {
			$url = self::get_page_url();
		}

		return $url;
	}

	function compare_counter_html(){
		return '<span class="rey-compareCounter-number --empty">0</span>';
	}

	function compare_title(){
		return self::get_texts('compare__text');
	}

	function get_compare_ids( $ids ){

		$product_ids = self::get_ids();

		if( empty($product_ids) ){
			return $ids;
		}

		return array_reverse($product_ids);
	}

	public function ajax__add_to_user_meta(){

		if( ! $this->is_enabled ){
			return;
		}

		if( ! is_user_logged_in() ){
			return ['errors' => esc_html__('Not logged in!', 'rey-core')];
		}

		$user = wp_get_current_user();
		$product_ids = self::get_cookie_products_ids();

		if( update_user_meta($user->ID, self::get_cookie_key(), $product_ids) ){
			return $product_ids;
		}

	}

	public function update_ids_after_login( $user_login, $user){

		$product_ids = self::get_cookie_products_ids();
		$saved_product_ids = get_user_meta($user->ID, self::get_cookie_key(), true);

		if( ! is_array($saved_product_ids) ) {
			$saved_product_ids = [];
		}

		update_user_meta($user->ID, self::get_cookie_key(), array_unique( array_merge($product_ids, $saved_product_ids) ) );
	}

	function track_products() {

		$track = false;

		if ( is_singular( 'product' ) ) {
			$track = true;
		}

		$is_quickview = get_query_var('rey__is_quickview', false) === true;

		if( $is_quickview ){
			$track = true;
		}

		$track = apply_filters('reycore/woocommerce/track_product_view', $track);

		if ( ! $track ) {
			return;
		}

		global $post;

		$viewed_products = [];

		if ( ! empty( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) ); // @codingStandardsIgnoreLine.
		}

		$product_id = $post->ID;

		if( (is_tax() || is_shop()) && ! $is_quickview ){
			$product_id = '';
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $product_id ] ) ) {
			unset( $viewed_products[ $keys[ $product_id ] ] );
		}

		if( $product_id ){
			$viewed_products[] = $product_id;
		}


		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( self::get_cookie_key('recently_viewed'), implode( '|', $viewed_products ) );
	}

	function get_tracked_products(){

		$products = [];

		if ( ! empty( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) { // @codingStandardsIgnoreLine.
			$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) ); // @codingStandardsIgnoreLine.
		}

		return $products;
	}

	function ajax__get_viewed_products(){

		if( ! $this->is_enabled ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons');

		$ids = $this->get_tracked_products();

		if( empty($ids) ){
			return ['errors' => self::get_texts('no_products')];
		}

		$html = '';

		foreach ($ids as $key => $pid) {
			$product = wc_get_product($pid);

			if( ! $product ){
				continue;
			}

			$html .= '<li>';

				$html .= wp_get_attachment_image($product->get_image_id(), 'thumbnail');
				$html .= sprintf('<h4><a href="%2$s">%1$s</a></h4>', $product->get_title(), esc_url( get_the_permalink( $pid ) ));
				$html .= $product->get_price_html();
				$html .= sprintf('<a href="#" class="btn btn-line-active rey-compare-recentProducts-add" data-id="%d">%s</a>', $pid, self::get_texts('recently_viewed_add'));

			$html .= '</li>';

		}

		if( $html ){

			$content = '<ul class="rey-compare-recentProducts">';
			$content .= $html;
			$content .= '</ul>';

			return $content;
		}

	}

	public static function fields( $with_attr = true ) {

		$fields = [
			'image'       => __( 'Image', 'rey-core' ),
			'title'       => __( 'Title', 'rey-core' ),
			'description' => __( 'Description', 'rey-core' ),
			'sku'         => __( 'Sku', 'rey-core' ),
			'stock'       => __( 'Availability', 'rey-core' ),
			'weight'      => __( 'Weight', 'rey-core' ),
			'dimensions'  => __( 'Dimensions', 'rey-core' ),
		];

		if( $with_attr ){
			$fields = array_merge( $fields, self::attribute_taxonomies() );
		}

		$fields['price'] = __( 'Price', 'rey-core' );
		$fields['add-to-cart'] = __( 'Add to cart', 'rey-core' );

		if( $product = wc_get_product() ){
			$fields['add-to-cart'] = $product->single_add_to_cart_text();
		}

		return apply_filters( 'reycore/woocommerce/compare/fields', $fields );
	}

	public static function attribute_taxonomies() {

		$attributes = [];

		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if( empty( $attribute_taxonomies ) )
			return [];
		foreach( $attribute_taxonomies as $attribute ) {
			$tax = wc_attribute_taxonomy_name( $attribute->attribute_name );
			if ( taxonomy_exists( $tax ) ) {
				$attributes[$tax] = $attribute->attribute_label;
			}
		}

		return $attributes;
	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_compare',
			[
				'label' => esc_html__( 'Compare', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$stack->end_injection();

	}

	function is_enabled(){
		return $this->is_enabled;
	}


	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Compare Products', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds Product Comparing ability in a table with all features showcased.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page', 'product catalog'],
			'help'        => reycore__support_url('kb/compare-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}


}
