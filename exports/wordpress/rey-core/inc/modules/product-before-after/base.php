<?php
namespace ReyCore\Modules\ProductBeforeAfter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-ba';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		new AcfFields();

		add_filter( 'acf/prepare_field/name=content_before_global_section', [$this, 'populate_gs']);
		add_filter( 'acf/prepare_field/name=content_after_global_section', [$this, 'populate_gs']);
		add_action( 'reycore/woocommerce/content_product/before', [$this, '_before'], 10);
		add_action( 'reycore/woocommerce/content_product/after', [$this, '_after'], 10);
		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_action( 'manage_product_posts_custom_column', [$this, 'admin_title'], 20, 2 );
		add_action( 'woocommerce_after_single_product_summary', [$this, 'prevent_ba'], 10);
		add_action( 'woocommerce_cart_collaterals', [$this, 'prevent_ba'], 10);
		add_action( 'reycore/woocommerce/wishlist/render_products', [$this, 'prevent_ba'], 10);
	}

	function prevent_ba(){
		add_filter('reycore/woocommerce/catalog/before_after/enable', '__return_false');
	}

	function get_ba_title($product_id, $after = false){

		if( ! $after ){
			$pos = 'before';
			$pos_title = esc_html_x('Before:', 'Title in admin product list.', 'rey-core');
		}
		else {
			$pos = 'after';
			$pos_title = esc_html_x('After:', 'Title in admin product list.', 'rey-core');
		}

		$content_type = [
			'gs' => 'Global Section',
			'product' => 'Product'
		];

		if( ! ($content = reycore__acf_get_field('content_' . $pos, $product_id)) ){
			return;
		}

		if( $content === 'gs' && ($content__gs = reycore__acf_get_field('content_'. $pos .'_global_section', $product_id)) ){
			$ba_id = $content__gs;
		}
		elseif( $content === 'product' && ($content__product = reycore__acf_get_field('content_'. $pos .'_product', $product_id)) ){
			if( isset($content__product[0]) ){
				$ba_id = $content__product[0];
			}
		}

		$url = add_query_arg([
				'post' => $ba_id,
				'action' => 'edit',
			],
			admin_url( 'post.php' )
		);

		return sprintf('<a href="%3$s" class="rey-ba-item --%5$s" title="%4$s"><strong>%1$s</strong> %4$s (%2$s)</a>',
			$pos_title,
			$content_type[$content],
			esc_url($url),
			esc_attr( get_the_title($ba_id) ),
			$pos
		);

	}

	function admin_title( $column_name, $product_id ){

		if( $column_name !== 'name' ){
			return;
		}

		echo '<div class="rey-ba-items">';
		echo $this->get_ba_title($product_id);
		echo $this->get_ba_title($product_id, true);
		echo '</div>';
	}

	function _before( $product ){

		if( ! $product ){
			return;
		}

		$this->render_content( $product );
	}

	function _after( $product ){

		if( ! $product ){
			return;
		}

		$this->render_content( $product, true );
	}

	function render_content( $product, $after = false ){

		if( ! apply_filters('reycore/woocommerce/catalog/before_after/enable', true, $product) ){
			return;
		}

		$pos = $after ? 'after' : 'before';

		$product_id = $product->get_id();

		if( ! ( $content = reycore__acf_get_field('content_' . $pos, $product_id) ) ){
			return;
		}

		$classes['product_ba'] = '--ba-item';
		$classes['product_ba_pos_' . $pos] = '--ba-item-' . $pos;
		$classes['product_ba_type_' . $pos] = '--ba-type-' . esc_attr( $content );

		if( $content === 'gs' && ($content__gs = reycore__acf_get_field('content_'. $pos .'_global_section', $product_id)) ){

			$gs_options = [
				'section' => $content__gs,
				'parent_product_id' => $product_id,
				'classes' => implode( ' ', $classes ),
				'position' => $pos,
			];

			if( ($content__colspan = absint(reycore__acf_get_field('content_'. $pos .'_colspan', $product_id))) && $content__colspan > 1 ){
				$gs_options['colspan'] = $content__colspan;
			}

			$this->render_gs($gs_options);
		}

		elseif( $content === 'product' && ($content__product = reycore__acf_get_field('content_'. $pos .'_product', $product_id)) ){

			if( isset($content__product[0]) ){
				$this->render_product( [
					'setup_postdata' => true,
					'product_id' => $content__product[0],
					'parent_product_id' => $product_id,
					'classes' => implode( ' ', $classes )
				]);
			}
		}

		reycore_assets()->add_styles('rey-wc-tag-stretch');
		// reycore_assets()->add_scripts('reycore-wc-loop-stretch');

	}

	function render_product( $args = [] ){

		$args = wp_parse_args($args, [
			'setup_postdata' => true,
			'product_id' => false,
			'classes' => '',
		]);

		if( ! $args['product_id'] ){
			return;
		}

		if( isset($GLOBALS['post']) ) {
			$original_product = $GLOBALS['post'];
		}

		$GLOBALS['post'] = get_post( $args['product_id'] );

		setup_postdata( $GLOBALS['post'] );

		$new_product = wc_get_product($GLOBALS['post']);

		// Ensure visibility.
		if ( empty( $new_product ) || ! $new_product->is_visible() ) {
			return;
		}

		$args['classes'] .= ' post-p' . $new_product->get_id();

		echo '<li ';
			wc_product_class( $args['classes'] );
			printf( ' data-pid="p%d" ', esc_attr( $new_product->get_id() ));
		echo '>';
			do_action( 'woocommerce_before_shop_loop_item' );
			do_action( 'woocommerce_before_shop_loop_item_title' );
			do_action( 'woocommerce_shop_loop_item_title' );
			do_action( 'woocommerce_after_shop_loop_item_title' );
			do_action( 'woocommerce_after_shop_loop_item' );
		echo '</li>';

		if( isset($original_product) ) {
			$GLOBALS['post'] = $original_product;
			if( $args['setup_postdata'] ){
				setup_postdata( $GLOBALS['post'] );
			}
		}
	}

	function render_gs( $args = [] ){

		$args = wp_parse_args($args, [
			'section' => false,
			'parent_product_id' => false,
			'classes' => '',
			'colspan' => 0
		]);

		if( ! $args['section'] ){
			return;
		}

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return;
		}

		reycore_assets()->defer_page_styles('elementor-post-' . $args['section']);

		if( ! ($gs_content = \ReyCore\Elementor\GlobalSections::do_section( $args['section'], true, true )) ){
			return;
		}

		$args['classes'] .= ' post-x' . $args['parent_product_id'];

		echo '<li ';

			wc_product_class( $args['classes'] );

			printf( ' data-pid="x%d" ', $args['parent_product_id']);

			if( $args['colspan'] ){

				if( ($layout_cols = absint(wc_get_loop_prop('columns'))) && $layout_cols < $args['colspan'] ){
					$args['colspan'] = absint($layout_cols);
				}

				printf( ' data-colspan="%d" ', $args['colspan']);

				global $wp_query;

				$colspans = absint($args['colspan']);

				if( $existing_colspans = $wp_query->get('colspans') ){
					$colspans += (absint($existing_colspans) - 1);
				}

				$wp_query->set('colspans', $colspans);
			}
		echo '>';

			echo $gs_content;

			echo reycore__popover([
				'content' => sprintf(_x('<p>This is a Generic Global Section set as <b>Content %1$s</b> in "%2$s"\'s product Catalog Display settings. You can either <a href="%3$s" target="_blank"><u>edit it with Elementor</u></a>, or edit <a href="%4$s" target="_blank"><u>the product\'s settings</u></a>" in "Catalog Display > Content %1$s".</p>', 'Various admin. texts', 'rey-core'),
					ucfirst($args['position']),
					get_the_title($args['parent_product_id']),
					admin_url( sprintf('post.php?post=%d&action=elementor', $args['section']) ),
					get_edit_post_link($args['parent_product_id'])
				),
				'admin' => true,
				'class' => '--gs-popover'
			]);

		echo '</li>';

	}

	function product_page_classes($classes, $product)
	{

		if( ! apply_filters('reycore/woocommerce/catalog/before_after/enable', true, $product) ){
			return $classes;
		}

		$product_id = $product->get_id();

		if(
			$content_before = reycore__acf_get_field('content_before', $product_id) ||
			$content_after = reycore__acf_get_field('content_after', $product_id) ) {
			$classes['product_ba'] = '--ba-item';
		}

		return $classes;
	}

	function populate_gs($field)
	{
		if( class_exists('\ReyCore\Elementor\GlobalSections') && isset($field['choices']) ) {
			if( ($gs = \ReyCore\Elementor\GlobalSections::get_global_sections(['generic'])) && is_array($gs) ){
				$field['choices'] = $field['choices'] + $gs;
			}
		}
		return $field;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Catalog Products - Content Before / After product', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to show a product or global section, before or after a product in the catalog.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
			'help'        => reycore__support_url('kb/product-settings-options/#catalog-display-content-before-after'),
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
					'key' => 'content_before',
					'value'   => '',
					'compare' => 'NOT IN'
				],
				[
					'key' => 'content_after',
					'value'   => '',
					'compare' => 'NOT IN'
				],
			]
		]);

		return ! empty($post_ids);

	}
}
