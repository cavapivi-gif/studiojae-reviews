<?php
namespace ReyCore\Modules\Compare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_ids = Base::get_ids();

if( empty($product_ids) ){
	return;
}

$products_data = [];
$product_ids = array_reverse($product_ids);

$product_attributes = [];

$p_attribute_names = [
	'weight' => __( 'Weight', 'rey-core' ),
	'dimensions' => __( 'Dimensions', 'rey-core' ),
];

$stock_status = [
	'instock' => esc_html__( 'In Stock', 'rey-core' ),
	'outofstock' => esc_html__( 'Out of stock', 'rey-core' ),
	'onbackorder' => esc_html__( 'Available on backorder', 'rey-core' ),
];

$excludes = get_theme_mod('compare__excludes', []);

foreach ( $product_ids as $product_id ) :

	$product = wc_get_product($product_id);

	// Ensure visibility.
	if ( empty( $product ) || false === wc_get_loop_product_visibility( $product->get_id() ) || ! $product->is_visible() ) {
		continue;
	}

	$p_data =  [
		'id'          => $product->get_id(),
		'title'       => $product->get_title(),
		'image'       => wp_get_attachment_image($product->get_image_id(), 'woocommerce_thumbnail'),
		'description' => $product->get_short_description(),
		'sku'         => $product->get_sku(),
		'stock'       => $stock_status [ $product->get_stock_status() ],
		'price'       => apply_filters('reycore/woocommerce/compare/price', $product->get_price_html(), $product),
	];

	/**
	 * Add to cart button
	 */
	$atc_args = [
		'quantity' => 1,
		'class' => implode(' ', array_filter([
			'btn btn-primary',
			'product_type_' . $product->get_type(),
			$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			$product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
		])),
		'attributes' => [
			'data-product_id' => $p_data['id'],
			'data-product_sku' => $p_data['sku'],
			'aria-label' => strip_tags( $product->add_to_cart_description() ),
			'rel' => 'nofollow',
		]
	];

	$cart_layout = get_theme_mod('header_cart_layout', 'bag');
	$cart_layout = 'disabled'; // temp
	$cart_icon = !($cart_layout === 'disabled' || $cart_layout === 'text') ? reycore__get_svg_icon([ 'id'=> $cart_layout ]) : '';
	$add_to_cart_contents = sprintf('<span>%s</span> %s', $product->add_to_cart_text(), $cart_icon);

	reycore_assets()->add_styles('rey-buttons');

	$p_data['add-to-cart'] = sprintf(
		'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $atc_args['quantity'] ) ? $atc_args['quantity'] : 1 ),
		esc_attr( isset( $atc_args['class'] ) ? $atc_args['class'] : 'btn' ),
		isset( $atc_args['attributes'] ) ? reycore__implode_html_attributes( $atc_args['attributes'] ) : '',
		apply_filters('reycore/woocommerce/loop/add_to_cart/content', $add_to_cart_contents, $product)
	);

	/**
	 * Rating
	 */
	if ( wc_review_ratings_enabled() ) {
		$rating_count   = $product->get_rating_count();
		$review_count   = $product->get_review_count();
		$rating_average = $product->get_average_rating();
		$p_data['rating'] = wc_get_rating_html( $rating_average, $rating_count );
	}

	$p_attribute = [];
	$p_attribute[ 'weight' ] = $product->get_weight();
	$p_attribute[ 'dimensions' ] = wc_format_dimensions( $product->get_dimensions(false) );

	foreach ( $product->get_attributes() as $attribute ) {

		$taxonomy = $attribute->get_name();

		if ( $attribute->is_taxonomy() ) {
			$terms = $attribute->get_visible() ? implode( ', ', wc_get_product_terms( $product->get_id(), $taxonomy, ['fields' => 'names'] ) ) : '';
			$p_attribute_names[$taxonomy] = wc_attribute_label($taxonomy);
		} else {
			$terms = implode( ', ', $attribute->get_options() );
			$p_attribute_names[$taxonomy] = $taxonomy;
		}

		$p_attribute[ $taxonomy ] = $terms;
	}

	foreach ($excludes as $exclude) {
		unset($p_data[$exclude]);
		unset($p_attribute[$exclude]);
	}

	$products_data[$product_id] = apply_filters('reycore/woocommerce/compare/product_data', $p_data, $product);
	$product_attributes[ $product_id ] = apply_filters('reycore/woocommerce/compare/product_attributes', $p_attribute, $product);
	$p_attribute_names = apply_filters('reycore/woocommerce/compare/product_attribute_names', $p_attribute_names);

endforeach; ?>

<div class="rey-comparePage-scroll">
	<table class="rey-comparePage-table" data-count="<?php echo count($products_data) ?>" style="--products-count: <?php echo count($products_data) ?>">
		<thead>
			<tr>
				<th class="__empty-cell">
					<?php echo Base::get_compare_icon(); ?>
				</th>
				<?php
				foreach ($products_data as $product_id => $product) :

					$product_title = $product['title'];
					$link['start'] = sprintf('<a href="%s" title="%s" class="__head">', esc_url(get_permalink($product_id)), esc_attr($product_title));
					$link['end'] = '</a>';
					?>

					<th data-id="<?php echo $product_id; ?>">

						<?php
						if(isset($product['image'])):
							echo $link['start'] . $product['image'] . $link['end'];
						endif; ?>

						<h4 class="__title">
							<?php echo $link['start'] . $product_title . $link['end']; ?>
						</h4>

						<?php if(isset($product['stock'])): ?>
						<p class="__head-stock"><span><?php echo $product['stock']; ?></span></p>
						<?php endif; ?>

						<?php if(isset($product['description'])): ?>
						<p class="__head-desc"><?php echo $product['description']; ?></p>
						<?php endif; ?>

						<?php if(isset($product['price'])): ?>
						<p class="__head-price"><?php echo $product['price']; ?></p>
						<?php endif; ?>

						<?php
						if(isset($product['rating'])):
							echo $product['rating'];
						endif; ?>

						<?php
						if(isset($product['add-to-cart'])):
							echo $product['add-to-cart'];
						endif; ?>

					</th>
				<?php
				endforeach; ?>
			</tr>
		</thead>
		<tbody>

			<?php
			foreach ($p_attribute_names as $key => $title) :

				if( ! empty($excludes) && in_array($key, $excludes, true) ){
					continue;
				} ?>

				<tr class="__field-<?php echo esc_attr($key); ?>">

					<td class="__field-title">
						<?php echo $title; ?>
					</td>

					<?php
					foreach ($product_attributes as $product_id => $attributes):
						$value = !empty($attributes[$key]) ? $attributes[$key] : '&mdash;';
						printf('<td data-id="%d">%s</td>', $product_id, $value );
					endforeach; ?>

				</tr>

			<?php
			endforeach; ?>

			<tr class="__remove-buttons">
				<td class="__empty-cell"></td>
				<?php
				$remove_btn_text = sprintf('%s <span>%s</span>',
					reycore__get_svg_icon(['id' => 'close']),
					esc_html__('REMOVE PRODUCT', 'rey-core')
				);
				foreach ($products_data as $product_id => $product) :
					printf('<td data-id="%2$d"><a href="#" title="%1$s" class="rey-compare-removeBtn" data-id="%2$d">%3$s</a></td>',
						$product['title'],
						$product['id'],
						$remove_btn_text
					);
				endforeach; ?>
			</tr>

		</tbody>
	</table>
</div>

<p class="rey-comparePage-tip --dnone-lg">
	<?php echo Base::get_texts('mobile_tip') ?>
</p>
