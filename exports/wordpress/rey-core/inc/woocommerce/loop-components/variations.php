<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Variations extends Component {

	public $selected_attribute_taxonomy;
	public $selected_attribute_taxonomy_data = [];

	public function status(){
		return get_theme_mod('woocommerce_loop_variation', 'disabled') !== 'disabled';
	}

	public function get_id(){
		return 'variations';
	}

	public function get_name(){
		return 'Variations';
	}

	public function scheme(){

		$position = get_theme_mod('woocommerce_loop_variation_position', 'after');

		$hooks = [
			'first' => [
				'hook'     => 'woocommerce_before_shop_loop_item_title',
				'priority' => 20,
			],
			'before' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 5,
			],
			'after_price' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 20,
			],
			'after' => [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 905,
			],
		];

		if( ! isset($hooks[ $position ]) ){
			return [];
		}

		return [
			'type'          => 'action',
			'tag'           => $hooks[ $position ]['hook'],
			'priority'      => $hooks[ $position ]['priority'],
		];

	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		global $product;

		if ( ! ($product && 'variable' === $product->get_type()) ) {
			return;
		}

		if( ! $this->selected_attribute_taxonomy ){
			if( ! ($this->selected_attribute_taxonomy = $this->get_selected_attribute_taxonomy()) ){
				return;
			}
		}

		if( 'disabled' === $this->selected_attribute_taxonomy ){
			return;
		}

		// Render All attributes
		if( 'all_attributes' === $this->selected_attribute_taxonomy ){
			do_action('reycore/woocommerce/variations/catalog/render_all', $product, $this);
			return;
		}

		if( ! $this->selected_attribute_taxonomy_data ){
			if( ! ($this->selected_attribute_taxonomy_data = $this->get_attribute_taxonomy_data()) ){
				return;
			}
		}

		if( ($display_text = get_theme_mod('woocommerce_loop_variation_force_regular', '')) &&
			($regular_attribute = $this->get_regular_attribute_data( $product, $display_text )) ){
			echo $regular_attribute;
			return;
		}

		if( empty( $this->selected_attribute_taxonomy_data ) ){
			return;
		}

		do_action('reycore/woocommerce/variations/catalog/render_single', $product, $this);

	}

	/**
	 * Get the selected visible attribute data
	 *
	 * @since 1.0.0
	 */
	function get_attribute_taxonomy_data()
	{
		$data['name'] = $this->selected_attribute_taxonomy;
		$data['taxonomy_name'] = wc_attribute_taxonomy_name( $data['name'] );
		$data['type'] = 'select';
		return apply_filters('reycore/woocommerce/variations/variation_attribute_data', $data);
	}


	/**
	 * Gets a regular attribute data, regardless if
	 * product is variable
	 *
	 * @since 1.3.9
	 */
	function get_regular_attribute_data( $product, $display_type ){

		$attr_data = $this->selected_attribute_taxonomy_data;
		$all_attributes = $product->get_attributes();

		if( ! (isset( $all_attributes[$attr_data['taxonomy_name']] ) && $attribute = $all_attributes[$attr_data['taxonomy_name']]) ){
			return;
		}

		if( ! is_object($attribute) ){
			return;
		}

		if( ! $attribute->get_visible() ){
			return;
		}

		$custom_text = get_theme_mod('woocommerce_loop_variation_text_display', '');

		// count
		if( 'count' === $display_type ){

			$text = $custom_text ? $custom_text : esc_html__('items', 'rey-core');

			if( ($options = $attribute->get_options()) && !empty($options) && count($options) > 1 ){
				return sprintf(
					'<div class="rey-productAttribute"><span class="__count">%s</span> <span class="__text">%s</span></div>',
					count($options),
					$text
				);
			}
		}
		// text list
		else {

			$tax = $attribute->get_name();
			$attribute_values   = wc_get_product_terms( $product->get_id(), $tax, [ 'fields' => 'all' ] );

			$values = [];

			foreach ( $attribute_values as $attribute_value ) {
				$values[] = esc_html( ucfirst( $attribute_value->name ) );
			}

			if( $custom_text ){
				$text = $custom_text;
			}
			elseif ($custom_text == 0) {
				$text = '';
			}
			else {
				$text = strtoupper(wc_attribute_label( $tax )) . ': ';
			}

			if( ! empty($values) ){
				return sprintf(
					'<div class="rey-productAttribute">' . apply_filters('reycore/woocommerce/loop_attribute_display', '%s%s', $attribute, $values) . '</div>',
					$text,
					implode( ', ', $values )
				);
			}
		}

	}

	function get_selected_attribute_taxonomy(){
		return get_theme_mod('woocommerce_loop_variation', 'disabled');
	}

}
