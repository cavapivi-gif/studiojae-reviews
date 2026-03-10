<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/rey-core/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$reviews_title_tag = apply_filters('reycore/woocommerce/reviews/title_tag', 'h2');

do_action('reycore/woocommerce/reviews/before'); ?>

<div id="reviews" class="<?php echo implode(' ', array_map('esc_attr', apply_filters('reycore/woocommerce/single/reviews_classes', ['woocommerce-Reviews']))) ?>">
	<div id="comments">

		<<?php echo esc_html($reviews_title_tag) ?> class="woocommerce-Reviews-title">
			<?php

			$count = $product->get_review_count();
			$rating_count = $product->get_rating_count();
			$rating_average = $product->get_average_rating();

			printf('<span class="rey-reviewTop-title %s">', (!$count ? '--empty' : ''));

			if ( $count && wc_review_ratings_enabled() ) {

				echo sprintf('<div class="rey-reviewTop">%s <span><strong>%s</strong>/5</span></div>', wc_get_rating_html( $rating_average, $count ), $rating_average);

				/* translators: 1: reviews count 2: product name */
				$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );

				echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ); // WPCS: XSS ok.

			} else {
				esc_html_e( 'Reviews', 'woocommerce' );
			}

			echo '</span>';

			if ( $count && wc_review_ratings_enabled() ) {
				printf('<a href="#review_form" class="rey-reviewTop-add">%s</a>', esc_html__( 'Add a review', 'woocommerce' ));
			} ?>

		</<?php echo esc_html($reviews_title_tag) ?>>

		<?php
			if ( $count && wc_review_ratings_enabled() && get_theme_mod('single_reviews_info', true) ) {
				?>
				<div class="rey-reviewsOverall"><?php

				$star_counts = [];

				for ( $i = 1; $i <= 5; $i ++ ) {
					$star_counts[ $i ] = get_comments([
						'post_id'     => $product->get_id(),
						'count'       => true,
						'status'      => 'approve',
						'post_status' => 'publish',
						'post_type'   => 'product',
						'parent'      => 0,
						'meta_query'  => [
							'relation' => 'AND',
							[
								'key'     => 'rating',
								'value'   => $i,
								'compare' => '='
							]
						]
					]);
				}

				for ( $i = 5; $i > 0; $i -- ) {

					$percent = 0;

					if ( $count > 0 ) {
						$percent = ( 100 * ( $star_counts[ $i ] / $count ) );
					} ?>

					<div class="rey-reviewsOverall-row">
						<div class="rey-reviewsOverall-number"><?php echo $i ?></div>
						<div class="rey-reviewsOverall-star"><?php echo wc_get_rating_html( $i ) ?></div>
						<div class="rey-reviewsOverall-percentCount"><?php echo round( $percent ) ?>%</div>
						<div class="rey-reviewsOverall-percent"><span class="rey-reviewsOverall-percentLevel" style="width:<?php echo $percent ?>%;"></span></div>
						<div class="rey-reviewsOverall-ratingsCount"><?php echo $star_counts[ $i ]; ?></div>
					</div>

				<?php } ?>
				</div><?php
			}
		?>

		<?php if ( have_comments() && $count ) :

			reycore_assets()->add_styles('rey-buttons'); ?>

			<?php if( get_theme_mod('single_reviews_ajax', true) ): ?>

				<ul class="rey-reviewSort">
					<li><?php echo esc_html__('Sort:', 'rey-core') ?></li>
					<li class="--active" data-key="newest"><span><?php echo esc_html__('Newest', 'rey-core') ?></span></li>
					<li data-key="oldest"><span><?php echo esc_html__('Oldest', 'rey-core') ?></span></li>
					<li data-key="highest"><span><?php echo esc_html__('Highest ratings', 'rey-core') ?></span></li>
					<li data-key="lowest"><span><?php echo esc_html__('Lowest ratings', 'rey-core') ?></span></li>
				</ul>

				<ol class="commentlist">
					<li class="__loader"><div class="rey-lineLoader"></div></li>
				</ol>

				<div class="rey-ajaxRatings-buttons">

					<?php
					$total_pages = ceil( $count / absint(get_theme_mod('single_reviews_ajax_limit', 5)) ); ?>

					<button class="btn btn-secondary rey-ajaxRatings-btn --disabled" data-config='<?php echo wp_json_encode([
						'qid' => get_the_ID(),
						'total' => $total_pages
					]) ?>'>
						<span class="rey-ajaxRatings-btnText">
							<?php echo esc_html__('Show more reviews', 'rey-core') ?>
							<span class="__count" data-total="<?php echo esc_attr($total_pages) ?>" data-current="1">/</span>
						</span>
						<div class="rey-lineLoader"></div>
					</button>

					<button class="btn btn-primary rey-ajaxRatings-addBtn">
						<?php echo esc_html__('Add a review', 'woocommerce') ?>
					</button>

				</div>

			<?php else: ?>

				<ol class="commentlist">
					<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [
						'callback' => 'woocommerce_comments',
					] ) ); ?>
				</ol>

				<?php
				if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
					echo '<nav class="woocommerce-pagination">';
					paginate_comments_links(
						apply_filters(
							'woocommerce_comment_pagination_args',
							array(
								'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
								'next_text' => is_rtl() ? '&larr;' : '&rarr;',
								'type'      => 'list',
							)
						)
					);
					echo '</nav>';
				endif;
			endif;
			?>
		<?php else : ?>

			<div class="woocommerce-noreviewsWrapper">
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
				<button class="btn btn-primary rey-ajaxRatings-addBtn">
					<?php echo esc_html__('Add a review', 'woocommerce') ?>
				</button>
			</div>

		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
					'class_submit'        => 'submit button',
					'submit_button'       => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'    => __( 'Name', 'woocommerce' ),
						'type'     => 'text',
						'value'    => $commenter['comment_author'],
						'required' => $name_email_required,
					),
					'email'  => array(
						'label'    => __( 'Email', 'woocommerce' ),
						'type'     => 'email',
						'value'    => $commenter['comment_author_email'],
						'required' => $name_email_required,
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
					</select></div>';
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>

<?php
do_action('reycore/woocommerce/reviews/after');
