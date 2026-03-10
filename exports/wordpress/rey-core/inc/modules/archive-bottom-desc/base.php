<?php
namespace ReyCore\Modules\ArchiveBottomDesc;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private static $tax_bottom_description = '';
	private static $tax_bottom_gs = '';
	public $set = false;

	public function __construct()
	{
		add_action('init', [$this, 'add_controls']);
		add_action('wp', [$this, 'wp']);
		add_action('reycore/dynamic_tags', [$this, 'dynamic_tag']);
	}

	public function wp(){

		if( ! is_tax() ){
			return;
		}

		$this->set_acf_fields();

		if( self::$tax_bottom_description || self::$tax_bottom_gs ){

			// DOCS: https://support.reytheme.com/kb/archives-bottom-description/#overriding-the-bottom-description-position
			add_action(apply_filters('reycore/woocommerce/taxonomies/position', 'woocommerce_after_main_content'), [$this, 'output_bottom_content'], 50);

			// append class
			add_filter('rey/site_content_classes', [$this, 'content_classes']);

			// force update on filtering
			add_filter('reycore/ajaxfilters/js_params', [$this, 'extra_containers']);
		}
	}

	public function set_acf_fields( $queried_id = '' ){

		if( ! class_exists('\ACF') ) {
			return;
		}

		$queried_object = get_queried_object();

		if( $queried_id ){
			$queried_object = get_term($queried_id);
		}

		if ( $content = get_field('tax_bottom_description', $queried_object) ){
			self::$tax_bottom_description = $content;
		}

		if ( $gs = get_field('tax_bottom_gs', $queried_object) ){
			self::$tax_bottom_gs = $gs;
		}

		$this->set = true;
	}

	public static function get_bottom_description(){
		return self::$tax_bottom_description;
	}

	public function output_bottom_content( $queried_id = '' ){

		if( ! $this->set ){
			$this->set_acf_fields( $queried_id );
		}

		$content = '';

		$gs_content = class_exists('\ReyCore\Elementor\GlobalSections') && $gs = self::$tax_bottom_gs;

		if( apply_filters('reycore/woocommerce/taxonomies/show_gs', $gs_content ) ){
			reycore_assets()->defer_page_styles('elementor-post-' . $gs, true);
			$content .= sprintf('<div class="rey-taxBottom --gs">%s</div>', \ReyCore\Elementor\GlobalSections::do_section($gs) );
		}

		$desc_content = self::$tax_bottom_description;

		if( apply_filters('reycore/woocommerce/taxonomies/show_desc', $desc_content ) ){
			$desc_content = $this->process_more_tag( $desc_content );
			$desc_content = reycore__parse_text_editor($desc_content);
			$content .= sprintf('<div class="rey-taxBottom --desc">%s</div>', $desc_content);
		}

		if( $content ){
			printf( '<div class="rey-taxBottom-wrapper rey-noSp">%s</div>', $content );
		}

	}

	public function content_classes( $classes ){

		$classes['bottom_desc'] = '--bottom-desc';

		return $classes;
	}

	public function extra_containers( $params ){

		$params['extra_containers'][] = '.rey-taxBottom-wrapper';

		return $params;
	}

	public function add_controls(){

		$this->acf_controls();

		add_action( 'elementor/element/text-editor/section_editor/before_section_end', [$this, 'elementor_text_widget_controls'], 10);
		add_filter( 'reycore/elementor/dynamic_text/render_text', [$this, 'elementor_render_dynamic_text'], 10, 2);

	}

	public function elementor_text_widget_controls( $element )
	{

		$dynamic_source = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'rey_dynamic_source' );

		if( is_wp_error($dynamic_source) ){
			return;
		}

		$dynamic_source['options']['bottom_desc'] = esc_html__('Bottom description', 'rey-core');
		$element->update_control( 'rey_dynamic_source', $dynamic_source );

	}

	public function elementor_render_dynamic_text($text, $settings){

		if( isset($settings['rey_dynamic_source']) && 'bottom_desc' === $settings['rey_dynamic_source'] ){
			// desc content is already used in GS, so better disable the DESC and keep GS
			add_filter('reycore/woocommerce/taxonomies/show_desc', '__return_false');
			return self::$tax_bottom_description;
		}

		return $text;
	}

	public function acf_controls(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => 'group_604909c1983b4',
			'title' => 'Extra taxonomy settings',
			'fields' => array(
				array(
					'key' => 'tax_bottom_description',
					'label' => 'Bottom Description',
					'name' => 'tax_bottom_description',
					'type' => 'wysiwyg',
					'instructions' => esc_html_x('This description will be displayed in this public taxonomy\'s footer ( after the items grid ).', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 1,
				),
				array (
					'key' => 'tax_bottom_gs',
					'label' => 'Bottom Global Section',
					'instructions' => esc_html_x('This generic global section will be displayed in this public taxonomy\'s footer ( after the items grid ).', 'Backend setting description', 'rey-core'),
					'name' => 'tax_bottom_gs',
					'type' => 'global_sections',
					'menu_order' => 0,
					'instructions' => '',
					'required' => 0,
					'gs_type' => 'generic',
					'rey_export' => 'post_id',
				)
			),
			'location' => array(
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'all',
					),
					array(
						'param' => 'wc_prod_attr_visibility',
						'operator' => '==',
						'value' => 'attribute_public',
					),
				),
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'product_cat', // CATEGORY
					),
				),
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'product_tag', // TAG
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}

	public function dynamic_tag( $tags ){
		$class_name = '\ReyCore\Modules\ArchiveBottomDesc\DynamicTag';
		if( class_exists($class_name) ){
			$tags->get_manager()->register( new $class_name );
		}
	}


	/**
	 * Process More Tag
	 *
	 * Respect the native WP (<!--more-->) tag
	 *
	 * @access private
	 * @since 2.0.4
	 *
	 * @param $content
	 *
	 * @return string
	 */
	private function process_more_tag( $content ) {

		$content = str_replace( '<p><!--more--></p>', '<!--more-->', $content );
		$content = str_replace( '&lt;p&gt;&lt;!--more--&gt;&lt;/p&gt;', '<!--more-->', $content );
		$content = str_replace( '&lt;!--more--&gt;', '<!--more-->', $content );
		$parts = get_extended( $content );

		if ( empty( $parts['extended'] ) ) {
			return $content;
		}

		$output = '<div class="u-toggle-block --collapsed">';
			$output .= force_balance_tags( $parts['main'] );
			$output .= '<div class="u-toggle-content">';
				$output .= $parts['extended'];
			$output .= '</div>';
			$output .= '<p><button aria-label="Toggle" class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core') .'"></button></p>';
		$output .= '</div>';

		reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
		reycore_assets()->add_scripts('reycore-text-toggle');

		return $output;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Archives Bottom Description', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds new content controls inside product archives or public attributes, to show a description or global section, at the bottom after the products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product catalog'],
			'help'        => reycore__support_url('kb/archives-bottom-description/'),
			'video' => true,
		];
	}

	public function module_in_use(){

		$post_ids = [];

		$product_taxonomies = ['product_cat', 'product_tag'];

		foreach (get_object_taxonomies( 'product' ) as $key => $tax) {
			if( strpos($tax, 'pa_') === 0 ){
				$product_taxonomies[] = $tax;
			}
		}

		foreach ($product_taxonomies as $taxonomy) {

			$post_ids = array_merge($post_ids, get_terms([
				'taxonomy' => $taxonomy,
				'fields' => 'tt_ids',
				'hide_empty' => false,
				'meta_query' => [
					'relation' => 'OR',
					[
						'key' => 'tax_bottom_description',
						'value'   => '',
						'compare' => '!='
					],
					[
						'key' => 'tax_bottom_gs',
						'value'   => '',
						'compare' => '!='
					],
				]
			]) );

		}

		return !empty( $post_ids );
	}
}
