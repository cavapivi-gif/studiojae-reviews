<?php
namespace ReyCore\Modules\ProductLoopGs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const GSTYPE = 'product-loop';

	const ASSET_HANDLE = 'reycore-module-product-grid-items';

	const WIDGET_PREFIX = 'reycore-woo-grid';

	const WIDGET_CAT = 'rey-woocommerce-grid';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/global_sections/types', [$this, 'add_support'], 20);
		add_filter( 'reycore/acf/global_section_icons', [$this, 'add_icon'], 20);
		add_filter( 'reycore/acf/global_section_descriptions', [$this, 'add_description'], 20);
		add_action( 'reycore/elementor/document_settings/gs/before', [$this, 'gs_settings'], 10, 4);
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
		add_action( 'reycore/global_section_template/before_the_content', [ $this, 'editor_template_before'] );
		add_action( 'reycore/global_section_template/after_the_content', [ $this, 'editor_template_after'] );
		add_action( 'reycore/woocommerce/loop/init', [ $this, 'register_template_skin'] );
		add_action( 'reycore/elementor/products/after_loop_skin', [$this, 'add_template_control_in_widgets']);
		add_action( 'reycore/woocommerce/loop/before_grid', [ $this, 'add_custom_product_content'] );
		add_action( 'reycore/woocommerce/loop/after_grid', [ $this, 'remove_custom_product_content'] );
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

	}

	public function add_support( $gs ){
		$gs[self::GSTYPE]  = __( 'Product Item', 'rey-core' );
		return $gs;
	}

	public function add_description( $gs ){
		$gs[self::GSTYPE]  = esc_html_x('Create custom templates with Elementor to be used in catalog and grid product items.', 'Global section description', 'rey-core');
		return $gs;
	}

	public function add_icon( $gs ){
		$gs[self::GSTYPE]  = 'woo-catalog-product-components';
		return $gs;
	}

	/**
	 * Add page settings into Elementor
	 *
	 * @since 2.4.4
	 */
	public function gs_settings( $doc, $page, $gs_type, $page_id )
	{

		$params = $doc->get_params();
		$params['preview_width'][] = self::GSTYPE;
		$doc->set_params($params);

		$page->add_control(
			'grid_product_id',
			[
				'label' => esc_html_x( 'Preview Product', 'Elementor control label', 'rey-core' ),
				'description' => esc_html_x( 'Setting a product will make this element inherit its properties. Leaving empty will just pull the latest added product.', 'Elementor control label', 'rey-core' ),
				'default' => '',
				'label_block' => true,
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'posts',
					'post_type' => 'product',
				],
				'condition' => [
					'gs_type' => self::GSTYPE,
				],
			]
		);

	}

	/**
	 * On Widgets Registered
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function register_widgets( $widgets_manager ) {

		$widgets = [
			'title',
			'short-desc',
			'price',
			'add-to-cart',
			'attributes',
			'variations',
			'stock',
			'new-badge',
			'sale-badge',
			'featured-badge',
			'categories',
			'rating',
			'thumbnail',
		];

		foreach ( $widgets as $widget_id ) {

			$class_name = ucwords( str_replace( '-', ' ', $widget_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = "\\ReyCore\\Modules\\ProductLoopGs\\ElementorWidgets\\$class_name";

			if( class_exists($class_name) ){
				$widgets_manager->register( new $class_name );
			}
		}

		do_action('reycore/product_grid/register_widgets', $widgets_manager, $widgets);
	}

	public function register_template_skin( $manager ){
		$manager->register_skin( new TemplateLoopSkin() );
	}

	/**
	 * Adds control into Elementor's Product Grid & Product Archive elements
	 *
	 * @param object $stack
	 * @return void
	 */
	public function add_template_control_in_widgets( $stack ){

		$stack->add_control( 'product_loop_template', [
			'type'     => 'rey-query',
			'label_block' => true,
			'label'    => __('Select Template Item', 'rey-core'),
			'default'    => '',
			'query_args' => [
				'type'        => 'posts',
				'post_type'   => \ReyCore\Elementor\GlobalSections::POST_TYPE,
				'meta'        => [
					'meta_key'   => 'gs_type',
					'meta_value' => Base::GSTYPE,
				],
				'edit_link'   => true,
			],
			'condition' => [
				'loop_skin' => 'template',
				'_skin' => ['', 'carousel'],
			],
		] );

	}

	/**
	 * In preview and editor, wrap into a products grid
	 *
	 * @param string $type
	 * @return void
	 */
	public function editor_template_before($type){

		if( self::GSTYPE !== $type ){
			return;
		}

		// $is_preview = isset($_REQUEST[\ReyCore\Elementor\GlobalSections::POST_TYPE], $_REQUEST['preview_id']) && get_post_type() === \ReyCore\Elementor\GlobalSections::POST_TYPE;

		echo '<div class="woocommerce">';
		echo '<ul class="products">';

			$attributes = [
				'class' => 'product'
			];

			if( $product = wc_get_product() ){
				$attributes = apply_filters('reycore/woocommerce/content_product/attributes', [
					'class' => implode( ' ', wc_get_product_class( '', $product ) ),
					'data-pid' => absint( $product->get_id() ),
				], $product);
			}

			printf('<li %s>', reycore__implode_html_attributes($attributes));

	}

	/**
	 * In preview and editor, wrap into a products grid
	 *
	 * @param string $type
	 * @return void
	 */
	public function editor_template_after($type){

		if( self::GSTYPE !== $type ){
			return;
		}

			echo '</li>';

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Hook custom product content.
	 * Prevent leaking the widget option outside of it
	 *
	 * @return void
	 */
	public function add_custom_product_content(){
		add_action( 'reycore/woocommerce/content_product/custom', [ $this, 'general_render_content'] );
	}

	/**
	 * Remove the hook custom product content.
	 * Prevent leaking the widget option outside of it
	 *
	 * @return void
	 */
	public function remove_custom_product_content(){
		remove_action( 'reycore/woocommerce/content_product/custom', [ $this, 'general_render_content'] );
	}

	/**
	 * Render global section template
	 *
	 * @param object $product
	 * @return void
	 */
	public function general_render_content( $product )
	{
		if( 'template' === get_theme_mod('loop_skin', 'basic') ){
			if( $template_id = absint(get_theme_mod('product_loop_template', '')) ){
				echo \ReyCore\Elementor\GlobalSections::render([
					'post_id'     => $template_id,
					'css_classes' => ['--box-styler'],
					'disable_container_spacing' => true,
				]);
			}
		}
	}

	public static function get_default_preview_id(){

		$transient_name = 'grid_product_builder_default_preview_id';

		$id = get_transient( $transient_name );
		$id = 0;

		if( ! $id ){

			$latest_posts = get_posts( [
				'posts_per_page' => 1,
				'post_type'      => 'product',
			] );

			if ( ! empty( $latest_posts ) ) {
				$id = $latest_posts[0]->ID;
				set_transient( $transient_name, $id, HOUR_IN_SECONDS * 12 );
			}

		}

		if( $id ){
			return $id;
		}

		return 0;
	}

	/**
	 * Add Rey Widget Categories
	 *
	 * @since 1.0.0
	 */
	public function add_elementor_widget_categories( $elements_manager ) {

		if( ! class_exists('\WooCommerce') ){
			return;
		}

		$categories = [
			self::WIDGET_CAT => [
				'title' => __( 'WooCommerce <strong>Product Grid</strong>', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
			],
		];

		foreach( $categories as $key => $data ){

			if( ! $this->maybe_show_category($key) ){
				continue;
			}

			$elements_manager->add_category($key, $data);
		}
	}

	public function maybe_show_category( $type ){

		// show everywhere
		if( apply_filters('reycore/module/product_grid_template/always_show_elements', false, $this) ){
			return true;
		}

		return \ReyCore\Elementor\GlobalSections::POST_TYPE === get_post_type() && self::GSTYPE === get_field( 'gs_type', get_the_ID() );

	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
			]
		]);

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Loop Global Section', 'Module name', 'rey-core'),
			'description' => esc_html_x('Build global sections which are used as templates for product items in Catalog pages and Loops', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor', 'woocommerce'],
			'keywords'    => ['elementor', 'carousel', 'grid', 'product'],
			'video'       => true,
			'help'        => reycore__support_url('kb/product-loop-global-section/'),
		];
	}

	public function module_in_use(){
		return ! empty(\ReyCore\Elementor\GlobalSections::get_global_sections(self::GSTYPE));
	}

}
