<?php
namespace ReyCore\Compatibility\JetSmartFilter;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {
		add_action('wp_footer', [$this, 'scripts'], 20);
	}

	public function scripts() {
		?><script type="text/javascript">
			jQuery(document).on('jet-filter-content-rendered', function() {

				// gather a selectors list
				var gridSelectors = [
					'.rey-siteMain ul.products',
					'.elementor-widget-loop-grid ul.products',
					'.elementor-widget-woocommerce-products ul.products',
				];

				// find the product grid
				document.querySelectorAll(gridSelectors.join(',')).forEach(grid => {
					if( rey ){
						rey.hooks.doAction('product/loaded', grid.querySelectorAll('li.product') );
					}
				});

			});
		</script><?php
	}

}
