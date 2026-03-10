<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Category extends Component {

	public function init(){
		add_action('reycore/elementor/product_grid/mini_grid/content', [$this, 'render_mini_skin'], 5);
	}

	public function status(){
		return get_theme_mod('loop_show_categories', '2') == '1';
	}

	public function get_id(){
		return 'category';
	}

	public function get_name(){
		return 'Category';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_shop_loop_item_title',
			'priority'      => 5,
		];

	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		$product = wc_get_product();

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$__categories = wp_get_post_terms( $id, 'product_cat', apply_filters('reycore/woocommerce/loop/category_args', [
			'order' 	=> 'ASC',
			'orderby'	=> 'name',
		], $id ) );

		if( is_wp_error( $__categories ) ){
			return;
		}

		if( empty($__categories) ){
			return;
		}

		$categories = [];

		// loop through each cat
		foreach($__categories as $category):

			if( get_theme_mod('loop_categories__exclude_parents', false) ){
				// get the children (if any) of the current cat
				$children = get_categories(['taxonomy' => 'product_cat', 'parent' => $category->term_id ]);
				if ( count($children) == 0 ) {
					// if no children, then get the category name.
					$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
				}
			}
			else {
				$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
			}

		endforeach;

		if( empty($categories) ){
			return;
		}

		$separator = apply_filters('reycore/woocommerce/loop/categories_sep', '');

		printf('<div class="rey-productCategories">%s</div>', apply_filters('reycore/woocommerce/loop/categories', implode($separator, $categories), $product));

	}


	public function render_mini_skin( $element ){
		if( $element->_settings['hide_category'] !== 'yes' ){
			$this->render();
		}
	}
}
