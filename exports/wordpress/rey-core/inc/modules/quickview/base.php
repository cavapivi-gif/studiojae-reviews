<?php
namespace ReyCore\Modules\Quickview;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $loaded_assets = false;

	const ACTION = 'get_quickview_product';

	const ASSET_HANDLE = 'reycore-quickview';

	public $load_markup = false;

	public function __construct()
	{
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_components']);
	}

	public function init(){

		new Customizer();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );

		if( ! $this->is_enabled() ){
			return;
		}

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/woocommerce/loop/quickview_button', [$this, 'render_button']);
		add_filter( 'reycore/woocommerce/cross_sells/item', [$this, 'add_to_fragments'], 10, 2);
		add_filter( 'reycore/woocommerce/recent/item', [$this, 'add_to_fragments'], 10, 2);
		add_action( 'reycore/woocommerce/cart/crosssells/after', [$this, 'render_in_products']);
		add_action( 'reycore/woocommerce/cart/cart_recent/after', [ $this, 'render_in_products' ] );
		add_action( 'reycore/woocommerce/minicart/products_scripts', [ $this, 'add_assets' ] );
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'add_assets' ] );
		add_action( 'wp_footer', [ $this, 'panel_markup'], 10 );
		add_action( 'reycore/module/extra_variation_images/ajax', [ $this, 'extra_variations_fix_gallery'], 10 );

	}

	public function register_components( $base ){

		$base->register_component( new CompBottom );
		$base->register_component( new CompBottomRight );
		$base->register_component( new CompTopRight );

	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{

		$params['quickview_only'] = get_theme_mod('loop_quickview__link_all', false);
		$params['quickview_mobile'] = $this->show_mobile();

		return $params;
	}

	public function show_mobile(){
		return apply_filters('reycore/woocommerce/quickview/mobile', get_theme_mod('loop_quickview__link_all', false) );
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	/**
	 * Load module main assets
	 *
	 * @return void
	 */
	public function add_assets(){

		static $__qv_assets_added;

		if( ! is_null($__qv_assets_added) ){
			return;
		}

		$__qv_assets_added = true;

		/**
		 * Load for desktop only
		 * since it's a desktop only feature.
		 */
		if( reycore_assets()->mobile ){
			return;
		}

		$this->load_markup = true;

		reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE]);
		reycore_assets()->add_scripts([ 'animejs', self::ASSET_HANDLE ]);

	}

	/**
	 * Load Ajax dependency scripts within the ajax response
	 *
	 * @return void
	 */
	public function add_dependency_assets()
	{

		$assets = [
			'styles' => [
				'rey-wc-general',
				'rey-wc-general-deferred',
				'rey-wc-product',
				'rey-wc-product-lite',
				'rey-wc-product-gallery',
				'rey-wc-product-gallery-horizontal',
				'rey-simple-scrollbar',
				'reycore-post-social-share',
				'rey-splide'
			],
			'scripts' => [
				'splidejs',
				'rey-splide',
				'rey-simple-scrollbar',
				'reycore-wc-product-page-general',
				'reycore-wc-product-gallery',
				'wc-add-to-cart-variation',
			]
		];

		if( is_product() ){
			unset($assets['styles']['rey-wc-product']);
			unset($assets['styles']['rey-wc-product-lite']);
			unset($assets['styles']['rey-wc-product-gallery']);
			unset($assets['styles']['rey-wc-product-gallery-horizontal']);
			unset($assets['styles']['reycore-post-social-share']);
			unset($assets['styles']['rey-splide']);
			unset($assets['scripts']['reycore-wc-product-page-general']);
			unset($assets['scripts']['reycore-wc-product-gallery']);
			unset($assets['scripts']['rey-splide']);
		}

		if( \ReyCore\WooCommerce\Pdp::product_page_ajax_add_to_cart() ){
			$assets['scripts'][] = 'reycore-wc-product-page-ajax-add-to-cart';
		}

		if( get_theme_mod('single_atc_qty_controls', true) ){
			$assets['scripts'][] = 'reycore-wc-product-page-qty-controls';
			if( 'select' === get_theme_mod('single_atc_qty_controls_styles', 'default') ){
				$assets['scripts'][] = 'reycore-wc-product-page-qty-select';
			}
		}

		$assets = apply_filters('reycore/quickview/dependency_assets', $assets);

		reycore_assets()->add_scripts($assets['scripts']);
		reycore_assets()->add_styles($assets['styles']);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'get_quickview_product', [$this, 'ajax__get_product'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );
	}

	/**
	 * Get product
	 *
	 * @since   1.0.0
	 */
	public function ajax__get_product( $data )
	{
		if( ! $this->is_enabled() ){
			return;
		}

		if( ! (isset($data['id']) && ($pid = absint($data['id']))) ){
			return ['errors'=> esc_html__('Missing product ID.', 'rey-core')];
		}

		$this->add_dependency_assets();
		$this->fix_page();

		ob_start();

		reycore__get_template_part('template-parts/woocommerce/quickview-panel', false, false, [
			'pid' => $pid
		]);

		do_action('reycore/module/quickview/panel_content');

		$return = [
			'content' => ob_get_clean()
		];

		// When lazy loaded, load the markup
		if( 'true' == reycore__clean($data['markup']) ){

			ob_start();
			$this->panel_markup(true);
			$return['markup'] = ob_get_clean();

		}

		if( isset($data['woo_template']) && ! absint($data['woo_template']) ){
			ob_start();
			wc_get_template( 'single-product/add-to-cart/variation.php' );
			$return['woo-template-scripts'] = ob_get_clean();
		}

		return apply_filters('reycore/quickview/ajax_response', $return, $data);

	}

	/**
	 * Load the panel's markup
	 *
	 * @param boolean $force
	 * @return void
	 */
	public function panel_markup( $force = false )
	{

		if( ! $force && ! apply_filters('reycore/quickview/can_load_markup', $this->load_markup) ){
			return;
		}

		if( is_admin() || is_checkout() ){
			return;
		}

		if( ! reycore__can_add_public_content() ){
			return;
		}

		$args['classes'] = [
			$this->show_mobile() ? '--show-mobile' : '',
		];

		$args['panel_style'] = get_theme_mod('loop_quickview__panel_style', 'curtain');

		reycore__get_template_part('template-parts/woocommerce/quickview-markup', false, false, $args);

		if( $this->show_mobile() ){
			echo '<style>.rey-productFooter-item.rey-productFooter-item--quickview{display:block;}</style>';
		}

		do_action('reycore/module/quickview/panel_markup');
	}

	public function fix_page(){

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		\ReyCore\WooCommerce\Base::handle_catalog_mode();

		set_query_var('rey__is_quickview', true);

		add_filter('reycore/woocommerce/pdp/render/product_nav', '__return_false'); // remove pdp navigation (allow custom one)
		add_filter('theme_mod_product_gallery_thumbs_disable_cropping', '__return_false'); // remove pdp navigation (allow custom one)

		add_filter('theme_mod_single_skin', function(){
			return 'default';
		});

		add_filter('theme_mod_single_breadcrumbs', function(){
			return 'no';
		});

		add_filter('theme_mod_product_gallery_layout', function(){
			return 'horizontal';
		});

		// Temporary disable reviews
		add_filter('theme_mod_single__accordion_items', function($tabs){

			foreach ($tabs as $key => $value) {
				if( isset($value['item']) && 'reviews' === $value['item'] ){
					unset($tabs[$key]);
				}
			}

			return $tabs;
		});

		add_filter('woocommerce_post_class', function($classes){
			if( array_key_exists('product_page_class', $classes) ){
				unset($classes['product_page_class']);
			}
			return $classes;
		});

		add_filter('reycore/woocommerce/product_image/params', function($params){

			$params['type'] = 'horizontal';
			$params['zoom'] = false;
			$params['cascade_bullets'] = false;
			$params['thumb_event'] = 'click';
			$params['autoheight'] = false;

			$params['loop'] = true;
			$params['start_index'] = 0;

			if( $params['lightbox'] !== false ){
				$params['lightbox'] = true;
			}

			return $params;
		}, 20);

		$gallery_type = get_theme_mod('loop_quickview_gallery_type', 'thumbs');

		add_filter('woocommerce_single_product_image_gallery_classes', function( $css_classes ) use ($gallery_type){
			$css_classes['qv_type'] = '--qv-type-' . esc_attr($gallery_type);
			return $css_classes;
		});

		add_filter('reycore/woocommerce/pdp/gallery/with_thumbs', function( $status ) use ($gallery_type){
			if( $gallery_type !== 'thumbs' ) {
				$status = false;
			}
			return $status;
		});

		add_filter('theme_mod_product_gallery_thumbs_nav_style', function( $style ){
			return 'boxed';
		});

		add_filter('theme_mod_product_gallery_mobile_nav_style', function( $style ) use ($gallery_type){
			if( $gallery_type !== 'thumbs' ) {
				return 'circle';
			}
			return $style;
		});

		add_filter('reycore/woocommerce/pdp/gallery/mobile_nav_classes', function( $css_classes ){
			unset($css_classes['hide_desktop']);
			return $css_classes;
		});

		\ReyCore\Plugin::instance()->woocommerce_pdp->product_page();

		if( $gallery = reycore_wc__get_pdp_component('gallery') ){
			$gallery->init();
		}

		// make short desc shorter
		add_filter('reycore_theme_mod_product_short_desc_toggle_v2', '__return_true');

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 1 );

		/**
		 * add custom title with link
		 */
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		add_action( 'woocommerce_single_product_summary', function() {

			echo '<div class="rey-qvNav">';

				echo sprintf(
					'<button class="rey-qvNav-prev" data-id="" title="%2$s">%1$s</button>',
					reycore__arrowSvg(false),
					esc_html__('Navigate to previous', 'rey-core')
				);

				echo sprintf(
					'<button class="rey-qvNav-next" data-id="" title="%2$s">%1$s</button>',
					reycore__arrowSvg(),
					esc_html__('Navigate to next', 'rey-core')
				);

			echo '</div>';

			echo sprintf( '<h1 class="product_title entry-title"><a href="%s">%s</a></h1>',
				get_the_permalink(),
				get_the_title()
			);

		}, 3 );

		/**
		 * add specifications
		 */

		add_action('woocommerce_single_product_summary', [$this, 'single_product_summary'], 100);

		do_action('reycore/module/quickview/product');

	}

	/**
	 * Fix gallery when using extra variation images, because it doesn;t
	 * keep the settings when switching variations.
	 *
	 * @param array $data (not sanitized)
	 * @return void
	 */
	public function extra_variations_fix_gallery( $data ){

		if ( ! (isset( $data['source'] ) && ($source = reycore__clean($data['source']))) ) {
			return;
		}

		if( 'quickview-gallery' !== $source ){
			return;
		}

		$this->fix_page();

	}

	public function get_acc_tabs_items(){
		return array_column(get_theme_mod('single__accordion_items', []), 'item');
	}

	public function single_product_summary(){

		if( ! get_theme_mod('loop_quickview_specifications', true) ){
			return;
		}

		if( ($acc_items = $this->get_acc_tabs_items()) && in_array('additional_information', $acc_items, true) ){
			return;
		}

		global $product;

		if( !$product ){
			return;
		}

		ob_start();
		do_action( 'woocommerce_product_additional_information', $product );
		$content = ob_get_clean();

		if( ! empty($content) ){
			echo '<div class="rey-qvSpecs">';
			$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Specifications', 'rey-core' ) );
			if ( $heading ) :
				printf('<h2>%s</h2>', esc_html( $heading ));
			endif;
			echo $content;
			echo '</div>';
		}
	}

	public function render_button($args = []){

		if( ! $this->is_enabled() ){
			return;
		}

		$args = wp_parse_args($args, [
			'product_id' => '',
			'class' => 'button',
		]);

		$this->add_assets();

		echo $this->get_button_html($args);
	}

	public function maybe_hide(){

		static $__maybe_hide;

		if( ! is_null($__maybe_hide) ){
			return $__maybe_hide;
		}

		$__maybe_hide = get_theme_mod('loop_quickview__link_all', false) && get_theme_mod('loop_quickview__link_all_hide', false);

		return $__maybe_hide;
	}

	public function get_button_attributes(){

		static $__button_attributes;

		if( ! is_null($__button_attributes) ){
			return $__button_attributes;
		}

		$text = apply_filters('reycore/woocommerce/quickview/text', esc_html_x('QUICKVIEW', 'Quickview button text in products listing.', 'rey-core'));

		$button_content = $text;
		$btn_style = reycore_wc__get_setting('loop_quickview_style');
		$button_class = ' rey-btn--' . $btn_style;

		if( $this->get_type() === 'icon' ){
			$button_content = apply_filters('reycore/woocommerce/quickview/btn_icon', reycore__get_svg_icon([ 'id'=> reycore_wc__get_setting('loop_quickview_icon_type') ]) );
			$button_class .= ' rey-btn--qicon';
		}

		if( $this->show_mobile() ){
			$button_class .= ' --show-mobile';
		}

		return $__button_attributes = [
			'button_class' => $button_class,
			'text' => $text,
			'button_content' => $button_content,
		];
	}

	/**
	 * Print quickview button
	 */
	public function get_button_html( $args = [] )
	{

		if( $this->maybe_hide() ){
			return;
		}

		if( $args['product_id'] !== '' ){

			$id = $args['product_id'];
			$product = wc_get_product($id);

		}
		else {

			if( ! ($product = wc_get_product()) ){
				global $product;
			}

			if ( ! ($product && $id = $product->get_id()) ) {
				return;
			}

		}

		$button_attributes = $this->get_button_attributes();

		reycore_assets()->add_styles('rey-buttons');

		$btn_html = sprintf(
			'<button class="%1$s rey-quickviewBtn js-rey-quickviewBtn" data-id="%2$s" title="%3$s">%4$s</button>',
			$args['class'] . $button_attributes['button_class'],
			esc_attr( $id ),
			$button_attributes['text'],
			$button_attributes['button_content']
		);

		return apply_filters('reycore/woocommerce/quickview/btn_html', $btn_html, $product);
	}

	public function get_type()
	{
		return reycore_wc__get_setting('loop_quickview');
	}

	/**
	 * Add placeholders into Cross-sells markup template
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function render_in_products(){
		echo '<# if(items[i].quickview){ #> {{{items[i].quickview}}} <# } #>';
	}

	/**
	 * Add brands into Cross-sells fragments data
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function add_to_fragments( $data, $product ){

		if( ! apply_filters('reycore/woocommerce/cart/crosssells/quickview', true) ) {
			return $data;
		}

		ob_start();

		$this->render_button([
			'product_id' => $product->get_id(),
			'class' => 'btn btn-line-active'
		]);

		$data['quickview'] = ob_get_clean();

		return $data;
	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_quickview',
			[
				'label' => esc_html__( 'Quickview', 'rey-core' ),
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

	public function is_enabled() {
		return get_theme_mod('loop_quickview') !== '2';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Quickview', 'Module name', 'rey-core'),
			'description' => esc_html_x('Peek products information with a quickview button inside products in catalog.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
			// 'help'        => reycore__support_url('kb/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
