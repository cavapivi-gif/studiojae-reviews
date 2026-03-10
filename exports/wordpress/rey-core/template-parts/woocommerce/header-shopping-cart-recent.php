<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$thumb_classes = '';

if( class_exists('\ReyCore\WooCommerce\Loop') && \ReyCore\WooCommerce\Loop::is_custom_image_height() ){
	$thumb_classes .= ' --customImageContainerHeight';
} ?>

<script type="text/html" id="tmpl-reyCartRecent">

<# var items = data.items; #>
<# if( items.length ){ #>
<div data-ss-container>
	<div class="rey-cartRecent-items">
		<# for (var i = 0; i < items.length; i++) { #>
		<div class="rey-cartRecent-item __cart-product" data-id="{{items[i].id}}">
			<div class="rey-cartRecent-itemThumb <?php echo esc_attr($thumb_classes) ?>">
				<a href="{{items[i].link}}" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
					{{{items[i].image}}}
				</a>
			</div>
			<div class="rey-cartRecent-itemContent">
				<?php do_action('reycore/woocommerce/cart/cart_recent/before'); ?>
				<<?php echo reycore_wc__minicart_product_title_tag() ?> class="rey-cartRecent-itemTitle"><a href="{{items[i].link}}">{{{items[i].title}}}</a></<?php echo reycore_wc__minicart_product_title_tag() ?>>
				<span class="price rey-loopPrice">{{{items[i].price}}}</span>
				<div class="rey-cartRecent-itemButtons">
					{{{items[i].button}}}
					<?php do_action('reycore/woocommerce/cart/cart_recent/after'); ?>
				</div>
			</div>
		</div>
		<# } #>
	</div>
</div>
<# } else { #>
	<?php printf('<p class="woocommerce-mini-cart__empty-message">%s</p>', esc_html__('No products in the list.', 'rey-core')); ?>
<# } #>

</script>
