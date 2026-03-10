<?php
namespace ReyCore\Modules\ProductTeasers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-product-teasers';

	public $settings;

	public $epro_shop_page_fix = false;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'woo_init']);
	}

	public function woo_init(){

		add_action( 'wp', [$this, 'init']);

		new Customizer();

	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! apply_filters('reycore/woocommerce/catalog/teasers/enable', is_main_query()) ){
			return;
		}

		$this->settings = apply_filters('reycore/woocommerce/catalog/teasers/settings', [
			'count_previous_indexes' => true
		]);

		$loop_teaser = $this->option();

		if( empty($loop_teaser) ){
			return;
		}

		add_action( 'elementor/theme/before_do_archive', [$this, 'epro_location_before']);
		add_action( 'reycore/templates/tpl/before_render', [$this, 'epro_location_before']);

		foreach ($loop_teaser as $key => $teaser) {

			if( !(isset($teaser['gs']) && ($gs = $teaser['gs'])) ){
				continue;
			}

			$should_display_shop_page = is_shop() && $teaser['shop_page'] === 'yes';
			$should_display_tags_page = is_product_tag() && isset($teaser['tags_page']) && $teaser['tags_page'] === 'yes';
			$should_display_category_page = is_product_category( apply_filters('reycore/translate_ids', $teaser['categories'], 'product_cat') );

			if( ! apply_filters('reycore/woocommerce/catalog/teasers/display_check',
				$should_display_shop_page || $should_display_category_page || $should_display_tags_page ,
				$teaser ) ){
				continue;
			}

			$cols = wc_get_loop_prop('columns');
			$position = $teaser['position'] ? $teaser['position'] : 'start';
			$size = $teaser['size'] ? $teaser['size'] : 2;
			$row = $teaser['row'] ? $teaser['row'] : 1;

			if( $position === 'start' ){
				$index = ($row * $cols) - $cols;
			}
			elseif( $position === 'end' ){
				$index = ($row * $cols) - $size;
			}

			$prev_indexes = 0;

			if( $this->settings['count_previous_indexes'] && isset($GLOBALS['reycore_teasers']) ){
				if( $prev_indexes = wp_list_pluck($GLOBALS['reycore_teasers'], 'size') ){
					$prev_indexes = array_sum($prev_indexes);
				}
			}

			$GLOBALS['reycore_teasers'][$key] = [
				'index' => $index - $prev_indexes,
				'repeat' => $teaser['repeat'] === 'yes',
				'gs' => $gs,
				'size' => $size,
			];

			add_action( 'reycore/woocommerce/content_product/before', [$this, '_before'], 10);
		}

	}


	function _before( $product ){

		if( ! (isset($GLOBALS['reycore_teasers']) && ($teasers = $GLOBALS['reycore_teasers'])) ){
			return;
		}

		if( $this->epro_shop_page_fix && isset($GLOBALS['wp_query']->posts) && isset( $GLOBALS['wp_query']->current_post ) ){
			$GLOBALS['wp_query']->next_post();
		}

		foreach ($teasers as $key => $teaser) {

			if( ! (isset($GLOBALS['wp_query']) && $GLOBALS['wp_query']->current_post === $teaser['index']) ){
				continue;
			}

			if( ! $teaser['repeat'] && wc_get_loop_prop('current_page') > 1 ){
				continue;
			}

			if( isset($teaser['gs']) && ($gs = $teaser['gs']) ){

				if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
					continue;
				}

				reycore_assets()->defer_page_styles('elementor-post-' . $gs);

				if( ! ($gs_html = \ReyCore\Elementor\GlobalSections::do_section( $gs, true, true )) ){
					continue;
				}

				echo '<li ';

					wc_product_class( '--teaser' );

					if( $teaser['size'] > 1 ){

						if( ($layout_cols = absint(wc_get_loop_prop('columns'))) && $layout_cols < $teaser['size'] ){
							$teaser['size'] = absint($layout_cols);
						}

						printf( ' data-colspan="%d" ', $teaser['size']);
					}

				echo '>';

					echo $gs_html;

					echo reycore__popover([
						'content' => sprintf(_x('<p>This is a Generic Global Section set as <b>Product List Teaser</b>. You can either <a href="%1$s" target="_blank"><u>edit it with Elementor</u></a>, or edit its settings in <a href="%2$s" target="_blank"><u>Customizer > WooCommerce > Grid Components > Product list teasers</u></a>".</p>', 'Various admin. texts', 'rey-core'),
							admin_url( sprintf('post.php?post=%d&action=elementor', $gs) ),
							add_query_arg([
								'autofocus[section]' => 'woo-catalog-grid-components'
								], admin_url( 'customize.php' )
							)
						),
						'admin' => true,
						'class' => '--gs-popover'
					]);

				echo '</li>';

			}

		}

		reycore_assets()->add_styles('rey-wc-tag-stretch');
		// reycore_assets()->add_scripts('reycore-wc-loop-stretch');

	}

	function epro_location_before( $instance ){
		if( class_exists('\WooCommerce') && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) ){
			$this->epro_shop_page_fix = true;
		}
	}

	public function option(){
		return get_theme_mod('loop_teasers', []);
	}

	public function is_enabled() {
		return ! empty( $this->option() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Teasers in Product Catalog', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to inject global sections inside the catalog, in specific locations.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
			'help'        => reycore__support_url('kb/product-teasers'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
