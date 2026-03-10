<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( $accordion_tabs = get_theme_mod('single__accordion_items', []) ):

	$layout = get_theme_mod('single__accordion_layout', 'acc');
	$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );
	$new_product_tabs = [];
	$has_reviews = false;

	foreach ($product_tabs as $key => $tab) {
		if( ! empty($tab['force_acc']) ){
			$accordion_tabs[]['item'] = $tab['force_acc'];
		}
	}

	foreach ($accordion_tabs as $key => $value) {

		if( 'reviews' === $value['item'] ){
			$has_reviews = true;
		}

		if( isset($product_tabs[ $value['item'] ]) ){

			$new_product_tabs[ $value['item'] ] = $product_tabs[ $value['item'] ];

			if( isset($value['title']) && !empty($value['title']) && empty($product_tabs[ $value['item'] ]['override_title']) ){
				$new_product_tabs[ $value['item'] ]['title'] = $value['title'];
			}

		}

		// Inject Short Description
		elseif( $value['item'] === 'short_desc' ){
			$new_product_tabs[ $value['item'] ] = [
				'title' => isset($value['title']) ? $value['title'] : esc_html__('Description', 'rey-core'),
				'callback' => ['\ReyCore\WooCommerce\Tags\Tabs', 'render_short_description'],
			];
		}

	}

	$classes = [
		'--layout-' . $layout
	];

	if( $has_reviews ){
		$classes[] = '--has-reviews';
	}
	?>

	<div class="rey-summaryAcc <?php echo implode(' ', $classes) ?>">

		<?php if( $layout === 'tabs' ): ?>
		<ul class="rey-summaryAcc-tabList" role="tablist">
			<?php
			$ti = 0;
			foreach ( $new_product_tabs as $key => $product_tab ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>_acctab <?php echo $ti === 0 ? '--active' : ''; ?>" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
					<a href="#acctab-<?php echo esc_attr( $key ); ?>" class="rey-summaryAcc-tabList-link <?php echo 'reviews' === $key ? '--reviews' : ''; ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
			<?php
			$ti++;
			endforeach; ?>
		</ul>
		<?php endif; ?>

		<?php
			$ci = 0;

			foreach ( $new_product_tabs as $key => $product_tab ) :
				$class = \ReyCore\Modules\PdpTabsAccordion\Base::determine_acc_tab_to_start_opened($ci) ? '--active' : '';

				if( 'reviews' === $key ){
					$class .= ' --reviews';
				}
				?>

			<?php if( $layout === 'acc' ): ?>
				<a class="rey-summaryAcc-accItem <?php echo $class; ?>" href="#acctab-<?php echo esc_attr( $key ); ?>">
					<span><?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?></span>

					<?php
						if( 'reviews' === $key && ($product = wc_get_product()) ){
							$count = $product->get_review_count();
							$rating_average = $product->get_average_rating();
							echo '<div class="star-rating" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating_average ) ) . '">' . wc_get_star_rating_html( $rating_average, $count ) . '</div>';
						}

						if( apply_filters('reycore/woocommerce/single/accordions_icon/use_arrow', false) ){
							echo reycore__get_svg_icon(['id'=>'arrow', 'class'=>'__arrow']);
						}
						else {
							echo reycore__get_svg_icon(['id'=>'plus', 'class'=>'--closed']);
							echo reycore__get_svg_icon(['id'=>'minus', 'class'=>'--opened']);
						}
					 ?>
				</a>
			<?php endif; ?>

			<div class="rey-summaryAcc-item rey-summaryAcc-item--<?php echo esc_attr( $key ); ?> <?php echo $class; ?>" id="acctab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="acctab-title-<?php echo esc_attr( $key ); ?>">
				<div class="__inner">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				} ?>
				</div>
			</div>

		<?php
		$ci++;
		endforeach; ?>

	</div>

<?php endif; ?>
