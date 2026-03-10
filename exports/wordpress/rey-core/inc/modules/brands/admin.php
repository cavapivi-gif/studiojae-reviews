<?php
namespace ReyCore\Modules\Brands;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Admin
{

	public function __construct()
	{
		add_action( 'admin_init', [$this, 'admin_init'] );
		add_action( 'init', [$this, 'options_acf'], 5);
		add_action( 'reycore/customizer/control=cover__shop_tag', [$this, 'add_brand_cover_option'], 10, 2);
		add_action( 'reycore/customizer/control=search__include', [$this, 'add_to_search'], 20, 2);
		add_action( 'admin_menu', [$this, 'admin_menu'] );
		add_action( 'reycore/updates/up_2_3_7', [$this, 'update__make_public'] );
		add_action( 'customize_save_brand_taxonomy', [$this, 'ensure_enabled_archives_on_change_tax']);
		add_action( 'admin_enqueue_scripts', [$this, 'admin_scripts'] );
		add_action( 'elementor/element/reycore-wc-attributes/section_sett_advanced/before_section_end', [$this, 'elementor_add_brand_settings']);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/after_section_start', [$this, 'elementor_grid_component_visiblity']);
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/after_section_start', [$this, 'elementor_grid_component_visiblity']);
		add_filter( 'reycore/cards/attribute_source/thumb_key', [$this, 'cards_attribute_source_thumb_key'], 10, 2);
	}

	function admin_init(){

		add_filter('manage_product_posts_columns', [$this, 'add_column']);
		add_action('manage_posts_custom_column', [$this, 'populate_column'], 10, 2);

		add_action( 'restrict_manage_posts', [$this, 'admin__add_filter_list'], 20 );
		add_action( 'pre_get_posts', [$this, 'admin__filter_products_list'] );

		// bulk edit
		add_action('woocommerce_product_bulk_edit_end',  [$this, 'admin__bulk_edit_add_brands_field']);
		add_action('woocommerce_product_bulk_edit_save',  [$this, 'admin__bulk_save'], 99);

		add_action( 'admin_head', [$this, 'fix_posts_table_layout_fixed']);

		$brand_attribute = Base::instance()->get_brand_attribute();

		add_filter( "manage_edit-{$brand_attribute}_columns", [$this, 'add_image_column'] );
		add_filter( "manage_{$brand_attribute}_custom_column", [$this, 'add_column_for_image'], 10, 3 );

	}

