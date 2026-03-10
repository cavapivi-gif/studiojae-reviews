<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Frontend
{

	public $loop_variations;

	public static $scrips_loaded;

	public $args = [];

	public $swatch_html;

	const CATALOG_MODE = 'rey_swatch_catalog_mode';

	public function __construct()
	{
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action( 'reycore/woocommerce/product_page/scripts', [$this, 'enqueue_scripts']);
		add_action( 'reycore/module/after-atc-popup/scripts', [$this, 'load_scripts']);
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [$this, 'variation_dropdown_start'], -1);
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [$this, 'variation_dropdown_end'], 2000);
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [$this, 'variation_dropdown'], 100, 2);
		add_filter( 'woocommerce_dropdown_variation_attribute_options_args', [$this, 'variation_dropdown_args'], 100);
		add_filter( 'woocommerce_ajax_variation_threshold', [$this, 'ajax_variation_threshold'], 10, 2 );
		add_filter( 'reycore/woocommerce/variations/variation_attribute_data', [$this, 'variation_attribute_data'] );
		add_action( 'reycore/woocommerce/variations/catalog/render_single', [$this, 'catalog_render_single_swatch'], 10, 2);
		add_action( 'reycore/woocommerce/variations/catalog/render_all', [$this, 'catalog_render_all_swatches'], 10, 2);
		add_filter( 'post_class', [$this, 'product_classes'], 30 );
		add_filter( 'woocommerce_reset_variations_link', [$this, 'reset_variations_link']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_filter( 'rey/main_script_params', [$this, 'script_params']);
		add_filter( 'wp_footer', [$this, 'load_variations_template_catalog']);
		add_action( 'woocommerce_after_variations_table', [$this, 'table_rows_classes']);
		add_filter( 'woocommerce_available_variation', [$this, 'set_stock_status'], 10, 3);

	}

	/**
	 * Register Ajax Actions
	 *
	 * @param object $ajax_manager
	 * @return void
	 */
	public function register_actions( $ajax_manager ){
		// $ajax_manager->register_ajax_action( 'variations_load_catalog', [$this, 'ajax__lazy_load_catalog'], 3 );
	}

	public function get_product(){

		global $post;

		if( ! isset($post->ID) ){
			return;
		}

		$product_id = $post->ID && ('product' === get_post_type()) ? $post->ID : false;

		if( ! ( $product = wc_get_product( $product_id ) ) ){
			return;
		}

		return $product;
	}

	/**
	 * Product page load assets
	 *
	 * @return void
	 */
	public function enqueue_scripts(){

		if( Base::$settings['optimized_assets_load'] ){
			return;
		}

		if( ! reycore_wc__is_product() ){
			return;
		}
		$product = $this->get_product();

		if ( ! ($product && 'variable' === $product->get_type()) ) {
			return;
		}

		self::load_scripts();
	}

	public static function get_asset_handle( $type ){
		return Base::ASSET_HANDLE . '-' . $type;
	}

	/**
	 * Load frontend CSS and JS
	 *
	 * @return void
	 */
	public static function load_scripts(){

		if( self::$scrips_loaded ){
			return;
		}

		reycore_assets()->add_scripts([Base::ASSET_HANDLE, 'wc-add-to-cart-variation']);
		reycore_assets()->add_styles([Base::ASSET_HANDLE . '-lite', Base::ASSET_HANDLE]);

		self::$scrips_loaded = true;
	}

	/**
	 * Register mod assets
	 *
	 * @return void
	 */
	public function register_assets($assets){

		$assets->register_asset('styles', [
			Base::ASSET_HANDLE . '-lite' => [
				'src'      => Base::get_path( basename( __DIR__ ) ) . '/frontend-style-lite.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'high',
			]
		]);

		$assets->register_asset('styles', [
			Base::ASSET_HANDLE => [
				'src'      => Base::get_path( basename( __DIR__ ) ) . '/frontend-style.css',
				'deps'     => [Base::ASSET_HANDLE . '-lite'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('scripts', [
			Base::ASSET_HANDLE => [
				'src'     => Base::get_path( basename( __DIR__ ) ) . '/frontend-script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function script_params($params){
		$params['updateCatalogPriceSingleAttribute'] = Base::$settings['update_catalog_price_if_single_attribute'];
		$params['updateCatalogStockSingleAttribute'] = Base::$settings['update_catalog_stock_if_single_attribute'];
		return $params;
	}

	public function can_add_swatches(){
		return apply_filters('reycore/variation_swatches/support', true);
	}

	public function variation_dropdown_args( $args ){

		if( ! $this->can_add_swatches() ){
			return $args;
		}

		global $product;

		// replace id html attribute to be unique
		if( $product && ($product_id = $product->get_id()) ){

			if( get_queried_object_id() === $product_id ){
				if( Base::$settings['update_product_page_url'] ){
					$args['class'] = '--update-url';
				}
			}
			else {
				$args['id'] = $args['attribute'] . $product_id;
			}
		}

		$attribute_name = str_replace('pa_', '', $args['attribute']);

		if( $attribute_name === get_theme_mod('pdp_swatches__update_gallery', '') ){
			$args['class'] = '--update';
		}

		// Activate single variations from lists
		if(
			Base::$settings['autoselect_single_variation'] &&
			((isset( $args['selected'] ) && '' === $args['selected']) || ! isset( $args['selected'] )) &&
			isset( $args['options'] ) && 1 === count($args['options'])
		){
			$args['selected'] = $args['options'][0];
		}

		return $args;
	}

	public function variation_dropdown_start($html){
		return '<div class="__s-wrapper">' . $html;
	}

	public function variation_dropdown_end($html){
		return $html . '</div>';
	}

	public function table_rows_classes(){
		?><script type="text/javascript" id="reycore-variations-row-classes">
			document.querySelectorAll('.variations_form .variations tr [data-tr-class]').forEach(list => {
				var row = list.closest('tr');
				row.classList.add( list.getAttribute('data-tr-class') );
				row.setAttribute( 'data-id', list.getAttribute('data-tr-attribute') );
			});
		</script>
		<?php
	}

	/**
	 * Filter the variation dropdown, hide it and add html
	 *
	 * @param string $html
	 * @param array $args
	 * @return string
	 */
	public function variation_dropdown($html, $args){

		if( ! $this->can_add_swatches() ){
			return $html;
		}

		$this->swatch_html = $this->get_swatch_list_output($args);

		$html = apply_filters('reycore/variation_dropdown/before_render', $html, $this, $args);

		if( ! $this->swatch_html ){
			return $html;
		}

		$style_search = '<select ';
		$style_replace = 'style="display:none" ';

		$html = str_replace($style_search, $style_search . $style_replace, $html);

		if( ! Base::$settings['autoselect_catalog_variation'] && ! \ReyCore\WooCommerce\Pdp::is_single_true_product() ){
			$html = str_replace(' selected=', ' data-selected=', $html);
		}

		return $html . $this->swatch_html;
	}

	/**
	 * Outputs swatches list
	 *
	 * @param array $attribute
	 * @return string
	 */
	public function get_swatch_list_output( $args ){

		if( empty($args['options']) ){
			return;
		}

		if( ! ($taxonomy = $args['attribute']) ){
			return;
		}

		$attribute = [];

		$custom_attribute = false;

		$attribute_id = wc_attribute_taxonomy_id_by_name($taxonomy);

		if( $attribute_id && 0 !== strpos( $taxonomy, 'pa_') ){
			$attribute_id = false;
		}

		// custom attributes
		if( ! $attribute_id ){

			if( get_theme_mod('pdp_swatches__custom_use_button', false) ){

				$attribute = [
					'name'         => $taxonomy,
					'slug'         => sanitize_title( $taxonomy ),
					'type'         => Base::instance()->get_default_swatch(),
					'order_by'     => false,
					'has_archives' => false,
				];

				if( ($custom_attributes_data = self::get_custom_attributes_data()) && isset($custom_attributes_data[ $taxonomy ])  ){
					$attribute['type'] = $custom_attributes_data[ $taxonomy ]['type'];
				}

				$custom_attribute = true;
			}

			// bail bc. there's no attribute data
			else {
				return;
			}
		}
		else {
			$attribute = (array) wc_get_attribute($attribute_id);
		}

		if( empty($attribute) ){
			return;
		}

		// the scripts are only loaded for Rey's swatches, which is fine,
		// but the select is also needed to start the scripts properly
		if( 'select' === $attribute['type'] && Base::$settings['optimized_assets_load'] ){
			self::load_scripts();
		}

		if ( ! array_key_exists( $attribute['type'], Base::instance()->get_swatches_list() ) ) {
			return;
		}

		if( Base::$settings['optimized_assets_load'] ){
			self::load_scripts();
		}

		$list_args = $args;
		$list_args['attribute_data'] = $attribute;
		$list_args['custom_attribute'] = $custom_attribute;

		if( self::is_catalog_mode() && \ReyCore\Modules\VariationSwatches\SwatchLargeButton::TYPE_KEY === $attribute['type'] ){
			// get taxonomy's Large button settings
			$attribute_settings = Base::get_attributes_swatch_settings( $list_args['attribute_data']['slug'] );
			// set the type to fallback
			$attribute['type'] = isset($attribute_settings['swatch_fallback']) && ($fallback = $attribute_settings['swatch_fallback']) ? $fallback : 'rey_color';
		}

		return Base::instance()->get_swatches( $attribute['type'] )->render_list( $list_args );
	}

	public static function get_custom_attributes_data(){
		return apply_filters('reycore/variation_swatches/custom_attributes', []);
	}

	/**
	 * Checks if catalog mode
	 *
	 * @return boolean
	 */
	public static function is_catalog_mode(){
		return get_query_var(self::CATALOG_MODE, false);
	}

	public function ajax_variation_threshold( $threshold, $product ){

		// Force bump the treshold when "Disabled" or "Out of stock" variations
		// is enabled so that it's able to visually handle them.
		if( get_theme_mod('single_product_hide_out_of_stock_variation', true) ){
			return 100;
		}

		if( $custom = absint(get_theme_mod('pdp_swatches__ajax_threshold', 50)) ){
			return $custom;
		}

		return $threshold;
	}

	function variation_attribute_data( $data ){

		if( ! $this->can_add_swatches() ){
			return $data;
		}

		$attribute_id = wc_attribute_taxonomy_id_by_name($data['taxonomy_name']);
		$attribute = (array) wc_get_attribute($attribute_id);

		if ( isset($attribute['type']) && array_key_exists( $attribute['type'], Base::instance()->get_swatches_list() ) ) {
			$data['type'] =  $attribute['type'];
		}

		return $data;
	}

	public function set_stock_status( $available_attr, $product, $variation ){

		if( is_admin() ){
			return $available_attr;
		}

		$available_attr['stock_status'] = $variation->get_stock_status();

		return $available_attr;
	}

	public function get_transient_name($product_id){
		return \ReyCore\WooCommerce\Tags\VariationsLoop::transient_name( $product_id );
	}

	public function ajax__lazy_load_catalog( $data ){
	}

	/**
	 * Handle ajax variations loaded in product page.
	 * Makes a call to extract unavailable variations.
	 * Work in progress.
	 *
	 * @param array $a_data
	 * @return array
	 */
	public function ajax__variations_pdp( $a_data ){

		if( ! (isset($a_data['pid']) && ($product_id = absint($a_data['pid'])))){
			return;
		}

		if( ! ($product = wc_get_product($product_id)) ){
			return;
		}

		/**
		 * @var \WC_Product_Variable $product
		 */
		$available_variations = $product->get_available_variations();

		$unavailable_variations = [];

        foreach ($available_variations as $var) {

			if( ! $var['is_purchasable'] ){
				$unavailable_variations[] = $var['attributes'];
				continue;
			}

			if( ! $var['variation_is_active'] ){
				$unavailable_variations[] = $var['attributes'];
				continue;
			}

			if( ! $var['variation_is_visible'] ){
				$unavailable_variations[] = $var['attributes'];
				continue;
			}

			if( ! $var['is_in_stock'] && ! $var['backorders_allowed'] ){
				$unavailable_variations[] = $var['attributes'];
				continue;
			}

        }

		return $unavailable_variations;
	}

	protected function get_catalog_data( $product, $tax = '' ){

		$data = false;

		$product_id = $product->get_id();

		if( Base::$settings['loop_cache'] ){
			$data = get_transient( $this->get_transient_name($product_id) );
		}

		if( false === $data ){

			$data = '';

			$attributes = $product->get_variation_attributes();

			if( ! empty($attributes) ){

				$attr_output = '';

				foreach ( $attributes as $attribute_name => $options ) :

					if( $tax ){
						if( $attribute_name !== $tax ){
							continue;
						}
						if( count($options) === 1 && ! Base::$settings['catalog_show_single'] ){
							continue;
						}
					}

					ob_start();

					if( ! $tax && Base::$settings['show_all_labels'] ){
						printf('<div class="rey-swatchList-label"><label for="%s">%s</label></div>',
							esc_attr( sanitize_title( $attribute_name ) ),
							wc_attribute_label( $attribute_name )
						); // WPCS: XSS ok.
					}

					$dropdown_args = [
						'options'   => $options,
						'attribute' => $attribute_name,
						'product'   => $product,
					];

					$options_count = count($options);

					if( $tax ){
						if( ($limit = get_theme_mod('woocommerce_loop_variation_limit', 0)) && $limit < $options_count ){
							$dropdown_args['limit'] = $limit;
							$dropdown_args['limit_diff'] = $options_count - $limit;
							$dropdown_args['options'] = array_slice($options, 0, $limit);
						}
					}

					// set catalog mode
					set_query_var(self::CATALOG_MODE, true);

					wc_dropdown_variation_attribute_options( $dropdown_args );

					// reset
					set_query_var(self::CATALOG_MODE, false);

					$attr_output .= ob_get_clean();

				endforeach;

				if( $attr_output ){
					$data = sprintf('<form class="variations_form cart --catalog" action="%1$s" method="post" enctype="multipart/form-data" data-product_id="%2$s" data-product_variations="%3$s" data-atc-text="%4$s"><div class="variations">%5$s</div></form>',
						esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ),
						absint( $product->get_id() ),
						wc_esc_json( wp_json_encode( $product->get_available_variations() ) ),
						esc_attr( $product->single_add_to_cart_text() ),
						$attr_output
					);
				}
			}

			if( Base::$settings['loop_cache'] ){
				set_transient( $this->get_transient_name($product_id), $data, MONTH_IN_SECONDS );
			}

		}

		return $data;
	}

	public function catalog_render( $product, $tax = '' ){

		// $over_threshold = count( $product->get_children() ) >= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
		$over_threshold = false;

		$data = false;

		if( ! $over_threshold ){
			$data = $this->get_catalog_data( $product, $tax );
		}

		if( $data || $over_threshold ){

			$position = get_theme_mod('woocommerce_loop_variation_position', 'after');
			$all = 'all_attributes' === $this->loop_variations->selected_attribute_taxonomy;

			$html_list_classes = [
				'rey-productVariations2',
				($all) ? '--all-attr' : '--single-attr',
				$over_threshold ? '--ajax' : '',
			];

			if(
				! $all &&
				get_theme_mod('woocommerce_loop_variation_single_click', false)
			 ){
				$html_list_classes[] = '--redirect-pdp';
			}

			if(
				in_array($position, ['first', 'before'], true) &&
				! $all &&
				get_theme_mod('woocommerce_loop_variation__side', false)
			 ){
				$html_list_classes[] = '--side';
			}

			if(
				! $all &&
				get_theme_mod('woocommerce_loop_variation__mobile', false)
			 ){
				$html_list_classes[] = '--show-mobile';
			}

			$html_list_attributes = [
				'data-attribute' => esc_attr( $this->loop_variations->selected_attribute_taxonomy ),
				'data-position' => esc_attr( get_theme_mod('woocommerce_loop_variation_position', 'after') ),
			];

			$html_list_attributes['class'] = esc_attr( implode(' ', apply_filters('reycore/variation_swatches/catalog_classes', $html_list_classes) ) );

			printf('<div %s>%s</div>', reycore__implode_html_attributes($html_list_attributes),  $data);

			self::load_scripts();

		}

		else if( Base::$settings['show_empty_catalog_div'] ){
			echo '<div class="rey-productVariations2"></div>';
		}

	}

	function catalog_render_all_swatches( $product, $rey_catalog_variations ){
		if( ! $this->can_add_swatches() ){
			return;
		}
		$this->loop_variations = $rey_catalog_variations;
		$this->catalog_render( $product );
	}

	function catalog_render_single_swatch( $product, $rey_catalog_variations ){
		if( ! $this->can_add_swatches() ){
			return;
		}
		$this->loop_variations = $rey_catalog_variations;
		$this->catalog_render( $product, $this->loop_variations->selected_attribute_taxonomy_data['taxonomy_name'] );
	}

	/**
	 * Adds custom classes to the product element (div/li)
	 *
	 * @param array $classes
	 * @return array
	 */
	function product_classes( $classes ){

		if( ! $this->can_add_swatches() ){
			return $classes;
		}

		$product = $this->get_product();

		if ( ! $product ) {
			return $classes;
		}

		$classes['swatches'] = 'rey-swatches';
		$classes['show_selected'] = ($show_sel = get_theme_mod('pdp_swatches__selected_name_v2', '')) ? 'rey-swatches--show-selected--' . esc_attr($show_sel) : '';
		$classes['stretch_labels'] = get_theme_mod('pdp_swatches__stretch_label', false) ? 'rey-swatches--stretch-labels' : '';

		if( '' !== get_theme_mod('pdp_swatches__update_gallery', '') ){
			$classes['update_gallery'] = '--swatch-update-gallery';
		}

		return $classes;
	}

	/**
	 * Reset button
	 *
	 * @since 2.2.2
	 **/
	function reset_variations_link( $html )
	{

		if( ! $this->swatch_html ){
			return $html;
		}

		if( ! in_array(get_theme_mod('pdp_swatches__deselection', 'clear'), ['clear', 'both'], true) ){
			return '';
		}

		return '<a class="rey-resetVariations --hidden" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>';
	}

	public static function extract_custom_image($data){

		if( ! $data ){
			return;
		}

		$custom_image_url = false;

		if( isset($data['url']) && ($img_url = $data['url']) ){
			$custom_image_url = $img_url;
		}

		else if( isset($data['id']) && ($img_id = $data['id']) ) {
			$custom_image_url = wp_get_attachment_url($img_id);
		}

		if( $custom_image_url ){
			return sprintf('background-image:url(%s);', $custom_image_url );
		}

	}

	public function load_variations_template_catalog(){

		if( is_admin() || is_404() ){
			return;
		}

		if( is_account_page() || is_cart() || is_checkout() ){
			return;
		}

		if( 'disabled' === get_theme_mod('woocommerce_loop_variation', 'disabled') ){
			return;
		}

		wc_get_template( 'single-product/add-to-cart/variation.php' );

	}

}
