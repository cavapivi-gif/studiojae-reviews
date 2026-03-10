<?php
namespace ReyCore\Modules\ProductSizeGuides;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const POST_TYPE = REY_CORE_THEME_NAME . '-size-guides';

	const ASSET_HANDLE = 'reycore-module-size-guides';

	const TRANSIENT_NAME = '_rey_get_all_guides';

	const AJAX_EVENT = 'get_guide_content';

	const SHORTCODE_NAME = 'rey_size_guide';

	public $settings = [];

	private $__inline_attribute;

	public $guide_ids = null;

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		new AcfFields();
		new Customizer();

		$this->register_post_type();

		add_action( 'wp', [$this, 'add_button_hooks']);
		add_action( 'reycore/module/quickview/product', [$this, 'add_button_hooks']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'admin_menu', [$this, 'register_admin_menu'], 50 );
		add_action( 'admin_footer', [$this, 'admin_add_top_buttons']);
		add_action( 'admin_head', [$this, 'hide_template_dropdown']);
		add_action( 'save_post_' . self::POST_TYPE, [$this, 'save_guide'], 20, 3 );
		add_action( 'delete_post', [$this, 'delete_guide'], 20 );
		add_action( 'wp_trash_post', [$this, 'delete_guide'], 20 );
		add_filter( 'template_include', [$this, 'add_post_type_template'] );
		add_action( 'template_redirect', [ $this, 'block_frontend' ] );
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/import/task/cleanup', [$this, 'after_import_refresh'] );

		add_shortcode( self::SHORTCODE_NAME, [$this, 'get_size_guide_content__shortcode']);
		add_shortcode( self::SHORTCODE_NAME . '_button', [$this, 'render_button']);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public static function get_position(){
		return get_theme_mod('pdp_size_guides_button_position', 'before_atc');
	}

	public function add_button_hooks(){

		$this->remove_header_footer(); // edit mode

		if( ! (is_product() || \ReyCore\WooCommerce\Pdp::is_quickview()) ){
			return;
		}

		if( ! ($btn_position = self::get_position()) ){
			return;
		}

		if( ! $this->get_all_guides() ){
			return;
		}

		$button_positions = [
			'before_atc' => [
				'simple' => [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 29
				],
				'variable' => [
					'hook' => 'woocommerce_before_single_variation',
					'priority' => 10
				]
			],
			'after_atc' => [
				'simple' => [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 31
				],
				'variable' => [
					// 'hook' => 'woocommerce_after_single_variation', // it was inside the single variation wrap
					'hook' => 'woocommerce_after_add_to_cart_form',
					'priority' => 10
				]
			],
			'inline_atc' => [
				'simple' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 0
				],
				'variable' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 0
				],
			],
		];

		// patch when catalog mode
		if( get_theme_mod('shop_catalog', false) ){
			$button_positions['before_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
			$button_positions['before_atc']['variable']['priority'] = 30;
			$button_positions['after_atc']['variable']['hook'] = 'woocommerce_single_product_summary';
			$button_positions['after_atc']['variable']['priority'] = 30;
		}

		$product_type = 'simple';
		if( ($product = wc_get_product()) && $product->is_type( 'variable' ) ){
			$product_type = 'variable';
		}

		if( isset($button_positions[$btn_position]) ){
			add_action($button_positions[$btn_position][$product_type]['hook'], [$this, 'render_button'], $button_positions[$btn_position][$product_type]['priority']);
		}

		else if( 'inline_attribute' === $btn_position ){
			if( 'variable' === $product_type ){
				add_action('woocommerce_before_variations_form', [$this, 'add_inline_button'], 0);
				add_action('woocommerce_after_variations_form', [$this, 'remove_inline_button'], 0);
			}
			else {
				add_action('woocommerce_single_product_summary', [$this, 'render_button'], 31);
			}
		}
	}

	public function swatches_inline_button($item_output, $term, $swatch_base, $params){

		if( ! $this->__inline_attribute ){
			return $item_output;
		}

		if( $params['is_last'] && wc_attribute_taxonomy_name($this->__inline_attribute) === $term->taxonomy ){
			ob_start();
			$this->render_button();
			$item_output .= ob_get_clean();
		}

		return $item_output;
	}

	public function select_inline_button($item_output, $swatches, $args){

		if( ! $this->__inline_attribute ){
			return $item_output;
		}

		if( $swatches->swatch_html ){
			return $item_output;
		}

		if( wc_attribute_taxonomy_name($this->__inline_attribute) === $args['attribute'] ){
			ob_start();
			$this->render_button();
			$item_output .= ob_get_clean();
		}

		return $item_output;
	}

	public function add_inline_button(){
		if( $this->__inline_attribute = get_theme_mod('pdp_size_guides_button_attribute') ){
			add_filter('reycore/variation_swatches/render_item', [$this, 'swatches_inline_button'], 10, 4 );
			add_filter('reycore/variation_dropdown/before_render', [$this, 'select_inline_button'], 10, 4 );
		}
	}

	public function remove_inline_button(){
		$this->__inline_attribute = null;
		remove_filter('reycore/variation_swatches/render_item', [$this, 'swatches_inline_button']);
		remove_filter('reycore/variation_dropdown/before_render', [$this, 'select_inline_button']);
	}

	public function get_all_guides(){

		if( ! is_null($this->guide_ids) ){
			return $this->guide_ids;
		}

		$transient_name = self::TRANSIENT_NAME . ( ($lang = reycore__is_multilanguage()) ? '_' . $lang : '' );

		if( false !== ($all_guides = get_transient($transient_name)) ){
			return $this->guide_ids = $all_guides;
		}

		$posts = get_posts([
			'post_type'   => self::POST_TYPE,
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'ids',
			'orderby'     => 'menu_order',
			'order'       => 'DESC',
		]);

		if( ! $posts ){
			return $this->guide_ids = [];
		}

		$guides = [];

		foreach ($posts as $post_id) {

			$guides[] = [
				'id'         => $post_id,
				'categories' => get_field('guides_product_categories', $post_id),
				'tags'       => get_field('guides_product_tags', $post_id),
				'attributes' => get_field('guides_product_attributes', $post_id),
				'products'   => get_field('guides_product_products', $post_id),
			];

		}

		set_transient($transient_name, $guides, WEEK_IN_SECONDS);

		return $this->guide_ids = $guides;
	}

	public static function has_terms( $taxonomy, $terms, $include_sub = null ){

		// check subcategory
		if( $include_sub ){
			foreach ($terms as $term) {
				if( $subterms = get_term_children( $term, $taxonomy ) ){
					foreach ($subterms as $subterm) {
						$terms[] = $subterm;
					}
				}
			}
		}

		return has_term( array_unique($terms), $taxonomy, get_the_ID() );
	}

	public function maybe_get_guide_id(){

		$ids = [];

		foreach ($this->get_all_guides() as $guide) {

			if( reycore__is_multilanguage() ){
				$guide['id'] = apply_filters('reycore/translate_ids', $guide['id'], self::POST_TYPE);
			}

			if( ($categories = (array) $guide['categories']) && self::has_terms('product_cat', $categories, true ) ){
				$ids[] = $guide['id'];
			}

			if( ($tags = (array) $guide['tags']) && self::has_terms('product_tag', $tags, false ) ){
				$ids[] = $guide['id'];
			}

			if( $attributes = (array) $guide['attributes'] ){
				foreach (reycore_wc__get_product_taxonomies(['product_cat', 'product_tag']) as $tax) {
					if( self::has_terms($tax, $attributes, true ) ){
						$ids[] = $guide['id'];
					}
				}
			}

			if( $product_ids = reycore__clean( $guide['products'] ) ){

				$valid_products = [];

				foreach ($product_ids as $product_id) {

					if( reycore__is_multilanguage() ){
						$product_id = apply_filters('reycore/translate_ids', $product_id, 'product');
					}

					$valid_products[$product_id] = is_single( $product_id );

				}

				if( ! empty($valid_products) && in_array( true, $valid_products, true ) ) {
					$ids[] = $guide['id'];
				}
			}
		}

		return array_unique(array_filter($ids));
	}

	/**
	 * Get the current active guide ID, either determined by:
	 * - default set in Customizer
	 * - product backend settings
	 * - conditions set inside the Guide.
	 *
	 * @return null|string
	 */
	public function get_guide_id( $product_id = null ){

		if( is_null($product_id) ){
			if( ($product = wc_get_product()) && ($product_pid = $product->get_id()) ){
				if( reycore__is_multilanguage() ){
					$product_pid = apply_filters('reycore/translate_ids', $product_pid, 'product');
				}
				$product_id = $product_pid;
			}
		}

		if( ! $product_id ){
			return self::get_default_guide();
		}

		// Per page
		if( $product_display = get_field('pdp_size_guide_display', $product_id) ){
			if( 'show' === $product_display ){
				if( $product_display_custom = absint(get_field('pdp_select_size_guide', $product_id)) ){
					if( reycore__is_multilanguage() ){
						$product_display_custom = apply_filters('reycore/translate_ids', $product_display_custom, self::POST_TYPE);
					}
					return $product_display_custom;
				}
				else {
					return self::get_default_guide();
				}
			}
			else if( 'hide' === $product_display ){
				return;
			}
		}

		// if by conditions
		if( $ids = $this->maybe_get_guide_id() ){
			return $ids[0];
		}

		return self::get_default_guide();
	}

	public function render_button(){

		static $did;

		if( ! is_null($did) ){
			return;
		}

		$did = true;

		$button_data = [
			'id'            => $this->get_guide_id(),
			'attributes'    => [],
			'before_button' => '',
			'after_button'  => '',
			'before'        => '',
			'after'         => '',
		];

		if( ! $button_data['id'] ){
			return;
		}

		$this->settings = apply_filters('reycore/size_guides/settings', [
			'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M17 3l4 4l-14 14l-4 -4z"></path><path d="M16 7l-1.5 -1.5"></path><path d="M13 10l-1.5 -1.5"></path><path d="M10 13l-1.5 -1.5"></path><path d="M7 16l-1.5 -1.5"></path></svg>',
			'icon_after' => true,
			'modal_width' => 650,
		]);

		$button_data['class'] = [
			'rey-sizeGuide-btn',
			'btn',
			'btn-' . esc_attr( get_theme_mod('pdp_size_guides_button_style', 'line-active') ),
		];

		$button_data['text'] = sprintf('<span class="__text">%s</span>', ($text = get_theme_mod('pdp_size_guides_button_text', '')) ? $text : esc_html__('Size Guide', 'rey-core') );

		if( get_theme_mod('pdp_size_guides_button_icon', false) ){
			if( $this->settings['icon_after'] ){
				$button_data['after'] = $this->settings['icon'];
			}
			else {
				$button_data['before'] = $this->settings['icon'];
			}
		}

		$position = self::get_position();

		$button_data['attributes'][] = sprintf('data-position="%s"', $position);
		$button_data['attributes'][] = sprintf('data-modal-width="%s"', absint($this->settings['modal_width']));

		if( 'inline_attribute' === $position && $this->__inline_attribute ){
			$button_data['before_button'] = '<div class="rey-swatchList-item--dummy">';
			$button_data['after_button'] = '</div>';
		}

		$button_data = apply_filters('reycore/size_guides/button_data', $button_data, $this);

		printf('%6$s<button class="%1$s" data-id="%2$s" %8$s>%4$s%3$s%5$s</button>%7$s',
			implode(' ', $button_data['class']),
			absint($button_data['id']),
			$button_data['text'],
			$button_data['before'],
			$button_data['after'],
			$button_data['before_button'],
			$button_data['after_button'],
			implode(' ', $button_data['attributes']),
		);

		$this->enqueue_scripts();
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(['rey-buttons', 'reycore-modals', self::ASSET_HANDLE . '-button']);
		reycore_assets()->add_scripts(['reycore-modals', self::ASSET_HANDLE]);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( self::AJAX_EVENT, [$this, 'ajax__get_content'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
			'transient' => [
				'expiration'         => 2 * WEEK_IN_SECONDS,
				'unique_id'          => 'id',
				'unique_id_sanitize' => 'absint',
			],
		] );
		$ajax_manager->register_ajax_action( 'import_guide_sample_data', [$this, 'ajax__import_sample'], 1 );
	}

	/**
	 * Get a size guide post's content
	 *
	 * @param int $id the guide ID
	 * @return string
	 */
	public function get_size_guide_content( $id = null ){

		// if no guide ID provided
		// perhaps there's one assigned to this product
		if( is_null($id) ){
			if( $guide_id = $this->get_guide_id() ){
				$id = $guide_id;
			}
			else {
				return ['error' => 'Cannot retrieve guide ID.'];
			}
		}

		// if( reycore__is_multilanguage() ){
		// 	$id = apply_filters('reycore/translate_ids', absint($id), self::POST_TYPE);
		// }

		if( ! ($post = get_post($id)) ){
			return ['error' => 'Cannot retrieve post'];
		}

		$GLOBALS['provider_post_id']['pv__'.$id] = $id;

		if( ( $document = \Elementor\Plugin::$instance->documents->get( $post->ID ) ) && $document->is_built_with_elementor() ){
			$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post->ID, true );
		}
		else {
			$content = ($post_content = reycore__parse_text_editor($post->post_content)) ? sprintf('<div class="__content">%s</div>', $post_content) : '';
		}

		$content .= $this->get_tables($id);

		if( $edit_link = get_edit_post_link($post) ){
			$content .= sprintf('<div class="__edit-link"><a href="%s" target="_blank">%s "%s"</a></div>', $edit_link, esc_html__('Edit', 'rey-core'), get_the_title($post));
		}


		unset($GLOBALS['provider_post_id']['pv__'.$id]);

		reycore_assets()->add_styles(['rey-tables', self::ASSET_HANDLE]);

		return sprintf('<div class="rey-sizeGuide-modal">%s</div>', $content);
	}

	public function get_tables( $id ){

		if( get_field('rey_guides_hide_tables', $id) === true ){
			return;
		}

		if( ! ($tables = get_field('rey_guides_tables', $id)) ){
			return;
		}

		$tabs = $tabs_output = $tables_content = $tables_output = '';

		$display = ($d = get_field('rey_guides_display_as', $id)) ? $d : 'stacked';

		foreach ( (array) $tables as $key => $table) {

			if( ! is_array($table) ){
				continue;
			}

			$name = $table['table_name'];
			$caption = 'stacked' === $display ? $name : '';
			$uid = 'sg-table-' . $key;

			$tables_content .= \ReyCore\ACF\Helper::get_table_html([
				'table' => $table['table_data'],
				'css_class' => '--basic',
				'caption' => $caption,
				'wrapper_id' => $uid,
				'wrapper_class' => $key !== 0 && 'tabs' === $display ? ' --hidden' : '',
			]);

			if( 'tabs' === $display ){
				$tabs .= sprintf('<button class="btn btn-simple %s" data-id="%s" >%s</button>', ($key === 0 ? '--active' : ''), $uid, $name);
			}
		}

		if( $tabs ){
			$tabs_output = sprintf('<div class="__tables-tabs">%s</div>', $tabs);
		}

		if( $tables_content ){
			$tables_output = sprintf('<div class="__tables %s">%s</div>', (!empty($tabs) ? '--tabs' : ''), $tables_content);
		}

		return $tabs_output . $tables_output;
	}


	public function get_size_guide_content__shortcode($atts){

		$id = null;

		if( isset($atts['id']) && ($s_id = $atts['id']) ){
			$id = $s_id;
		}

		$this->enqueue_scripts();

		return $this->get_size_guide_content($id);
	}

	public function ajax__get_content( $data ){

		if( ! (isset($data['id']) && ($id = absint($data['id']))) ){
			return ['error' => 'No ID provided'];
		}

		return $this->get_size_guide_content($id);
	}

	public function ajax__import_sample(){

		if ( ! current_user_can('install_plugins') ) {
			return [ 'error' => 'Operation not allowed!' ];
		}

		$title = 'Sample size guide';

		$page_args = [
			'post_status'    => 'publish',
			'post_type'      => self::POST_TYPE,
			'post_name'      => sanitize_title($title),
			'post_title'     => $title,
			'comment_status' => 'closed',
		];

		$post_id = wp_insert_post( $page_args );

		if( $post_id ){

			$meta_value = '[{"table_name":"CM","table_data":{"acftf":{"v":"1.0.0"},"p":{"o":{"uh":1},"ca":""},"c":[{"p":""},{"p":""},{"p":""},{"p":""},{"p":""}],"h":[{"c":"Size"},{"c":"Bust"},{"c":"Hip"},{"c":"Low Hip"},{"c":"Waist"}],"b":[[{"c":"UK 4"},{"c":"79"},{"c":"76"},{"c":"84"},{"c":"60"}],[{"c":"UK 6"},{"c":"81.5"},{"c":"78.5"},{"c":"86.5"},{"c":"62.5"}],[{"c":"UK 8"},{"c":"84"},{"c":"81"},{"c":"89"},{"c":"65"}],[{"c":"UK 10"},{"c":"89"},{"c":"86"},{"c":"94"},{"c":"70"}],[{"c":"UK 12"},{"c":"94"},{"c":"91"},{"c":"99"},{"c":"75"}],[{"c":"UK 14"},{"c":"99"},{"c":"96"},{"c":"104"},{"c":"80"}],[{"c":"UK 16"},{"c":"104"},{"c":"101"},{"c":"109"},{"c":"85"}],[{"c":"UK 18"},{"c":"111"},{"c":"108"},{"c":"116"},{"c":"92"}]]}},{"table_name":"INCH","table_data":{"acftf":{"v":"1.0.0"},"p":{"o":{"uh":1},"ca":""},"c":[{"p":""},{"p":""},{"p":""},{"p":""},{"p":""}],"h":[{"c":"Size"},{"c":"Bust"},{"c":"Hip"},{"c":"Low Hip"},{"c":"Waist"}],"b":[[{"c":"UK 4"},{"c":"31.1"},{"c":"29.9"},{"c":"33.1"},{"c":"23.6"}],[{"c":"UK 6"},{"c":"32.1"},{"c":"30.9"},{"c":"34.1"},{"c":"24.6"}],[{"c":"UK 8"},{"c":"33.1"},{"c":"31.9"},{"c":"35"},{"c":"25.6"}],[{"c":"UK 10"},{"c":"35"},{"c":"33.9"},{"c":"37"},{"c":"27.6"}],[{"c":"UK 12"},{"c":"37"},{"c":"35.8"},{"c":"39"},{"c":"29.5"}],[{"c":"UK 14"},{"c":"39"},{"c":"37.8"},{"c":"40.9"},{"c":"31.5"}],[{"c":"UK 16"},{"c":"40.9"},{"c":"39.8"},{"c":"42.9"},{"c":"33.5"}],[{"c":"UK 18"},{"c":"43.7"},{"c":"42.5"},{"c":"45.7"},{"c":"36.2"}]]}},{"table_name":"INTERNATIONAL CONVERSIONS","table_data":{"acftf":{"v":"1.0.0"},"p":{"o":{"uh":1},"ca":""},"c":[{"p":""},{"p":""},{"p":""},{"p":""},{"p":""},{"p":""},{"p":""},{"p":""}],"h":[{"c":"EU"},{"c":"UK"},{"c":"US"},{"c":"FR"},{"c":"ES"},{"c":"IT"},{"c":"RU"},{"c":"AU"}],"b":[[{"c":"32"},{"c":"4"},{"c":"0"},{"c":"32"},{"c":"32"},{"c":"36"},{"c":"38"},{"c":"4"}],[{"c":"34"},{"c":"6"},{"c":"2"},{"c":"34"},{"c":"34"},{"c":"38"},{"c":"40"},{"c":"6"}],[{"c":"36"},{"c":"8"},{"c":"4"},{"c":"36"},{"c":"36"},{"c":"40"},{"c":"42"},{"c":"8"}],[{"c":"38"},{"c":"10"},{"c":"6"},{"c":"38"},{"c":"38"},{"c":"42"},{"c":"44"},{"c":"10"}],[{"c":"40"},{"c":"12"},{"c":"8"},{"c":"40"},{"c":"40"},{"c":"44"},{"c":"46"},{"c":"12"}],[{"c":"42"},{"c":"14"},{"c":"10"},{"c":"42"},{"c":"42"},{"c":"46"},{"c":"48"},{"c":"14"}],[{"c":"44"},{"c":"16"},{"c":"12"},{"c":"44"},{"c":"44"},{"c":"48"},{"c":"50"},{"c":"16"}],[{"c":"46"},{"c":"18"},{"c":"14"},{"c":"46"},{"c":"46"},{"c":"50"},{"c":"52"},{"c":"18"}]]}}]';

			update_field( 'rey_guides_tables', json_decode($meta_value, true), $post_id );
		}

		return $post_id;
	}

	public function add_import_button(){

		global $current_screen;

		if( ! ( $current_screen && 'edit-' . self::POST_TYPE === $current_screen->id && self::POST_TYPE === $current_screen->post_type) ){
			return;
		} ?>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				if( document.querySelector('body.post-type-<?php echo self::POST_TYPE ?>.edit-php') ){

					var theTarget;
					var topTarget = document.querySelectorAll('.wrap .wp-heading-inline ~ a.page-title-action');

					if( topTarget.length ){
						theTarget = topTarget[ topTarget.length - 1 ];
					}

					var importPost = document.createElement('a');
						importPost.classList.add('page-title-action');
						importPost.textContent = '<?php esc_html_e('Import Sample Guide', 'rey-core') ?>';

					if( theTarget ){

						theTarget.parentNode.insertBefore(importPost, theTarget.nextSibling);

						importPost.addEventListener('click', e => {
							e.preventDefault();
							var button = e.currentTarget;

							button.classList.add('--loading');

							rey.ajax.request( 'import_guide_sample_data', {
								cb: response => {

									if( ! (response && response.success) ){
										console.log(response);
										return;
									}

									if( response.data.error ){
										console.log(response.data.error);
										return;
									}

									button.classList.remove('--loading');
									button.textContent = 'Reloading page..';

									setTimeout(function () {
										window.location.reload();
									}, 1000);

								},
							});
						});
					}
				};
            });
		</script>
		<?php
	}

	public function admin_add_top_buttons(){

		\ReyCore\Admin::render_top_buttons([
			'post_type' => self::POST_TYPE,
			'export_label' => 'Size Guide',
		]);

		$this->add_import_button();
	}

	public function register_post_type() {

		$args = [
			'labels'               => [
				'name'                  => _x( 'Size Guides', 'Post Type General Name', 'rey-core' ),
				'singular_name'         => _x( 'Size Guide', 'Post Type Singular Name', 'rey-core' ),
				'menu_name'             => __( 'Size Guides', 'rey-core' ),
				'name_admin_bar'        => __( 'Size Guide', 'rey-core' ),
				'archives'              => __( 'List Archives', 'rey-core' ),
				'parent_item_colon'     => __( 'Parent List:', 'rey-core' ),
				'all_items'             => __( 'All Size Guides', 'rey-core' ),
				'add_new_item'          => __( 'Add New Size Guide', 'rey-core' ),
				'add_new'               => __( 'Add New', 'rey-core' ),
				'new_item'              => __( 'New Size Guide', 'rey-core' ),
				'edit_item'             => __( 'Edit Size Guide', 'rey-core' ),
				'update_item'           => __( 'Update Size Guide', 'rey-core' ),
				'view_item'             => __( 'View Size Guide', 'rey-core' ),
				'search_items'          => __( 'Search Size Guide', 'rey-core' ),
				'not_found'             => __( 'Not found', 'rey-core' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'rey-core' )
			],
			'public'               => true,
			'rewrite'              => false,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'show_in_nav_menus'    => false,
			'show_in_admin_bar'    => true,
			'exclude_from_search'  => true,
			'capability_type'      => 'post',
			'hierarchical'         => false,
			'supports'             => [ 'title', 'elementor', 'editor', 'page-attributes' ],
			// 'register_meta_box_cb' => [$this, 'remove_meta_box']
		];

		add_filter('manage_' . self::POST_TYPE . '_posts_columns', function ($columns) {
			$columns['menu_order'] = __("Order");
			$columns['reycore_shortcode_column'] = __( 'Shortcode', 'rey-core' );
			return $columns;
		});

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', function ($column_name, $post_id){
			if ($column_name == 'menu_order') {
				echo get_post($post_id)->menu_order;
			}
			elseif ($column_name == 'reycore_shortcode_column') {
				printf('<div class="rey-codeBlock"><button class="button button-small js-rey-copy-code" type="button" data-text="%s">%s</button><pre>[%s id="%s"]</pre></div>', esc_html__(' - COPIED!', 'rey-core'), esc_html__('COPY SHORTCODE', 'rey-core'), self::SHORTCODE_NAME, $post_id );
			}
		}, 10, 2);

		add_filter("manage_edit-" . self::POST_TYPE . "_sortable_columns", function ($columns){
			$columns['menu_order'] = 'menu_order';
			return $columns;
		});

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Block guide in frontend
	 *
	 * Don't display the single view of the post type in the frontend for users that don't have the proper permissions.
	 *
	 * Fired by `template_redirect` action.
	 *
	 * @since 3.0.1
	 * @access public
	 */
	public function block_frontend() {
		if ( ! empty( $_GET[ self::POST_TYPE ] ) && ! is_admin() && ! current_user_can( 'edit_posts' ) ) {
			wp_safe_redirect( site_url(), 301 );
			die;
		}
	}

	/**
	 * Force custom template
	 *
	 * @since 1.0.0
	 */
	public function add_post_type_template( $template_path ) {

		if ( get_post_type() !== self::POST_TYPE ) {
			return $template_path;
		}

		if ( is_single() ) {
			// checks if the file exists in the theme first,
			// otherwise serve the file from the plugin
			if ( $theme_file = locate_template( ['single-'. self::POST_TYPE .'.php'] ) ) {
				$template_path = $theme_file;
			} else {
				$template_path = __DIR__ . '/single-template.php';
			}
		}

		return $template_path;
	}

	/**
	 * Remove header & Footer
	 *
	 * @since 1.0.0
	 */
	public function remove_header_footer(){
		if ( get_post_type() === self::POST_TYPE ) {
			remove_all_actions( 'rey/header' );
			remove_all_actions( 'rey/footer' );
		}
	}

	public function remove_meta_box(){
		remove_meta_box( 'pageparentdiv', self::POST_TYPE, 'side' );
	}

	public function hide_template_dropdown() {
		global $post_type;

		if (self::POST_TYPE === $post_type) {
			echo '<style>
				#pageparentdiv #page_template,
				#pageparentdiv .page-template-label-wrapper { display: none }
			</style>';
		}
	}

	public function delete_guide($post_id){

		global $post;

		if( is_null($post) ){
			return;
		}

		if( ! isset($post->post_type) ){
			return;
		}

		if( self::POST_TYPE !== $post->post_type ){
			return;
		}

		$this->flush_transient($post_id, $post);

	}

	public function save_guide($post_id, $post, $update){

		// Avoiding autosave, auto-draft, and revisions
		if (
			defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
			$post->post_status == 'auto-draft' ||
			wp_is_post_revision($post_id)) {
			return;
		}

		// not checking for $update because the template type might be changed

		// if updating a guide, delete the transient used for getting the guide content via ajax
		if( $update ){
			\ReyCore\Helper::clean_db_transient( implode('_', [ \ReyCore\Ajax::AJAX_TRANSIENT_NAME, self::AJAX_EVENT, absint($post_id) ] ) );
		}

		$this->flush_transient($post_id, $post);
	}

	/**
	 * Flush the transient used for getting all guides
	 *
	 * @param int $post_id
	 * @param object $post
	 * @return void
	 */
	public function flush_transient($post_id, $post){
		delete_transient(
			self::TRANSIENT_NAME . ( ($lang = reycore__is_multilanguage()) ? '_' . $lang : '' )
		);
	}

	public static function get_default_guide(){

		static $guide_id;

		if( is_null($guide_id) ){
			if( $guide_id_raw = get_theme_mod('pdp_size_guides', '') ){
				$guide_id = absint($guide_id_raw);
				if( reycore__is_multilanguage() ){
					$guide_id = apply_filters('reycore/translate_ids', $guide_id, self::POST_TYPE);
				}
			}
		}

		return $guide_id;

	}

	/**
	 * Register the admin menu.
	 *
	 * @since  1.0.0
	 */
	public function register_admin_menu()
	{
		if( $dashboard_id = reycore__get_dashboard_page_id() ){
			add_submenu_page(
				$dashboard_id,
				__( 'Size Guides', 'rey-core' ),
				__( 'Size Guides', 'rey-core' ),
				'edit_pages',
				'edit.php?post_type=' . self::POST_TYPE
			);
		}
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-button' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/button.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	/**
	 * Refresh guides after import.
	 * the ACF data doesn't seem to be refreshed after import
	 *
	 * @return void
	 */
	public function after_import_refresh(){
		// foreach ($this->get_all_guides() as $guide_id) {}
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Size Guides for Products', 'Module name', 'rey-core'),
			'description' => esc_html_x('Create Size guide (charts) templates for products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'video' => true,
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product Page', 'Product catalog'],
			'help'        => reycore__support_url('kb/how-to-add-a-size-chart/'),
		];
	}

	public function module_in_use(){
		return $this->get_all_guides() && self::get_default_guide();
	}
}
