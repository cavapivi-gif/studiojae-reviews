<?php
/**
 * Rey Ajax Product Filter in stock
 */
if (!class_exists('REYAJAXFILTERS_Stock_Filter_Widget')) {
	class REYAJAXFILTERS_Stock_Filter_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */

		 function __construct() {

			parent::__construct(
				'reyajfilter-stock-filter', // Base ID
				__('Filter - In Stock', 'rey-core'), // Name
				array('description' => __('Filter WooCommerce products in stock.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'             => '',
				'label_title'       => '',
				'layout'            => 'checkbox',
				'label_buttons_all' => '',
				'label_buttons_in'  => '',
				'label_buttons_out' => '',
				'show_count'        => false,
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
		public function widget($args, $instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			if( \ReyCore\Modules\AjaxFilters\Base::should_hide_widget($instance) ){
				return;
			}

			$html = '';

			// required scripts
			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			reycore_assets()->add_styles(['reycore-ajaxfilter-checkbox-filters', 'reycore-ajaxfilter-stock']);

			// get values from url
			$hide_all = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) || ($instance['layout'] === 'buttons' && empty($instance['label_buttons_all']));

			// get values from url
			$in_stock = 0;

			if ( $hide_all ) {
				$in_stock = 1;
			}

			if (isset($_GET['in-stock']) && !empty($_GET['in-stock'])) {
				$in_stock = absint( $_GET['in-stock'] );
			}

			extract($args);

			if( !isset($widget_id) || empty($widget_id) ){
				$widget_id = uniqid();
			}

			$id = $widget_id . '-stock-check';

			if( $instance['layout'] === 'checkbox' ) {
				$html .= '<div class="reyajfilter-stock-filter js-reyajfilter-check-filter rey-filterCheckbox">';
					$html .= sprintf('<input type="checkbox" id="%1$s" name="%1$s" data-key="in-stock" value="1" %2$s />', $id, checked(1, $in_stock, false) );

					$products_count = reyajaxfilter_get_filtered_product_counts__general([
						'meta_query' => [
							'stock' => $in_stock
						],
						'cache_key' => 'stock'
					]);

					$count = '';
					if( $instance['show_count'] ){
						$count = sprintf('<span class="__count">%s</span>', $products_count);
					}

					$html .= sprintf('<label for="%s"><span class="__checkbox"></span><span class="__text">%s</span>%s</label>', $id, $instance['label_title'], $count);
				$html .= '</div>';
			}

			else if ($instance['layout'] === 'buttons'){
				$html .= '<div class="reyajfilter-stock-filter js-reyajfilter-check-filter --buttons">';
					if ( ! $hide_all ) {
						$html .= sprintf('<input type="radio" name="%1$s" id="%2$s" class="%3$s" data-key="in-stock" value="0" %4$s /><label for="%2$s">%5$s</label>', $id, $id . '_all', '', checked(0, $in_stock, false ), $instance['label_buttons_all'] );
					}
					$html .= sprintf('<input type="radio" name="%1$s" id="%2$s" class="%3$s" data-key="in-stock" value="1" %4$s /><label for="%2$s">%5$s</label>', $id, $id . '_in', '', checked(1, $in_stock, false ), $instance['label_buttons_in'] );
					$html .= sprintf('<input type="radio" name="%1$s" id="%2$s" class="%3$s" data-key="in-stock" value="2" %4$s /><label for="%2$s">%5$s</label>', $id, $id . '_out', '', checked(2, $in_stock, false ), $instance['label_buttons_out'] );
				$html .= '</div>';
			}

			$widget_class = 'woocommerce reyajfilter-stock-filter-widget reyajfilter-ajax-term-filter';

			if( isset($products_count) && ! $products_count ){
				$widget_class .= ' reyajfilter-widget-hidden';
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

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$widget_fields = new \ReyCore\Modules\AjaxFilters\WidgetFields($this, $instance);

			do_action('reycore/ajaxfilters/before_widget_controls', $instance); ?>
			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
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
						'name' => 'layout',
						'type' => 'select',
						'label' => __( 'Layout', 'rey-core' ),
						'value' => 'checkbox',
						'options' => [
							'checkbox'  => esc_html__( 'Single Checkbox', 'rey-core' ),
							'buttons'  => esc_html__( 'Button Group', 'rey-core' ),
						]
					]);

					$widget_fields->add_field([
						'name' => 'label_title',
						'type' => 'text',
						'label' => __( 'Label Title', 'rey-core' ),
						'value' => esc_html__('In Stock Only', 'rey-core'),
						'placeholder' => esc_html__('eg: In Stock Only', 'rey-core'),
						'conditions' => [
							[
								'name' => 'layout',
								'value' => 'checkbox',
								'compare' => '=='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'label_buttons_all',
						'type' => 'text',
						'label' => __( '"All" Label', 'rey-core' ),
						'value' => esc_html_x('All', 'Widget option title', 'rey-core'),
						'placeholder' => esc_html_x('eg: All', 'Widget option title preloader', 'rey-core'),
						'conditions' => [
							[
								'name' => 'layout',
								'value' => 'buttons',
								'compare' => '=='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'label_buttons_in',
						'type' => 'text',
						'label' => __( '"In Stock" Label', 'rey-core' ),
						'value' => esc_html_x('In Stock', 'Widget option title', 'rey-core'),
						'placeholder' => esc_html_x('eg: In Stock', 'Widget option title preloader', 'rey-core'),
						'conditions' => [
							[
								'name' => 'layout',
								'value' => 'buttons',
								'compare' => '=='
							],
						],
					]);

					$widget_fields->add_field([
						'name' => 'label_buttons_out',
						'type' => 'text',
						'label' => __( '"Out of stock" Label', 'rey-core' ),
						'value' => esc_html_x('Out Of Stock', 'Widget option title', 'rey-core'),
						'placeholder' => esc_html_x('eg: Out Of Stock', 'Widget option title preloader', 'rey-core'),
						'conditions' => [
							[
								'name' => 'layout',
								'value' => 'buttons',
								'compare' => '=='
							],
						],
					]);


					$widget_fields->add_field([
						'name' => 'show_count',
						'type' => 'checkbox',
						'label' => __( 'Show Counter', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'layout',
								'value' => 'checkbox',
								'compare' => '=='
							],
						],
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
					]); ?>
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
if (!function_exists('reyajaxfilter_register_stock_filter_widget')) {
	function reyajaxfilter_register_stock_filter_widget() {
		register_widget('REYAJAXFILTERS_Stock_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_stock_filter_widget');
}
