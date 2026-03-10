<?php
/**
 * Rey Ajax Product Filter by Category
 */
if (!class_exists('REYAJAXFILTERS_Category_Filter_Widget')) {
	class REYAJAXFILTERS_Category_Filter_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-category-filter', // Base ID
				__('Filter - by Category', 'rey-core'), // Name
				array('description' => __('Filter WooCommerce products by category.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'                   => '',
				'custom_height'           => '',
				'query_type'              => 'or',
				'hide_empty'              => '',
				'order_by'                => 'name',
				'search_box'              => false,
				'enable_multiple'         => false,
				'show_count'              => false,
				'count_stretch'           => '',
				'hierarchical'            => false,
				'cat_structure'           => '',
				'manual_cat_ids'           => '',
				'show_back_btn'           => false,
				'accordion_list'          => false,
				'parent_click_behaviour'  => 'toggle',
				'show_checkboxes'         => false,
				'show_checkboxes__radio'  => false,
				'display_type'            => 'list',
				'rey_multi_col'           => false,
				'alphabetic_menu'         => false,
				'drop_panel'              => false,
				'drop_panel_keep_active'  => false,
				// dropdown
				'placeholder'             => '',
				'dd_width'                => '',
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display'       => [],
				'value_type'              => 'id',
				'key_name'                => '',

				// Legacy
				'show_children_only'            => false,
				'show_children_only__ancestors' => false,
			];
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			if( \ReyCore\Modules\AjaxFilters\Base::should_hide_widget($instance) ){
				return;
			}

			if ( ! ($query_type = $instance['query_type']) ) {
				return;
			}

			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			$taxonomy   = 'product_cat';
			$display_type = $instance['display_type'];
			$is_list = $display_type === 'list';
			$data_key   = ($query_type === 'and') ? 'product-cata' : 'product-cato';

			if( $custom_key = esc_attr($instance['key_name']) ){
				$data_key = $custom_key;
			}

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$attr_args = [
				'taxonomy'           => $taxonomy,
				'data_key'           => $data_key,
				'url_array'          => $url_array,
				'query_type'         => $query_type,
				'enable_multiple'    => (bool) $instance['enable_multiple'],
				'show_count'         => (bool) $instance['show_count'],
				'enable_hierarchy'   => (bool) $instance['hierarchical'],
				'cat_structure' 	 => $instance['cat_structure'],
				'manual_cat_ids' 	 => $instance['manual_cat_ids'],
				'show_back_btn' 	 => (bool) $instance['show_back_btn'] && in_array($instance['cat_structure'], ['all_current', 'current']),
				'hide_empty'         => (bool) $instance['hide_empty'],
				'order_by'           => $instance['order_by'],
				'custom_height'      => (!empty($instance['custom_height']) && $is_list) ? $instance['custom_height']: '',
				'alphabetic_menu'    => ((bool) $instance['alphabetic_menu'] && $is_list),
				'search_box'         => ((bool) $instance['search_box']),
				'accordion_list'     => ((bool) $instance['accordion_list'] && $is_list && (bool) $instance['hierarchical'] ),
				'show_checkboxes'    => (bool) $instance['show_checkboxes'],
				'drop_panel'         => (bool) $instance['drop_panel'],
				'drop_panel_button'  => $instance['title'] ? $instance['title'] : esc_html__('Categories', 'rey-core'),
				'drop_panel_keep_active'  => (bool) $instance['drop_panel_keep_active'],
				'value_type'         => $instance['value_type'],

				'widget_id' => $args['widget_id'],

				// Legacy
				'dropdown'           => ($display_type === 'dropdown') && ! (bool) $instance['drop_panel'], // BC
				'placeholder'        => $instance['placeholder'],
				'dd_width'           => $instance['dd_width'],
				'show_children_only'            => (bool) $instance['show_children_only'],
				'show_children_only__ancestors' => (bool) $instance['show_children_only__ancestors'],
			];

			$attr_args['show_checkboxes__radio'] = $attr_args['show_checkboxes'] && (bool) $instance['show_checkboxes__radio'];

			$output = reyajaxfilter_terms_output($attr_args);

			if( !isset($output['html']) ){
				return;
			}

			$html = $output['html'];
			$found = $output['found'];

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			// if $selected_terms array is empty we will hide this widget totally
			if ($found === false) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-term-filter';
			} else {
				$widget_class = 'woocommerce reyajfilter-ajax-term-filter';

				// Backwards compatible
				if( $display_type !== 'dropdown'){

					if(
						'list' === $display_type
						&& ! $instance['hierarchical']
						&& (bool) $instance['rey_multi_col']
					){
						$widget_class .= ' rey-filterList-cols';
						$before_widget .= \ReyCore\Modules\AjaxFilters\Base::multicols_css();
					}

					if( (bool) $instance['show_count'] && (bool) $instance['count_stretch'] ){
						$widget_class .= ' --count-stretch';
					}
				}

			}

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			echo $before_widget;

			// For drop panel, title will be used as button
			if ( ! empty($instance['title']) && ! $instance['drop_panel'] ) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title'], $instance). $args['after_title'];
			}

			echo $html;

			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form($instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			do_action('reycore/ajaxfilters/before_widget_controls', $instance);

			$widget_fields = new \ReyCore\Modules\AjaxFilters\WidgetFields($this, $instance);

			$display_name = $this->get_field_name('display_type'); ?>

			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
					<span data-tab="style"><?php esc_html_e('Styles', 'rey-core') ?></span>
					<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
				</div>

				<div class="rey-widgetTabs-tabContent --active" data-tab="basic">

					<?php

					$widget_fields->add_field([
						'name' => 'title',
						'type' => 'text',
						'label' => __( 'Title', 'rey-core' ),
						'value' => '',
						'field_class' => 'widefat'
					]);

					$widget_fields->add_field([
						'name' => 'display_type',
						'type' => 'select',
						'label' => __( 'Display Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'list' => esc_html__('List', 'rey-core'),
							'dropdown' => esc_html__('Dropdown (Deprecated)', 'rey-core'),
						]
					]);

					// Show all on first timers
					$default_structure = 'all';

					// Legacy: show Current if previous "show_children_only" was enabled
					if( isset($instance['show_children_only']) && $instance['show_children_only'] ){
						$default_structure = 'current';

						// show Ancestors if previous "show_children_only__ancestors" was enabled
						if( isset($instance['show_children_only__ancestors']) && $instance['show_children_only__ancestors'] ){
							$default_structure = 'all_ancestors';
						}
					}

					// Just show what was previously saved
					if( $instance['cat_structure'] ){
						$default_structure = $instance['cat_structure'];
					}

					$widget_fields->add_field([
						'name' => 'cat_structure',
						'type' => 'select',
						'label' => __( 'Structure', 'rey-core' ),
						'value' => $default_structure,
						'options' => [
							'all'  => esc_html__( 'Show all categories and sub-categories', 'rey-core' ),
							'all_current'  => esc_html__( 'Show all sub-categories of the current category', 'rey-core' ),
							'all_ancestors'  => esc_html__( 'Show ancestors and current sub-categories', 'rey-core' ),
							'current'  => esc_html__( 'Show only direct sub-categories of the current category', 'rey-core' ),
							'manual'  => esc_html__( 'Manually pick categories', 'rey-core' ),
							'manual_sub'  => esc_html__( 'Manually picked Parents & Sub-categories', 'rey-core' ),
						]
					]);

					$widget_fields->add_field([
						'name' => 'manual_cat_ids',
						'type' => 'text',
						'label' => __( 'Product Category IDs', 'rey-core' ),
						'value' => '',
						'placeholder' => 'eg: 11, 12, 13',
						'field_class' => 'widefat',
						'conditions' => [
							[
								'name' => 'cat_structure',
								'value' => ['manual', 'manual_sub'],
								'compare' => 'in'
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'drop_panel',
						'type' => 'checkbox',
						'label' => __( 'Display as Drop-down', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown', // make sure to avoid dropdown
								'compare' => '!='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'drop_panel_keep_active',
						'type' => 'checkbox',
						'label' => __( 'Keep dropdown open after selection', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'drop_panel',
								'value' => '',
								'compare' => '!='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'hierarchical',
						'type' => 'checkbox',
						'label' => __( 'Show hierarchy', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'enable_multiple',
						'type' => 'checkbox',
						'label' => __( 'Enable multiple filter', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'show_count',
						'type' => 'checkbox',
						'label' => __( 'Show Counter', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'count_stretch',
						'type' => 'checkbox',
						'label' => __( 'Stretch Counter', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'show_count',
								'value' => '',
								'compare' => '!='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'show_back_btn',
						'type' => 'checkbox',
						'label' => __( 'Show a back to parent button at the top.', 'rey-core' ),
						'value' => '1',
						// 'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'cat_structure',
								'value' => ['all_current', 'current'],
								'compare' => 'in'
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'hide_empty',
						'type' => 'checkbox',
						'label' => __( 'Hide empty', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'order_by',
						'type' => 'select',
						'label' => __( 'Order By', 'rey-core' ),
						'value' => 'name',
						'options' => [
							'name'  => esc_html__( 'Name', 'rey-core' ),
							'menu_order'  => esc_html__( 'Category Order', 'rey-core' ),
						],
						'conditions' => [
							[
								'name' => 'cat_structure',
								'value' => 'manual',
								'compare' => '!='
							],
						],
					]);

					?>
				</div>
				<!-- end tab -->


				<div class="rey-widgetTabs-tabContent" data-tab="style">

				<?php

					$widget_fields->add_field([
						'name' => 'show_checkboxes',
						'type' => 'checkbox',
						'label' => __( 'Show checkboxes', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'show_checkboxes__radio',
						'type' => 'checkbox',
						'label' => __( 'Display checkboxes as radio', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'show_checkboxes',
								'value' => true,
								'compare' => '=='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'search_box',
						'type' => 'checkbox',
						'label' => __( 'Show search (filter) field', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'type' => 'title',
						'label' => __( 'LIST OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					$widget_fields->add_field([
						'name' => 'rey_multi_col',
						'type' => 'checkbox',
						'label' => __( 'Display list on 2 columns', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '!='
							],
						]
					]);

					$widget_fields->add_field([
						'name' => 'accordion_list',
						'type' => 'checkbox',
						'label' => __( 'Display list as accordion', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '=='
							],
							[
								'name' => 'cat_structure',
								'value' => ['all', 'all_ancestors', 'all_current'],
								'compare' => 'in'
							],
						]
					]);

					$widget_fields->add_field([
						'name' => 'alphabetic_menu',
						'type' => 'checkbox',
						'label' => __( 'Show alphabetic menu', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						]
					]);

					$widget_fields->add_field([
						'name' => 'custom_height',
						'type' => 'number',
						'label' => __( 'Custom Height', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					// $widget_fields->add_field([
					// 	'name' => 'item_font_size',
					// 	'type' => 'number',
					// 	'label' => __( 'Font Size', 'rey-core' ),
					// 	'value' => '',
					// 	'field_class' => 'small-text',
					// 	'options' => [
					// 		'step' => 1,
					// 		'min' => 50,
					// 		'max' => 1000,
					// 	],
					// 	'suffix' => 'px'
					// ]);

					// START BC

					$widget_fields->add_field([
						'type' => 'title',
						'label' => __( 'DROPDOWN OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					$widget_fields->add_field([
						'name' => 'placeholder',
						'type' => 'text',
						'label' => __( 'Placeholder', 'rey-core' ),
						'value' => '',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'placeholder' => esc_html__('eg: Choose', 'rey-core')
					]);

					$widget_fields->add_field([
						'name' => 'dd_width',
						'type' => 'number',
						'label' => __( 'Custom dropdown width', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'compare' => '==',
								'value' => 'dropdown',
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					// END BC


				?>

				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<?php

					$widget_fields->add_field([
						'type' => 'title',
						'label' => 'URL',
						'wrapper_class' => '',
						'field_class' => 'rey-widget-innerTitle',
					]);

					$widget_fields->add_field([
						'name'        => 'key_name',
						'type'        => 'text',
						'label'       => __( 'Key Name', 'rey-core' ),
						'value'       => '',
						'placeholder' => esc_html__('eg: category', 'rey-core')
					]);

					$widget_fields->add_field([
						'name' => 'value_type',
						'type' => 'select',
						'label' => __( 'Key Value type', 'rey-core' ),
						'value' => 'id',
						'options' => [
							'id' => esc_html__('ID', 'rey-core'),
							'slug' => esc_html__('Slug', 'rey-core'),
						],
					]);

					echo '<hr>';

					$widget_fields->add_field([
						'type' => 'title',
						'label' => 'VISIBILITY',
						'wrapper_class' => '',
						'field_class' => 'rey-widget-innerTitle',
					]);

					$widget_fields->add_field([
						'name' => 'show_hide_categories',
						'type' => 'select',
						'label' => __( 'Show or Hide widget on certain categories:', 'rey-core' ),
						'value' => 'hide',
						'options' => [
							'show' => esc_html__('Show', 'rey-core'),
							'hide' => esc_html__('Hide', 'rey-core'),
						]
					]);

					$widget_fields->add_field([
						'name' => 'show_only_on_categories',
						'type' => 'select',
						'multiple' => true,
						'label' => __( 'Categories:', 'rey-core' ),
						'wrapper_class' => '--stretch',
						'options' => function_exists('reycore_wc__product_categories') ? reycore_wc__product_categories() : []
					]);

					echo '<hr>';

					$default_selective_display = [];

					if( isset($instance['selective_display']) && 'cat_attr_tag' === $instance['selective_display'] ){
						$default_selective_display = ['cat', 'attr', 'tag'];
					}

					$widget_fields->add_field([
						'name' => 'selective_display',
						'type' => 'select',
						'multiple' => true,
						'label' => __( 'Display widget only on:', 'rey-core' ),
						'value' => $default_selective_display,
						'wrapper_class' => '--stretch',
						'options' => [
							'shop' => esc_html__('Shop Page', 'rey-core'),
							'search' => esc_html__('Search Page', 'rey-core'),
							'cat' => esc_html__('Product Categories', 'rey-core'),
							'attr' => esc_html__('Product Attributes (public archive)', 'rey-core'),
							'tag' => esc_html__('Product Tags (archive)', 'rey-core'),
						]
					]);

					echo '<hr>';

					$widget_fields->add_field([
						'type' => 'title',
						'label' => 'QUERY',
						'wrapper_class' => '',
						'field_class' => 'rey-widget-innerTitle',
					]);

					$widget_fields->add_field([
						'name' => 'query_type',
						'type' => 'select',
						'label' => __( 'Query Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'or' => esc_html__('OR', 'rey-core'),
							'and' => esc_html__('AND', 'rey-core'),
						]
					]);

					$widget_fields->add_field([
						'type' => 'title',
						'label' => '<small>' . __( '"AND" - show only products that satisfy both conditions set by the user through applied filters (for this particular widget).<br>"OR" - search returns all products that satisfy at least one of the conditions set by the user through applied filters (for this particular widget).', 'rey-core' ) . '</small>',
						'wrapper_class' => 'description',
						'field_class' => '',
					]);
					?>

				</div>
				<!-- end tab -->

			</div>

			<?php
				reyajaxfilters__filter_admin_titles( $instance['show_only_on_categories'], $instance['show_hide_categories'] );
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update($new_instance, $old_instance) {

			$instance = \ReyCore\Modules\AjaxFilters\Helpers::update_widgets([
				'widget' => $this,
				'new_instance' => $new_instance,
			]);

			\ReyCore\Modules\AjaxFilters\Helpers::save_custom_key([
				'new_instance' => $new_instance,
				'old_instance' => $old_instance,
				'taxonomy'     => 'product_cat',
			]);

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_category_filter_widget')) {
	function reyajaxfilter_register_category_filter_widget() {
		register_widget('REYAJAXFILTERS_Category_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_category_filter_widget');
}
