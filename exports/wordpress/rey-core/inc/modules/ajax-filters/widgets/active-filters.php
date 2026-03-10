<?php
/**
 * WC Ajax Active Filters
 */
if (!class_exists('REYAJAXFILTERS_Active_Filters_Widget')) {
	class REYAJAXFILTERS_Active_Filters_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-active-filters', // Base ID
				__('Filter - Active list', 'rey-core'), // Name
				array('description' => __('Shows active filters (attributes, categories, etc.) so visitors can see and deactivate them.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title' => '',
				'button_text' => '',
				'active_items' => '',
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

			$filter_query = \ReyCore\Modules\AjaxFilters\Base::get_the_filters_query();
			$filters_data = $filter_query->get_filter_data();

			if( is_product() ){
				return;
			}

			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			$html = $filters_html = '';

			$close = reycore__get_svg_icon(['id' => 'close']);

			$show_active_items = $instance['active_items'] === '';

			if( $show_active_items ) {

				foreach ($filters_data as $key => $active_filter) {

					if ($key === 'tax') {
						foreach ($active_filter as $taxonomy => $terms) {
							unset($terms['query_type']);
							foreach ($terms as $key => $term_data) {
								$term_obj = reyajaxfilter_get_term_data($term_data['id'], $taxonomy);
								if( isset($term_obj->name) ){
									$term_value = !empty($term_data['slug']) ? $term_obj->slug : $term_data['id'];
									$filters_html .= '<a href="javascript:void(0)" data-key="' . $term_data['key'] . '" data-value="' . $term_value. '">' . $close. '<span>' . $term_obj->name . '</span></a>';
								}
							}
						}
					}

					elseif ($key === 'cf') {
						foreach ($active_filter as $custom_fields) {
							$cf_values = \ReyCore\Modules\AjaxFilters\Helpers::get_meta_converted_values($custom_fields['field_name']);
							foreach ($custom_fields['terms'] as $value)
							{
								if( empty($cf_values[$value]) ){
									$value = strtolower(urlencode($value)); // check for encoded values (eg: Cyrilic characters)
									if( empty($cf_values[$value]) ){
										continue;
									}
								}
								$filters_html .= '<a href="javascript:void(0)" data-key="' . $custom_fields['key'] . '" data-value="' . $value. '">' . $close. '<span>' . $cf_values[$value] . '</span></a>';
							}
						}
					}

					elseif ($key === 'range_min') {
						foreach ($active_filter as $taxonomy => $term_name) {
							$filters_html .= sprintf('<a href="javascript:void(0)" data-key="min-range-%5$s">%1$s<span>%2$s %3$s: %4$s</span></a>',
								$close,
								_x('Min.', 'Range min filter', 'rey-core'),
								wc_attribute_label($taxonomy),
								$term_name,
								str_replace( 'pa_', '', $taxonomy )
							);
						}
					}

					elseif ($key === 'range_max') {
						foreach ($active_filter as $taxonomy => $term_name) {
							$filters_html .= sprintf('<a href="javascript:void(0)" data-key="max-range-%5$s">%1$s<span>%2$s %3$s: %4$s</span></a>',
								$close,
								_x('Max.', 'Range max filter', 'rey-core'),
								wc_attribute_label($taxonomy),
								$term_name,
								str_replace( 'pa_', '', $taxonomy )
							);
						}
					}

					elseif ($key === 'keyword') {
						$filters_html .= '<a href="javascript:void(0)" data-key="keyword">' . $close . '<span>' . __('Search For: ', 'rey-core') . $active_filter . '</span></a>';
					}

					elseif (apply_filters('reycore/ajaxfilters/active_filters/order_display', false) && $key === 'orderby') {
						$filters_html .= '<a href="javascript:void(0)" data-key="orderby">' . $close . '<span>' . __('Orderby: ', 'rey-core') . $active_filter . '</span></a>';
					}

					elseif ($key === 'min_price' ) {
						$filters_html .= '<a href="javascript:void(0)" data-key="min-price">'. $close . '<span>' . __('Min Price: ', 'rey-core') . $active_filter . '</span></a>';
					}

					elseif ($key === 'max_price') {
						$filters_html .= '<a href="javascript:void(0)" data-key="max-price">' . $close . '<span>' . __('Max Price: ', 'rey-core') . $active_filter . '</span></a>';
					}

					elseif ($key === 'in-stock') {
						$filters_html .= '<a href="javascript:void(0)" data-key="in-stock">' . $close . '<span>' . __('Stock', 'rey-core') . '</span></a>';
					}

					elseif ($key === 'on-sale') {
						$filters_html .= '<a href="javascript:void(0)" data-key="on-sale">' . $close . '<span>' . __('On Sale', 'rey-core') . '</span></a>';
					}

					elseif ($key === 'is-featured') {
						$filters_html .= '<a href="javascript:void(0)" data-key="is-featured">' . $close . '<span>' . __('Featured', 'rey-core') . '</span></a>';
					}

					elseif ($key === 'product-meta') {

						foreach ($active_filter as $hash) {

							$pm_data = \ReyCore\Modules\AjaxFilters\Helpers::get_registered_meta_query_data( $hash );

							if( !empty($pm_data) ) {
								$filters_html .= sprintf( '<a href="javascript:void(0)" data-key="product-meta" data-value="%2$s">%3$s<span>%1$s</span></a>',
									$pm_data['title'],
									$hash,
									$close
								);
							}
						}
					}

				}
			}

			$filters_html = apply_filters('reycore/ajaxfilters/active_filters/html', $filters_html, $close);

			if ( (! empty($filters_html) || ! $show_active_items) && !empty($instance['button_text'])) {

				if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
					$link = home_url();
				} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
					$link = get_post_type_archive_link( 'product' );
				} elseif ( is_tax( get_object_taxonomies('product') ) ) {
					$link = get_term_link( get_queried_object_id() );
				} elseif( get_query_var('term') && get_query_var('taxonomy') ) {
					$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
				} else {
					$link = get_page_link();
				}

				/**
				 * Search Arg.
				 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
				 */
				if ( ($search_query = get_search_query()) && ! isset($_REQUEST['keyword']) ) {
					$link = add_query_arg( 's', rawurlencode( wp_specialchars_decode( $search_query ) ), $link );
				}

				// Post Type Arg
				if ( isset( $_GET['post_type'] ) && $_GET['post_type'] ) {
					$link = add_query_arg( 'post_type', wc_clean( $_GET['post_type'] ), $link );
				}

				$link = apply_filters('reycore/ajaxfilters/active_filters/link', $link);

				$filters_html .= '<a href="javascript:void(0)" class="reset" data-location="' . $link . '">' . $close . '<span>' . $instance['button_text'] . '</span></a>';
			}

			if( ! empty($filters_html) ){
				$html .= '<div class="reyajfilter-active-filters">' . $filters_html . '</div>';
			}

			extract($args);

			$widget_class = 'woocommerce reyajfilter-ajax-term-filter' . (empty($filters_html) ? ' reyajfilter-widget-hidden' : '');

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			echo $before_widget;

			if (!empty($instance['title'])) {
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

			do_action('reycore/ajaxfilters/before_widget_controls', $instance);
			$widget_fields = new \ReyCore\Modules\AjaxFilters\WidgetFields($this, $instance);

			$widget_fields->add_field([
				'name' => 'title',
				'type' => 'text',
				'label' => __( 'Title:', 'rey-core' ),
				'value' => '',
			]);

			$widget_fields->add_field([
				'name' => 'button_text',
				'type' => 'text',
				'label' => __( 'Button Text:', 'rey-core' ),
				'value' => '',
			]);

			$widget_fields->add_field([
				'name' => 'active_items',
				'type' => 'checkbox',
				'label' => __( 'Hide active items', 'rey-core' ),
				'value' => '1',
			]);

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
if (!function_exists('reyajaxfilter_register_active_filters_widget')) {
	function reyajaxfilter_register_active_filters_widget() {
		register_widget('REYAJAXFILTERS_Active_Filters_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_active_filters_widget');
}
