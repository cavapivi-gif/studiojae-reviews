<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Templates {

	private $custom_templates = [];

	public function __construct()
	{
		add_filter('wc_get_template', [$this, 'override_templates'], 10, 2);
		add_filter('wc_get_template_part', [$this, 'override_template_part'], 10, 3);
	}

	public static function get_override( $override ){

		$file = false;

		$rey_core_slug_path = str_replace('template-parts/', 'rey-core/', $override);

		if ( file_exists( trailingslashit(STYLESHEETPATH) . $rey_core_slug_path ) ) {
			$file = trailingslashit(STYLESHEETPATH) . $rey_core_slug_path;
		}
		elseif ( file_exists( STYLESHEETPATH . '/' . $override ) ) {
			$file = STYLESHEETPATH . '/' . $override;
		}
		elseif ( file_exists( TEMPLATEPATH . '/' . $override ) ) {
			$file = TEMPLATEPATH . '/' . $override;
		}
		elseif ( file_exists( REY_CORE_DIR . $override ) ) {
			$file = REY_CORE_DIR . $override;
		}

		return $file;
	}

	function override_templates( $template, $template_name ){

		$custom_templates = apply_filters('reycore/woocommerce/wc_get_template', [
			[
				// Loop - Pagination
				'template_name' => 'loop/pagination.php',
				'template' => sprintf( 'template-parts/woocommerce/loop-pagination-%s.php', get_theme_mod('loop_pagination', 'paged') )
			],
			[
				// Product page - Blocks
				'template_name' => 'single-product/tabs/tabs.php',
				'template' => sprintf('template-parts/woocommerce/single-%s.php', get_theme_mod('product_content_layout', 'blocks'))
			],
			[
				// Order by select list
				'template_name' => 'loop/orderby.php',
				'template' => 'template-parts/woocommerce/loop-orderby.php'
			],
			[
				// Results counts
				'template_name' => 'loop/result-count.php',
				'template' => 'template-parts/woocommerce/loop-result-count.php'
			],
			[
				// Price lopp
				'template_name' => 'loop/price.php',
				'template' => 'template-parts/woocommerce/loop-price.php'
			],
			[
				// Mini Cart
				'template_name' => 'cart/mini-cart.php',
				'template' => 'template-parts/woocommerce/cart/mini-cart.php'
			],
			[
				// Single product - Meta
				'template_name' => 'single-product/meta.php',
				'template' => 'template-parts/woocommerce/single-meta.php'
			],
			[
				// Single product - Variation Data
				'template_name' => 'single-product/add-to-cart/variation.php',
				'template' => 'template-parts/woocommerce/single-variation-data.php'
			],
			[
				// Single product image
				'template_name' => 'single-product/product-image.php',
				'template' => 'template-parts/woocommerce/single-product-image.php'
			],
			[
				// Simple ATC Button
				'template_name' => 'single-product/add-to-cart/simple.php',
				'template' => 'template-parts/woocommerce/single-simple-add-to-cart-button.php'
			],
			[
				// Variable ATC Button
				'template_name' => 'single-product/add-to-cart/variation-add-to-cart-button.php',
				'template' => 'template-parts/woocommerce/single-variation-add-to-cart-button.php'
			],
			[
				// Cart Item Data
				'template_name' => 'cart/cart-item-data.php',
				'template' => 'template-parts/woocommerce/cart/cart-item-data.php'
			],
			[
				// Product Page Specifications
				'template_name' => 'single-product/product-attributes.php',
				'template' => 'template-parts/woocommerce/single-product-attributes.php'
			],
			[
				// My Account Items
				'template_name' => 'myaccount/navigation.php',
				'template' => 'template-parts/woocommerce/header-account-menu.php'
			],

		]);

		foreach ($custom_templates as $t) {
			$custom_templates[$t['template_name']] = $t;
		}

		if( ! isset($custom_templates[$template_name]) ){
			return $template;
		}

		$override = $custom_templates[$template_name];

		if( $custom_template = self::get_override($override['template']) ){
			return $custom_template;
		}

		return $template;
	}

	/**
	 * Load custom WooCommerce template part
	 *
	 * @since 1.0.0
	 */

	function override_template_part( $template, $slug, $name ){

		$templates = [];

		// Loop - Content Product
		if( $slug === 'content' && $name === 'product' ){
			$templates[] = 'template-parts/woocommerce/content-product.php';
		}

		$templates = apply_filters('reycore/woocommerce/wc_get_template_part', $templates, $template, $slug, $name);

		foreach ($templates as $tpl) {
			if( $custom_template = self::get_override($tpl) ){
				$template = $custom_template;
			}
		}

		return $template;
	}

}
