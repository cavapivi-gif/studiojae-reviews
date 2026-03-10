<?php
namespace ReyCore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QueryControlAjaxList
{

	const AJAX_PREFIX = 'rey_ajax_list_control__';

	public function __construct(){
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
	}

	/**
	 * Register Elementor Ajax Actions
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function register_ajax_actions( $ajax_manager ) {

		$registered_actions = [

			'get_social_share_icons' => function (){
				return reycore__social_icons_list_select2('share');
			},

			'get_social_icons' => function (){
				return reycore__social_icons_list_select2();
			},

			'global_sections_list' => function ($data = []){
				return \ReyCore\Elementor\GlobalSections::get_global_sections_options($data);
			},

			'global_section_types' => function (){
				return \ReyCore\Elementor\GlobalSections::get_global_section_types();
			},

			'get_nav_menus_options' => function ($data = []){
				return \ReyCore\Menus::get_nav_menus_options( $data );
			},

			/**
			 * Get CTP list
			 *
			 * @since 2.4.5
			 **/
			'get_post_types_list' => function () {
				return ['' => esc_html__('None', 'rey-core')] + reycore__get_post_types_list();
			},

			/**
			 * Get CTP list except products
			 *
			 * @since 2.4.5
			 **/
			'post_types_list_except_product' => function () {
				return reycore__get_post_types_list([
					'exclude' => [
						'product'
					]
				]);
			},

			'get_cf7_forms' => function(){
				return apply_filters('reycore/cf7/forms', []);
			},

			/**
			 * Get forms.
			 *
			 * Retrieve an array of forms from the MailChimp for WordPress plugin.
			 *
			 * @since 1.0.0
			 * @access public
			 * @static
			 *
			 * @return array An array containing button sizes.
			 */
			'get_mc4wp_forms' => function(){

				if( ! function_exists('mc4wp_get_forms') ){
					return [];
				}

				$forms = mc4wp_get_forms();
				$data = ['' => '- Select -'];

				foreach ($forms as $form) {
					$data[$form->ID] = $form->name;
				}

				return $data;
			},

			/**
			 * Get forms.
			 *
			 * Retrieve an array of forms from the Brevo (Sendinblue) for WordPress plugin.
			 *
			 * @since 1.0.0
			 * @access public
			 * @static
			 *
			 * @return array An array containing button sizes.
			 */
			'get_sendinblue_forms' => function(){

				if( ! class_exists('SIB_Forms') ){
					return [];
				}

				$forms = \SIB_Forms::getForms();
				$data = ['' => '- Select -'];

				foreach ($forms as $form) {
					$data[$form['id']] = $form['title'];
				}

				return $data;
			},

			'get_mailerlite_forms' => function(){

				if( ! class_exists( '\MailerLiteForms\Core' ) ){
					return [];
				}

				global $wpdb;

				$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->base_prefix}mailerlite_forms" );

				$data = ['' => '- Select -'];

				foreach ($forms as $form) {
					if( isset($form->id) ){
						$data[ $form->id ] = $form->name;
					}
				}

				return $data;
			},

			'get_rev_sliders' => function() {

				$list = [];

				try {

					if( ! class_exists('\RevSliderSlider') ){
						return $list;
					}

					$_slider = new \RevSliderSlider();

					if( method_exists('\RevSliderSlider', 'get_sliders') ){

						$sliders	= $_slider->get_sliders();

						if(!empty($sliders)){
							foreach($sliders as $slider){
								$id			= $slider->get_alias();
								$list[$id] = $slider->get_title();
							}
						}
					}
				}catch( \Exception $e){}

				return $list;
			},

			'insta_v2_feeds_get_list' => function(){

				$feeds = get_posts([
					'post_type' => 'wpz-insta_feed',
					'post_status' => 'publish',
				]);

				if( $feeds && is_array($feeds) ){

					$options = [];

					foreach ($feeds as $feed ) {
						$options[ $feed->ID ] = sprintf('%s (%d)', $feed->post_title, $feed->ID );
					}

					return $options;
				}

				return [];

			},

			'get_attributes_list' => function (){

				$options[''] = esc_html__('- Select -', 'rey-core');

				if( function_exists('reycore_wc__get_attributes_list') ){
					$options = array_merge($options, reycore_wc__get_attributes_list(true));
				}

				return $options;
			},

			'get_button_sidebars' => function (){

				global $wp_registered_sidebars;

				$options = [];

				if ( ! $wp_registered_sidebars ) {
					$options[''] = __( 'No sidebars were found', 'rey-core' );
				} else {
					$options[''] = __( 'Choose Sidebar', 'rey-core' );

					foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
						$options[ $sidebar_id ] = $sidebar['name'];
					}
				}

				return $options;
			},

			'get_taxonomies_list' => function (){

				$excludes = [
					'nav_menu',
					'link_category',
					'post_format',
					'wp_theme',
					'wp_template_part_area',
					'elementor_library_type',
					'elementor_library_category',
					'product_type',
					'product_visibility',
					'product_cat',
					'product_shipping_class',
				];

				$items = [];

				foreach ( get_taxonomies() as $taxonomy ) {

					if( in_array($taxonomy, $excludes, true) ){
						continue;
					}

					$id           = $taxonomy;
					$taxonomy     = get_taxonomy( $taxonomy );
					$items[ $id ] = sprintf('%s (%s)', $taxonomy->labels->name, $id);
				}

				return $items;

			},

			'get_tabs' => function(){

				$blocks = [
					'description' => esc_html__('Description', 'rey-core'),
					'additional_information' => esc_html__('Specifications (Additional Information)', 'rey-core'),
					'information' => esc_html__('Custom Information', 'rey-core'),
					'reviews' => esc_html__('Reviews', 'rey-core'),
				];

				if( ($custom_tabs = get_theme_mod('single__custom_tabs', '')) && is_array($custom_tabs) && !empty($custom_tabs) ){
					foreach ($custom_tabs as $key => $c_tab) {
						$blocks['custom_tab_' . $key] = sprintf('%s (%s)', $c_tab['text'] , esc_html__('Custom', 'rey-core') );
					}
				}

				return $blocks;
			},

			'get_gallery_types' => function(){

				$gallery_types = [''  => esc_html__( '- Inherit -', 'rey-core' )];

				if( $gallery = reycore_wc__get_pdp_component('gallery') ){
					$gallery_types = $gallery->get_gallery_types();
				}

				return $gallery_types;

			},

			'get_loop_skins' => function(){

				$product_skins = [
					'' => esc_html__( '- Inherit -', 'rey-core' )
				];

				if( $woo = \ReyCore\Plugin::instance()->woocommerce_loop ){
					$product_skins = array_merge($product_skins, $woo->get_skins_list());
				}

				return apply_filters('reycore/woocommerce/loop/control_skins', $product_skins);
			},

		];

		foreach ($registered_actions as $action => $func) {
			$ajax_manager->register_ajax_action( self::AJAX_PREFIX . $action, $func);
		}

		do_action( 'reycore/elementor/ajax_list/register_actions', $ajax_manager, $this );

	}

}
