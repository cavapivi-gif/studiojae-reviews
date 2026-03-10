<?php
namespace ReyCore\Modules\ProductPageAfterContent;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-pdp-after-content';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
	}

	public function init() {

		new AcfFields();
		new Customizer();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'wp', [$this, 'global_section_after_content_choose'], 10 );
		add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_product_summary'], 6 );
		add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_category'], 10, 2);
		add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_page'], 20, 2);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	/**
	 * Adds global section *after product summary*
	 *
	 * @since 1.0.0
	 */
	function global_section_after_product_summary(){

		if( ! class_exists('\ReyCore\Elementor\GlobalSections' ) ){
			return;
		}

		$global_section = '';

		if( ($gs = reycore__get_option('product_content_after_summary', 'none')) && $gs !== 'none' ) {
			$global_section = $gs;
		}

		$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'summary' ) );

		if( $global_section ){
			reycore_assets()->defer_page_styles('elementor-post-' . $global_section, true);
			echo \ReyCore\Elementor\GlobalSections::render( [
				'post_id' => $global_section,
				'css_classes' => ['--do-stretch']
			] );
		}
	}


	function global_section_after_content_choose(){

		if( apply_filters('reycore/woocommerce/global_section_after_content/after_related', false) ){
			add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_content'], 20 );
			return;
		}

		$can_display_before_reviews[] = get_theme_mod('product_content_after_content__before_reviews', false) && wc_reviews_enabled();

		if( $product = wc_get_product() ){
			$can_display_before_reviews[] = $product->get_reviews_allowed();
		}

		if( ! in_array(false, $can_display_before_reviews, true) ){
			add_action( 'reycore/woocommerce/before_block_reviews', [$this, 'global_section_after_content'], 10 );
		}
		else {
			add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_content'], 10 );
		}
	}

	/**
	 * Adds global section *after content*
	 *
	 * @since 1.0.0
	 */
	function global_section_after_content(){
		if( ! class_exists('\ReyCore\Elementor\GlobalSections' ) ){
			return;
		}

		$global_section = '';

		if( ($gs = reycore__get_option('product_content_after_content', 'none')) && $gs !== 'none' ) {
			$global_section = $gs;
		}

		$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'content' ) );

		if( $global_section ){
			reycore_assets()->defer_page_styles('elementor-post-' . $global_section, true);
			echo \ReyCore\Elementor\GlobalSections::render( [
				'post_id' => $global_section,
				'css_classes' => ['--do-stretch']
			] );
		}
	}

	/**
	 * Adds global section after content, to products that belong to certain categories.
	 *
	 * @since 1.4.0
	 */
	function global_section_after_content_per_category( $gs, $position ){

		$choices = get_theme_mod('product_content_after_content_per_category', []);

		if( empty($choices) ){
			return $gs;
		}

		$chosen_gs = [];

		foreach($choices as $gs_item):

			if( ! empty($gs_item['categories']) ){

				$cats = apply_filters('reycore/translate_ids', $gs_item['categories'], 'product_cat');

				if( has_term( $cats, 'product_cat', get_the_ID() ) && $gs_item['position'] === $position ){
					$gs = absint($gs_item['gs']);
					$chosen_gs[] = absint($gs_item['gs']);
				}

			}

			if( ! empty($gs_item['attributes']) ){

				foreach ($gs_item['attributes'] as $key => $term_id) {

					$term = get_term($term_id);

					if( is_wp_error($term) ){
						continue;
					}

					if( isset($term->taxonomy) && has_term( $term_id, $term->taxonomy, get_the_ID() ) && $gs_item['position'] === $position ){
						$gs = absint($gs_item['gs']);
						$chosen_gs[] = absint($gs_item['gs']);
					}
				}

			}

		endforeach;

		if( ! empty($chosen_gs) && ($valid_gs = array_unique($chosen_gs)) && isset($valid_gs[0]) ){
			return $valid_gs[0];
		}

		return $gs;
	}

	/**
	 * Adds global section after content, to products that belong to certain categories.
	 *
	 * @since 1.4.0
	 */
	function global_section_after_content_per_page( $gs, $position ){

		if( $position === 'content' && ($acf_content_gs = reycore__acf_get_field('product_content_after_content')) ){

			if( $acf_content_gs === 'none' ){
				return false;
			}

			return absint($acf_content_gs);
		}

		elseif( $position === 'summary' && ($acf_summary_gs = reycore__acf_get_field('product_content_after_summary')) ){

			if( $acf_summary_gs === 'none' ){
				return false;
			}

			return absint($acf_summary_gs);
		}

		return $gs;
	}


	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Page - Content After Summary', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to extend product pages with custom Elementor built global sections.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/editing-product-pages-archives-with-elementor/#product-pages-insert-after-summary-global-sections'),
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
					'key' => 'product_content_after_summary',
					'value'   => '',
					'compare' => '!='
				],
				[
					'key' => 'product_content_after_content',
					'value'   => '',
					'compare' => '!='
				]
			]
		]);

		return ! empty($post_ids) || ! empty(get_theme_mod('product_content_after_content_per_category', []));

	}
}
