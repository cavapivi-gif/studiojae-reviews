<?php
namespace ReyCore\Compatibility\UniCpo;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {
		add_action('wp_footer', [$this, 'scripts'], 20);
	}

	public function scripts() {

		if (!(function_exists('is_product') && is_product())) {
			return;
		} ?>

		<script>
			(function($){
				$(document.body).on("uni_cpo_options_product_image_replaced_event", function(event, data, image){
					$(".woocommerce-product-gallery__mobile .woocommerce-product-gallery__mobile--0").attr("src", data.imgLarge_image);
					$(".woocommerce-product-gallery__mobile-thumbs .woocommerce-product-gallery__mobile--0").attr("src", data.imgLarge_image);
				});
			})(jQuery);
		</script>

		<?php
	}

}
