<?php
namespace ReyCore\Compatibility\FacetWP;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {
		add_action('wp_footer', [$this, 'scripts'], 20);
	}

	public function scripts() {
		?><script type="text/javascript">
			document.addEventListener('facetwp-loaded', function() {

				// gather a selectors list
				var gridSelectors = [
					'.rey-siteMain ul.products',
					'.facetwp-template.elementor-widget-loop-grid ul.products',
					'.facetwp-template.elementor-widget-woocommerce-products ul.products',
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