	public function admin_menu(){

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		if( ! ($tax = Base::instance()->get_brand_attribute()) ){
			return;
		}

		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Brands', 'rey-core' ),
			__( 'Brands', 'rey-core' ),
			'edit_products',
			sprintf('edit-tags.php?taxonomy=%s&post_type=product', $tax),
			'',
			5
		);

	}

	function add_column( $column_array ) {

		if( ! Base::instance()->brands_tax_exists() ){
			return $column_array;
		}

		$column_array['brand'] = esc_html__('Brand', 'rey-core');
		return $column_array;
	}

	function populate_column( $column_name, $post_id ) {

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		if( $column_name === 'brand' ) {
			echo Base::instance()->get_brand_name($post_id);
		}
	}

	function fix_posts_table_layout_fixed(){
		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}
		echo '<style>
		table.wp-list-table.fixed.posts {
			table-layout: auto;
		}</style>';
	}

	public function admin_scripts(){

		$current_screen = get_current_screen();

		if( ! (isset($current_screen->id) && 'product_page_product_attributes' === $current_screen->id) ){
			return;
		}

		wp_enqueue_script(
			Base::ASSET_HANDLE . '-admin',
			Base::get_path( basename( __DIR__ ) ) . '/admin.js',
			['jquery'],
			REY_CORE_VERSION,
			true
		);

		wp_localize_script(Base::ASSET_HANDLE . '-admin', 'reyCoreBrandsAdmin', [
			'tax' => Base::instance()->get_brand_attribute(),
			'label_text' => sprintf('%s brands', REY_CORE_THEME_NAME),
		]);
	}

	function admin__add_filter_list( $post_type, $args = [] ){

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		if( $post_type !== 'product' ) {
			return;
		}

		$args = wp_parse_args($args, [
			'name'             => 'rey_brand_term',
			'by'               => 'slug',
			'check_active'     => true,
			'hide_empty'       => true,
			'unbranded_option' => true
		]);

		$brands = get_terms([
			'taxonomy'   => Base::instance()->get_brand_attribute(),
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => $args['hide_empty'],
			'parent'     => 0,
		]);

		$brands_options = [];

		$active = '';

		if( $args['check_active'] && isset($_GET[$args['name']]) && $active_brand = wc_clean($_GET[$args['name']]) ){
			$active = $active_brand;
		}

		foreach ($brands as $key => $brand) {
			if( isset($brand->{$args['by']}) && isset($brand->name) ){
				$brands_options[] = sprintf('<option value="%1$s" %3$s>%2$s</option>', $brand->{$args['by']}, $brand->name, selected($active, $brand->{$args['by']}, false));
			}
		}

		if( !empty($brands_options) ){
			echo sprintf('<select name="%s">', $args['name']);
			echo sprintf('<option value="">%s</option>', esc_html__('Select a brand', 'rey-core'));

			if( $args['unbranded_option'] ){
				echo sprintf('<option value="-1" %2$s>%1$s</option>', esc_html__('Unbranded', 'rey-core'), selected($active, '-1', false));
			}

			echo implode( '', $brands_options );
			echo '</select>';
		}
	}

	public function cards_attribute_source_thumb_key( $key, $taxonomy){

		if( Base::instance()->get_brand_attribute() === $taxonomy ){
			$key = 'rey_brand_image';
		}

		return $key;
	}

	function admin__filter_products_list( $query ){

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		global $pagenow;

		if ( ! ($query->is_admin && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'product') ) {
			return $query;
		}

		if( ! (isset($_GET['rey_brand_term']) && $active_brand = wc_clean($_GET['rey_brand_term'])) ){
			return $query;
		}

		$tax_query = [
			'relation' => 'AND',
		];

		$query_data = [
			'taxonomy'         => Base::instance()->get_brand_attribute(),
			'field'            => 'slug',
			'terms'            => $active_brand,
			'operator'         => 'IN',
			'include_children' => false,
		];

		if( $active_brand === '-1' ){
			$query_data = [
				'taxonomy'         => Base::instance()->get_brand_attribute(),
				'operator'         => 'NOT EXISTS',
			];
		}

		if( ! $query->tax_query ){
			return;
		}

		$query->tax_query->queries[] = $query_data;

		foreach ( $query->tax_query->queries as $q ) {
			$tax_query[] = $q;
		}

		$query->set('tax_query', $tax_query);

	}

	function admin__bulk_edit_add_brands_field() {

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		?>
		<div class="inline-edit-group">
		  <label class="alignleft">
			 <span class="title"><?php _e( 'Brand', 'rey-core' ); ?></span>
			 <span class="input-text-wrap">
				<?php
					$this->admin__add_filter_list('product', [
						'name'             => 'rey_brand_term_bulk',
						'by'               => 'term_id',
						'check_active'     => false,
						'hide_empty'       => false,
						'unbranded_option' => false,
					]);
				?>
			 </span>
			</label>
		</div>
		<?php
	}

	function admin__bulk_save( $product ){

		if( ! Base::instance()->brands_tax_exists() ){
			return;
		}

		if( ! (isset($_REQUEST['rey_brand_term_bulk']) && $brand = absint($_REQUEST['rey_brand_term_bulk'])) ){
			return;
		}

		$brand_tax_name = Base::instance()->get_brand_attribute();
		$product_id = $product->get_id();

		$meta_attributes = get_post_meta( $product->get_id(), '_product_attributes', true );

		$attributes   = [];

		/**
		 * WC_Product_Variable_Data_Store_CPT
		 * read_attributes
		 */
		if ( ! empty( $meta_attributes ) && is_array( $meta_attributes ) ) {

			$force_update = false;
			$has_brand = false;

			foreach ( $meta_attributes as $meta_attribute_key => $meta_attribute_value ) {

				$meta_value = array_merge(
					array(
						'name'         => '',
						'value'        => '',
						'position'     => 0,
						'is_visible'   => 0,
						'is_variation' => 0,
						'is_taxonomy'  => 0,
					),
					(array) $meta_attribute_value
				);

				// Check if is a taxonomy attribute.
				if ( ! empty( $meta_value['is_taxonomy'] ) ) {
					if ( ! taxonomy_exists( $meta_value['name'] ) ) {
						continue;
					}
					$id      = wc_attribute_taxonomy_id_by_name( $meta_value['name'] );
					$options = wc_get_object_terms( $product->get_id(), $meta_value['name'], 'term_id' );
				} else {
					$id      = 0;
					$options = wc_get_text_attributes( $meta_value['value'] );
				}

				// tell only to modify it
				if( $meta_value['name'] === $brand_tax_name ){
					$options = array_map('absint', wc_get_text_attributes( $brand ));
					$has_brand = true;
					$force_update = true;
				}

				$attribute = new \WC_Product_Attribute();
				$attribute->set_id( $id );
				$attribute->set_name( $meta_value['name'] );
				$attribute->set_options( $options );
				$attribute->set_position( $meta_value['position'] );
				$attribute->set_visible( $meta_value['is_visible'] );
				$attribute->set_variation( $meta_value['is_variation'] );
				$attributes[] = $attribute;
			}

			// doesn't have brand, add it
			if( ! $has_brand ){
				$b_attribute = new \WC_Product_Attribute();
				$b_attribute->set_id( wc_attribute_taxonomy_id_by_name( $brand_tax_name ) );
				$b_attribute->set_name( $brand_tax_name );
				$b_attribute->set_options( array_map('absint', wc_get_text_attributes( $brand )) );
				$b_attribute->set_position( count($attributes) + 1 );
				$b_attribute->set_visible( true );
				$b_attribute->set_variation( false );
				$attributes[] = $b_attribute;
				$force_update = true;
			}

		}

		// is empty
		else {
			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( wc_attribute_taxonomy_id_by_name( $brand_tax_name ) );
			$attribute->set_name( $brand_tax_name );
			$attribute->set_options( array_map('absint', wc_get_text_attributes( $brand )) );
			$attribute->set_visible( true );
			$attributes[] = $attribute;
			$force_update = true;
		}

		if( empty($attributes) ){
			return;
		}

		$product->set_attributes( $attributes );

		if ( $force_update ) {
			$data_store   = \WC_Data_Store::load( 'product' );
			$data_store->update( $product );
		}

	}

	function add_brand_cover_option( $control_args, $section ){

		if( ! Base::instance()->brands_taxonomy_is_public() ){
			return;
		}

		// Shop Brands
		$section->add_title( esc_html__('Brands', 'rey-core'), [
			'description' => esc_html__('Select a page cover to display in product brands. You can always disable or change the Page Cover of a specific brand, in its options.', 'rey-core'),
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__shop_brands',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => '',
			'choices'     => class_exists('\ReyCore\Elementor\GlobalSections') ? \ReyCore\Elementor\GlobalSections::get_global_sections('cover', [
				'no'  => esc_attr__( 'Disabled', 'rey-core' ),
				'' => esc_html__('- Inherit -', 'rey-core')
			]) : [],
		] );
	}

	function add_to_search($control_args, $section){

		if( !($key = Base::instance()->get_brand_attribute()) ){
			return;
		}

		if( ! taxonomy_exists($key) ){
			return;
		}

		$current_control = $section->get_control($control_args['settings']);
		$current_control['choices'][$key] = esc_html__( 'Brand names', 'rey-core' );
		$section->update_control( $current_control );
	}


	function options_acf(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => 'group_5fcba3fc1d798',
			'title' => 'Brand options',
			'fields' => array(
				array(
					'key' => 'field_5fcba41e2d985',
					'label' => 'Brand Image',
					'name' => 'rey_brand_image',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => Base::instance()->get_brand_attribute(),
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));
	}

	function elementor_grid_component_visiblity( $element ){

		$element->add_control(
			'hide_brands',
			[
				'label' => __( 'Brand', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);
	}

	function elementor_add_brand_settings( $element ){

		$brand_attribute = Base::instance()->get_brand_attribute_slug();

		$element->add_control(
			'brand_options_title',
			[
			   'label' => esc_html__( 'BRANDS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
				],
			]
		);

		$element->add_control(
			'show_brand_images',
			[
				'label' => esc_html__( 'Show Brand Images', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
				],
			]
		);

		$element->add_responsive_control(
			'brand_images_size',
			[
				'label' => esc_html__( 'Image size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
					'show_brand_images!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .__img-link img' => 'max-width: {{VALUE}}px;',
				],
			]
		);

		$element->add_control(
			'hide_brand_link',
			[
				'label' => esc_html__( 'Text link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'rey-core' ),
				'label_off' => esc_html__( 'Hide', 'rey-core' ),
				'return_value' => 'none',
				'default' => '',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
					'show_brand_images!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .__img-link .__text:not(:only-child)' => 'display: {{VALUE}};',
				],
			]
		);

	}

	/**
	 * Runs on v2.3.7 update and sets the Brands
	 * taxonomy public, if it exists.
	 *
	 * @return void
	 */
	public function update__make_public( $custom_taxonomy = '' ){

		// if it's a custom tax (from customizer),
		// just make it public (if it's not already)
		if( $custom_taxonomy && taxonomy_exists( $custom_taxonomy ) ){

			$is_public = ! empty( array_filter( wc_get_attribute_taxonomies(), function($attribute) use ($custom_taxonomy){
				return (bool) $attribute->attribute_public && $attribute->attribute_name === str_replace('pa_', '', $custom_taxonomy);
			} ) );

			// make sure it's not public, otherwise stop.
			if( ! $is_public ){
				return;
			}

			$brand_attribute = $custom_taxonomy;
		}

		else {

			// Brand taxonomy does not exist.
			if( ! ($brand_attribute = Base::instance()->brands_tax_exists()) ){
				return;
			}

			// Already public!
			if( Base::instance()->brands_taxonomy_is_public() ){
				return;
			}
		}

		if( ! current_user_can('manage_woocommerce') ){
			return;
		}

		$update = wc_update_attribute( wc_attribute_taxonomy_id_by_name( $brand_attribute ), [
			'has_archives' => 1
		] );

		// Cannot set to public
		if( is_wp_error($update) ){
			return;
		}

		// Clear cache and flush rewrite rules.
		wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
		delete_transient( 'wc_attribute_taxonomies' );
		\WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );

	}

	function add_image_column( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( $key === 'cb' ) { // Insert after the 'cb' column
				$new_columns['thumb'] = __( 'Image', 'woocommerce' );
			}
		}
		return $new_columns;
	}

	function add_column_for_image( $columns, $column, $term_id ) {

		if ( 'thumb' === $column ) {
			$term = get_term( $term_id, Base::instance()->get_brand_attribute() );
			$thumbnail_id = get_field( 'rey_brand_image', $term);

			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = wc_placeholder_img_src();
			}

			// Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605 .
			$image    = str_replace( ' ', '%20', $image );
			$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'woocommerce' ) . '" class="wp-post-image" height="48" width="48" />';
		}

		return $columns;
	}

	/**
	 * After a brand taxonomy is changed in Customizer,
	 * ensure it'll be set as public (if it's not already)
	 *
	 * @param object $setting
	 * @return void
	 */
	public function ensure_enabled_archives_on_change_tax($setting){

		if( ! method_exists($setting, 'value') ){
			return;
		}

		if( ! ($old_value = $setting->value()) ){
			return;
		}

		$new_tax = $setting->post_value();

		// ensure it's not the same tax
		if( $old_value === $new_tax ){
			return;
		}

		$this->update__make_public( $new_tax );
	}

}
