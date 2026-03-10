<?php
namespace ReyCore\Compatibility\BerocketWoocommerceFilters;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct() {
		add_action('wp_footer', [$this, 'compatibility_ajax_load_more'], 20);
	}

	public function compatibility_ajax_load_more() {
		?>
		<script>
			(function($){
				$(document).on('berocket_ajax_filtering_end', function(e){

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
			})(jQuery);
		</script>
		<?php
	}

}
