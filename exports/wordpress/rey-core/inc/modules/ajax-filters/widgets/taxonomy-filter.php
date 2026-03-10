<?php

class REYAJAXFILTERS_Taxonomy_Filter_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'reyajfilter-taxonomy-filter', // Base ID
			__('Filter - by Taxonomy', 'rey-core'), // Name
			array('description' => __('Filter WooCommerce products by taxonomy.', 'rey-core')) // Args
		);

		$this->defaults = [
			'title'              => '',
			'taxonomy'           => '',
			'custom_height'      => '',
			'query_type'         => 'or',
			'hide_empty'         => true,
			'search_box'         => false,
			'enable_multiple'    => false,
			'show_count'         => false,
			'hierarchical'       => false,
			'accordion_list'     => false,
			'show_checkboxes'    => false,
			'show_checkboxes__radio'  => false,
			'show_children_only' => false,
			'display_type'       => 'list',
			'rey_multi_col'      => false,
			'alphabetic_menu'    => false,
			'drop_panel'              => false,
			'drop_panel_keep_active'  => false,
			'placeholder'        => '',
			'dd_width'        => '',
			// Advanced
			'show_hide_categories'    => 'hide',
			'show_only_on_categories' => [],
			'selective_display' => [],
			// URL
			'value_type'              => 'id',
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
	public function widget($args, $instance) {

		$instance = wp_parse_args( (array) $instance, $this->defaults );

		if( \ReyCore\Modules\AjaxFilters\Base::should_hide_widget($instance) ){
			return;
		}

		if ( ! ($query_type = $instance['query_type']) ) {
			return;
		}

		reycore_assets()->add_scripts('rey-simple-scrollbar');
		reycore_assets()->add_styles('rey-simple-scrollbar');

		// enqueue necessary scripts
		\ReyCore\Modules\AjaxFilters\Base::load_scripts();

		$display_type = $instance['display_type'];
		$is_list = $display_type === 'list';

		$taxonomy   = (!empty($instance['taxonomy'])) ? wc_clean($instance['taxonomy']) : '';
		$taxonomy_name = '';
		$taxonomies = $this->get_taxonomies();

		foreach ($taxonomies as $value) {
			if( $value['id'] === $taxonomy ){
				$taxonomy_name = $value['name'];
				continue;
			}
		}

		if( empty($taxonomy) || empty($taxonomy_name) ){
			return;
		}

		$key_slug = str_replace('-', '', sanitize_title( $taxonomy_name ));
		$data_key   = ($query_type === 'and') ? "product-{$key_slug}a" : "product-{$key_slug}o";

		if( $custom_key = esc_attr($instance['key_name']) ){
			$data_key = $custom_key;
		}

		// parse url
		$url = $_SERVER['QUERY_STRING'];
		parse_str($url, $url_array);

		$attr_args = [
			'taxonomy'           => $taxonomy,
			'taxonomy_name'      => $taxonomy_name,
			'data_key'           => $data_key,
			'query_type'         => $query_type,
			'url_array'          => $url_array,
			'enable_multiple'    => (bool) $instance['enable_multiple'],
			'show_count'         => (bool) $instance['show_count'],
			'enable_hierarchy'   => (bool) $instance['hierarchical'],
			'show_children_only' => (bool) $instance['show_children_only'],
			'hide_empty'         => (bool) $instance['hide_empty'],
			'custom_height'      => (!empty($instance['custom_height']) && $is_list) ? $instance['custom_height']: '',
			'alphabetic_menu'    => ((bool) $instance['alphabetic_menu'] && $is_list),
			'search_box'         => ((bool) $instance['search_box']),
			'accordion_list'     => ((bool) $instance['accordion_list'] && $is_list && (bool) $instance['hierarchical'] ),
			'show_checkboxes'    => ((bool) $instance['show_checkboxes']),
			'drop_panel'         => (bool) $instance['drop_panel'],
			'drop_panel_button'  => $instance['title'] ? $instance['title'] : esc_html__('Select Item', 'rey-core'),
			'drop_panel_keep_active'  => (bool) $instance['drop_panel_keep_active'],
			'dropdown'           => ($display_type === 'dropdown') && ! (bool) $instance['drop_panel'], // BC
			'placeholder'        => $instance['placeholder'],
			'dd_width'           => $instance['dd_width'],
			'widget_id' => $args['widget_id'],
			'value_type'         => $instance['value_type'],
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

			if( $display_type !== 'dropdown' && $instance['rey_multi_col'] ){
				$widget_class .= ' rey-filterList-cols';
				$before_widget .= \ReyCore\Modules\AjaxFilters\Base::multicols_css();
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
			echo $args['before_title'] . apply_filters('widget_title', $instance['title'], $instance). $args['after_title'];
		}

		echo $html;

		echo $args['after_widget'];
	}

	public function get_taxonomies(){
		return \ReyCore\Modules\AjaxFilters\Base::get_registered_taxonomies();
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
				<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
			</div>

			<div class="rey-widgetTabs-tabContent --active" data-tab="basic">
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:', 'rey-core'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy') ?></label>
					<select class="widefat " id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
						<option><?php _e('- Select -', 'rey-core'); ?></option>
						<?php
						$taxonomies = $this->get_taxonomies();
						foreach ($taxonomies as $value) {
							printf('<option value="%2$s" %3$s>%1$s</option>',
								$value['name'],
								$value['id'],
								isset($instance['taxonomy']) ? selected($instance['taxonomy'], $value['id'], false) : ''
							);
						}
						?>
					</select>
					<small><?php printf(_x('If the product taxonomy is not listed here, please manually register its ID and Name in <a href="%s" target="_blank">Customizer > WooCommerce > Ajax Filters</a> (at the very bottom).', 'Widget control description', 'rey-core'), add_query_arg( ['autofocus[section]' => 'ajax-filters'], admin_url( 'customize.php' ) ) ); ?></small>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('display_type'); ?>"><?php esc_html_e('Display Type', 'rey-core'); ?></label>
					<select class="widefat" id="<?php echo $this->get_field_id('display_type'); ?>" name="<?php echo $display_name; ?>">
						<option value="list" <?php selected( $instance['display_type'], 'list'); ?>><?php esc_html_e('List', 'rey-core'); ?></option>
						<option value="dropdown" <?php selected( $instance['display_type'], 'dropdown'); ?>><?php esc_html_e('Dropdown (Deprecated)', 'rey-core'); ?></option>
					</select>
				</p>

				<?php

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
				?>
				<p>
					<input id="<?php echo $this->get_field_id('enable_multiple'); ?>" name="<?php echo $this->get_field_name('enable_multiple'); ?>" type="checkbox" value="1" <?php checked($instance['enable_multiple']); ?>>
					<label for="<?php echo $this->get_field_id('enable_multiple'); ?>"><?php esc_html_e('Enable multiple filter', 'rey-core'); ?></label>
				</p>
				<p>
					<input id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" type="checkbox" value="1" <?php checked($instance['show_count']); ?>>
					<label for="<?php echo $this->get_field_id('show_count'); ?>"><?php esc_html_e('Show count', 'rey-core'); ?></label>
				</p>
				<p>
					<input id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>" type="checkbox" value="1" <?php checked($instance['hierarchical']); ?>>
					<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php esc_html_e('Show hierarchy', 'rey-core'); ?></label>
				</p>
				<p>
					<input id="<?php echo $this->get_field_id('show_children_only'); ?>" name="<?php echo $this->get_field_name('show_children_only'); ?>" type="checkbox" value="1" <?php checked($instance['show_children_only']); ?>>
					<label for="<?php echo $this->get_field_id('show_children_only'); ?>"><?php esc_html_e('Only show children of the current term', 'rey-core'); ?></label>
				</p>

				<p>
					<input id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" type="checkbox" value="1" <?php checked( $instance['hide_empty'] ); ?>>
					<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php esc_html_e('Hide empty', 'rey-core'); ?></label>
				</p>

				<p id="<?php echo $this->get_field_id('show_checkboxes'); ?>-wrapper">
					<input id="<?php echo $this->get_field_id('show_checkboxes'); ?>" name="<?php echo $this->get_field_name('show_checkboxes'); ?>" type="checkbox" value="1" <?php checked( $instance['show_checkboxes'] ); ?>>
					<label for="<?php echo $this->get_field_id('show_checkboxes'); ?>"><?php esc_html_e('Show checkboxes', 'rey-core'); ?></label>
				</p>

				<?php

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
								'compare' => '=='
							],
							[
								'name' => 'show_checkboxes',
								'value' => true,
								'compare' => '=='
							],
						],
					]);
				?>

				<p id="<?php echo $this->get_field_id('search_box'); ?>-wrapper">
					<input id="<?php echo $this->get_field_id('search_box'); ?>" name="<?php echo $this->get_field_name('search_box'); ?>" type="checkbox" value="1" <?php checked( $instance['search_box'] ); ?>>
					<label for="<?php echo $this->get_field_id('search_box'); ?>"><?php esc_html_e('Show search (filter) field', 'rey-core'); ?></label>
				</p>

				<?php
					$list_condition = wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'list',
							'compare' => '==='
						]
					]);
				?>

				<p data-condition='<?php echo $list_condition; ?>'><strong><?php esc_html_e('LIST OPTIONS', 'rey-core') ?></strong></p>

				<p id="<?php echo $this->get_field_id('rey_multi_col'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'list',
							'compare' => '==='
						],
						[
							'name' => $this->get_field_name('hierarchical'),
							'value' => true,
							'compare' => '!='
						],
					]); ?>'>
					<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('rey_multi_col'); ?>" name="<?php echo $this->get_field_name('rey_multi_col'); ?>" <?php checked( $instance['rey_multi_col'] ); ?> value="1" />
					<label for="<?php echo $this->get_field_id('rey_multi_col'); ?>">
						<?php _e( 'Display list on 2 columns', 'rey-core' ); ?>
					</label>
				</p>

				<p id="<?php echo $this->get_field_id('accordion_list'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'list',
							'compare' => '==='
						],
						[
							'name' => $this->get_field_name('hierarchical'),
							'value' => true,
							'compare' => '=='
						],
						[
							'name' => $this->get_field_name('show_children_only'),
							'value' => true,
							'compare' => '!='
						],
					]); ?>'>
					<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('accordion_list'); ?>" name="<?php echo $this->get_field_name('accordion_list'); ?>" <?php checked( $instance['accordion_list'] ); ?> value="1" />
					<label for="<?php echo $this->get_field_id('accordion_list'); ?>">
						<?php _e( 'Display list as accordion', 'rey-core' ); ?>
					</label>
				</p>

				<p id="<?php echo $this->get_field_id('alphabetic_menu'); ?>-wrapper" data-condition='<?php echo $list_condition; ?>'>
					<input id="<?php echo $this->get_field_id('alphabetic_menu'); ?>" name="<?php echo $this->get_field_name('alphabetic_menu'); ?>" type="checkbox" value="1" <?php checked( $instance['alphabetic_menu'] ); ?>>
					<label for="<?php echo $this->get_field_id('alphabetic_menu'); ?>"><?php esc_html_e('Show alphabetic menu', 'rey-core'); ?></label>
				</p>

				<p id="<?php echo $this->get_field_id('custom_height'); ?>-wrapper" data-condition='<?php echo $list_condition; ?>'>
					<label for="<?php echo $this->get_field_id('custom_height'); ?>">
						<?php _e( 'Custom Height (px)', 'rey-core' ); ?>
					</label>
					<input class="tiny-text" type="number" step="1" min="50" max="1000" value="<?php echo esc_attr($instance['custom_height']) ?>" id="<?php echo $this->get_field_id('custom_height'); ?>" name="<?php echo $this->get_field_name('custom_height'); ?>" style="width: 100px" />
				</p>

				<?php
				$dd_condition = wp_json_encode([
					[
						'name' => $display_name,
						'value' => 'dropdown',
						'compare' => '==='
					]
				]); ?>

				<p data-condition='<?php echo $dd_condition; ?>'><strong><?php esc_html_e('DROPDOWN OPTIONS', 'rey-core') ?></strong></p>

				<p data-condition='<?php echo $dd_condition; ?>'>
					<label for="<?php echo $this->get_field_id('placeholder'); ?>"><?php esc_html_e('Placeholder:', 'rey-core'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" type="text" value="<?php echo esc_attr($instance['placeholder']); ?>" placeholder="<?php esc_html_e('eg: Choose', 'rey-core') ?>">
				</p>

				<p data-condition='<?php echo $dd_condition; ?>'>
					<label for="<?php echo $this->get_field_id('dd_width'); ?>">
						<?php _e( 'Custom dropdown width', 'rey-core' ); ?>
					</label>
					<input class="tiny-text" type="number" step="1" min="50" max="1000" value="<?php echo esc_attr($instance['dd_width']) ?>" id="<?php echo $this->get_field_id('dd_width'); ?>" name="<?php echo $this->get_field_name('dd_width'); ?>" style="width: 100px" />
					<span><small><?php _e( 'px', 'rey-core' ); ?></small></span>
				</p>
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
					],
					'conditions' => [
						[
							'name' => 'display_type',
							'value' => 'range',
							'compare' => '!='
						],
						[
							'name' => 'display_type',
							'value' => 'range_points',
							'compare' => '!='
						],
					],
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

		if( $tax = reycore__clean($instance['taxonomy']) ){
			\ReyCore\Modules\AjaxFilters\Helpers::save_custom_key([
				'new_instance' => $new_instance,
				'old_instance' => $old_instance,
				'taxonomy'     => $tax,
			]);
		}

		return $instance;
	}
}

add_action('widgets_init', function(){
	register_widget('REYAJAXFILTERS_Taxonomy_Filter_Widget');
});
