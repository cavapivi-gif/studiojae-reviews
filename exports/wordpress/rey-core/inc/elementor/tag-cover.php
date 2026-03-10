<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TagCover
{

	private static $_instance = null;

	/**
	 * Holds the cover ID
	 */
	private $cover = false;

	/**
	 * Wether the cover is set per page
	 */
	private $_cover_per_page;

	private function __construct()
	{
		add_action( 'wp', [$this, 'get_cover']);
		add_action( 'rey/after_header_outside', [$this, 'cover_markup']);
		add_action( 'reycore/cover/content', [$this, 'add_cover_into_page']);
		add_filter( 'rey/add_titles', [$this, 'remove_page_title']);
		add_action( 'wp_enqueue_scripts', [$this, 'load_css'], 500 );
		add_action( 'reycore/admin_bar_menu/page_components/nodes', [$this, 'add_page_component_node'], 10, 2 );
	}

	/**
	 * Gets the cover ID
	 *
	 * @since 1.0.0
	 */
	public function get_cover_id(){
		return $this->cover;
	}

	/**
	 * Check option after checking first the ACF options
	 *
	 * @since 1.0.0
	 */
	public function get_option( $opt = 'cover__pages', $default = 'no' ){
		return get_theme_mod($opt, $default);
	}

	/**
	 * Determine if cover is enabled
	 *
	 * @since 1.0.0
	 */
	public function get_cover(){

		if( $this->cover ) {
			return;
		}

		$cover = false;

		// Front page
		if( is_front_page() && ( $cover_frontpage = $this->get_option('cover__frontpage') ) && $cover_frontpage != 'no'  ) {
			$cover = $cover_frontpage;
		}

		// Blog page
		elseif( is_home() && !is_front_page() && 'post' == get_post_type() && ( $cover_blog_home = $this->get_option('cover__blog_home') ) && $cover_blog_home != 'no'  ) {
			$cover = $cover_blog_home;
		}

		// Blog categories
		elseif( is_category() && !is_home() && !is_front_page() && 'post' == get_post_type() && ( $cover_blog_category = $this->get_option('cover__blog_cat') ) && $cover_blog_category != 'no'  ) {
			$cover = $cover_blog_category;
		}

		// Blog post
		elseif( is_single() && 'post' == get_post_type() && ( $cover_blog_post = $this->get_option('cover__blog_post') ) && $cover_blog_post != 'no'  ) {
			$cover = $cover_blog_post;
		}

		// Product Page
		elseif( class_exists('\WooCommerce') && is_product() && ( $cover_product_page = $this->get_option('cover__product_page') ) && $cover_product_page != 'no'  ) {
			$cover = $cover_product_page;
		}

		// Shop Tag
		elseif( class_exists('\WooCommerce') && is_product_tag() && $cover_shop_tag = get_theme_mod('cover__shop_tag', '') ) {
			$cover = $cover_shop_tag;
		}

		// Shop Page
		elseif( class_exists('\WooCommerce') && (is_shop() || (is_product_taxonomy() && !is_product_category())) && !is_front_page() && ( $cover_shop_page = $this->get_option('cover__shop_page') ) && $cover_shop_page != 'no'  ) {
			$cover = $cover_shop_page;
		}

		// Shop Category Page
		elseif( class_exists('\WooCommerce') && is_product_category() && ( $cover_shop_category = $this->get_option('cover__shop_cat') ) && $cover_shop_category != 'no'  ) {
			$cover = $cover_shop_category;
		}

		// Pages
		elseif( is_page() && !is_front_page() && !is_home() && ( $cover_pages = $this->get_option('cover__pages') ) && $cover_pages != 'no' ){
			$cover = $cover_pages;
		}

		$cover = $this->product_categories_custom_covers($cover);
		$cover = $this->product_page_custom_covers($cover);
		$cover = $this->inherit_category_cover($cover);
		$cover = $this->cover_per_page($cover);

		$this->cover = apply_filters('reycore/cover/get_cover', $cover, $this);

		$this->disable_woocommerce_page_titles();
	}

	/**
	 * Load Global Sections into the page
	 *
	 * @since 1.0.0
	 */
	public function add_cover_into_page(){
		$GLOBALS['rey_is_cover'] = true;
		if( $this->has_cover() && class_exists('\ReyCore\Elementor\GlobalSections' ) ) {
			echo \ReyCore\Elementor\GlobalSections::do_section(
				$this->cover,
				( ! empty($_REQUEST['reynotemplate']) && absint($_REQUEST['reynotemplate']) == 1 )
			);
		}
		unset($GLOBALS['rey_is_cover']);
	}

	/**
	 * Disable page titles
	 *
	 * @since 1.0.0
	 */
	public function disable_woocommerce_page_titles()
	{

		if( ! class_exists('\WooCommerce') ){
			return;
		}

		if(
			apply_filters( 'reycore/cover/disable_titles/archive',
			(
				$this->has_cover() &&
				( is_product_category() || is_product_tag() || is_product_taxonomy() || is_shop() ))
			)
		) {
			// remove title
			add_filter( 'woocommerce_show_page_title', '__return_false', 10 );
			// remove breadcrumb too
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			// remove description
			remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
			remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		}

	}

	/**
	 * Adds the cover markup
	 *
	 * @since 1.0.0
	 */
	public function cover_markup(){

		if( ! $this->has_cover() ){
			return;
		}

		$classes[] = 'rey-pageCover--h-' . reycore__get_option('header_position', 'rel');

		$contain_opt = \ReyCore\Elementor\Helper::get_elementor_option( $this->cover, 'gs_cover_contain' );
		if( $contain_opt === 'yes' ){
			$classes[] = '--contain';
			reycore_assets()->add_styles('reycore-elementor-cover');
		}

		?>
		<section class="rey-pageCover <?php echo esc_attr( implode(' ', apply_filters('reycore/cover/css_classes', $classes ))) ?>">
			<?php
			/**
			 * Prints content inside the cover
			 * @hook reycore/cover/content
			 */
			do_action('reycore/cover/content'); ?>
		</section>
		<!-- .rey-pageCover -->
		<?php
	}

	function has_cover(){
		return $this->cover && is_numeric($this->cover);
	}

	/**
	 * Load Sections custom CSS to prevent FOUC
	 *
	 * @since 1.0.0
	 */
	public function load_css() {

		if( $this->has_cover() ) {

			reycore_assets()->add_styles(['reycore-elementor-frontend', 'reycore-elementor-frontend-deferred']);

			$css_file = '';

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $this->cover );
			}

			if( !empty($css_file) ){
				$css_file->enqueue();
			}
		}
	}

	/**
	 * Remove titles when cover is enabled
	 *
	 * @since 1.0.0
	 */
	public function remove_page_title( $status ){
		if( apply_filters( 'reycore/cover/disable_titles/page', $this->has_cover() ) ) {
			$status = false;
		}
		return $status;
	}


	/**
	 * Override cover per product category
	 *
	 * @since 1.4.0
	 */
	public function product_categories_custom_covers( $cover ){

		if( !class_exists('\WooCommerce') ){
			return $cover;
		}

		// checks if it's a category
		if( ! is_product_category() ){
			return $cover;
		}

		$custom_cats = get_theme_mod('cover__shop_cat_custom', []);

		if( empty($custom_cats) ){
			return $cover;
		}

		foreach($custom_cats as $gs_cat){
			if( ! empty($gs_cat['categories']) && is_product_category( $gs_cat['categories'] ) ){
				return absint($gs_cat['gs']);
			}
		}

		return $cover;
	}

	/**
	 * Override cover per product page
	 *
	 * @since 1.6.6
	 */
	public function product_page_custom_covers( $cover ){

		$conditions = get_theme_mod('cover__product_page_custom', []);

		if( !class_exists('\WooCommerce') || empty($conditions) ){
			return $cover;
		}

		// checks if it's a product page
		if( ! is_product() ){
			return $cover;
		}

		foreach($conditions as $condition){

			if( empty($condition['gs']) ){
				continue;
			}

			$is_cat = !empty($condition['categories']) && has_term( $condition['categories'], 'product_cat', get_the_ID() );
			$is_tag = !empty($condition['tags']) && has_term( $condition['tags'], 'product_tag', get_the_ID() );

			if( $is_cat || $is_tag ){
				return absint($condition['gs']);
			}
		}

		return $cover;
	}

	/**
	 * Override cover at individual level
	 *
	 * @since 1.4.0
	 */
	public function cover_per_page( $cover ){

		if( ($acf_page_cover = reycore__acf_get_field('page_cover')) ){
			$this->_cover_per_page = true;
			return $acf_page_cover;
		}

		return $cover;
	}

	public function is_cover_per_page(){
		return $this->_cover_per_page;
	}

	public function inherit_category_cover($cover){

		if( ! apply_filters('reycore/cover/inherit_category_cover', get_theme_mod('cover__shop_cat_inherit', false)) ){
			return $cover;
		}

		if( ! is_tax( 'product_cat' ) ){
			return $cover;
		}

		$current_cat = get_queried_object();
		$ancestors = get_ancestors($current_cat->term_id, 'product_cat');
		$is_parent = count( $ancestors ) === 0;

		if( $is_parent ){
			return $cover;
		}

		foreach ($ancestors as $parent_id) {
			// check if parent has a cover (per page)
			if( $parent_cover = reycore__acf_get_field('page_cover', get_term_by('term_taxonomy_id', $parent_id) ) ){
				return absint($parent_cover);
			}
			// check in the cover per category
			else if( $custom_cats = get_theme_mod('cover__shop_cat_custom', []) ){
				foreach($custom_cats as $gs_cat){
					if( !empty($gs_cat['categories']) && in_array( $parent_id, $gs_cat['categories'] ) ){
						$cover = absint($gs_cat['gs']);
						break;
					}
				}
			}
		}

		return $cover;
	}

	public function add_page_component_node($admin_bar, $slug){

		if( $cover = $this->get_cover_id() ){
			$admin_bar->add_node(
				[
					'id'     => 'edit-rey-cover',
					'title'  => esc_html__('Edit Page Cover', 'rey-core'),
					'href'   => admin_url( sprintf('post.php?post=%d&action=elementor', $cover) ),
					'parent' => $slug,
				]
			);
		}

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return TagCover
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
