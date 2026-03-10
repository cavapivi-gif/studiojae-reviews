<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Reviews {

	public function __construct() {
		add_action( 'init', [$this, 'init']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );

	}

	function init(){

		add_filter( 'comments_template', [$this, 'comments_template_loader'] );
		add_filter( 'reycore/woocommerce/single/reviews_btn', [$this, 'reviews_btn']);
		add_action( 'woocommerce_review_before_comment_meta', [$this, 'handle_stars_in_review'], 5);
		add_filter( 'get_avatar', [$this, 'hide_avatar']);
		add_filter( 'reycore/woocommerce/single/reviews_classes', [$this, 'reviews_classes']);

		// add_action( 'reycore/woocommerce/reviews/before', [$this, 'before_reviews']);
		// add_action( 'reycore/woocommerce/reviews/after', [$this, 'after_reviews']);
	}

	function reviews_btn($classes){

		if( get_theme_mod('single_reviews_start_opened', false) ){
			$classes[] = '--toggled';
		}

		return $classes;
	}

	function reviews_classes( $classes ){

		$classes['style'] = '--style-' . esc_attr($this->get_review_layout());

		if( get_theme_mod('single_reviews_ajax', true) ){
			$classes['is_ajax'] = '--ajax';
		}

		// if( get_theme_mod('single_reviews_start_opened', false) ){
		// 	$classes['is_visible'] = '--visible';
		// }

		return $classes;
	}

	function get_review_layout(){
		return get_theme_mod('single_reviews_layout', 'default');
	}


	function hide_avatar($avatar){

		if( 'minimal' === $this->get_review_layout() ){
			return '';
		}

		if( ! get_theme_mod('single_reviews_avatar', true) ){
			return '';
		}

		return $avatar;
	}

	function before_review_text(){

		echo '<div class="rey-descWrap">';

		woocommerce_review_display_rating();

		global $comment;

		$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

		if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) {
			echo '<span class="woocommerce-review__verified verified">';
			echo apply_filters('reycore/woocommerce/single/reviews/verified_owner', '(' . esc_attr__( 'verified owner', 'woocommerce' ) . ')', $comment);
			echo '</span>';
		}
	}

	function after_review_text(){
		echo '</div>';
	}

	function handle_stars_in_review(){

		if( 'minimal' !== $this->get_review_layout() ){
			return;
		}

		remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10);
		add_action( 'woocommerce_review_comment_text', [$this, 'before_review_text'], 9);
		add_action( 'woocommerce_review_comment_text', [$this, 'after_review_text'], 11);

	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'load_more_reviews', [$this, 'ajax__load_more_reviews'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
		$ajax_manager->register_ajax_action( 'submit_review', [$this, 'ajax__submit_review'], [
			'auth'   => 3,
		] );
	}

	public function ajax__load_more_reviews( $action_data ){

		if( ! (isset($action_data['qid']) && $product_id = absint($action_data['qid'])) ){
			return ['errors' => 'Missing PID'];
		}

		if( ! isset($action_data['page']) ){
			return ['errors' => 'Missing Page'];
		}

		add_filter( 'option_thread_comments', '__return_false' );

		$order = 'newest';

		if( isset($action_data['order']) && $custom_order = reycore__clean($action_data['order']) ){
			$order = $custom_order;
		}

		$limit = get_theme_mod('single_reviews_ajax_limit', 5);
		$page = absint($action_data['page']);

		add_filter('option_comments_per_page', function() use ($limit){
			return $limit;
		});

		add_action('pre_get_comments', function($query) use ($limit, $page, $product_id, $order){

			$query->query_vars['post_id'] = apply_filters('reycore/translate_ids', $product_id, 'product');
			$query->query_vars['number'] = $limit;
			$query->query_vars['offset'] = $limit * $page;

			if( 'newest' === $order ){
				$query->query_vars['order'] = 'DESC';
			}
			elseif( 'oldest' === $order ){
				$query->query_vars['order'] = 'ASC';
			}
			elseif( 'highest' === $order ){
				$query->query_vars['orderby'] = 'meta_value_num';
				$query->query_vars['meta_key'] = 'rating';
				$query->query_vars['order'] = 'DESC';
			}
			elseif( 'lowest' === $order ){
				$query->query_vars['orderby'] = 'meta_value_num';
				$query->query_vars['meta_key'] = 'rating';
				$query->query_vars['order'] = 'ASC';
			}

		});

		ob_start();

			wp_list_comments(
				apply_filters( 'woocommerce_product_review_list_args', [
					'callback'          => 'woocommerce_comments',
					'per_page'          => $limit,
					'max_depth'         => 0,
					'page'              => 0,
				] ),
				apply_filters( 'reycore/woocommerce/single/reviews/list_comments', null)
			);

		$data = ob_get_clean();

		if( empty($data) ){
			return ['errors' => 'Empty content.'];
		}

		return $data;
	}

	public static function get_woo_notice($message, $type = 'error'){
		ob_start();

		wc_get_template(
			"notices/{$type}.php", [
				'notices'  => [
					[
						'notice' => $message
					],
				],
			]
		);

		return ob_get_clean();
	}

	public function ajax__submit_review(){

		$comment = wp_handle_comment_submission( reycore__clean( $_POST ) );

		if ( is_wp_error( $comment ) ) {
			$data = (int) $comment->get_error_data();
			if ( ! empty( $data ) ) {
				return [
					'errors' => self::get_woo_notice($comment->get_error_message()),
				];
			}
		}

		$user            = wp_get_current_user();
		$cookies_consent = ( isset( $_POST['wp-comment-cookies-consent'] ) );

		/**
		 * Perform other actions when comment cookies are set.
		 *
		 * @since 3.4.0
		 * @since 4.9.6 The `$cookies_consent` parameter was added.
		 *
		 * @param WP_Comment $comment         Comment object.
		 * @param WP_User    $user            Comment author's user object. The user may not exist.
		 * @param bool       $cookies_consent Comment author's consent to store cookies.
		 */
		do_action( 'set_comment_cookies', $comment, $user, $cookies_consent );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['comment'] = $comment;

		ob_start();

		wc_get_template(
			'single-product/review.php',
			[
				'comment' => $comment,
				'args'    => [],
				'depth'   => 1,
			]
		);

		return ob_get_clean();
	}

	function comments_template_loader( $template ){

		if( ! apply_filters('reycore/woocommerce/single/reviews_template', true) ){
			return $template;
		}

		if ( get_post_type() !== 'product' ) {
			return $template;
		}

		$fn = basename($template);

		// check if child theme template exists (from WooCommerce)
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . WC()->template_path() . $fn ) ) {
			return $template;
		}

		$check_dirs = array(
			trailingslashit( STYLESHEETPATH ),
			trailingslashit( TEMPLATEPATH ),
			trailingslashit( REY_CORE_DIR ),
		);

		foreach ( $check_dirs as $dir ) {

			if ( file_exists( $dir . 'template-parts/woocommerce/' . $fn ) ) {

				reycore_assets()->add_styles('rey-wc-product-reviews');

				return $dir . 'template-parts/woocommerce/' . $fn;
			}
		}

	}
}
