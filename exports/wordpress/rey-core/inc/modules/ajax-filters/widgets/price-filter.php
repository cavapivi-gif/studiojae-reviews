<?php
/**
 * Rey Ajax Product Filter by Price
 */
if (!class_exists('REYAJAXFILTERS_Price_Filter_Widget')) {
	class REYAJAXFILTERS_Price_Filter_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-price-filter', // Base ID
				__('Filter - by Price', 'rey-core'), // Name
				array('description' => __('Filter WooCommerce products by price.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'            => '',
				'display_type'     => 'slider',
				'show_currency'    => false,
				'show_as_dropdown' => false,
				'slider_margin' => 10,
				'price_list'       => [],
				'price_list_start' => [
					'enable' => '',
					'text' => __('Under', 'rey-core'),
					'max' => '',
				],
				'price_list_end'   => [
					'enable' => '',
					'text' => __('Over', 'rey-core'),
					'min' => '',
				],
				'show_checkboxes'  => false,
				'show_checkboxes__radio'  => false,
				'drop_panel'              => false,
				'drop_panel_keep_active'  => false,
				// DD
				'placeholder'      => '',
				'dd_width'         => '',
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display' => [],
				'custom__separator'         => '',
			];
		}

		public function validate_number( $num ){

			if( ! is_numeric($num) ){
				$num = 1;
			}

			if( is_nan($num) ){
				$num = 1;
			}

			if( $num == 'nan' ){
				$num = 1;
			}

			return $num;
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

			// display type, slider or list
			$display_type = $instance['display_type'];
			$html = '';

			$prices = reyajaxfilter_get_prices_range();
			$step = max( apply_filters( 'woocommerce_price_filter_widget_step', 1 ), 1 );

			// to be sure that these values are number
			$min_price = $max_price = 0;

			if (count($prices) === 2) {
				$min_price = $prices['min_price'];
				$max_price = $prices['max_price'];
			}

			// Check to see if we should add taxes to the prices if store are excl tax but display incl.
			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
				$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
				$tax_rates = WC_Tax::get_rates( $tax_class );

				if ( $tax_rates ) {
					$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
					$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
				}
			}

			$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step, $min_price, $step );
			$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step, $max_price, $step );

			if ( ! absint($min_price) && ! absint($max_price) ) {
				$display_type = false;
			}

			// required scripts
			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			$before = $after = '';

			if ($instance['show_currency'])  {

				$currency_symbol = get_woocommerce_currency_symbol();
				$currency_position = get_option('woocommerce_currency_pos');

				if ($currency_position === 'left') {
					$before = $currency_symbol;
				} elseif ($currency_position === 'left_space') {
					$before = $currency_symbol . ' ';
				} elseif ($currency_position === 'right') {
					$after = $currency_symbol;
				} elseif ($currency_position === 'right_space') {
					$after = ' ' . $currency_symbol;
				}
			}

			// HTML markup for price slider
			// Slider markup
			// If both min and max are equal, we don't need a slider.
			if ( $display_type === 'slider' && ! ($min_price === $max_price) ) {

				reycore_assets()->add_scripts('reycore-nouislider');
				reycore_assets()->add_styles(['reycore-ajaxfilter-price-slider', 'reycore-nouislider' ]);

				$current_min_price = isset( $_GET['min-price'] ) ? floor( floatval( wp_unslash( $_GET['min-price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
				$current_max_price = isset( $_GET['max-price'] ) ? ceil( floatval( wp_unslash( $_GET['max-price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

				$html .= '<div class="reyajfilter-price-filter-wrapper">';

					$html .= sprintf('<div id="reyajfilter-noui-slider" class="noUi-extended" data-min="%s" data-max="%s" data-set-min="%s" data-set-max="%s" data-before="%s" data-after="%s" data-margin="%d"></div>',
						$this->validate_number($min_price),
						$this->validate_number($max_price),
						$this->validate_number($current_min_price),
						$this->validate_number($current_max_price),
						$before,
						$after,
						absint($instance['slider_margin'])
					);

					// $filter_query = new \ReyCore\Modules\AjaxFilters\FilterQuery();
					// $filters_data = $filter_query->get_filter_data();
					// if( isset($filters_data['min_price']) || isset($filters_data['min_price']) ){
					// 	$html .= '<br />';
					// 	$html .= '<div class="slider-values">';
					// 		$html .= '<p>' . __('Min Price', 'rey-core') . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-min"></span></p>';
					// 		$html .= '<p>' . __('Max Price', 'rey-core') . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-max"></span></p>';
					// 	$html .= '</div>';
					// }

				$html .= '</div>';
			}

			// List markup
			elseif( $display_type === 'list' ) {

				$list_html = $dd_html = $prefix = '';

				// show checkboxes
				if( $instance['show_checkboxes'] ){
					$radio = $instance['show_checkboxes__radio'] ? '--radio' : '';
					$prefix = sprintf('<span class="__checkbox %s"></span>', $radio);
				}

				// price start
				if( $instance['price_list_start']['enable'] == 1 && $instance['price_list_start']['max'] ){
					$price_start = [
						'min' => '',
						'max' => $instance['price_list_start']['max'],
						'text' => $instance['price_list_start']['text'],
					];
					array_unshift($instance['price_list'], $price_start);
				}

				// price end
				if( $instance['price_list_end']['enable'] == 1 && $instance['price_list_end']['min'] ){
					$instance['price_list'][] = [
						'max' => '',
						'min' => $instance['price_list_end']['min'],
						'text' => $instance['price_list_end']['text'],
					];
				}

				foreach ($instance['price_list'] as $price_list) {
					$is_selected = false;

					if (isset($_GET['min-price']) && $_GET['min-price'] == $price_list['min']) {
						$is_selected = true;
						$list_html .= '<li class="chosen">';
					} elseif (isset($_GET['max-price']) && $_GET['max-price'] == $price_list['max']) {
						$is_selected = true;
						$list_html .= '<li class="chosen">';
					} else {
						$list_html .= '<li>';
					}

					reycore_assets()->add_styles('reycore-ajaxfilter-range-points');

					$list_html .= sprintf(
						'<a class="reyajfilter-rangePoints-listItem" href="javascript:void(0)" data-key-min="min-price" data-value-min="%s" data-key-max="max-price" data-value-max="%s">',
						$price_list['min'],
						$price_list['max']
					);

					$list_html .= $prefix;

					$dd_html .= sprintf('<option value="%1$s" data-key-min="min-price" data-value-min="%2$s" data-key-max="max-price" data-value-max="%3$s" %4$s>',
						$price_list['min'] . $price_list['max'],
						$price_list['min'],
						$price_list['max'],
						selected(true, $is_selected, false)
					);

					if (isset($price_list['text']) && $price_list['text']) {
						$list_html .= '<span class="__text">' . $price_list['text'] . '</span>';
						$dd_html .= $price_list['text'];
					}

					if (isset($price_list['min']) && $price_list['min']) {
						$list_html .= '<span class="__min">' . $before . $price_list['min'] . $after . '</span>';
						$dd_html .= $before . $price_list['min'] . $after;
					}

					if (isset($price_list['to']) && $price_list['to']) {
						$list_html .= '<span class="__to">' . $price_list['to'] . '</span>';
						$dd_html .= ' ' . $price_list['to'] . ' ';
					}

					if (isset($price_list['max']) && $price_list['max']) {
						$list_html .= '<span class="__max">' . $before . $price_list['max'] . $after . '</span>';
						$dd_html .= $before . $price_list['max'] . $after;
					}

					$list_html .= '</a></li>';
					$dd_html .= '</option>';
				}

				if( $instance['show_as_dropdown'] && ! $instance['drop_panel'] ){

					// required scripts
					reycore_assets()->add_styles(['reycore-ajaxfilter-select2', 'rey-form-select2', 'rey-wc-select2']);
					reycore_assets()->add_scripts('reycore-ajaxfilter-select2');

					$placeholder = $instance['placeholder'] ? $instance['placeholder'] : esc_html__('Select Price', 'rey-core');

					$attributes = sprintf('data-placeholder="%s"', $placeholder);

					if( $instance['show_checkboxes'] ):
						reycore_assets()->add_scripts('reycore-ajaxfilter-select2-multi-checkboxes');
						$attributes .= ' data-checkboxes="true"';
					endif;

					if( isset($instance['dd_width']) && $dropdown_width = $instance['dd_width'] ){
						$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
							'min-width' => $dropdown_width . 'px'
						]));
					}

					$html .= '<div class="reyajfilter-dropdown-nav">';
					$html .= '<select class="reyajfilter-select2 reyajfilter-select2-single reyajfilter-select2--prices" style="width: 100%;" '. $attributes .'>';
						$html .= '<option></option>';
						$html .= $dd_html;
					$html .= '</select>';
					$html .= '</div>';
				}
				else {

					$list_classes[] = '--style-' . ($instance['show_checkboxes'] ? 'checkboxes' : 'default');
					$list_classes[] = '--range-points';

					$html .= sprintf('<div class="reyajfilter-layered-nav %s">', implode(' ', $list_classes));

						$html .= '<ul>';
							$html .= $list_html;
						$html .= '</ul>';
					$html .= '</div>';

					reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav');
				}

			}

			elseif( $display_type === 'custom' ){

				reycore_assets()->add_styles('reycore-ajaxfilter-price-custom');

				global $wp;

				if ( '' === get_option( 'permalink_structure' ) ) {
					$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
				} else {
					$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
				}

				$html .= '<form method="get" action="'. esc_url( $form_action ) .'" class="reyajfilter-price-filter--custom">';

					$current_min_price = isset( $_GET['min-price'] ) ? floor( floatval( wp_unslash( $_GET['min-price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
					$current_max_price = isset( $_GET['max-price'] ) ? ceil( floatval( wp_unslash( $_GET['max-price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

					if( ! (bool) $current_max_price && isset($prices['max_price']) ){
						$current_max_price = ceil( floatval( wp_unslash( $prices['max_price'] ) ) / $step ) * $step;
					}

					if ($instance['show_currency'])  {
						$currency_symbol = get_woocommerce_currency_symbol();
						$html .= sprintf('<span class="__currency">%s</span>', $currency_symbol);
					}

					$html .= sprintf(
						'<input type="number" id="%3$s" name="min-price" value="%1$s" class="__min" />',
						esc_attr( $current_min_price ),
						esc_attr( $min_price ),
						esc_attr($args['widget_id']) . '-min-price'
					);

					if ( $separator_text = $instance['custom__separator'] )  {
						$html .= sprintf('<span class="__separator">%s</span>', $separator_text);
					}

					$html .= sprintf(
						'<input type="number" id="%3$s" name="max-price" value="%1$s" class="__max" />',
						esc_attr( $current_max_price ),
						esc_attr( $max_price ),
						esc_attr($args['widget_id']) . '-max-price'
					);

					$html .= sprintf(
						'<button type="submit" class="button">%s</button>',
						reycore__arrowSvg()
					);

				$html .= '</form>';
			}

			elseif( $display_type === 'pricing' ){
				// reycore_assets()->add_styles('reycore-ajaxfilter-price-custom');
			}

			$drop_output = '';

			if( $instance['drop_panel'] ){

				$drop_output = reyajaxfilter_droppanel_output( $html, [
					'button' => $instance['title'] ? $instance['title'] : esc_html__('Price', 'rey-core'),
					'keep_active' => $instance['drop_panel_keep_active'],
					'key' => ['min-price', 'max-price'],
					'selection' => (isset( $_GET['min-price'] ) || isset( $_GET['max-price'] )),
					'clear_text' => esc_html__('Reset price', 'rey-core')
				] );

			}

			if( $drop_output ){
				$html = $drop_output;
			}

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget


			if ($display_type === 'slider') {
				$widget_class = 'woocommerce reyajfilter-price-filter-widget reyajfilter-price-filter-widget--slider';
			} else {
				$widget_class = 'woocommerce reyajfilter-price-filter-widget reyajfilter-ajax-term-filter';
			}

			if ($display_type === false) {
				$widget_class .= ' reyajfilter-widget-hidden';
			}

			$widget_class .= ' --style-' . $display_type;

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			if( isset($GLOBALS['widgets_ids']) && in_array($args['widget_id'], $GLOBALS['widgets_ids'], true) ){
				$the_id = $args['widget_id'];
				$args['widget_id'] = $the_id . uniqid('-');
				$before_widget = str_replace(
					sprintf(' id="%s" ', $the_id),
					sprintf(' id="%s" ', $args['widget_id']),
					$before_widget
				);
			}

			$GLOBALS['widgets_ids'][] = $args['widget_id'];

			echo $before_widget;

			if (!empty($instance['title']) && !$instance['drop_panel']) {
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

			$display_name = $this->get_field_name('display_type');
			?>
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
						'name'        => 'display_type',
						'type'        => 'select',
						'label'       => __( 'Display Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options'     => [
							'slider'    => esc_html__('Slider', 'rey-core'),
							'list'      => esc_html__('List', 'rey-core'),
							'custom'    => esc_html__('Custom (From/To)', 'rey-core'),
							// 'pricing'   => esc_html__('Pricing type', 'rey-core'),
						]
					]);

					$widget_fields->add_field([
						'name' => 'show_currency',
						'type' => 'checkbox',
						'label' => __( 'Show currency', 'rey-core' ),
						'value' => '1',
					]);

					$widget_fields->add_field([
						'name' => 'slider_margin',
						'type' => 'number',
						'label' => __( 'Margin between ranges', 'rey-core' ),
						'value' => 10,
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'slider',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 1,
							'min' => 1,
							'max' => 500,
						],
					]);

					$widget_fields->add_field([
						'name' => 'show_checkboxes',
						'type' => 'checkbox',
						'label' => __( 'Show checkboxes', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '==='
							],
						],
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
						'name' => 'price_list',
						'type' => 'range_points',
						'label' => __( 'PRICING POINTS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '==='
							]
						],
					]);

					$widget_fields->add_field([
						'name' => 'drop_panel',
						'type' => 'checkbox',
						'label' => __( 'Display as Drop-down', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'show_as_dropdown',
								'value' => false,
								'compare' => '=='
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
						'name' => 'show_as_dropdown',
						'type' => 'hidden',
						'separator' => 'before'
					]);

					// Custom From To
					$widget_fields->add_field([
						'name' => 'custom__separator',
						'type' => 'text',
						'label' => __( 'Separator', 'rey-core' ),
						'value' => __( 'To', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'custom',
								'compare' => '==='
							],
						],
						'placeholder' => esc_html__('eg: To', 'rey-core')
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
if (!function_exists('reyajaxfilter_register_price_filter_widget')) {
	function reyajaxfilter_register_price_filter_widget() {
		register_widget('REYAJAXFILTERS_Price_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_price_filter_widget');
}
