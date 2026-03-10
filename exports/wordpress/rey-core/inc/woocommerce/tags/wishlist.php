<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Wishlist {

	public function __construct() {
		add_action( 'init', [$this, 'init']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public static function is_enabled(){
		return apply_filters('reycore/woocommerce/wishlist/tag/enabled', false);
	}

	public function init(){

		if( ! self::is_enabled() ){
			return;
		}

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/woocommerce/account_panel', [ $this, 'display_wishlist_in_account_panel']);
		add_action( 'reycore/woocommerce/loop/wishlist_button', [$this, 'render_button']);

	}

	public function display_wishlist_in_account_panel(){
		reycore__get_template_part('template-parts/woocommerce/header-account-tabs');
		reycore__get_template_part('template-parts/woocommerce/header-account-wishlist');
		$this->load_dependencies();
	}

	public function load_dependencies(){
		reycore_assets()->add_scripts(['rey-simple-scrollbar', 'reycore-tooltips']);
		reycore_assets()->add_styles(['rey-simple-scrollbar', 'reycore-tooltips']);
		add_action( 'wp_footer', [ $this, 'item_template']);
	}

	public static function catalog_default_position(){
		return apply_filters('reycore/woocommerce/wishlist/default_catalog_position', reycore_wc__get_setting('loop_wishlist_position'));
	}

	public static function get_wishlist_url(){
		return apply_filters('reycore/woocommerce/wishlist/url', false);
	}

	public static function get_wishlist_counter_html(){
		$html = '<span class="rey-wishlist-counter"><span class="rey-wishlist-counterNb"></span></span>';
		return apply_filters('reycore/woocommerce/wishlist/counter_html', $html);
	}

	public static function title(){
		return apply_filters('reycore/woocommerce/wishlist/title', esc_html__('WISHLIST', 'rey-core'));
	}

	function render_button($args = []){

		if( ! get_theme_mod('loop_wishlist_enable', true) ){
			return;
		}

		echo $this->get_button_html($args);
	}

	/**
	* Add the icon, wrapped in custom div
	*
	* @since 1.0.0
	*/
	public static function get_button_html($args = []){

		$args = wp_parse_args($args, [
			'position' => '',
			'before' => '',
			'after' => '',
		]);

		if( $args['position'] && self::catalog_default_position() !== $args['position'] ){
			return;
		}

		echo $args['before'];
		echo apply_filters('reycore/woocommerce/wishlist/button_html', '');
		echo $args['after'];
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['wishlist_type'] = 'native';
		$params['wishlist_empty_text'] = esc_html__('Your Wishlist is currently empty.', 'rey-core');
		return $params;
	}

	public function get_wishlist_products_query(){

		$items = apply_filters('reycore/woocommerce/wishlist/ids', []);

		if( empty($items) ){
			return [];
		}

		$args = [
			'limit'      => 16,
			'visibility' => 'catalog',
			'include'    => array_map('absint', $items),
			'orderby'    => 'post__in',
			'rey_wishlist' => true
		];

		return wc_get_products( $args );
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'get_wishlist_data', [$this, 'ajax__get_wishlist_data'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	/**
	 * Get wihslist
	 *
	 * @since   1.1.1
	 */
	public function ajax__get_wishlist_data( $adata )
	{
		$products = $this->get_wishlist_products_query();

		if( empty($products) ){
			return;
		}

		$data = [];

		foreach ($products as $key => $product) {

			if( isset($adata['ids']) && absint($adata['ids']) === 1 ){
				$data[] = $product->get_id();
				continue;
			}

			$product_data = [
				'id' => $product->get_id(),
				'type' => $product->get_type(),
				'title' => $product->get_title(),
				'image' => $product->get_image(),
				'stock_status' => $product->get_stock_status(),
				'url' => esc_url( get_the_permalink( $product->get_id() ) ),
				'price' => $product->get_price_html(),
				'is_purchasable' => $product->is_purchasable(),
				'is_in_stock' => $product->is_in_stock(),
				'supports_ajax_add_to_cart' => $product->supports('ajax_add_to_cart'),
				'sku' => $product->get_sku(),
				'add_to_cart_description' => strip_tags( $product->add_to_cart_description() ),
			];

			// ATC Button

			$atc_args = [
				'quantity' => 1,
				'class' => implode(' ', array_filter([
					'button',
					'product_type_' . $product_data['type'],
					$product_data['is_purchasable'] && $product_data['is_in_stock'] ? 'add_to_cart_button' : '',
					$product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
				])),
				'attributes' => [
					'data-product_id' => $product_data['id'],
					'data-product_sku' => $product_data['sku'],
					'aria-label' => $product_data['add_to_cart_description'],
					'rel' => 'nofollow',
				]
			];

			$cart_layout = get_theme_mod('header_cart_layout', 'bag');
			$cart_icon = !($cart_layout === 'disabled' || $cart_layout === 'text') ? reycore__get_svg_icon([ 'id'=> $cart_layout ]) : '';
			$add_to_cart_contents = sprintf('<span>%s</span> %s', $product->add_to_cart_text(), $cart_icon);

			$product_data['add_to_cart'] = sprintf(
				'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( isset( $atc_args['quantity'] ) ? $atc_args['quantity'] : 1 ),
				esc_attr( isset( $atc_args['class'] ) ? $atc_args['class'] : 'button' ),
				isset( $atc_args['attributes'] ) ? reycore__implode_html_attributes( $atc_args['attributes'] ) : '',
				$add_to_cart_contents
			);

			$product_data['add_to_cart_text'] = $product->add_to_cart_text();

			$data[] = $product_data;
		}

		return apply_filters('reycore/woocommerce/wishlist/get_data', $data);
	}

	/**
	 * template used for wishlist items.
	 * @since 1.0.0
	 */
	public function item_template(){

		$thumb_classes = '';

		if( \ReyCore\WooCommerce\Loop::is_custom_image_height() ){
			$thumb_classes .= ' --customImageContainerHeight';
		}

		$remove = sprintf('<a class="rey-wishlistItem-remove" data-rey-tooltip="%1$s" {{{ttFixed}}} data-rey-tooltip-source="wishlist"  href="#" data-id="{{data.ob[i].id}}" aria-label="%1$s">%2$s</a>',
			get_theme_mod('wishlist__texts_rm',
			esc_html__('Remove from wishlist', 'rey-core')),
			reycore__get_svg_icon(['id' => 'close'])
		);

		$cart_button = '';
		if( ! reycore_wc__is_catalog()){
			$cart_button .= '<# if( typeof data.ob[i].add_to_cart !== "undefined" ){ #>';
			$cart_button .= '<div class="rey-wishlistItem-atc --no-var-popup" data-rey-tooltip="{{data.ob[i].add_to_cart_text}}" data-rey-tooltip-source="wishlist-atc" {{{ttFixed}}}>{{{data.ob[i].add_to_cart}}}</div>';
			$cart_button .= '<# } #>';
		} ?>

		<script type="text/html" id="tmpl-reyWishlistItem">
			<div class="rey-wishlist-list">
				<# for (var i = 0; i < data.num; i++) { #>
					<# var ttFixed = typeof data.fixedContainer !== "undefined" && data.fixedContainer ? "data-fx-tooltip" : ""; #>
					<div class="rey-wishlistItem" style="transition-delay: {{i * 0.07}}s ">
						<div class="rey-wishlistItem-thumbnail <?php echo esc_attr($thumb_classes) ?>">
							<a href="{{data.ob[i].url}}" class="rey-wishlistItem-thumbnailLink">{{{data.ob[i].image}}}</a>
							<?php printf('%s', $cart_button) ?>
							<?php printf('%s', $remove) ?>
						</div>
						<div class="rey-wishlistItem-name">
							<a href="{{data.ob[i].url}}">{{data.ob[i].title}}</a>
							<# if(!data.grid){ #>
								<div class="rey-wishlistItem-price">{{{data.ob[i].price}}}</div>
							<# } #>
						</div>
						<# if(data.grid){ #>
							<div class="rey-wishlistItem-price">{{{data.ob[i].price}}}</div>
						<# } #>
						<?php printf('%s', $cart_button) ?>
						<?php printf('%s', $remove) ?>
					</div>
				<# } #>
				<div class="rey-wishlistItem --placeholder" style="transition-delay: {{data.num * 0.07}}s ">
			</div>
		</script>

		<?php
	}

}
