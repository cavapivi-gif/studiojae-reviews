<?php
namespace ReyCore\Elementor\Widgets\Menu;

use ReyCore\Menus;
use ReyCore\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinProductCategories extends \Elementor\Skin_Base
{

	public $_settings = [];

	public function get_id() {
		return 'product-categories';
	}

	public function get_title() {
		return __( 'Product Categories', 'rey-core' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'register_prod_cat_controls' ] );
	}

	public function register_prod_cat_controls( $element ){

		$element->add_control(
			'pcat_type',
			[
				'label' => __( 'Selection', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'    => __( 'All Categories', 'rey-core' ),
					'parent' => __( 'Parent Categories', 'rey-core' ),
					'sub'    => __( 'Sub-Categories', 'rey-core' ),
					'manual' => __( 'Manually Pick Categories', 'rey-core' ),
					'manual_order' => __( 'Manually Pick Categories (Exact order)', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'product-categories',
				],
			]
		);

		$element->add_control(
			'pcat_categories',
			[
				'label' => esc_html__('Manually Select Categories', 'rey-core'),
				'type' => 'rey-query',
				'label_block' => true,
				'multiple' => true,
				'default'     => [],
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
					'field' => 'slug'
				],
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'manual',
				],
			]
		);

		$element->add_control(
			'pcat_categories_acf',
			[
				'label' => esc_html__( 'Or select from ACF field', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: acf_taxonomy_field_key', 'rey-core' ),
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'manual',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		// Custom order
		$custom_order_cats = new \Elementor\Repeater();

			$custom_order_cats->add_control(
				'item',
				[
					'label' => esc_html__( 'Select category', 'rey-core' ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => 'product_cat',
						// 'field' => 'slug'
					],
					'label_block' => true,
					'default' => [],
				]
			);

		$element->add_control(
			'pcat_categories_order',
			[
				'label' => __( 'Manually Select Categories (in Order)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $custom_order_cats->get_controls(),
				'default' => [],
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'manual_order',
				],
			]
		);

		$element->add_control(
			'pcat_parent_category',
			[
				'label' => esc_html__('Categories', 'rey-core'),
				'type' => 'rey-query',
				'label_block' => true,
				'default' => [],
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
					'field' => 'slug'
				],
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'sub',
				],
				'placeholder' => esc_html__('- Automatic -', 'rey-core'),
			]
		);

		$element->add_control(
			'hide_empty',
			[
				'label' => esc_html__( 'Hide Empty Categories', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type!' => ['manual', 'manual_order'],
				],
			]
		);

		$element->add_control(
			'hide_uncateg',
			[
				'label' => esc_html__( 'Hide "Uncategorized"', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type!' => ['manual', 'manual_order'],
				],
			]
		);

		$element->add_control(
			'hierarchical',
			[
				'label' => esc_html__( 'Hierarchical', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'all',
				],
			]
		);

		$element->add_control(
			'depth',
			[
				'label' => esc_html__( 'Depth', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'all',
				],
			]
		);

		$element->add_control(
			'orderby',
			[
				'label' => esc_html__( 'Order By', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'  => esc_html__( 'Name', 'rey-core' ),
					'menu_order'  => esc_html__( 'Menu Order', 'rey-core' ),
					'term_id'  => esc_html__( 'Term ID', 'rey-core' ),
					'term_group'  => esc_html__( 'Term Group', 'rey-core' ),
					'parent'  => esc_html__( 'Parent', 'rey-core' ),
					'count'  => esc_html__( 'Count', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type!' => ['manual', 'manual_order'],
				],
			]
		);

		$element->add_control(
			'pcat_exclude',
			[
				'label'       => esc_html__('Exclude Categories', 'rey-core'),
				'type'        => 'rey-query',
				'label_block' => true,
				'multiple'    => true,
				'default'     => [],
				'query_args'  => [
					'type'     => 'terms',
					'taxonomy' => 'product_cat',
					// 'field' => 'slug'
				],
				'condition' => [
					'_skin'      => 'product-categories',
					'pcat_type!' => ['manual', 'manual_order'],
				],
			]
		);

		$element->add_control(
			'all_button',
			[
				'label' => esc_html__( 'Append "All" button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'all',
				],
				// 'separator' => 'before'
			]
		);

		$element->add_control(
			'all_button_text',
			[
				'label' => esc_html__( '"All" button text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'All', 'rey-core' ),
				'placeholder' => esc_html__( 'eg: All', 'rey-core' ),
				'condition' => [
					'_skin' => 'product-categories',
					'pcat_type' => 'all',
					'all_button!' => '',
				],
			]
		);

	}

	public function get_categories()
	{
		$orderby = 'name';

		if( isset($this->_settings['orderby']) ){
			$orderby = $this->_settings['orderby'];
		}

		if( $this->_settings['pcat_type'] === 'all' ){
			$cats = reycore_wc__product_categories([
				'hide_empty' => $this->_settings['hide_empty'] === 'yes',
				'orderby' => $orderby,
				'hide_uncategorized' => $this->_settings['hide_uncateg'] !== ''
			]);
		}

		elseif( $this->_settings['pcat_type'] === 'manual' ){

			$selected_cats = $this->_settings['pcat_categories'];

			if( empty($selected_cats) && ($acf_categories_key = $this->_settings['pcat_categories_acf']) ){

				$acf_categories_list = \ReyCore\ACF\Helper::get_field_from_elementor([
					'key' => $acf_categories_key,
				]);

				$cats = [];
				if( ! empty($acf_categories_list) && is_array($acf_categories_list) ){
					foreach ($acf_categories_list as $term_id) {
						if( reycore__is_multilanguage() ){
							$term_id = apply_filters('reycore/translate_ids', $term_id, 'product_cat');
						}
						if( ($tm = get_term( $term_id )) && isset($tm->name) ){
							$cats[$tm->slug] = $tm->name;
						}
					}
				}
			}
			else {

				// hide_uncateg
				$cats = reycore_wc__product_categories([
					'hide_empty' => $this->_settings['hide_empty'] === 'yes',
				]);

				$new_cats = [];
				if( ! empty($selected_cats) ){
					foreach ($selected_cats as $selected_cat) {
						if( isset($cats[$selected_cat]) ){
							$new_cats[$selected_cat] = $cats[$selected_cat];
						}
					}
				}

				$cats = $new_cats;
			}

		}

		elseif( $this->_settings['pcat_type'] === 'manual_order' ){

			$cats = [];

			if( isset($this->_settings['pcat_categories_order']) && ($handpicked_ordered_terms = $this->_settings['pcat_categories_order']) ){

				$handpicked_ordered_terms__clean           = array_filter( wp_list_pluck($handpicked_ordered_terms, 'item') );
				foreach ($handpicked_ordered_terms__clean as $term_id) {

					if( ($tm = get_term( $term_id )) && isset($tm->name) ){
						$cats[$term_id] = $tm->name;
					}
				}
			}

		}

		elseif( $this->_settings['pcat_type'] === 'parent' ){
			$cats = reycore_wc__product_categories( [
				'hide_empty' => $this->_settings['hide_empty'] === 'yes',
				'parent' => 0,
				'orderby' => $orderby,
				'hide_uncategorized' => $this->_settings['hide_uncateg'] !== '',
				'exclude' => ! empty($this->_settings['pcat_exclude']) ? $this->_settings['pcat_exclude'] : [],
			] );
		}

		elseif( $this->_settings['pcat_type'] === 'sub' ){
			$cats = reycore_wc__product_categories( [
				'hide_empty' => $this->_settings['hide_empty'] === 'yes',
				'parent' => ($parent_cat = $this->_settings['pcat_parent_category']) ? $parent_cat : '',
				'orderby' => $orderby,
				'hide_uncategorized' => $this->_settings['hide_uncateg'] !== '',
				'exclude' => ! empty($this->_settings['pcat_exclude']) ? $this->_settings['pcat_exclude'] : [],
			] );
		}

		return $cats;
	}

	public function render_items( $cats )
	{

		printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $this->_settings['hover_style']);

		if( $this->_settings['all_button'] !== '' && $this->_settings['pcat_type'] === 'all' ){
			printf(
				'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
				$this->_settings['all_button_text'],
				get_permalink( wc_get_page_id('shop') ),
				is_shop() ? 'current-menu-item' : ''
			);
		}

		foreach ($cats as $id_or_slug => $category_name) {

			if( is_wp_error($id_or_slug) || is_wp_error($category_name) || is_object($id_or_slug) || is_null($category_name) ){
				continue;
			}

			if( !(is_string($id_or_slug) || is_numeric($id_or_slug)) ){
				continue;
			}

			$active_term_class = is_tax( 'product_cat', $id_or_slug )  ? 'current-menu-item' : '';
			$term_link = get_term_link( $id_or_slug, 'product_cat' );

			printf(
				'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
				$category_name,
				! is_wp_error($term_link) ? $term_link : '#',
				$active_term_class
			);
		}

		echo '</ul>';
	}

	public function render_all_categories(){

		$list_args = [
			'taxonomy'                   => 'product_cat',
			'show_count'                 => false,
			'hierarchical'               => $this->_settings['hierarchical'] == 'yes',
			'hide_empty'                 => $this->_settings['hide_empty'] == 'yes',
			'orderby'                    => $this->_settings['orderby'],
			'title_li'                   => '',
			'pad_counts'                 => 1,
			'show_option_none'           => __( 'No product categories exist.', 'woocommerce' ),
			'current_category'           => is_tax('product_cat') ? get_queried_object_id() : false,
			'current_category_ancestors' => [],
			'class_pattern'	             => 'menu',
			'submenu_class'	             => 'sub-menu',
			'echo' => true
		];

		if( class_exists('\Rey_WC_Product_Cat_List_Walker') ){
			$list_args['walker'] = new \Rey_WC_Product_Cat_List_Walker();
		}

		if( $this->_settings['depth'] ){
			$list_args['depth'] = $this->_settings['depth'];
		}

		if( $this->_settings['hide_uncateg'] !== '' && $uncategorized = get_option( 'default_product_cat' ) ){
			$list_args['exclude'] = (array) $uncategorized;
		}

		if( ! empty($this->_settings['pcat_exclude']) ){
			$exclude = isset($list_args['exclude']) ? $list_args['exclude'] : [];
			$list_args['exclude'] = array_merge($exclude, $this->_settings['pcat_exclude']);
		}

		printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $this->_settings['hover_style']);

			if( $this->_settings['all_button'] !== '' && $this->_settings['pcat_type'] === 'all' ){
				printf(
					'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
					$this->_settings['all_button_text'],
					get_permalink( wc_get_page_id('shop') ),
					is_shop() ? 'current-menu-item' : ''
				);
			}

			wp_list_categories( apply_filters( 'woocommerce_product_categories_widget_args', $list_args ) );

		echo '</ul>';

	}

	public function render_menu()
	{

		$html = '';

		if( $this->_settings['pcat_type'] === 'all' && function_exists('WC') ){
			ob_start();
			$cats = [];
			$this->render_all_categories();
			$html = ob_get_clean();
		}
		else {
			$cats = $this->get_categories();
			ob_start();
			$this->render_items( $cats );
			$html = ob_get_clean();
		}

		if( !empty($html) ){
			echo '<div class="reyEl-menu-navWrapper">';
			echo apply_filters('reycore/elementor/menu/product_categories_skin/render_menu', $html, $cats, $this->_settings, $this->parent);
			echo '</div>';
		}
	}

	public function get_transient_name(){

		$controls = [
			'pcat_type',
			'pcat_categories',
			'item',
			'pcat_categories_order',
			'pcat_parent_category',
			'hide_empty',
			'hide_uncateg',
			'hierarchical',
			'depth',
			'orderby',
			'pcat_exclude',
			'all_button',
			'all_button_text',
		];

		$settings = [];

		foreach ($controls as $control) {
			$settings[$control] = isset($this->_settings[$control]) ? $this->_settings[$control] : '';
		}

		$lang = ($_l = reycore__is_multilanguage()) ? $_l : '';

		return Menus::CAT_TRANSIENT . Helper::hash($settings) . $lang . reycore__versions_hash();
	}

	public function can_cache()
	{

		if( \ReyCore\Plugin::is_dev_mode() ){
			return false;
		}

		if( 'sub' === $this->_settings['pcat_type'] ){
			// it's dynamic (extract sub-categories)
			if( empty($this->_settings['pcat_parent_category']) ){
				return false;
			}
		}
		else if( $this->_settings['pcat_type'] === 'manual' && ! empty($this->_settings['pcat_categories_acf']) ){
			return false;
		}

		return true;

	}

	public function render() {

		if( ! \ReyCore\Plugin::instance()->woo ){
			echo esc_html__('WooCommerce not installed', 'rey-core');
			return;
		}

		reycore_assets()->add_styles( $this->parent->get_style_name('style') );

		$this->_settings = $this->parent->get_settings_for_display();

		ob_start();
		$this->parent->render_start();
		$this->parent->render_title();
		$start = ob_get_clean();

		ob_start();
		$this->parent->render_end();
		$end = ob_get_clean();

		$can_cache = $this->can_cache();

		$transient_name = $this->get_transient_name();

		if(
			$can_cache &&
			! reycore__elementor_edit_mode()
			&& false !== ( $cached_menu = get_transient($transient_name) )
		){
			echo $start . $cached_menu . $end;
			return;
		}

		ob_start();
		$this->render_menu();
		$menu = ob_get_clean();

		if( $can_cache ){
			set_transient($transient_name, $menu, MONTH_IN_SECONDS);
		}

		echo $start . $menu . $end;


	}
}
