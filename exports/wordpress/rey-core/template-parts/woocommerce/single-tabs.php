<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/single-tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action('reycore/woocommerce/product/tabs/before', 'tabs');

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );

// Remove blocks that should go into Accordion/Tabs
if( $accordion_tabs = get_theme_mod('single__accordion_items', []) ){
	foreach ($accordion_tabs as $key => $value) {
		unset( $product_tabs[ $value['item'] ] );
	}
}

if ( ! empty( $product_tabs ) ) :

	reycore_assets()->add_styles('rey-wc-product-tabs'); ?>

	<div class="woocommerce-tabs wc-tabs-wrapper" id="wc-tabs-wrapper">

		<div class="rey-wcTabs-wrapper">
			<ul class="tabs wc-tabs" role="tablist">
				<?php foreach ( $product_tabs as $key => $product_tab ) :
					if( ! empty($tab['force_acc']) ){
						continue;
					} ?>
					<li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
						<a href="#tab-<?php echo esc_attr( $key ); ?>">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php foreach ( $product_tabs as $key => $product_tab ) :
			if( ! empty($tab['force_acc']) ){
				continue;
			} ?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>
<?php endif;

do_action('reycore/woocommerce/product/tabs/after', 'tabs');
