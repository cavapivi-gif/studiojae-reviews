<?php
namespace ReyCore\Modules\CustomTemplates;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Conditions
{
	private static $_instance = null;

	private $all_templates = [];

	private function __construct() { }

	private function get_templates( $type ){

		if( ! isset($this->all_templates[$type]) ){
			return [];
		}

		return call_user_func( [ $this, 'get_template_type__' . str_replace('-', '_', $type) ], $this->all_templates[$type] );
	}

	private function get_template_type__page( $items ){

		$templates = [];

		foreach ($items as $template) {

			$template = self::fix_priority($template);

			$conditions = [];

			$page_type = $template['page_types'];

			if( $page_type === 'p404' ){

				if( is_404() ){
					$templates[] = $template;
				}

			}
			else if( $page_type === 'generic' && $pages = reycore__clean( $template['pages'] ) ){

				$pages_operator = $template['pages_operator'];

				$valid_pages = [];

				foreach ($pages as $page) {
					$valid_pages[$page] = is_page( $page );
				}

				if( !empty($valid_pages) ) {
					$conditions[ $pages_operator ][ 'pages' ] = in_array( true, $valid_pages, true );
				}

				if( !empty($conditions) && $this->check_include_exclude( $conditions ) ){
					$templates[] = $template;
				}

			}
			// if no conditions, all
			else {
				$templates[] = $template;
			}

		}

		return $templates;
	}

	private function get_template_type__archive( $items ){

		$templates = [];

		foreach ($items as $template) {

			$template = self::fix_priority($template);

			$conditions = [];

			if( ($archive_conditions = $template['archive_conditions']) && is_array($archive_conditions) ){

				foreach ($archive_conditions as $archive_condition) {

					$operator = $archive_condition['operator'];

					if( $location = $archive_condition['location'] ){

						switch ($location) {

							case 'categories':

								if( isset($archive_condition['categories']) ){

									$categories = $archive_condition['categories'] ? $archive_condition['categories'] : [];
									$include_sub = isset($archive_condition['include_sub']) ? $archive_condition['include_sub'] : true;

									if( $include_sub ){
										$ct_childs = [];
										foreach ($categories as $cat) {
											$ct_childs = get_term_children($cat, 'category');
										}
										$categories = $categories + $ct_childs;
									}

									$conditions[ $operator ][ 'categories' ] = is_category($categories);

								}

								break;

							case 'tags':

								if( isset($archive_condition['tags']) ){
									$tags = $archive_condition['tags'] ? $archive_condition['tags'] : [];
									$conditions[ $operator ][ 'tags' ] = is_tag($tags);
								}

								break;

							default:

								if( isset($archive_condition[ $location . '_taxes']) ){
									$taxes = $archive_condition[$location . '_taxes'] ? $archive_condition[$location . '_taxes'] : [];
									$conditions[ $operator ][ $location ] = is_tax($location, $taxes);
								}

								break;

						}
					}
					// all archives
					else {
						$conditions[ $operator ][ 'archives' ] = true;
					}

				}

				if( !empty($conditions) && $this->check_include_exclude( $conditions ) ){
					$templates[] = $template;
				}

			}
			else {
				$templates[] = $template;
			}

		}

		return $templates;
	}

	private function get_template_type__product( $items ){

		$templates = [];

		foreach ($items as $template) {

			$template = self::fix_priority($template);

			$conditions = [];

			if( ($product_conditions = $template['product_conditions']) && is_array($product_conditions) ){

				foreach ($product_conditions as $product_condition) {

					// bail if no location specified
					if( ! ($location = $product_condition['location']) ){
						continue;
					}

					$id = get_the_ID();
					$operator = $product_condition['operator'];

					switch ($location) {

						case 'categories':

							if( isset($product_condition['categories']) && ($categories = $product_condition['categories']) ){

								if( reycore__is_multilanguage() ){
									$categories = apply_filters('reycore/translate_ids', $categories, 'product_cat');
								}

								$include_sub = isset($product_condition['include_sub']) ? $product_condition['include_sub'] : true;
								$conditions[ $operator ][ 'categories' ] = $this->__has_terms('product_cat', $categories, $include_sub );
							}

							break;

						case 'tags':

							if( isset($product_condition['tags']) && ($product_tags = $product_condition['tags']) ){
								if( reycore__is_multilanguage() ){
									$product_tags = apply_filters('reycore/translate_ids', $product_tags, 'product_tag');
								}
								$conditions[ $operator ][ 'tags' ] = $this->__has_terms('product_tag', $product_tags);
							}

							break;

						case 'attributes':

							if( isset($product_condition['attributes']) && !empty($product_condition['attributes'])){

								// terms ids
								$valid_attributes = [];
								$attributes = reycore__clean( $product_condition['attributes'] );

								foreach ($attributes as $attribute) {
									$attribute_parts = explode('|', reycore__clean($attribute));

									if( count($attribute_parts) > 1 ){
										$valid_attributes[] = has_term( $attribute_parts[0], $attribute_parts[1], $id );
									}
								}

								if( !empty($valid_attributes) ) {
									// $conditions[ $operator ][ 'attributes' ] = ! in_array( false, $valid_attributes, true );
									$conditions[ $operator ][ 'attributes' ] = in_array( true, $valid_attributes, true );
								}

							}

							break;

						case 'products':

							if( isset($product_condition['products']) && !empty($product_condition['products'])){

								$valid_products = [];
								$product_ids = reycore__clean( $product_condition['products'] );

								foreach ($product_ids as $product_id) {

									if( reycore__is_multilanguage() ){
										$product_id = apply_filters('reycore/translate_ids', $product_id, 'product');
									}

									$valid_products[$product_id] = is_single( $product_id );
								}

								if( !empty($valid_products) ) {
									// $conditions[ $operator ][ 'products' ] = ! in_array( false, $valid_products, true );
									$conditions[ $operator ][ 'products' ] = in_array( true, $valid_products, true );
								}

							}
							break;
					}

				}

				if( !empty($conditions) && $this->check_include_exclude( $conditions ) ){
					$templates[] = $template;
				}

			}
			else {
				$templates[] = $template;
			}

		}

		return $templates;
	}

