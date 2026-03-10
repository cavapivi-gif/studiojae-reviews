<?php
/**
 * Rey Ajax Product Filter by Custom Fields
 */
if (!class_exists('REYAJAXFILTERS_CustomFields_Filter_Widget')) {
	class REYAJAXFILTERS_CustomFields_Filter_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-custom-fields-filter', // Base ID
				__('Filter - by Meta Fields', 'rey-core'), // Name
				[
					'description' => __('Filter WooCommerce products by their meta fields.', 'rey-core')
				]
			);

			$this->defaults = [
				'title'                   => '',
				'display_type'            => 'list',
				'custom_fields'           => [],
				'search_box'              => false,
				'enable_multiple'         => false,
				'show_count'              => false,
				'count_stretch'           => '',
				'show_checkboxes'         => false,
				'show_checkboxes__radio'  => false,
				'custom_height'           => '',
				'rey_multi_col'           => false,
				'alphabetic_menu'         => false,
				'drop_panel'              => false,
				'drop_panel_keep_active'  => false,
				// dropdown
				'dropdown'              => false,
				'placeholder'             => '',
				'dd_width'                => '',
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display' => [],
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
		public function widget($widget_args, $instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			if( \ReyCore\Modules\AjaxFilters\Base::should_hide_widget($instance) ){
				return;
			}

			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			$display_type = $instance['display_type'];
			$is_list = $display_type === 'list';

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$args = [
				'data_key'        => 'product-meta',
				'url_array'          => $url_array,
				'display_type'   => $instance['display_type'],
				'custom_fields'   => $instance['custom_fields'],
				'custom_height'   => (!empty($instance['custom_height']) && $instance['display_type'] === 'list') ? $instance['custom_height']: '',
				'show_checkboxes' => (bool) $instance['show_checkboxes'],
				'alphabetic_menu' => ((bool) $instance['alphabetic_menu'] && $instance['display_type'] === 'list'),
				'search_box'      => ((bool) $instance['search_box']),
				'show_count'      => (bool) $instance['show_count'],
				'enable_multiple' => (bool) $instance['enable_multiple'],
				'drop_panel'         => (bool) $instance['drop_panel'],
				'drop_panel_button'  => $instance['title'] ? $instance['title'] : esc_html__('Select Field', 'rey-core'),
				'drop_panel_keep_active'  => (bool) $instance['drop_panel_keep_active'],
				'dropdown'           => ($instance['display_type'] === 'dropdown') && ! (bool) $instance['drop_panel'], // BC
				'placeholder'     => $instance['placeholder'] ? $instance['placeholder'] : sprintf(__('Choose %s', 'rey-core'), $instance['title']),
				'dd_width'        => $instance['dd_width'],
			];

			$args['show_checkboxes__radio'] = $args['show_checkboxes'] && (bool) $instance['show_checkboxes__radio'];

			$selected_queries = [];

			$filter_query = new \ReyCore\Modules\AjaxFilters\FilterQuery();

			if( ($filters = $filter_query->get_filter_data()) && isset($filters['product-meta']) ){
				$selected_queries = $filters['product-meta'];
			}

			$html = $list_items = $list_html = '';

			foreach ($instance['custom_fields'] as $key => $field):

				$li_attribute = $li_classes = $before_text = $after_text = '';

				$count = '';

				if( $args['show_count'] ){
					$count = reyajaxfilter_get_filtered_meta_product_counts($field['meta_query']);

					// hide empty counts if specified
					if( empty($count) ){
						continue;
					}
				}

				$query_data = \ReyCore\Modules\AjaxFilters\Helpers::get_registered_meta_query_data($field['meta_query']);

				$is_active = in_array($field['meta_query'], $selected_queries, true);

				if( $display_type === 'list' ){

					if( $is_active ){
						$li_classes .= 'chosen';
					}

					if( $args['alphabetic_menu'] && strlen($query_data['title']) > 0 ){
						$li_attribute = sprintf('data-letter="%s"', mb_substr($query_data['title'], 0, 1, 'UTF-8') );
					}

					$list_items .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

					// show checkboxes

					if( $args['show_checkboxes'] ){
						$radio = $instance['show_checkboxes__radio'] ? '--radio' : '';
						$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
					}

					if( !empty($count) ){
						$after_text .= sprintf('<span class="__count">%s</span>', $count);
					}

					$link_attributes = [];
					$link_attributes[] = sprintf('data-key="%s"', esc_attr($args['data_key']));
					$link_attributes[] = sprintf('data-value="%s"', esc_attr($field['meta_query']));
					$link_attributes[] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));

					if( isset($query_data['title']) ):
						$list_items .= sprintf(
							'<a href="%1$s" %5$s>%4$s %2$s %3$s</a>',
							'javascript:void(0)',
							$query_data['title'],
							$after_text,
							$before_text,
							implode(' ', $link_attributes)
						);
					endif;

					$list_items .= '</li>';

				}

				elseif( $display_type === 'dropdown' ){

					$list_items .= sprintf( '<option value="%1$s" %2$s data-count="%4$s">%3$s</option>',
						esc_attr($field['meta_query']),
						($is_active ? 'selected="selected"' : ''),
						$query_data['title'],
						(!empty($count) ? $count : '')
					);

				}

			endforeach;

			$list_classes = $list_wrapper_styles = $list_attributes = [];

			if( $display_type === 'list' ){

				if( $custom_height = absint($args['custom_height'] ) ){
					$list_wrapper_styles[] = sprintf('height:%spx', $custom_height);
					$list_attributes[] = sprintf('data-height="%s"', $custom_height);
					reycore_assets()->add_scripts('rey-simple-scrollbar');
					reycore_assets()->add_styles('rey-simple-scrollbar');
				}

				$list_classes[] = '--style-' . ($args['show_checkboxes'] ? 'checkboxes' : 'default');

				if( $args['alphabetic_menu'] ){
					reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-alphabetic');
					$list_html .= sprintf('<div class="reyajfilter-alphabetic"><span class="reyajfilter-alphabetic-all %3$s" data-key="%2$s">%1$s</span></div>',
						esc_html__('All', 'rey-core'),
						esc_attr($args['data_key']),
						'' // --reset-filter
					);
				}

				if( $args['search_box'] ){
					reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-search');
					$list_html .= '<div class="reyajfilter-searchbox js-reyajfilter-searchbox">';
					$list_html .= reycore__get_svg_icon(['id'=>'search']);
					$searchbox_label = sprintf(esc_html__('Search %s', 'rey-core'), $instance['title']);
					$list_html .= sprintf('<input type="text" placeholder="%s" name="rey-filter-search-mf-list" id="%s-searchbox" >', $searchbox_label, $args['widget_id']);
					$list_html .= '</div>';
				}

				$list_html .= sprintf('<div class="reyajfilter-layered-nav %s" %s>', implode(' ', $list_classes), implode(' ', $list_attributes));

					$list_html .= sprintf('<div class="reyajfilter-layered-navInner" style="%s">', implode(' ', $list_wrapper_styles));
					$list_html .= '<ul class="reyajfilter-layered-list">';
					$list_html .= $list_items;
					$list_html .= '</ul>';
					$list_html .= '</div>';

					if( $custom_height ){
						$list_html .= '<span class="reyajfilter-customHeight-all">'. esc_html__('Show All +', 'rey-core') .'</span>';
					}

				$list_html .= '</div>';

				reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav');

			}

			elseif( $display_type === 'dropdown' ){

				// required scripts
				reycore_assets()->add_styles(['reycore-ajaxfilter-select2', 'rey-form-select2', 'rey-wc-select2']);
				reycore_assets()->add_scripts('reycore-ajaxfilter-select2');

				$list_html .= '<div class="reyajfilter-dropdown-nav">';

				$list_classes[] = 'reycore-ajaxfilter-select2';
				$list_classes['type'] = 'reycore-ajaxfilter-select2-single';

				if( $args['enable_multiple'] ){
					$list_classes['type'] = 'reycore-ajaxfilter-select2-multiple';
					$list_attributes[] = 'multiple="multiple"';
				}

				if( $args['search_box'] ):
					$list_attributes[] = 'data-search="true"';
				endif;

				if( $args['show_checkboxes'] ):

					if( $args['enable_multiple'] ) {
						reycore_assets()->add_scripts('reycore-ajaxfilter-select2-multi-checkboxes');
					}

					$list_attributes[] = 'data-checkboxes="true"';
				endif;

				if( isset($args['dd_width']) && $dropdown_width = $args['dd_width'] ){
					$list_attributes[] = sprintf('data-ddcss=\'%s\'', wp_json_encode([
						'min-width' => $dropdown_width . 'px'
					]));
				}

				if ( ! $args['enable_multiple']) {
					$list_html = '<option value=""></option>' . $list_html;
				}

				$list_html .= sprintf( '<select class="%1$s" name="%2$s" style="width: 100%%;" %3$s data-placeholder="%4$s">%5$s</select>',
					implode(' ', $list_classes),
					esc_attr($args['data_key']),
					implode(' ', $list_attributes),
					esc_attr($args['placeholder']),
					$list_items
				);

				$list_html .= '</div>';
			}

			$html = $list_html;

			/* ------------------------------------------------------------------------ */

			extract($widget_args);

			if (empty($list_items)) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-meta-filter';
			} else {

				$widget_class = 'woocommerce reyajfilter-ajax-meta-filter';

				// List
				if( $display_type === 'list'){

					if( (bool) $instance['rey_multi_col']  ){
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

			if (!empty($instance['title']) && ! $instance['drop_panel'] ) {
				echo $widget_args['before_title'] . apply_filters('widget_title', $instance['title'], $instance) . $widget_args['after_title'];
			}

			echo $html;

			echo $widget_args['after_widget'];
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

					$mq_choices = [];

					if( $registered_mq = get_theme_mod('ajaxfilters_meta_queries', []) ):
						foreach($registered_mq as $mq){
							$hash = substr( md5( wp_json_encode( $mq ) ), 0, 10 );
							$mq_choices[$hash] = sprintf('%s ("%s" %s %s)', $mq['title'], $mq['key'], strtolower( reycore__get_operators($mq['operator']) ), $mq['value']);
						}
					endif;

					$widget_fields->add_field([
						'name' => 'custom_fields',
						'type' => 'repeater',
						'label' => __( 'Custom fields', 'rey-core' ),
						'fields' => [
							[
								'key' => 'meta_query',
								'type' => 'select',
								'title' => esc_html__('Meta Query', 'rey-core'),
								'choices' => $mq_choices,
								'size' => 'full'
							],
						]
					]);

					?>
					<small><?php printf(_x('Please register meta query choices in <a href="%s" target="_blank">Customizer > WooCommerce > Ajax Filters</a> (at the very bottom). <a href="%s" target="_blank">Learn more</a> about this.', 'Widget control description', 'rey-core'), add_query_arg( ['autofocus[section]' => 'ajax-filters'], admin_url( 'customize.php' ) ), reycore__support_url('kb/ajax-filter-widgets/#how-to-work-with-filter-by-custom-fields-meta-data') ); ?></small>

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
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '==='
							],
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
								'value' => 'dropdown',
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

				?>

				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<?php

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
			$instance = [];

			foreach ($this->defaults as $key => $value) {
				$instance[$key] = isset($new_instance[$key]) ? reycore__clean( $new_instance[$key] ) : $value;
			}

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_custom_fields_filter_widget')) {
	function reyajaxfilter_register_custom_fields_filter_widget() {
		register_widget('REYAJAXFILTERS_CustomFields_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_custom_fields_filter_widget');
}
