<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchBase
{

	public $is_default = false;

	public $has_preview = false;

	protected $custom_image = false;

	public $list_args = [];
	public $attribute_settings = [];

	private $__term_swatch_data = null;

	function __construct(){

	}

	function get_id(){
		return '';
	}

	function get_name(){
		return '';
	}

	function get_item_generic_class(){
		return 'rey-swatchList-item--regular';
	}

	function get_swatch_style( $term ){}

	function get_attribute_data($term){
		return [];
	}

	function add_terms_settings(){}

	function get_attribute_settings_support(){
		return [];
	}

	/**
	 * Estabilish the location for the swatches fields
	 *
	 * @return array
	 */
	function get_term_settings_location(){

		$location = [];

		if( ! (isset($_REQUEST['taxonomy']) && $tax = reycore__clean($_REQUEST['taxonomy']) ) ) {
			return $location;
		}

		if( 0 !== strpos( $tax, 'pa_' ) ){
			return $location;
		}

		foreach ( wc_get_attribute_taxonomies() as $attribute ) {

			if( $tax !== wc_attribute_taxonomy_name( $attribute->attribute_name ) ){
				continue;
			}

			if( $this->get_id() !== $attribute->attribute_type ){
				continue;
			}

			$location[] = [
				[
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => $tax,
				]
			];

		}

		return $location;
	}

	public function get_term_swatch_data($tax, $term_id = 0 ){

		if( ! taxonomy_exists($tax) ){
			return [];
		}

		if( ! isset($this->__term_swatch_data[$tax]) ){

			$data = false;

			if( Base::$settings['attr_data_cache'] ){
				$data = get_transient( Base::get_attribute_transient_name( $tax ) );
			}

			if( false === $data ){

				$data = [];

				$terms = get_terms([
					'taxonomy'   => $tax,
					'hide_empty' => false,
					// can't because i need the objects
					// 'fields'     => 'tt_ids',
				]);

				foreach ($terms as $term) {

					if( is_wp_error($term) ){
						continue;
					}

					if( ! isset($term->term_id) ){
						continue;
					}

					if( isset($data[ $term->term_id ]) ){
						continue;
					}

					$data[ $term->term_id ] = $this->get_attribute_data($term);
				}

				if( Base::$settings['attr_data_cache'] ){
					set_transient( Base::get_attribute_transient_name( $tax ), $data, WEEK_IN_SECONDS );
				}
			}

			$this->__term_swatch_data[$tax] = $data;
		}
		else {
			$data = $this->__term_swatch_data[$tax];
		}

		if( $term_id && isset($data[ $term_id ]) ){
			return $data[ $term_id ];
		}

		return $data;
	}

	function get_term_swatch_data_by_term($term){

		if( ! isset($term->taxonomy) ){
			$term = Base::get_term($term);
			if( ! isset($term->taxonomy) ){
				return;
			}
		}

		return $this->get_term_swatch_data($term->taxonomy, $term->term_id);
	}

	function get_content__text($term){}

	function get_content__style($args){}

	function get_tooltip_content__before($term){}

	function get_tooltip_content__after($term){}

	function get_clean_swatch_id(){
		return str_replace( Base::TYPES_PREFIX, '', $this->get_id() );
	}

	function render_list( $args = [] ){

		if( ! ($this->list_args = $args) ){
			return;
		}

		// rename
		$this->list_args['taxonomy'] = $this->list_args['attribute'];

		$this->attribute_settings = Base::get_attributes_swatch_settings( $this->list_args['taxonomy'] );

		$list_style = [];

		// Only in Product page
		if( ! \ReyCore\Modules\VariationSwatches\Frontend::is_catalog_mode() ){

			$item_styles = apply_filters('reycore/variation_swatches/item_styles', [
				'swatch_width' => '--item-width:%spx',
				'swatch_height' => '--item-height:%spx',
				'swatch_radius' => '--item-radius:%spx',
				'swatch_padding' => '--item-padding:%spx',
				'swatch_spacing' => '--item-spacing:%spx',
				'swatch_font_size' => '--item-font-size:%spx',
			]);

			foreach ( $item_styles as $key => $style_css) {

				if( ! in_array($key, $this->get_attribute_settings_support(), true) ){
					continue;
				}

				if( ! (isset($this->attribute_settings[$key]) && $style_data = $this->attribute_settings[$key]) ){
					continue;
				}

				$list_style[] = sprintf($style_css, $style_data);
			}
		}

		$attr_tax = sanitize_title($this->list_args['taxonomy']);

		$html_list_attributes['aria-label'] = esc_attr($this->list_args['attribute_data']['name']);
		$html_list_attributes['data-attribute_name'] = 'attribute_' . $attr_tax;
		$html_list_attributes['style'] = esc_attr(implode(';', $list_style));
		$html_list_attributes['class'] = $this->get_list_html_classes();
		// $html_list_attributes['data-placeholdered'] = '';

		if( ! empty($this->attribute_settings['label_display']) ){
			$html_list_attributes['data-tr-class'] = '--label-' . esc_attr($this->attribute_settings['label_display']);
			$html_list_attributes['data-tr-attribute'] = $attr_tax;
		}

		$items_output = $this->render_item();

		if( ! $items_output ){
			return '';
		}

		$items_output .= $this->limit_link_output();

		return sprintf('<div %s>%s</div>',
			reycore__implode_html_attributes($html_list_attributes),
			$items_output
		);

	}

	function get_list_html_classes(){

		$list_classes = [
			'rey-swatchList'
		];

		$type = $this->get_clean_swatch_id();

		$list_classes['type'] = '--type-' . esc_attr( $type );
		$list_classes['tooltip_theme'] = sprintf('--%s-tooltips', Base::$settings['tooltip_theme']);
		$list_classes['disabled_behaviour'] = '--disabled-' . esc_attr( get_theme_mod('pdp_swatches__disabled_behaviour', 'dim') );
		$list_classes['deselection'] = '--deselection-' . esc_attr( get_theme_mod('pdp_swatches__deselection', 'clear') );

		if( Base::$settings['image_uses_tag'] && 'image' === $type ){
			$list_classes[] = '--image-tag';
		}

		return esc_attr(implode(' ', $list_classes));
	}

	function limit_link_output(){

		if( ! (isset($this->list_args['limit']) && $limit = $this->list_args['limit']) ){
			return;
		}

		return sprintf('<a href="%1$s" class="rey-swatchList-more" title="%3$s">%2$d+</a>',
			get_permalink( $this->list_args['product']->get_id() ),
			$this->list_args['limit_diff'],
			sprintf( esc_html__('Click to see %s more', 'rey-core'), $this->list_args['limit_diff'] )
		);

	}

	function default_item_classes(){
		return [
			'rey-swatchList-item',
			'--type-' . esc_attr( $this->get_clean_swatch_id() ),
			$this->get_item_generic_class()
		];
	}

	function default_item_html_attributes($term){

		// title
		$title_attr_name = 'title';
		if( isset($this->attribute_settings['swatch_tooltip']) && 'no' !== $this->attribute_settings['swatch_tooltip'] ){
			$title_attr_name = 'data-title';
		}
		$html_attributes[$title_attr_name] = esc_attr($term->name);

		$attr_tax = sanitize_title($this->list_args['taxonomy']);
		$html_attributes['data-term-id'] = absint($term->term_id);
		$html_attributes['data-term-slug'] = esc_attr($term->slug);
		$html_attributes['data-attribute-name'] = $attr_tax;
		$html_attributes['aria-label'] = esc_attr($term->name);
		$html_attributes['role'] = 'radio';
		$html_attributes['aria-checked'] = $this->list_args['selected'] === $term->slug ? 'true' : 'false';

		return $html_attributes;
	}

	function render_item(){

		$output = '';

		$options   = $this->list_args['options'];
		$product   = $this->list_args['product'];
		$attribute = $this->list_args['attribute'];

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		if ( empty( $options ) ) {
			return $output;
		}

		if ( ! $product ) {
			return $output;
		}

		if ( ! taxonomy_exists( $attribute ) ) {
			if( ! $this->list_args['custom_attribute'] ){
				return $output;
			}
		}

		if( $this->list_args['custom_attribute'] ){
			foreach ($options as $key => $option) {
				$terms[] = (object) [
					'name'        => $option,
					'slug'        => $option,
					'term_id'     => '',
					'description' => '',
				];
			}
		}

		else {
			// Get terms if this is a taxonomy - ordered. We need the names too.
			$terms = wc_get_product_terms( $product->get_id(), $attribute, [
				'fields' => 'all',
			] );
		}

		foreach ( $terms as $i => $term ) {

			if ( ! in_array( $term->slug, $options, true ) ) {
				continue;
			}

			$style = $this->get_content__style([
				'term' => $term,
				'custom_image' => $this->get_item_custom_image($term),
				'attribute' => $attribute,
			]);

			$classes = $this->default_item_classes();

			$maybe_select = true;

			if( ! Base::$settings['autoselect_catalog_variation'] && ! \ReyCore\WooCommerce\Pdp::is_single_true_product() ){
				$maybe_select = false;
			}

			$classes[] = $this->list_args['selected'] === $term->slug && $maybe_select ? '--selected' : '';

			if( Base::$settings['fallback_image_text'] && ! $style ){
				$classes[] = '--no-style';
			}

			$html_attributes = $this->default_item_html_attributes($term);
			$html_attributes['class'] = esc_attr(implode(' ', $classes));
			$html_attributes['data-description'] = esc_html($term->description);

			$item_output = sprintf('<div %s>%s</div>', reycore__implode_html_attributes($html_attributes), $this->get_item_content($term, $style) );

			$output .= apply_filters('reycore/variation_swatches/render_item', $item_output, $term, $this, [
				'is_last' => count($options) === ($i + 1),
				'html_attributes' => $html_attributes
			] );
		}


		return $output;
	}

	function get_item_custom_image($term){

		$custom_image = false;

		if( isset($this->attribute_settings['use_variation_img']) && 'no' !== $this->attribute_settings['use_variation_img'] ){
			$custom_image = $this->get_variation_image_data($term);
		}

		return $custom_image;
	}

	function get_item_content($term, $style){

		if( empty($term) ){
			return;
		}

		if( ! isset($term->taxonomy) && isset($term->name) ){
			$term_name = ! $style ? $term->name : '';
			return sprintf('<span class="rey-swatchList-itemContent" style="%2$s">%1$s</span>', $term_name, $style );
		}

		$text = $this->get_content__text( $term );

		if( Base::$settings['fallback_image_text'] && ! $style ){
			if( ! $text && isset($term->name) ){
				$text = $term->name;
			}
		}

		// check if it's a valid URL and create an image tag
		if( Base::$settings['image_uses_tag'] && filter_var( $style, FILTER_VALIDATE_URL ) ){
			$text .= sprintf('<img src="%s" loading="lazy">', $style);
			$style = ''; // reset style
		}

		$output = sprintf('<span class="rey-swatchList-itemContent" style="%1$s">%2$s</span>', $style, $text );

		if( isset($this->attribute_settings['swatch_tooltip']) && 'no' !== $this->attribute_settings['swatch_tooltip'] ){
			$output .= $this->render_tooltip($term);
		}

		return $output;
	}

	function render_tooltip($term){

		if( empty($term) ){
			return;
		}

		$content = $this->get_tooltip_content__before($term);

		if( in_array($this->attribute_settings['swatch_tooltip'], ['yes', 'yes_both'], true) )
		$content .= sprintf('<span class="__title">%s</span>', $term->name);

		if( in_array($this->attribute_settings['swatch_tooltip'], ['yes_desc', 'yes_both'], true) && ($desc = $term->description) ){
			$content .= sprintf('<span class="__desc">%s</span>', $desc);
		}

		$content .= $this->get_tooltip_content__after($term);

		$content .= '<div class="__corner"></div>';

		return sprintf('<div class="__tooltip"><div class="__holder">%s</div></div>', $content );

	}


	/**
	 * Gets a variation image based on arguments
	 *
	 * @param array $args
	 * @param object $term
	 * @return array
	 */
	function get_variation_image_data($term){

		$data = [];

		if( ! ($taxonomy = $term->taxonomy) ){
			return $data;
		}

		$variations = $this->list_args['product']->get_available_variations();
		// $product_image = $this->list_args['product']->get_image_id();

		foreach ( $variations as $variation ) {

			$attr_key = 'attribute_' . $taxonomy;

			if ( ! isset( $variation['attributes'][ $attr_key ] ) ) {
				continue;
			}

			if ( ! ( $attribute_name = $variation['attributes'][ $attr_key ] ) ) {
				continue;
			}

			if ( $attribute_name !== $term->slug ) {
				continue;
			}

			$image_size = Base::$settings['variations_image_size'];

			if( ! isset($variation['image'][$image_size]) ){

				// check for image ID in case URL is missing
				if( isset($variation['image_id']) && ! empty($variation['image_id']) ){
					$variation['image'][$image_size] = '';
				}

				// if not, just bail
				else {
					continue;
				}

			}

			// fallback to default data if variation doesn't have an image
			if( ! Base::$settings['variations_use_main_image'] ){

				$variation_product = wc_get_product( $variation['variation_id'] );

				if( ! $variation_product->get_image_id('edit') ){
					continue;
				}

			}

			$data = [
				'id' => $variation['image_id'],
				'url' => $variation['image'][$image_size],
			];

		}

		return $data;

	}

	function get_variation_price($term){

		$data = '';

		if( ! ($taxonomy = $term->taxonomy) ){
			return $data;
		}

		$variations = $this->list_args['product']->get_available_variations();

		foreach ( $variations as $variation ) {

			$attr_key = 'attribute_' . $taxonomy;

			if ( ! isset( $variation['attributes'][ $attr_key ] ) ) {
				continue;
			}

			if ( ! ( $attribute_name = $variation['attributes'][ $attr_key ] ) ) {
				continue;
			}

			if ( $attribute_name !== $term->slug ) {
				continue;
			}

		}

		return $data;

	}
}