	private function get_template_type__product_archive( $items ){

		$templates = [];

		foreach ($items as $template) {

			$template = self::fix_priority($template);

			$conditions = [];

			if( ($product_archive_conditions = $template['product_archive_conditions']) && is_array($product_archive_conditions) ){

				// if( ! empty($template['id']) && ($current_lang = reycore__is_multilanguage()) ){
				// 	$template['id'] = apply_filters('reycore/translate_ids', $template['id'], Base::POST_TYPE);
				// }

				foreach ($product_archive_conditions as $prod_archive_condition) {

					$operator = $prod_archive_condition['operator'];

					if( $location = $prod_archive_condition['location'] ){

						switch ($location):

							case 'categories':

								if( isset($prod_archive_condition['categories']) ){

									$is_empty = $prod_archive_condition['categories'] === false;
									$prod_categories = (array) $prod_archive_condition['categories'];
									$include_sub = isset($prod_archive_condition['include_sub']) ? $prod_archive_condition['include_sub'] : true;

									if( reycore__is_multilanguage() ){
										$prod_categories = apply_filters('reycore/translate_ids', $prod_categories, 'product_cat');
									}

									if( $include_sub ){
										$ct_childs = [];
										foreach ($prod_categories as $cat) {
											$ct_childs = get_term_children($cat, 'product_cat');
										}
										$prod_categories = $prod_categories + $ct_childs;
									}

									// if no categoriy specified, just run on all
									$conditions[ $operator ][ 'categories' ] = $is_empty ? true : is_product_category($prod_categories);

								}

								break;

							case 'tags':

								if( isset($prod_archive_condition['tags']) ){

									$is_empty = $prod_archive_condition['tags'] === false;
									$tags = (array) $prod_archive_condition['tags'];

									if( reycore__is_multilanguage() ){
										$tags = apply_filters('reycore/translate_ids', $tags, 'product_tag');
									}

									$conditions[ $operator ][ 'tags' ] = $is_empty ? true : is_product_tag($tags);
								}

								break;

							case 'attributes':

								if( isset($prod_archive_condition['attributes']) && ($attributes = reycore__clean( $prod_archive_condition['attributes'] ) )){

									foreach ($attributes as $attribute) {
										$valid_attributes[] = is_tax( $attribute );
									}

									if( !empty($valid_attributes) ) {
										// $conditions[ $operator ][ 'attributes' ] = ! in_array( false, $valid_attributes, true );
										$conditions[ $operator ][ 'attributes' ] = in_array( true, $valid_attributes, true );
									}

								}

								break;

							case 'shop_page':

								if( is_shop() && !is_search() ){
									$conditions[ $operator ][ 'shop_page' ] = true;

									// Force top priority
									if( $template['template_priority'] === '' ){
										$template['priority'] = 9999999;
									}

								}

								break;

							case 'search':

								if( is_search() ){

									$conditions[ $operator ][ 'search' ] = true;

									// Force top priority
									if( $template['template_priority'] === '' ){
										$template['priority'] = 9999999;
									}

								}

								break;

						endswitch;

					}
					// all archives
					else {
						$conditions[ $operator ][ 'product_archives' ] = true;
					}

				}

				if( !empty($conditions) && $this->check_include_exclude( $conditions ) ){
					$templates[] = $template;
				}

			}
			else {
				$templates[] = $template;
			}

		}

		return $templates;
	}

