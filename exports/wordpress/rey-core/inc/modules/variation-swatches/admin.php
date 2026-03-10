<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Admin
{
	private $attribute_settings_supports = [];
	private $tax_attribute_type = [];

	public $fields = [];

	public function __construct()
	{

		add_action( 'reycore/customizer/control=single_product_hide_out_of_stock_variation', [ $this, 'customizer__add_controls' ], 10, 2 );
		add_filter( 'reycore/woocommerce/loop/attributes_list', [ $this, 'attributes_list_all_choice' ]);
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_filter( 'product_attributes_type_selector', [ $this, 'add_attribute_types' ] );
		add_action( 'woocommerce_product_option_terms', [$this, 'woocommerce_product_option_terms'], 10, 3 );
		add_action( 'woocommerce_after_add_attribute_fields', [ $this, 'after_add_attribute_fields' ] );
		add_action( 'woocommerce_after_edit_attribute_fields', [ $this, 'after_edit_attribute_fields' ] );
		add_action( 'woocommerce_attribute_added', [ $this, 'attribute_added' ], 10, 2 );
		add_action( 'woocommerce_attribute_updated', [ $this, 'attribute_updated' ], 10, 3 );
		add_action( 'woocommerce_attribute_deleted', [$this, 'attribute_deleted'], 20, 2 );
		add_action( 'create_term', [$this, 'clear_attribute_term_transient'], 10, 3 );
		add_action( 'edit_term', [$this, 'clear_attribute_term_transient'], 10, 3 );
		add_action( 'delete_term', [$this, 'clear_attribute_term_transient'], 10, 3 );
		add_action( 'reycore/demo_import/attributes', [$this, 'demo_import'] );
		add_action( 'reycore/import/config', [$this, 'demo_import'] );
		add_filter( 'rey/export/config', [$this, 'export_swatches_options']);

		$this->add_term_controls();
		$this->add_attribute_taxonomy_columns();

	}

	function admin_scripts(){

		\wp_enqueue_style(
			Base::ASSET_HANDLE . '-admin',
			Base::get_path( basename( __DIR__ ) ) . '/admin-style.css',
			[],
			REY_CORE_VERSION
		);

	}

	function add_attribute_types($types)
	{
		return array_merge($types, Base::instance()->get_swatches_list());
	}

	function get_attributes_fields(){

		if( empty($this->fields) ){
			return $this->attributes_fields();
		}

		return $this->fields;
	}

	function attributes_fields(){

		$fields['swatches_title'] = [
			'type'          => 'heading',
			'label'         => esc_html__( 'Swatches Settings', 'rey-core' ),
		];

		$fields['swatch_tooltip'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Tooltip', 'rey-core' ),
			'value'         => 'no',
			'field_class'   => '',
			'options'       => [
				'no'       => esc_html__('No', 'rey-core'),
				'yes'      => esc_html__('Yes - Show title', 'rey-core'),
				'yes_desc' => esc_html__('Yes - Show description', 'rey-core'),
				'yes_both' => esc_html__('Yes - Show both', 'rey-core'),
			],
		];

		$fields['swatch_tooltip_image'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Tooltip - Show enlarged image', 'rey-core' ),
			'value'         => 'no',
			'field_class'   => '',
			'options'       => [
				'no'       => esc_html__('No', 'rey-core'),
				'yes'      => esc_html__('Yes', 'rey-core'),
			],
			'conditions'    => [
				[
					'name'    => Base::FIELDS_PREFIX . 'swatch_tooltip',
					'value'   => 'no',
					'compare' => '!=',
				]
			],
		];

		$fields['use_variation_img'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Use Variation Image', 'rey-core' ),
			'value'         => 'no',
			'field_class'   => '',
			'options'       => [
				'no'       => esc_html__('No', 'rey-core'),
				'yes'      => esc_html__('Yes', 'rey-core'),
			],
		];

		$fields['label_display'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Label display', 'rey-core' ),
			'value'         => '',
			'field_class'   => '',
			'options'       => [
				''  => esc_html__('Default', 'rey-core'),
				's' => esc_html__('Stretched', 'rey-core'),
				'i' => esc_html__('Inline', 'rey-core'),
			],
		];

		$fields['swatches_style'] = [
			'type'          => 'heading',
			'label'         => esc_html__( 'Swatches Styles (Product page)', 'rey-core' ),
		];

		$fields['swatch_width'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Width', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'placeholder'   => 'eg: 30',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 5,
				'max'  => 1000,
				'step' => 1,
			],
		];

		$fields['swatch_height'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Height', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'placeholder'   => 'eg: 30',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 5,
				'max'  => 1000,
				'step' => 1,
			],
		];

		$fields['swatch_radius'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Corner Radius', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 0,
				'max'  => 500,
				'step' => 1,
			],
		];

		$fields['swatch_font_size'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Font Size', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 8,
				'max'  => 500,
				'step' => 1,
			],
		];

		$fields['swatch_padding'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Padding', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 0,
				'max'  => 500,
				'step' => 1,
			],
		];

		$fields['swatch_spacing'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Spacing between items', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 0,
				'max'  => 500,
				'step' => 1,
			],
		];

		$fields = apply_filters('reycore/variation_swatches/attribute_fields', $fields, $this);

		$this->set_settings_support();

		$new_fields = [];

		foreach ($fields as $name => $field) {

			$field['name'] = $name;

			if( $conditions = $this->get_conditions( $field ) ){
				$field['conditions'] = $conditions;
			}

			$new_fields[] = $field;

		}

		return $new_fields;
	}

	function set_settings_support(){
		foreach ( Base::instance()->get_swatches() as $id => $type ) {
			$this->attribute_settings_supports[$id] = $type->get_attribute_settings_support();
		}
	}

	function get_conditions( $field ){

		$conditions = [];

		if( isset($field['conditions']) ){
			$conditions = $field['conditions'];
		}

		$a_type = [];

		foreach( $this->attribute_settings_supports as $id => $supports ){
			if( in_array($field[ 'name' ], $supports, true) ){
				$a_type[] = $id;
			}
		}

		if( ! empty($a_type) ){
			$conditions[] = [
				'name'    => 'attribute_type',
				'value'   => $a_type,
				'compare' => 'in',
			];
		}

		return $conditions;
	}

	function setup_controls(){

		$data = [];

		$attribute_id = isset( $_REQUEST['edit'] ) ? absint( sanitize_text_field( $_REQUEST['edit'] ) ) : 0;

		if( $attribute_id ){

			$attribute = (array) wc_get_attribute($attribute_id);

			if( isset($attribute['slug']) && ($taxonomy = $attribute['slug']) ){
				if( $saved_data = Base::get_attributes_swatch_settings( $taxonomy ) ){
					$data = $saved_data;
				}
			}

		}

		if( ! class_exists('\ReyCore\Libs\ControlFields') ){
			return;
		}

		return new \ReyCore\Libs\ControlFields([
			'data' => $data,
			'prefix' => Base::FIELDS_PREFIX
		]);
	}

	function controls_start(){
		?>
		<div
			class="rey-swatchesSetting-wrapper hidden"
			data-rey-controls='<?php echo wp_json_encode([
				'form-scope'    => '.rey-swatchesSetting-wrapper',
				'condition-attribute'   => 'data-rey-condition',
			]); ?>'
			data-rey-condition='<?php echo wp_json_encode([[
				'name'    => 'attribute_type',
				'value'   => array_keys( Base::instance()->get_swatches_list() ),
				'compare' => 'in',
			]]); ?>'
		>
		<?php
	}

	function controls_end(){
		?>
		</div>
		<?php
	}

	function after_edit_attribute_fields(){

		?>
			<tr class="form-field form-required">
				<td colspan="2">
		<?php

		if( $control_fields = $this->setup_controls() ){

			$this->controls_start();

			foreach($this->get_attributes_fields() as $field){
				$control_fields->add_control($field);
			}

			$this->controls_end();

		} ?>
			</td>
		</tr>
		<?php
	}

	function after_add_attribute_fields(){

		if( ! ($control_fields = $this->setup_controls()) ){
			return;
		}

		$this->controls_start();

		foreach($this->get_attributes_fields() as $field){
			$control_fields->add_control($field);
		}

		$this->controls_end();

	}

	private function update_attribute( $args ){

		$args = wp_parse_args($args, [
			'attribute_id' => '',
			'attribute'    => [],
			'flush'        => false,
			'old_attribute_name' => ''
		]);

		if( ! ($attribute_id = $args['attribute_id']) ){
			return;
		}
		if( empty($args['attribute']) ){
			return;
		}

		$attribute = $args['attribute'];
		$taxonomy = wc_attribute_taxonomy_name($attribute['attribute_name']);

		$data = Base::get_attributes_swatch_settings();

		foreach( $this->get_attributes_fields() as $field ){

			$field_name = Base::FIELDS_PREFIX . $field['name'];

			if( isset( $_REQUEST[ $field_name ] ) && isset($attribute['attribute_name']) ){
				$data[ $taxonomy ][ $field['name'] ] = reycore__clean($_REQUEST[ $field_name ]);
			}

		}

		if( empty($data) ){
			return;
		}

		$data[ $taxonomy ][ 'attribute_label' ] = $attribute['attribute_label'];
		$data[ $taxonomy ][ 'attribute_type' ] = $attribute['attribute_type'];
		$data[ $taxonomy ][ 'attribute_id' ] = $attribute_id;

		if( $args['old_attribute_name'] && $args['old_attribute_name'] !== $attribute['attribute_name'] ){
			unset($data[ wc_attribute_taxonomy_name($args['old_attribute_name']) ]);
		}

		if( Base::set_attributes_swatch_settings($data) ){
			if( $args['flush'] ){
				wp_cache_flush();
			}
		}

		$this->__clear_term_transient($taxonomy);
	}

	public function attribute_deleted($attribute_id, $attribute){

		if( ! isset($attribute['attribute_name']) ){
			return;
		}

		$taxonomy = wc_attribute_taxonomy_name($attribute['attribute_name']);

		$data = Base::get_attributes_swatch_settings();

		if( ! isset($data[ $taxonomy ]) ){
			return;
		}

		unset($data[ $taxonomy ]);

		Base::set_attributes_swatch_settings($data);
	}

	public function attribute_added( $attribute_id, $attribute ) {
		$this->update_attribute([
			'attribute_id' => $attribute_id,
			'attribute' => $attribute,
		]);
	}


	public function attribute_updated( $attribute_id, $attribute, $old_attribute_name ) {
		$this->update_attribute([
			'attribute_id' => $attribute_id,
			'attribute' => $attribute,
			'flush' => true,
			'old_attribute_name' => $old_attribute_name
		]);
	}

	function add_term_controls(){
		foreach (Base::instance()->get_swatches() as $swatch) {
			if( function_exists('acf_add_local_field_group') ){
				$swatch->add_terms_settings();
			}
		}
	}

	public function add_attribute_taxonomy_columns() {

		if( ! is_admin() ){
			return;
		}

		$attributes = wc_get_attribute_taxonomies();

		if ( ! $attributes ) {
			return;
		}

		foreach ( $attributes as $attribute ) {

			$tax = $attribute->attribute_name;

			add_filter( "manage_edit-pa_{$tax}_columns", [$this, 'taxonomy_columns'] );
			add_filter( "manage_pa_{$tax}_custom_column", [$this, 'taxonomy_columns_content'], 10, 3 );
		}
	}

	function get_tax_attribute_type( $tax ){

		if( isset($this->tax_attribute_type[$tax]) ){
			return $this->tax_attribute_type[$tax];
		}

		foreach (wc_get_attribute_taxonomies() as $key => $attribute) {

			if( ($attribute_name = str_replace('pa_', '', $tax)) && $attribute->attribute_name !== $attribute_name ){
				continue;
			}

			$this->tax_attribute_type[ $tax ] = $attribute->attribute_type;
		}

		if( isset($this->tax_attribute_type[$tax]) ){
			return $this->tax_attribute_type[$tax];
		}
	}

	function taxonomy_columns_content( $columns, $column, $term_id ) {

		if( 'rey-swatch-preview' !== $column ){
			return $columns;
		}

		if ( ! (isset( $_REQUEST['taxonomy'] ) && $tax = reycore__clean($_REQUEST['taxonomy'])) ) {
			return $columns;
		}

		if( ! ( $swatch_type = $this->get_tax_attribute_type($tax) ) ){
			return $columns;
		}

		if( $swatch_type === 'select' ){
			return $columns;
		}

		$swatch_style = Base::instance()->get_swatches($swatch_type)->get_swatch_style($term_id);

		if( empty($swatch_style) ){
			return $columns;
		}

		printf('<span class="rey-swatch-previewItem --%s" style="%s"></span>', esc_attr($swatch_type), esc_attr($swatch_style));

		return $columns;

	}

	public function taxonomy_columns( $columns ) {

		if ( ! (isset( $_REQUEST['taxonomy'] ) && $tax = reycore__clean($_REQUEST['taxonomy'])) ) {
			return $columns;
		}

		if( ! ( $swatch_type = $this->get_tax_attribute_type($tax) ) ){
			return $columns;
		}

		if( $swatch_type === 'select' ){
			return $columns;
		}

		if( ! Base::instance()->get_swatches($swatch_type)->has_preview ){
			return $columns;
		}

		$new_columns = [];

		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		$new_columns['rey-swatch-preview'] = '';

		if ( isset( $columns['cb'] ) ) {
			unset( $columns['cb'] );
		}

		return array_merge( $new_columns, $columns );
	}

	function __clear_term_transient($taxonomy){
		delete_transient( Base::get_attribute_transient_name( $taxonomy ) );
	}

	function clear_attribute_term_transient($term_id, $tt_id, $taxonomy){
		$this->__clear_term_transient($taxonomy);
	}

	public function export_swatches_options( $output ){

		if( $swatches_options = Base::get_attributes_swatch_settings() ){
			$output[Base::OPT] = $swatches_options;
		}

		return $output;
	}

	public function demo_import( $config ){

		if( isset($config['config'][ Base::OPT ]) && ($swatches_config = $config['config'][ Base::OPT ])){
			update_option(Base::OPT, $swatches_config, false);
		}

		$this->demo_import_clear_caches();
	}

	function demo_import_clear_caches(){
		delete_transient( 'wc_attribute_taxonomies' );
		\WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
	}

	/**
	 * Code copied from wp-content/plugins/woocommerce/includes/admin/meta-boxes/views/html-product-attribute.php
	 */
	function woocommerce_product_option_terms($attribute_taxonomy, $i, $attribute){

		if ( ! array_key_exists( $attribute_taxonomy->attribute_type, Base::instance()->get_swatches_list() ) ) {
			return;
		}

		?>
		<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
			<?php

			$args      = array(
				'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
				'hide_empty' => 0,
			);

			$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );

			if ( $all_terms ) {
				foreach ( $all_terms as $term ) {
					$options = $attribute->get_options();
					$options = ! empty( $options ) ? $options : array();
					echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options ) . '>' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
				}
			}
			?>
		</select>
		<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></button>
		<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></button>
		<button class="button fr plus add_new_attribute"><?php esc_html_e( 'Add new', 'woocommerce' ); ?></button>
		<?php

	}

	function customizer__add_controls($control_args, $section){

		if( ! Base::instance()->is_enabled() ){
			return;
		}

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'pdp_swatches__disabled_behaviour',
			'label'       => esc_html__( 'Disabled swatch behviour', 'rey-core' ),
			'default'     => 'dim',
			'choices'     => [
				'dim'   => esc_html__( 'Dim Swatch (Fade)', 'rey-core' ),
				'xmark' => esc_html__( 'Show "&times;" Mark', 'rey-core' ),
				'hide'  => esc_html__( 'Hide', 'rey-core' ),
				''  => esc_html__( '- None -', 'rey-core' ),
			],
			'help' => [
				esc_html__('Select the behaviour of the swatches when a variation is inactive/disabled. This applies to all button type swatches except Select lists.', 'rey-core'),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'pdp_swatches__deselection',
			'label'       => esc_html__( 'De-selecting swatches', 'rey-core' ),
			'default'     => 'clear',
			'choices'     => [
				'clear'   => esc_html__( 'Show "Clear" button', 'rey-core' ),
				'click' => esc_html__( 'Click again to deselect', 'rey-core' ),
				'both' => esc_html__( 'Both choices', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'pdp_swatches__selected_name_v2',
			'label'       => esc_html_x( 'Show selected', 'Customizer control', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				''   => esc_html__( 'No', 'rey-core' ),
				'name' => esc_html__( 'Yes - Attribute Name', 'rey-core' ),
				'desc' => esc_html__( 'Yes - Attribute Description', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'pdp_swatches__stretch_label',
			'label'       => __('Stretch Labels', 'rey-core'),
			'help' => [
				__('Enable if you want the swatch labels to be above the swatches, instead of next to them.', 'rey-core')
			],
			'default'     => false,
		] );

		$section->add_control( [
			'type'     => 'select',
			'settings' => 'pdp_swatches__update_gallery',
			'label'       => esc_html__('Single attribute update gallery', 'rey-core'),
			'help' => [
				__('Force a single attribute to update the gallery. By default only when choosing all variations the gallery updates.', 'rey-core')
			],
			'default'  => '',
			'choices'  => [
				'' => __('- Disabled -', 'rey-core'),
			],
			'ajax_choices' => 'get_woo_attributes_list',
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'pdp_swatches__custom_use_button',
			'label'       => __('Custom Attributes use Buttons', 'rey-core'),
			'help' => [
				__('Enable if you want the custom attributes created on the fly in products, to be displayed as buttons rather then select list.', 'rey-core')
			],
			'default'     => false,
		] );

		// Keep it disabled for some time
		// $section->add_control( [
		// 	'type'        => 'rey-number',
		// 	'settings'    => 'pdp_swatches__ajax_threshold',
		// 'label'       => __('Ajax Threshold', 'rey-core'),
		// 'help' => [
			// __('When your variable product has more than 30 variations, WooCommerce will use ajax to load variations data. If you set "1" all product variation will be loaded via ajax.', 'rey-core')
		// ],
		// 	'default'     => 50,
		// 	'choices'     => [
		// 		'min'  => 1,
		// 		'max'  => 100,
		// 		'step' => 1,
		// 	],
		// ] );

		$section->add_section_marker('swatches_after_settings');

	}

	function attributes_list_all_choice( $list ){
		if( ! Base::instance()->is_enabled() ){
			return $list;
		}
		return array_merge($list, ['all_attributes' => esc_html__( '- All Attributes -', 'rey-core' )]);
	}


}
