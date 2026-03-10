<?php
namespace ReyCore\WooCommerce\Tags;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class Tabs {

	public function __construct(){
		add_action( 'init', [$this, 'initialize']);
	}

	function initialize(){

		add_filter( 'wp', [$this, 'late_init']);
		add_filter( 'woocommerce_product_additional_information_heading', [$this,'rename_additional_info_panel'], 10);
		add_filter( 'woocommerce_product_description_heading', [$this,'rename_description_title'], 10);
		add_filter( 'woocommerce_product_tabs', [$this, 'manage_tabs'], 20);
		add_filter( 'wc_product_enable_dimensions_display', [$this, 'disable_specifications_dimensions'], 10);
		add_filter( 'rey/woocommerce/product_panels_classes', [$this, 'add_blocks_class']);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'move_reviews_tab_outside'], 10 );
		add_action( 'reycore/woocommerce/product/tabs/before', [$this, 'remove_tabs_titles'] );

		// add attr description
		if( get_theme_mod('woocommerce_product_page_attr_desc', false) ){
			add_filter('woocommerce_attribute', [$this, 'add_attribute_descriptions'], 20, 2);
		}
	}

	/**
	 * Add Information Panel
	 *
	 * @since 1.0.0
	 **/
	function information_panel_content()
	{
		echo reycore__parse_text_editor( reycore__get_option( 'product_info_content' ) );

		if( apply_filters('reycore/woocommerce/tabs_blocks/show_info_help', true) ){

			echo reycore__popover([
				'content' => sprintf(_x('<p>If you want to edit this text, access <strong><a href="%s" target="_blank">Customizer > WooCommerce > Product page - Tabs/Blocks</a></strong> and you should be able to find <strong>Information</strong> editor in there.</p>', 'Various admin texts', 'rey-core'),
					add_query_arg([
						'autofocus[section]' => 'woo-product-page-tabs'
						], admin_url( 'customize.php' )
					)
				),
				'admin' => true,
				'class' => '--gs-popover'
			]);

		}

	}

	/**
	 * Rename Description title
	 *
	 * @since 1.6.10
	 **/
	function rename_description_title($heading)
	{
		$title = get_theme_mod('product_content_blocks_title', '');

		// disable title
		if( $title == '0' ){
			return false;
		}

		// check if custom title
		if( $title ){
			return $title;
		}

		return $heading;
	}


	/**
	 * Rename Additional Information Panel
	 *
	 * @since 1.0.0
	 **/
	function rename_additional_info_panel($heading)
	{

		$title = get_theme_mod('single_specifications_title', '');

		// disable title
		if( $title == '0' ){
			return false;
		}

		// check if custom title
		if( $title ){
			return $title;
		}

		return esc_html__( 'Specifications', 'rey-core' );
	}

	function manage_tabs( $tabs ){

		$type = get_theme_mod('product_content_layout', 'blocks');

		if( $type === 'tabs' ){
			reycore_assets()->add_scripts(['reycore-wc-product-page-mobile-tabs']);
		}

		/**
		 * Adds Information Block/Tab
		 */

		$ip = reycore__get_option('product_info', '');

		if( ($ip = reycore__get_option('product_info', '')) && ($ip === true || $ip === '1' || $ip === 'custom') ) {

			$info_tab_title = __( 'Information', 'rey-core' );

			if( $custom_info_tab_title = get_theme_mod('single__product_info_title', '') ){
				$info_tab_title = $custom_info_tab_title;
			}

			$tabs['information'] = [
				'title'    => $info_tab_title,
				'priority' => absint(get_theme_mod('single_custom_info_priority', 15)),
				'callback' => [$this, 'information_panel_content'],
			];

		}

		// change priorities
		foreach ([
			'description' => get_theme_mod('single_description_priority', 10),
			'additional_information' => get_theme_mod('single_specs_priority', 20),
			'reviews' => get_theme_mod('single_reviews_priority', 30),
		] as $key => $value) {
			if( isset($tabs[$key]) && isset($tabs[$key]['priority']) ){
				$tabs[$key]['priority'] = absint($value);
			}
		}

		// Description title
		if( $desc_title = get_theme_mod('product_content_blocks_title', '') ){
			$tabs['description']['title'] = $desc_title;
		}

		// Specs title
		if( $specs_title = get_theme_mod('single_specifications_title', '') ){
			$tabs['additional_information']['title'] = $specs_title;
		}

		// disable specs tab
		if( ! get_theme_mod('single_specifications_block', true) || get_field('single_specifications_block') === false ){
			unset( $tabs['additional_information'] );
		}

		// disable reviews tab, to print outside
		if( get_theme_mod('single_tabs__reviews_outside', false) && $type === 'tabs' ){
			unset( $tabs['reviews'] );
		}

		// disable description
		if( ! get_theme_mod('product_tab_description', true) ){
			unset( $tabs['description'] );
		}

		return $tabs;
	}


	function move_reviews_tab_outside() {

		$maybe[] = get_theme_mod('single_tabs__reviews_outside', false) && get_theme_mod('product_content_layout', 'blocks') === 'tabs' && wc_reviews_enabled();

		if( $product = wc_get_product() ){
			$maybe[] = $product->get_reviews_allowed();
		}

		if( in_array(false, $maybe, true) ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/single-block-reviews');
	}

	function wrap_specifications_block(){
		echo '<div class="rey-summarySpecs">';
		woocommerce_product_additional_information_tab();
		echo '</div>';
	}


	/**
	 * Move Specifications / Additional Information block/tab into Summary
	 *
	 * @since 1.6.7
	 */
	function late_init(){

		if( is_product() ){
			add_filter( 'the_content', [$this, 'add_description_toggle']);
		}

		$this->move_specs_block();
	}

	function move_specs_block(){

		if( ! get_theme_mod('single_specifications_block', true) ){
			return;
		}

		if( ! ($pos = get_theme_mod('single_specifications_position', '')) ){
			return;
		}

		// move specifications / additional in summary
		add_action( 'woocommerce_single_product_summary', [$this, 'wrap_specifications_block'], $pos );

		add_filter( 'woocommerce_product_tabs', function( $tabs ) {
			unset( $tabs['additional_information'] );
			return $tabs;
		}, 99 );

	}


	function disable_specifications_dimensions(){
		return get_theme_mod('single_specifications_block_dimensions', true);
	}

	function remove_tabs_titles( $layout ){

		if( 'tabs' !== $layout ){
			return;
		}

		if( get_theme_mod('product_content_layout', 'blocks') !== 'tabs' ){
			return;
		}

		if( ! get_theme_mod('product_content_tabs_disable_titles', true) ){
			return;
		}

		add_filter('woocommerce_product_description_heading', '__return_false');
		add_filter('woocommerce_product_additional_information_heading', '__return_false');
		add_filter('woocommerce_post_class', function($classes){
			$classes['remove-titles'] = '--tabs-noTitles';
			return $classes;
		});
	}

	/**
	 * Customize product page's blocks
	 *
	 * @since 1.0.12
	 **/
	function add_blocks_class( $classes )
	{
		if( get_theme_mod('product_content_layout', 'blocks') === 'blocks' ){
			$classes[] = get_theme_mod('product_content_blocks_desc_stretch', false) ? '--stretch-desc' : '';
		}

		return array_filter($classes);
	}

	function add_description_toggle($content){

		if( get_theme_mod('product_content_blocks_desc_toggle', false) ){

			reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
			reycore_assets()->add_scripts('reycore-text-toggle');
			return sprintf(
				'<div class="rey-prodDescToggle u-toggle-text-next-btn %s">%s</div><button class="btn btn-minimal" aria-label="Toggle"><span data-read-more="%s" data-read-less="%s"></span></button>',
				apply_filters('reycore/productdesc/mobile_only', false) ? '--mobile' : '',
				$content,
				esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core'),
				esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core')
			);
		}

		return $content;
	}



	function add_attribute_descriptions($value, $attribute){

		if( $terms_ids = $attribute->get_options() ){

			$taxonomy = str_replace('pa_', '', $attribute->get_taxonomy());

			foreach ($terms_ids as $term_id) {

				if( $desc = term_description( $term_id, $taxonomy) ){
					$desc_no_tags = strip_tags($desc);
					$new_content = '<br><small class="woocommerce-product-attributes-item__desc">' . $desc_no_tags . '</small>';
					$value = str_replace('</p>', $new_content . '</p>', $value);
				}
			}
		}

		return $value;
	}


	public static function render_short_description(){

		add_filter('theme_mod_product_short_desc_enabled', '__return_true');
		add_filter('theme_mod_product_short_desc_toggle_v2', '__return_false');

		woocommerce_template_single_excerpt();

		remove_filter('theme_mod_product_short_desc_enabled', '__return_true');
		remove_filter('theme_mod_product_short_desc_toggle_v2', '__return_false');

	}


}
