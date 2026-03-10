<?php
namespace ReyCore\Compatibility\FilterEverything;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	// https://wordpress.org/plugins/filter-everything/
	// https://filtereverything.pro/

	public static $container = '.rey-siteMain';

	public function __construct() {

		add_action('wp_footer', [$this, 'scripts'], 20);
        add_filter('wpc_posts_containers', [$this, 'set_posts_container'], 100);
        add_filter('reycore/woocommerce/loop/can_add_filter_panel_sidebar', '__return_true');
        add_filter('reycore/ajaxfilters/pre_widgets_exist', '__return_true');
        add_filter('reycore/ajaxfilters/before_products_holder/load_scripts', '__return_true');

		do_action('reycore/compatibility/filtereverything', $this);

	}

	public function set_posts_container( $theme_posts_container ){

		$theme_posts_container['rey'] = self::$container;

		if( empty($theme_posts_container['default']) ){
			$theme_posts_container['default'] = self::$container;
		}

		return $theme_posts_container;
	}

	public function scripts() {
		?><script type="text/javascript">

			// The `ready` event inside filter-everything.js on ajax success, is too generic
			// and fires on page load.
			// We'll be using a custom event that's fired by FE, for "a3" (some plugin).

			jQuery(window).on( 'lazyshow', function() {

				if( ! rey ){
					return;
				}

				// gather a selectors list
				var gridSelectors = [
					'.rey-siteMain ul.products',
					'.elementor-widget-loop-grid ul.products',
					'.elementor-widget-woocommerce-products ul.products',
				];

				// find the product grid
				document.querySelectorAll(gridSelectors.join(',')).forEach(grid => {
					rey.hooks.doAction('product/loaded', grid.querySelectorAll('li.product') );
				});

				var gridContainer = "<?php echo esc_html(self::$container); ?>";
				if( typeof wpcFilterFront != 'undefined' && wpcFilterFront.wpcPostContainers['default'] ){
					gridContainer = wpcFilterFront.wpcPostContainers['default'];
				}

				rey.hooks.doAction('ajaxfilters/finished', document.querySelector(gridContainer) );

			});

		</script><?php
	}

}
