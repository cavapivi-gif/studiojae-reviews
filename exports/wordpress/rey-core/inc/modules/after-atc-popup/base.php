<?php
namespace ReyCore\Modules\AfterAtcPopup;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	const ASSET_HANDLE = 'reycore-aatc-popup';

	public $load_markup = false;

	public function __construct()
	{

		parent::__construct();

		add_action( 'reycore/customizer/control=product_page_after_add_to_cart_behviour', [ $this, 'customizer_options' ], 10, 2 );

		add_action( 'wp', [$this, 'init']);

		add_action( 'woocommerce_product_options_related', [$this, 'add_extra_product_edit_options'] );
		add_action( 'woocommerce_update_product', [$this, 'process_extra_product_edit_options'] );

		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );

	}

	public function init()
	{

		if( ! $this->is_enabled() ){
			return;
		}

		$this->set_settings();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'reycore/woocommerce/loop/before_grid', [ $this, 'loop_enqueue_scripts' ] );
		add_action( 'reycore/elementor/product_grid/lazy_load_assets', [ $this, 'loop_enqueue_scripts' ] );
		add_filter( 'reycore/modal_template/show', '__return_true' );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'] );
		add_action( 'wp_footer', [$this, 'modal_template']);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'remove_upsell_display'] );
		add_filter( 'theme_mod_header_cart__cross_sells_bubble', [$this, 'maybe_prevent_cross_sells_bubble'], 20 );
		add_filter( 'woocommerce_post_class', [ $this, 'product_classes'], 20, 2 );
	}

	private function set_settings(){
		$this->settings = apply_filters('reycore/module/after_add_to_cart_settings', [
			'limit'   => self::carousel_enabled() ? 10 : 4,
			'columns' => 4,
			'orderby' => 'rand', // @codingStandardsIgnoreLine.
			'order'   => 'desc',
		]);
	}

	function get_products_by_type($type, $product){

		$products = [];

		$pid = $product->get_id();

		if( ! $type ){
			$type = 'related';
		}

		switch ( $type ) {

			case "upsells":
				$products = $this->get_products_items([
					'products' => $product->get_upsell_ids(),
					'name' => 'up-sells'
				]);
				break;

			case "crosssells":
				$products = $this->get_products_items([
					'products' => $this->get_cross_sells($pid),
					'name' => 'cross-sells'
				]);
				break;

			case "related":
				$products = $this->get_products_items([
					'products' => wc_get_related_products( $pid, $this->settings['limit'], $product->get_upsell_ids() ),
					'limit' => false,
					'name' => 'related'
				]);
				break;

			case "categories":
				$products = $this->get_products_items([
					'products' => $this->get_category_products( $pid ),
					'product_objects' => false,
					'filter_visible' => false,
					'limit' => false,
					'name' => 'category-products'
				]);
				break;

			case "tags":
				$products = $this->get_products_items([
					'products' => $this->get_tags_products( $pid ),
					'product_objects' => false,
					'filter_visible' => false,
					'limit' => false,
					'name' => 'tags-products'
				]);
				break;
		}

		return $products;
	}

	function get_products_items($args = []){

		$args = wp_parse_args($args, [
			'products' => [],
			'product_objects' => true,
			'filter_visible' => true,
			'limit' => true,
			'name' => ''
		]);

		if( empty($args['products']) ){
			return;
		}

		$products = $args['products'];

		if( $args['product_objects'] ){
			$products = array_map( 'wc_get_product', $products );
		}

		if( $args['filter_visible'] ){
			$products = array_filter( $products, 'wc_products_array_filter_visible' );
		}

		$products = wc_products_array_orderby( $products, $this->settings['orderby'], $this->settings['order'] );

		if( $args['limit'] ){
			$products = $this->settings['limit'] > 0 ? array_slice( $products, 0, $this->settings['limit'] ) : $products;
		}

		wc_set_loop_prop( 'name', $args['name'] );

		return apply_filters('reycore/module/after_add_to_cart/products', $products, $args);
	}

	function get_cross_sells( $product_id ){
		return get_post_meta( $product_id, '_crosssell_ids', true );
	}

	function get_category_products( $product_id ){

		$cats = get_post_meta( $product_id, '_aatc_cat_ids', true );

		if( !$cats ){
			return [];
		}

		return wc_get_products([
			'category' => $cats,
			'visibility' => 'visible',
			'limit' => $this->settings['limit']
		]);
	}

	function get_tags_products( $product_id ){

		$tags = get_post_meta( $product_id, '_aatc_tags', true );

		if( !$tags ){
			return [];
		}

		return wc_get_products([
			'tag' => $tags,
			'visibility' => 'visible',
			'limit' => $this->settings['limit']
		]);
	}

	function set_loop_props(){

		wc_set_loop_prop( 'columns', $this->settings['columns'] );
		wc_set_loop_prop( 'is_paginated', false );

		if( self::carousel_enabled() ){
			wc_set_loop_prop( 'entry_animation', false );
		}
	}

	public static function no_products_text(){
		return esc_html__('No products were found matching your selection.', 'woocommerce');
	}

	function get_products_loop($product, $type = ''){

		$this->set_settings();

		if( empty($type) ){
			$type = $this->popup_type();
		}

		$selected_products = $this->get_products_by_type( $type, $product );

		if( empty($selected_products) ){
			return self::no_products_text();
		}

		add_filter('theme_mod_loop_quickview', function(){
			return '2';
		});

		add_filter('reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
		add_filter( 'theme_mod_loop_add_to_cart_mobile', '__return_true' );
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');
		add_filter( 'reycore/woocommerce/product_loop_classes', [ $this, 'product_loop_classes'] );
		add_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 5 );

		ob_start();

		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		foreach ( $selected_products as $selected_product ) :

			$post_object = get_post( $selected_product->get_id() );
			setup_postdata( $GLOBALS['post'] =& $post_object );
			wc_get_template_part( 'content', 'product' );

		endforeach;

		woocommerce_product_loop_end();

		remove_filter( 'theme_mod_loop_add_to_cart_mobile', '__return_true' );
		remove_filter('reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
		remove_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');

		do_action( 'woocommerce_after_shop_loop' );

		return ob_get_clean();
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'after_add_to_cart_popup', [$this, 'ajax__get_content'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );
	}

	public function ajax__get_content( $data ) {

		if( ! ( isset($data['product_id']) && $product_id = absint($data['product_id']) ) ){
			return ['errors' => esc_html__('Product ID not found.', 'rey-core')];
		}

		return $this->get_data( $product_id );
	}


	function get_data( $prod_id = '' ){

		$data = '';

		$product = wc_get_product( $prod_id );

		if( ! $product ){
			return $data;
		}

		do_action('reycore/module/after_add_to_cart_popup/data', $product);

		if( $this->is_products() ){

			$data = $this->get_products_loop($product);

			if( (! $data || $data === self::no_products_text()) && get_theme_mod('after_add_to_cart_popup_type_prod_fallback', 'related') === 'related' ){
				$data = $this->get_products_loop($product, 'related');
			}

		}

		// Global Section
		else if( $this->popup_type() === 'gs' ){
			if( ($gs = reycore__get_option('after_add_to_cart_popup_gs', '')) && $gs !== '' ) {
				reycore_assets()->defer_page_styles('elementor-post-' . $gs, true);
				$data = \ReyCore\Elementor\GlobalSections::do_section( $gs, false, true );
			}
		}

		// Editor content
		else if( $this->popup_type() === 'content' ){
			$data = reycore__parse_text_editor( reycore__get_option( 'after_add_to_cart_popup_content' ) );
		}

		return $data;
	}

	function modal_template(){

		if( ! $this->load_markup ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons'); ?>

		<div class="rey-acPopup --hidden">
			<header class="rey-acPopup-header">
				<?php echo apply_filters('reycore/module/after_add_to_cart/title', sprintf('<h2>%s</h2>', get_theme_mod('after_add_to_cart_popup_title', esc_html__( 'You might be interested in..', 'rey-core' )))); ?>
			</header>
			<div class="rey-acPopup-content woocommerce">
				<div class="reymodal__loader"></div>
			</div>
			<div class="rey-acPopup-buttons">
				<a href="#" class="btn btn-primary" data-reyclose><?php esc_html_e('Continue shopping', 'woocommerce') ?></a>
				<a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ) ?>" class="btn btn-secondary rey-acPopup-buttons-cart"><?php echo esc_html_x('View Cart', 'After "Added to cart" Popup text', 'rey-core') ?></a>
				<a href="<?php echo get_permalink( wc_get_page_id( 'checkout' ) ) ?>" class="btn btn-secondary"><?php echo esc_html_x('Checkout', 'After "Added to cart" Popup text', 'rey-core') ?></a>
			</div>
		</div>

		<?php
	}

	public function load_scripts(){

		$this->load_markup = true;

		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles([self::ASSET_HANDLE, 'rey-wc-general', 'rey-wc-general-deferred', 'rey-wc-loop']);

		if( self::carousel_enabled() ){
			reycore_assets()->add_scripts(['splidejs', 'rey-splide']);
			reycore_assets()->add_styles('rey-splide');
		}

		// load modal scripts
		add_filter( 'reycore/modals/always_load', '__return_true');

		do_action('reycore/module/after-atc-popup/scripts');
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

	public function register_scripts($scripts){

	}

	public function enqueue_scripts(){
		if( is_product() ){
			$this->load_scripts();
		}
	}

	public function loop_enqueue_scripts(){
		if( get_theme_mod('after_add_to_cart_popup_loop', false) ){
			$this->load_scripts();
		}
	}

	function product_loop_classes($classes)
	{
		$classes['grid_layout'] = 'rey-wcGrid-default';
		$classes['prevent_change_cols'] = '--prevent-change-cols';
		$classes['prevent_masonry'] = '--prevent-masonry';
		$classes['prevent_metro'] = '--prevent-metro';
		$classes['prevent_scattered'] = '--prevent-scattered'; // make sure scattered is not applied
		$classes['prevent_thumbnail_sliders'] = '--prevent-thumbnail-sliders'; // make sure it does not have thumbnail slideshow

		if( self::carousel_enabled() ){
			$classes['aatc_carousel'] = '--carousel';
		}

		return $classes;
	}

	function product_classes($classes, $product)
	{
		// Fallback to prevent popup if no products available
		if( get_theme_mod('after_add_to_cart_popup_type_prod_fallback', 'related') === '' && $this->is_products() ){

			if( 'related' === $this->popup_type() ){
				return $classes;
			}

			$product_ids = [];
			$pid = $product->get_id();

			if( 'upsells' === $this->popup_type() ){
				$product_ids = $product->get_upsell_ids();
			}
			else if( 'crosssells' === $this->popup_type() ){
				$product_ids = $this->get_cross_sells($pid);
			}
			else if( 'categories' === $this->popup_type() ){
				$product_ids = $this->get_category_products($pid);
			}
			else if( 'tags' === $this->popup_type() ){
				$product_ids = $this->get_tags_products($pid);
			}

			if( empty($product_ids) ){
				$classes['aatc_status'] = '--prevent-aatc';
			}
		}

		return $classes;
	}

	public function script_params($params)
	{

		$show_in_loop = get_theme_mod('after_add_to_cart_popup_loop', false);

		$params['after_add_to_cart_popup_config'] = [
			'autoplay'        => false,
			'autoplay_speed'  => 3000,
			'show_in_loop'    => $show_in_loop,
		];

		if( ! $show_in_loop && 'popup' === $params['after_add_to_cart'] && ! is_product() ){
			$params['after_add_to_cart'] = 'cart';
		}

		return $params;
	}

	public function remove_upsell_display(){

		if( $this->popup_type() === 'upsells' && get_theme_mod('after_add_to_cart_disable_upsells_section', false) ){
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		}
	}

	public function customizer_options( $control_args, $section ){

		$current_control = $section->get_control($control_args['settings']);
		$current_control['choices']['popup'] = esc_html__( 'Show Popup', 'rey-core' );
		$section->update_control( $current_control );

		$section->start_controls_group( [
			'label'    => esc_html__( 'Products Popup Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'product_page_ajax_add_to_cart',
					'operator' => '==',
					'value'    => 'yes',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'after_add_to_cart_popup_loop',
			'label'       => esc_html__( 'Add on catalog too?', 'rey-core' ),
			'help' => [
				esc_html__( 'If enabled, the same functionality will be added on product catalog items, when adding to cart.', 'rey-core' )
			],
			'default'     => false,
		] );

		$type_choices = [
			'upsells' => esc_html__( 'Up-sell products', 'rey-core' ),
			'crosssells' => esc_html__( 'Cross-sell products', 'rey-core' ),
			'related' => esc_html__( 'Related products', 'rey-core' ),
			'categories' => esc_html__( 'Products in Category', 'rey-core' ),
			'tags' => esc_html__( 'Products having tags', 'rey-core' ),
			'content' => esc_html__( 'Custom content', 'rey-core' ),
		];

		if( $this->supports_gs() ){
			$type_choices['gs'] = esc_html__( 'Global Section', 'rey-core' );
		}

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'after_add_to_cart_popup_type',
			'label'       => esc_html__( 'Type of content', 'rey-core' ),
			'default'     => 'upsells',
			'choices'     => $type_choices,
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'product_page_ajax_add_to_cart',
					'operator' => '==',
					'value'    => 'yes',
				],
			]
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'after_add_to_cart_popup_type_prod_fallback',
			'label'       => esc_html__( 'Fallback if no products', 'rey-core' ),
			'default'     => 'related',
			'choices'     => [
				'' => esc_html__( 'Don\'t display the popup', 'rey-core' ),
				'related' => esc_html__( 'Show Related', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'after_add_to_cart_popup_type',
					'operator' => 'in',
					'value'    => ['upsells', 'crosssells', 'related', 'categories', 'tags'],
				],
				[
					'setting'  => 'product_page_ajax_add_to_cart',
					'operator' => '==',
					'value'    => 'yes',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'after_add_to_cart_products_carousel',
			'label'       => esc_html__( 'Add Carousel?', 'rey-core' ),
			'help' => [
				esc_html__( 'If enabled, more products will be loaded showing as a carousel.', 'rey-core' )
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'product_page_ajax_add_to_cart',
					'operator' => '==',
					'value'    => 'yes',
				],
				[
					'setting'  => 'after_add_to_cart_popup_type',
					'operator' => 'in',
					'value'    => ['upsells', 'crosssells', 'related', 'categories', 'tags'],
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'after_add_to_cart_disable_upsells_section',
			'label'       => esc_html__( 'Disable Up-Sells Section', 'rey-core' ),
			'description'       => esc_html__( 'This option disables the Up-sells products section in the product page.', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'after_add_to_cart_popup_type',
					'operator' => '==',
					'value'    => 'upsells',
				],
			],
		] );

		$section->add_control( [
			'type'     => 'text',
			'settings' => 'after_add_to_cart_popup_title',
			'label'    => esc_html__( 'Popup title', 'rey-core' ),
			'default'  => esc_html__( 'You might be interested in..', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'editor',
			'settings'    => 'after_add_to_cart_popup_content',
			'label'       => esc_html__( 'Custom Content', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'product_page_after_add_to_cart_behviour',
					'operator' => '==',
					'value'    => 'popup',
				],
				[
					'setting'  => 'product_page_ajax_add_to_cart',
					'operator' => '==',
					'value'    => 'yes',
				],
				[
					'setting'  => 'after_add_to_cart_popup_type',
					'operator' => '==',
					'value'    => 'content',
				],
			],
		] );

		if( $this->supports_gs() ):

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'after_add_to_cart_popup_gs',
				'label'       => esc_html__( 'Select Global Section', 'rey-core' ),
				'description' => __( 'Select a Generic global section to add into the popup. ', 'rey-core' ),
				'default'     => '',
				'choices'     => \ReyCore\Elementor\GlobalSections::get_global_sections('generic', ['' => '- Select -']),
				'active_callback' => [
					[
						'setting'  => 'product_page_after_add_to_cart_behviour',
						'operator' => '==',
						'value'    => 'popup',
					],
					[
						'setting'  => 'product_page_ajax_add_to_cart',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'setting'  => 'after_add_to_cart_popup_type',
						'operator' => '==',
						'value'    => 'gs',
					],
				],
			] );

		endif;

		$section->end_controls_group();

	}


	function add_extra_product_edit_options(){

		if( !($this->is_categories() || $this->is_tags()) ){
			return;
		}

		?>
		<div class="options_group">

			<h4 style="margin-left: 12px;"><?php esc_html_e('After "Added to cart" popup:', 'rey-core') ?></h4>

			<?php if ( $this->is_categories() ): ?>

			<p class="form-field hide_if_grouped hide_if_external">

				<label for="aatc_cat_ids"><?php esc_html_e( 'Categories:', 'rey-core' ); ?></label>
				<select class="wc-category-search" multiple="multiple" style="width: 50%;" id="aatc_cat_ids" name="_aatc_cat_ids[]" data-placeholder="<?php esc_attr_e( 'Search for categories&hellip;', 'rey-core' ); ?>" >
					<?php

					$cats = get_post_meta( get_the_ID(), '_aatc_cat_ids', true );

					if( !empty($cats) ){
						if( is_array($cats) ):
							foreach ( $cats as $cat ) {
								$selected_category = get_term_by('slug', $cat, 'product_cat');
								echo '<option value="' . esc_attr( $cat ) . '" ' . selected( true, true, false ) . '>' . $selected_category->name . '</option>';
							}
						else:
							if( $selected_category = get_term_by('slug', $cats, 'product_cat') ){
								echo '<option value="' . esc_attr( $cats ) . '" >' . $selected_category->name . '</option>';
							}
						endif;
					}
					?>
				</select> <?php echo wc_help_tip( __( 'Select a category to display its products into the Added to Cart Popup.', 'rey-core' ) ); // WPCS: XSS ok. ?>
			</p>

			<?php endif; ?>

			<?php if ( $this->is_tags() ): ?>

			<p class="form-field hide_if_grouped hide_if_external">

				<label for="aatc_tags"><?php esc_html_e( 'Tags:', 'rey-core' ); ?></label>
				<select class="wc-enhanced-select" multiple="multiple" style="width: 50%;" id="aatc_tags" name="_aatc_tags[]" data-placeholder="<?php esc_attr_e( 'Select tags&hellip;', 'rey-core' ); ?>" >
					<?php

					$tags = get_terms( 'product_tag' );

					if ( ! empty( $tags ) && ! is_wp_error( $tags ) ){

						$selected_tags = get_post_meta( get_the_ID(), '_aatc_tags', true );

						foreach ( $tags as $tag ) {
							$selected = is_array($selected_tags) && in_array( $tag->slug, $selected_tags ) ? ' selected="selected" ' : '';
							echo '<option value="' . esc_attr( $tag->slug ) . '" ' . $selected . '>' . $tag->name . '</option>';
						}
					}

					?>
				</select> <?php echo wc_help_tip( __( 'Select one or more tags to display its products into the Added to Cart Popup.', 'rey-core' ) ); // WPCS: XSS ok. ?>
			</p>

			<?php endif; ?>

		</div>

		<?php
	}

	public function process_extra_product_edit_options( $product_id ) {

		if( ! $this->is_enabled() ){
			return;
		}

		if( !($this->is_categories() || $this->is_tags()) ){
			return;
		}

		if ( $this->is_categories() && ! empty( $_POST[ '_aatc_cat_ids' ] ) && $aatc_cats = wc_clean( $_POST[ '_aatc_cat_ids' ] ) ) {
			update_post_meta( $product_id, '_aatc_cat_ids', $aatc_cats );
		}

		if ( $this->is_tags() && ! empty( $_POST[ '_aatc_tags' ] ) && $aatc_tags = wc_clean( $_POST[ '_aatc_tags' ] ) ) {
			update_post_meta( $product_id, '_aatc_tags', $aatc_tags );
		}
	}

	public function maybe_prevent_cross_sells_bubble($mod){

		if( is_product() ){
			return false;
		}

		if( get_theme_mod('after_add_to_cart_popup_loop', false) ){
			return false;
		}

		return $mod;
	}


	public function is_enabled() {
		return get_theme_mod('product_page_ajax_add_to_cart', 'yes') === 'yes' && get_theme_mod('product_page_after_add_to_cart_behviour', 'cart') === 'popup';
	}

	public function popup_type() {
		return get_theme_mod('after_add_to_cart_popup_type', 'upsells');
	}

	public function is_products() {
		return in_array( $this->popup_type() , ['upsells', 'crosssells', 'related', 'categories', 'tags']);
	}

	public function is_categories() {
		return $this->popup_type() === 'categories';
	}

	public function is_tags() {
		return $this->popup_type() === 'tags';
	}

	public static function carousel_enabled() {
		return get_theme_mod('after_add_to_cart_products_carousel', true);
	}

	public function supports_gs(){
		return class_exists('\ReyCore\Elementor\GlobalSections');
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Products Popup, after "Added To Cart"', 'Module name', 'rey-core'),
			'description' => esc_html_x('After a product has been added to cart, a popup with handpicked or automatically generated products will be shown.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product page', 'Add to cart', 'Upselling'],
			'help'        => reycore__support_url('kb/products-popup-after-added-to-cart'),
			'video'       => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