	private function get_template_type__single( $items ){

		$templates = [];

		foreach ($items as $template) {

			$template = self::fix_priority($template);

			$conditions = [];

			if( ($general_conditions = $template['general_conditions']) && is_array($general_conditions) ){

				foreach ($general_conditions as $general_condition) {

					// bail if no post type specified
					if( ! ($post_type = $general_condition['post_type']) ){
						continue;
					}

					if( ! is_singular($post_type) ){
						continue;
					}

					$operator = $general_condition['operator'];

					// handle "post" differently
					if( $post_type === 'post' ){

						// bail if no location specified
						if( ! ($location = $general_condition['location']) ){
							continue;
						}

						switch ($location) {

							case 'categories':

								if( isset($general_condition['categories']) && ($categories = $general_condition['categories']) ){
									$include_sub = isset($general_condition['include_sub']) ? $general_condition['include_sub'] : true;
									$conditions[ $operator ][ 'categories' ] = $this->__has_terms('category', $categories, $include_sub);
								}

								break;

							case 'tags':

								if( isset($general_condition['tags']) && ($tags = $general_condition['tags']) ){
									$conditions[ $operator ][ 'tags' ] = $this->__has_terms('post_tag', $tags);
								}

								break;

							case 'posts':

								if( isset($general_condition['posts']) && ($post_ids = reycore__clean( $general_condition['posts'] )) ){

									$valid_posts = [];

									foreach ($post_ids as $post_id) {

										if( reycore__is_multilanguage() ){
											$post_id = apply_filters('reycore/translate_ids', $post_id );
										}

										$valid_posts[$post_id] = is_single( $post_id );
									}

									if( !empty($valid_posts) ) {
										$conditions[ $operator ][ 'posts' ] = in_array( true, $valid_posts, true );
									}

								}
								break;

						}
					}

					// other post types
					else {

						// get CTP's
						if( $ctps = reycore_rt__get_cpt() ){

							foreach ($ctps as $key => $ctp) {

								if( get_post_type() !== $ctp['post_type'] ){
									continue;
								}

								$valid_ctps = [];

								// check post
								if( isset($general_condition[$ctp['post_field_name']]) && ($ctp_posts = $general_condition[$ctp['post_field_name']]) ){

									$valid_posts = [];

									foreach ($ctp_posts as $ctp_post_id) {

										if( reycore__is_multilanguage() ){
											$ctp_post_id = apply_filters('reycore/translate_ids', $ctp_post_id, $ctp['post_type'] );
										}

										$valid_posts[$ctp_post_id] = is_single( $ctp_post_id );
									}

									if( !empty($valid_posts) ) {
										$valid_ctps[] = in_array( true, $valid_posts, true );
									}
								}

								foreach ($ctp['taxonomies'] as $ctp_tax) {

									if( isset($general_condition[$ctp_tax['tax_field_name']]) && ($ctp_terms = $general_condition[$ctp_tax['tax_field_name']]) ){
										$valid_ctps[] = $this->__has_terms($ctp_tax['tax_name'], $ctp_terms);
									}

								}

								$conditions[ $operator ][ 'ctps' ] = ! in_array( false, $valid_ctps, true );

							}
						}

					}

				}

				if( !empty($conditions) && $this->check_include_exclude( $conditions ) ){
					$templates[] = $template;
				}

			}
			else {
				$templates[] = $template;
			}

		}

		return $templates;
	}

	function check_conditions( $all_templates = [] ){

		if( empty($all_templates) ){
			return false;
		}

		$this->all_templates = $all_templates;

		$templates = [];

		// PAGE
		if( is_page() || is_404() ){
			$templates = $this->get_templates('page');
		}

		// single product
		else if ( class_exists('\WooCommerce') && is_product() ) {
			$templates = $this->get_templates('product');
		}

		// product archive
		else if ( class_exists('\WooCommerce') && ( is_shop() || is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ) {
			$templates = $this->get_templates('product-archive');
		}

		// archive
		else if ( is_tax() || is_category() || is_tag() ){
			$templates = $this->get_templates('archive');
		}

		// singular
		else if ( is_singular() ){
			$templates = $this->get_templates('single');
		}

		if( empty($templates) || !is_array($templates) ){
			return [];
		}

		// sort by priorities
		uasort( $templates, function( $a, $b ) {
			if ( ! isset( $a['priority'], $b['priority'] ) || $a['priority'] === $b['priority'] ) {
				return 0;
			}
			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		});

		return end($templates);
	}

	public static function fix_priority( $template ){
		$template['priority'] = $template['template_priority'] !== '' ? absint($template['template_priority']) : 1;
		return $template;
	}

	function check_include_exclude( $arr ){

		$arr = wp_parse_args($arr, [
			'include' => [],
			'exclude' => [],
		]);

		$validations = [];

		if( ! empty($arr[ 'include' ])  ){
			$validations[] = in_array( true, $arr[ 'include' ], true );
			// $validations[] = ! in_array( false, $arr[ 'include' ], true );
		}

		if( ! empty($arr[ 'exclude' ])  ){
			$validations[] = ! in_array( true, $arr[ 'exclude' ], true );
		}

		return ! in_array( false, $validations, true );
	}

	protected function __has_terms( $taxonomy, $terms, $include_sub = null ){

		// check subcategory
		if( $include_sub ){
			foreach ($terms as $term) {
				if( $subterms = get_term_children( $term, $taxonomy ) ){
					foreach ($subterms as $subterm) {
						$terms[] = $subterm;
					}
				}
			}
		}

		return has_term( array_unique($terms), $taxonomy, get_the_ID() );
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return Conditions
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
