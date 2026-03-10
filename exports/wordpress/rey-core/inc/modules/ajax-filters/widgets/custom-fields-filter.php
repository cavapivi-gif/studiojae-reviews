<?php
/**
 * Rey Ajax Product Filter by Custom Fields
 */
if (!class_exists('REYAJAXFILTERS_CustomFieldsACF_Filter_Widget')) {
	class REYAJAXFILTERS_CustomFieldsACF_Filter_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-auto-custom-fields-filter', // Base ID
				__('Filter - by Custom Fields & ACF', 'rey-core'), // Name
				[
					'description' => __('Filter WooCommerce products by Custom fields & ACF fields.', 'rey-core')
				]
			);

			$this->defaults = [
				'title'                   => '',
				'custom_field'            => '',
				'compare_operator'        => '!=empty',
				'custom_field_value'      => '',
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
				'dropdown'                => false,
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display'       => [],
				// URL
				'key_name'                => '',
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

			if( ! ($field_key = $instance['custom_field']) ){
				echo esc_html__('Please add the custom field key.', 'rey-core');
				return;
			}

			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			$filter_query = new \ReyCore\Modules\AjaxFilters\FilterQuery();
			$filters = $filter_query->get_filter_data();

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$args = [
				'url_array'              => $url_array,
				'data_key'               => \ReyCore\Modules\AjaxFilters\Base::CF_KEY . $field_key,
				'custom_field'           => $instance['custom_field'],
				'custom_height'          => !empty($instance['custom_height']) ? $instance['custom_height']: '',
				'show_checkboxes'        => (bool) $instance['show_checkboxes'],
				'alphabetic_menu'        => (bool) $instance['alphabetic_menu'],
				'search_box'             => (bool) $instance['search_box'],
				'show_count'             => (bool) $instance['show_count'],
				'enable_multiple'        => (bool) $instance['enable_multiple'],
				'drop_panel'             => (bool) $instance['drop_panel'],
				'drop_panel_button'      => $instance['title'] ? $instance['title'] : esc_html__('Select Field', 'rey-core'),
				'drop_panel_keep_active' => (bool) $instance['drop_panel_keep_active'],
				'dropdown'               => false, // BC
			];

			if( $custom_key = esc_attr($instance['key_name']) ){
				$args['data_key'] = $custom_key;
			}

			$args['show_checkboxes__radio'] = $args['show_checkboxes'] && (bool) $instance['show_checkboxes__radio'];

			/**
			 * RENDER LIST
			 */

			$query_args = [
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'fields'         => 'ids',
			];

			$query_args['meta_query']['relation'] = 'OR';
			$query_args['meta_query'][] = \ReyCore\Modules\AjaxFilters\Helpers::get_meta_query([
				'key'      => $field_key,
				'value'    => $instance['custom_field_value'],
				'operator' => $instance['compare_operator'],
			]);

			$query_args['meta_query'] = array_merge($query_args['meta_query'], reyajaxfilter_meta_query());
			unset($query_args['meta_query']['cf']); // prevent cf query to be applied here

			$query_args['tax_query'] = reyajaxfilter_tax_query();

			$list_items = [];
			$list_item_counters = [];

			// We have a query - let's see if cached results of this query already exist.
			$transient_name = 'reyajaxfilter_cfquery_' . reyajaxfilter_hash_query_string( wp_json_encode($query_args) );

			if( false !== ( $__results = get_transient( $transient_name ) ) ){
				$results = $__results;
			}
			else {
				$results = get_posts($query_args);
				set_transient( $transient_name, $results, reyajaxfilter_transient_lifespan() );
			}

			foreach( array_map( 'absint', $results ) as $id) {

				if( ! ($meta_value = get_post_meta($id, $field_key, true)) ){
					continue;
				}

				$meta_values = [];

				if( is_array($meta_value) ){
					$meta_values = array_merge( $meta_values, $meta_value );
				}
				else {
					$meta_values[] = $meta_value;
				}

				foreach ($meta_values as $mkey => $mvalue) {

					$meta_slug = sanitize_title($mvalue);
					$li_attribute = $li_classes = $before_text = $after_text = '';

					// make counter
					if( ! isset($list_items[$meta_slug])) {
						$list_item_counters[$meta_slug] = 1;
					}
					else {
						$list_item_counters[$meta_slug]++;
					}

					if( isset($filters['cf'][$field_key]['terms']) ){
						if(
							in_array( $meta_slug, $filters['cf'][$field_key]['terms'], true ) ||
							in_array( urldecode($meta_slug), $filters['cf'][$field_key]['terms'], true )
						){
							$li_classes .= 'chosen';
						}
					}

					if( $args['alphabetic_menu'] && strlen($meta_slug) > 0 ){
						$li_attribute .= sprintf('data-letter="%s"', mb_substr($meta_slug, 0, 1, 'UTF-8') );
					}

					// show checkboxes
					if( $args['show_checkboxes'] ){
						$radio = $instance['show_checkboxes__radio'] ? '--radio' : '';
						$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
					}

					if( $args['show_count'] ){
						$after_text .= sprintf('<span class="__count">%s</span>', $list_item_counters[$meta_slug]);
					}

					$link_attributes = [];
					$link_attributes[] = sprintf('data-key="%s"', esc_attr($args['data_key']));
					$link_attributes[] = sprintf('data-value="%s"', $meta_slug );
					$link_attributes[] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));

					$item_html = sprintf(
						'<a href="%1$s" %5$s>%4$s %2$s %3$s</a>',
						'javascript:void(0)',
						$mvalue,
						$after_text,
						$before_text,
						implode(' ', $link_attributes)
					);

					$list_items[$meta_slug] = sprintf('<li class="%s" %s>%s</li>', $li_classes, $li_attribute, $item_html);
				}

			}

			ksort($list_items);

			$list_html = $list_classes = $list_wrapper_styles = $list_attributes = [];

			/**
			 * RENDER CONTAINER
			 */

			if( $custom_height = absint($args['custom_height'] ) ){
				$list_wrapper_styles[] = sprintf('height:%spx', $custom_height);
				$list_attributes[] = sprintf('data-height="%s"', $custom_height);
				reycore_assets()->add_scripts('rey-simple-scrollbar');
				reycore_assets()->add_styles('rey-simple-scrollbar');
			}

			$list_classes[] = '--style-' . ($args['show_checkboxes'] ? 'checkboxes' : 'default');

			if( $args['alphabetic_menu'] ){
				reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-alphabetic');
				$list_html[] = sprintf('<div class="reyajfilter-alphabetic"><span class="reyajfilter-alphabetic-all %3$s" data-key="%2$s">%1$s</span></div>',
					esc_html__('All', 'rey-core'),
					esc_attr($args['data_key']),
					'' // --reset-filter
				);
			}

			if( $args['search_box'] ){
				reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-search');
				$list_html[] = '<div class="reyajfilter-searchbox js-reyajfilter-searchbox">';
				$list_html[] = reycore__get_svg_icon(['id'=>'search']);
				$searchbox_label = sprintf(esc_html__('Search %s', 'rey-core'), $instance['title']);
				$list_html[] = sprintf('<input type="text" placeholder="%s" name="rey-filter-search-cf-list" id="%s-searchbox" >', $searchbox_label, $args['widget_id']);
				$list_html[] = '</div>';
			}

			$list_html[] = sprintf('<div class="reyajfilter-layered-nav %s" %s>', implode(' ', $list_classes), implode(' ', $list_attributes));

				$list_html[] = sprintf('<div class="reyajfilter-layered-navInner" style="%s">', implode(' ', $list_wrapper_styles));
				$list_html[] = '<ul class="reyajfilter-layered-list">';
				$list_html[] = implode('', $list_items);
				$list_html[] = '</ul>';
				$list_html[] = '</div>';

				if( $custom_height ){
					$list_html[] = '<span class="reyajfilter-customHeight-all">'. esc_html__('Show All +', 'rey-core') .'</span>';
				}

			$list_html[] = '</div>';

			reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav');

			/* ------------------------------------------------------------------------ */

			extract($widget_args);

			if ( ! empty($list_items) )
			{
				$widget_class = 'woocommerce reyajfilter-ajax-meta-filter';

				if( (bool) $instance['rey_multi_col']  ){
					$widget_class .= ' rey-filterList-cols';
					$before_widget .= \ReyCore\Modules\AjaxFilters\Base::multicols_css();
				}

				if( (bool) $instance['show_count'] && (bool) $instance['count_stretch'] ){
					$widget_class .= ' --count-stretch';
				}
			}
			else {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-meta-filter';
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

				echo implode('', $list_html);

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
			?>

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
						'name'        => 'custom_field',
						'type'        => 'text',
						'label'       => __( 'Custom field key', 'rey-core' ),
						'value'       => '',
						'field_class' => 'widefat',
						'placeholder' => 'ex: some_field_key',
					]);

					$widget_fields->add_field([
						'name' => 'compare_operator',
						'type' => 'select',
						'label' => __( 'Compare Operator', 'rey-core' ),
						'value' => '!=empty',
						'options' => reycore__get_operators()
					]);

					$widget_fields->add_field([
						'name'        => 'custom_field_value',
						'type'        => 'text',
						'label'       => __( 'Custom field value', 'rey-core' ),
						'value'       => '',
						'field_class' => 'widefat',
						'placeholder' => 'ex: some_value',
						'conditions' => [
							[
								'name'    => 'compare_operator',
								'compare' => '!=',
								'value'   => '!=empty'
							],
							[
								'name'    => 'compare_operator',
								'compare' => '!=',
								'value'   => '==empty'
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'drop_panel',
						'type' => 'checkbox',
						'label' => __( 'Display as Drop-down', 'rey-core' ),
						'value' => '1',
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
								'name' => 'show_count',
								'value' => '',
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
						'field_class' => 'rey-widget-innerTitle'
					]);

					$widget_fields->add_field([
						'name' => 'rey_multi_col',
						'type' => 'checkbox',
						'label' => __( 'Display list on 2 columns', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'alphabetic_menu',
						'type' => 'checkbox',
						'label' => __( 'Show alphabetic menu', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'custom_height',
						'type' => 'number',
						'label' => __( 'Custom Height', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
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
						'placeholder' => esc_html__('eg: somekey', 'rey-core'),
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

			if( $cf = reycore__clean($instance['custom_field']) ){
				\ReyCore\Modules\AjaxFilters\Helpers::save_custom_key([
					'new_instance' => $new_instance,
					'old_instance' => $old_instance,
					'cf'           => $cf,
				]);
			}

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_custom_fields_acf_filter_widget')) {
	function reyajaxfilter_register_custom_fields_acf_filter_widget() {
		register_widget('REYAJAXFILTERS_CustomFieldsACF_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_custom_fields_acf_filter_widget');
}
