<?php
namespace ReyCore\Compatibility\WoocommerceGiftCards;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{

		add_action('wp_footer', [$this, 'handle_checkout'], 100);

	}

	public function handle_checkout(){
		if( ! is_checkout() ){
			return;
		} ?>

		<script>(function($){

			$( document.body ).on( 'init_checkout', function(){

				var $form = $('form.rey-checkoutPage-form');

				if( ! $form.length ){
					return;
				}

				var $review = $('.rey-checkoutPage-review');
				var $checkbox = $('#use_gift_card_balance', $review);

				if( ! $checkbox.length ){
					return;
				}

				var input = $('<input type="hidden" id="use_gift_card_balance_clone" name="use_gift_card_balance" value="'+ ($checkbox.prop('checked') ? 'on' : 'off') +'">').appendTo( $form );

				$review.on( 'change', '#use_gift_card_balance', function(){
					input.val( $checkbox.prop('checked') ? 'on' : 'off' );
					$( document.body ).trigger( 'update_checkout' );
				} );

			} );

		})(jQuery);</script>
		<style>
			.woocommerce-checkout-review-order-table tr.update_totals_on_change td,
			.woocommerce-checkout-review-order-table tr.gift-card--balance td { margin: 0 !important; }
			.woocommerce-checkout-review-order-table tr.update_totals_on_change th:empty { display: none !important; }
		</style>
		<?php
	}

}
