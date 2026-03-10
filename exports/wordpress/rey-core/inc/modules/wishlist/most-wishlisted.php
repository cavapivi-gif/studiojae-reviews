<?php
namespace ReyCore\Modules\Wishlist;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class MostWishlisted {

	const OPTION_KEY = 'rey_wishlist_counts';
	const BATCH_COUNT_TRANSIENT = 'rey_wishlist_batch_counts';

	public function __construct() {

		if( ! get_theme_mod('wishlist__analytics', false) ){
			return;
		}

		if( ! apply_filters('reycore/woocommerce/wishlist/most_wishlisted', true) ){
			return;
		}

		if( ! function_exists('as_next_scheduled_action') ){
			return;
		}

		// Schedule weekly wishlist scan if not already scheduled
		if ( ! as_next_scheduled_action('process_wishlist_batch_action')) {
			as_schedule_recurring_action(time(), WEEK_IN_SECONDS, 'process_wishlist_batch_action', [0, 100]);
		}

		add_action('process_wishlist_batch_action', [$this, 'process_wishlist_batch'], 10, 2);
		add_action('finalize_wishlist_aggregation', [$this, 'finalize_wishlist_aggregation']);
		add_filter('woocommerce_leaderboards', [$this, 'add_custom_leaderboard']);
		add_action('wp_ajax_rey_wishlist_rescan', [$this, 'ajax_reindex']);

		$position_hooks_map = self::hooks_map();
		$position_opt = get_theme_mod('wishlist__top_label_pos', 'top_left');
		add_action($position_hooks_map[ $position_opt ]['hook'], [$this, 'render_badge'], $position_hooks_map[ $position_opt ]['priority']);
	}

	function process_wishlist_batch($batch_index = 0, $batch_size = 100) {

		$offset = $batch_index * $batch_size;

		$user_query = new \WP_User_Query(array(
			'number' => $batch_size,
			'offset' => $offset,
			'fields' => 'ID',
		));

		$user_ids = $user_query->get_results();
		$wish_counts = get_transient(self::BATCH_COUNT_TRANSIENT) ?: [];

		foreach ($user_ids as $user_id) {
			$wishlist = get_user_meta($user_id, Base::get_cookie_key(), true);
			if ( ! empty($wishlist) && is_array($wishlist) ) {
				foreach ($wishlist as $product_id) {
					if (!isset($wish_counts[$product_id])) {
						$wish_counts[$product_id] = 0;
					}
					$wish_counts[$product_id]++;
				}
			}
		}

		set_transient(self::BATCH_COUNT_TRANSIENT, $wish_counts, DAY_IN_SECONDS);

		$total_users = $user_query->get_total();

		if ($offset + $batch_size < $total_users) {
			as_schedule_single_action(time() + 60, 'process_wishlist_batch_action', [$batch_index + 1, $batch_size]);
		} else {
			as_schedule_single_action(time() + 60, 'finalize_wishlist_aggregation');
		}
	}

	function finalize_wishlist_aggregation() {

		$wish_counts = get_transient(self::BATCH_COUNT_TRANSIENT);

		if (empty($wish_counts)) {
			return;
		}

		$wishlist_counts = get_option(self::OPTION_KEY, []);

		// Merge batch counts into the main counts
		foreach ($wish_counts as $product_id => $count) {
			if (!isset($wishlist_counts[$product_id])) {
				$wishlist_counts[$product_id] = 0;
			}
			$wishlist_counts[$product_id] += $count;
		}

		update_option(self::OPTION_KEY, $wishlist_counts, 'no');

		delete_transient(self::BATCH_COUNT_TRANSIENT);
	}

	public static function get_most_wishlisted_products($limit = 10) {

		static $most;

		if (! is_null($most)) {
			return $most;
		}

		$wishlist_counts = get_option(self::OPTION_KEY, []);
		$most = [];

		if (! empty($wishlist_counts)) {
			arsort($wishlist_counts);
			$most = array_slice($wishlist_counts, 0, $limit, true);
		}

		return $most;

	}

	public static function hooks_map(){
		return [
			'top_left' => [
				'hook'     => 'reycore/loop_inside_thumbnail/top-left',
				'priority' => 10,
			],
			'top_right' => [
				'hook'     => 'reycore/loop_inside_thumbnail/top-right',
				'priority' => 10,
			],
			'bottom_left' => [
				'hook'     => 'reycore/loop_inside_thumbnail/bottom-left',
				'priority' => 10,
			],
			'bottom_right' => [
				'hook'     => 'reycore/loop_inside_thumbnail/bottom-right',
				'priority' => 10,
			],
			'before_title' => [
				'hook'     => 'woocommerce_before_shop_loop_item_title',
				'priority' => 20,
			],
			'after_title' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 10,
			],
			'after_content' => [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 905,
			],
		];
	}

	public function render_badge(){
		global $product;

		if( ! $product ){
			return;
		}

		if( true !== get_theme_mod('wishlist__top_label', false ) ){
			return;
		}

		if( ! in_array( $product->get_id(), array_keys(self::get_most_wishlisted_products()) ) ){
			return;
		}

		printf("<div class='rey-topFav'>%s</div>", get_theme_mod('wishlist__top_label_text', esc_html__('Top Favorite', 'rey-core')) );

	}

	public function add_custom_leaderboard($leaderboards) {

		if( ! apply_filters('reycore/wishlist/analytics', true) ){
			return $leaderboards;
		}

		$leaderboards[] = [
			'id'      => 'rey_top_wishlist',
			'label'  => __('Top Wishlist', 'rey-core'),
			'headers' => [
				[
					'label' => __( 'Product', 'woocommerce' ),
				],
				[
					'label' => __( 'Count', 'rey-core' ),
				],
			],
			'rows'    => $this->get_custom_leaderboard_items(),
		];

		return $leaderboards;
	}

	private function get_custom_leaderboard_items() {
		$products_data = self::get_most_wishlisted_products();

		$rows = [];

		foreach ( $products_data as $product_id => $count ) {

			if( ! ($product = wc_get_product( $product_id )) ){
				continue;
			}

			$product_url  = get_permalink( $product_id );
			$product_name = $product->get_title();

			$rows[] = [
				[
					'display' => "<a href='{$product_url}'>{$product_name}</a>",
					'value'   => $product_name,
				],
				[
					'display' => wc_admin_number_format( $count ),
					'value'   => $count,
				],
			];
		}

		return $rows;
	}

	function ajax_reindex() {
		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
		}

		if( ! current_user_can('manage_options') ){
			wp_send_json_error();
		}

		if( ! as_enqueue_async_action('process_wishlist_batch_action', [0, 100]) ){
			wp_send_json_error();
		}

		wp_send_json_success('Scheduled!');
	}
}
