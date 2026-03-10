<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class WidgetFields
{

	private $widget;
	private $instance;

	public function __construct( $widget, $instance ){

		$this->widget = $widget;
		$this->instance = $instance;

	}

	public function add_field( $args ){

		if( empty($args) ){
			return;
		}

		$args = wp_parse_args($args, [
			'name'          => '',
			'type'          => '',
			'label'         => '',
			'value'         => '',
			'conditions'    => [],
			'wrapper_class' => '',
			'field_class'   => 'widefat',
			'separator'     => '',
			'placeholder'   => '',
			'suffix'        => '',
			'required'      =>  false,
			'description'   => '',
		]);

		if( empty($args['type']) ){
			return;
		}

		if( empty($args['name']) ){
			$args['name'] = 'widget-title-' . sanitize_title($args['label']);
		}

		if( !empty($args['conditions'])  ){
			$conditions = [];
			foreach ($args['conditions'] as $key => $value) {
				$condition = $value;
				$condition['name'] = $this->widget->get_field_name($value['name']);
				$conditions[] = $condition;
			}
			$args['conditions'] = $conditions;
		}

		$func = "option_{$args['type']}";

		// calc. separator
		if( !empty($args['separator']) ){
			$args['wrapper_class'] .= '--separator-' . $args['separator'];
		}

		if( ! is_callable([$this, $func]) ){
			return;
		}

		printf('<div id="%1$s-wrapper" class="rey-widget-field %3$s" %2$s>',
			$this->widget->get_field_id($args['name']),
			!empty($args['conditions']) ? sprintf("data-condition='%s'", wp_json_encode($args['conditions'])) : '',
			$args['wrapper_class']
		);

			$this->$func($args);

			if( $suffix = $args['suffix'] ){
				printf('<span class="__suffix">%s</span>', $suffix);
			}

		echo '</div>';

	}

	/**
	 * Generate widget checkbox
	 *
	 * @since 1.6.7
	 **/
	function option_checkbox( $args = [] )
	{
		printf( '<input class="checkbox %5$s" type="checkbox" id="%1$s" name="%2$s" %3$s value="%4$s" >',
			$this->widget->get_field_id($args['name']),
			$this->widget->get_field_name($args['name']),
			isset($this->instance[$args['name']]) ? checked( $this->instance[$args['name']], true, false ) : '',
			$args['value'],
			$args['field_class']
		);
		printf(
			'<label for="%1$s">%2$s</label>',
			$this->widget->get_field_id($args['name']),
			$args['label']
		);
	}

		/**
		 * Generate widget text
		 *
		 * @since 1.6.7
		 **/
		function option_text( $args = [] )
		{
			printf(
				'<label for="%1$s">%2$s</label>',
				$this->widget->get_field_id($args['name']),
				$args['label']
			);

			$attributes = [];

			if( $placeholder = $args['placeholder'] ){
				$attributes[] = sprintf('placeholder="%s"', $placeholder);
			}

			if( $args['required'] ){
				$attributes[] = 'required';
			}

			$value = $args['value'];

			if( isset( $this->instance[$args['name']] ) ){
				$value = $this->instance[$args['name']];
			}

			printf( '<input class="%4$s" type="text" id="%1$s" name="%2$s" value="%3$s" %5$s>',
				$this->widget->get_field_id($args['name']),
				$this->widget->get_field_name($args['name']),
				esc_attr($value),
				$args['field_class'],
				implode(' ', $attributes)
			);
		}


		/**
		 * Generate widget number
		 *
		 * @since 1.6.7
		 **/
		function option_number( $args = [] )
		{
			printf(
				'<label for="%1$s">%2$s</label>',
				$this->widget->get_field_id($args['name']),
				$args['label']
			);

			$attributes = [];

			if( isset($args['options']) && !empty($args['options']) ){

				if( isset($args['options']['step']) ){
					$attributes[] = sprintf('step="%s"', $args['options']['step']);
				}

				if( isset($args['options']['min']) ){
					$attributes[] = sprintf('min="%s"', $args['options']['min']);
				}

				if( isset($args['options']['max']) ){
					$attributes[] = sprintf('max="%s"', $args['options']['max']);
				}
			}

			printf( '<input class="%4$s" type="number" id="%1$s" name="%2$s" value="%3$s" %5$s>',
				$this->widget->get_field_id($args['name']),
				$this->widget->get_field_name($args['name']),
				esc_attr($this->instance[$args['name']]),
				$args['field_class'],
				implode(' ', $attributes)
			);
		}


		/**
		 * Generate widget select list
		 *
		 * @since 1.6.7
		 **/
		function option_select( $args = [] )
		{
			printf(
				'<label for="%1$s">%2$s</label>',
				$this->widget->get_field_id($args['name']),
				$args['label']
			);

			$is_multiple = isset( $args['multiple'] ) && $args['multiple'];

			$options = '';

			if( isset($args['options']) && !empty($args['options']) ){
				foreach ($args['options'] as $key => $value) {

					$saved = $this->instance[$args['name']];

					if( ! $this->instance[$args['name']] && $args['value'] ){
						$saved = $args['value'] ;
					}

					if( $is_multiple ){
						$is_selected = in_array( $key, (array) $saved, true ) ? 'selected' : '';
					}
					else {
						$is_selected = selected( $saved, $key, false);
					}

					$options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $key, $value, $is_selected );
				}
			}

			printf( '<select class="%4$s" id="%1$s" name="%2$s" %5$s>%3$s</select>',
				$this->widget->get_field_id($args['name']),
				$this->widget->get_field_name($args['name']) . ( $is_multiple ? '[]' : '' ),
				$options,
				$args['field_class'],
				$is_multiple ? 'multiple' : ''
			);

			if( $is_multiple ){
				printf('<p class="__tiny-desc">%s</p>', __('* Hold down the Ctrl (windows) / Command (Mac) key to select multiple choices.', 'rey-core'));
			}
		}


		/**
		 * Generate widget title
		 *
		 * @since 1.6.7
		 **/
		function option_title( $args = [] )
		{
			printf('<span class="%s">%s</span>', $args['field_class'], $args['label'] );
		}


		/**
		 * Generate widget range_points
		 *
		 * @since 1.6.7
		 **/
		function option_range_points( $args = [] )
		{
			$field_id = $this->widget->get_field_id($args['name']);
			$start_name = $this->widget->get_field_name($args['name'] . '_start');
			$field_name = $this->widget->get_field_name($args['name']);
			$end_name = $this->widget->get_field_name($args['name'] . '_end');
			$start_enabled = $this->instance[ $args['name'] . '_start']['enable'] == 1;
			$end_enabled = $this->instance[ $args['name'] . '_end']['enable'] == 1;
			$supports_label = isset($args['supports']) && in_array('labels', $args['supports'], true);
			?>

			<div class="rey-widgetRangePoints-wrapper" >

				<p class="rey-widget-innerTitle"><?php echo $args['label']; ?></p>

				<p class="rey-widgetRangePoints-list --start <?php echo ! $start_enabled ? '--hidden' : ''; ?>">
					<input type="hidden" name="<?php echo $start_name; ?>[enable]" value="<?php echo $this->instance[ $args['name'] . '_start']['enable']; ?>" />
					<input type="text" class="widefat __text" name="<?php echo $start_name; ?>[text]" value="<?php echo $this->instance[ $args['name'] . '_start']['text']; ?>" placeholder="<?php _e('eg: Under', 'rey-core'); ?>" />
					<input type="text" class="widefat __max" name="<?php echo $start_name; ?>[max]" value="<?php echo $this->instance[ $args['name'] . '_start']['max']; ?>" placeholder="<?php _e('eg: 100', 'rey-core'); ?>" />
					<a href="#" class="rey-widgetRangePoints-remove">&times;</a>
				</p>

				<div id="<?php echo $field_id; ?>-wrapper" data-id="<?php echo $field_id; ?>" class="rey-widgetRangePoints-listWrapper">
					<?php if (isset($this->instance[ $args['name'] ]) && !empty($this->instance[ $args['name'] ])): ?>
						<?php
							$items = array_values($this->instance[ $args['name'] ]);
							foreach ($items as $key => $item): ?>
							<p class="rey-widgetRangePoints-list --default" data-key="<?php echo $key ?>">
								<?php if( $supports_label ): ?>
									<input type="text" class="widefat __label" name="<?php printf('%s[%s][label]', $field_name, $key) ; ?>" value="<?php echo isset($item['label']) ? $item['label'] : ''; ?>" placeholder="<?php _e('Label', 'rey-core'); ?>" />
								<?php endif; ?>
								<input type="text" class="widefat __min" name="<?php printf('%s[%s][min]', $field_name, $key) ; ?>" value="<?php echo isset($item['min']) ? $item['min'] : ''; ?>" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
								<input type="text" class="widefat __to" name="<?php printf('%s[%s][to]', $field_name, $key) ; ?>" value="<?php echo isset($item['to']) ? $item['to'] : ''; ?>" placeholder="<?php _e('to', 'rey-core'); ?>" />
								<input type="text" class="widefat __max" name="<?php printf('%s[%s][max]', $field_name, $key) ; ?>" value="<?php echo isset($item['max']) ? $item['max'] : ''; ?>" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
								<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
							</p>
						<?php endforeach ?>
					<?php else: ?>
						<p class="rey-widgetRangePoints-list --default">
							<?php if( $supports_label ): ?>
								<input type="text" class="widefat __label" name="<?php echo $field_name; ?>[0][label]" value="" placeholder="<?php _e('Label', 'rey-core'); ?>" />
							<?php endif; ?>
							<input type="text" class="widefat __min" name="<?php echo $field_name; ?>[0][min]" value="" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
							<input type="text" class="widefat __to" name="<?php echo $field_name; ?>[0][to]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
							<input type="text" class="widefat __max" name="<?php echo $field_name; ?>[0][max]" value="" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
							<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
						</p>
					<?php endif ?>
				</div>

				<p class="rey-widgetRangePoints-list --end <?php echo ! $end_enabled ? '--hidden' : ''; ?>">
					<input type="hidden" name="<?php echo $end_name; ?>[enable]" value="<?php echo $this->instance[ $args['name'] . '_end']['enable']; ?>" />
					<input type="text" class="widefat __text" name="<?php echo $end_name; ?>[text]" value="<?php echo $this->instance[ $args['name'] . '_end']['text']; ?>" placeholder="<?php _e('eg: Over', 'rey-core'); ?>" />
					<input type="text" class="widefat __min" name="<?php echo $end_name; ?>[min]" value="<?php echo $this->instance[ $args['name'] . '_end']['min']; ?>" placeholder="<?php _e('eg: 1000', 'rey-core'); ?>" />
					<a href="#" class="rey-widgetRangePoints-remove">&times;</a>
				</p>

				<p class="rey-widgetRangePoints-addWrapper">
					<a href="javascript:void(0)" class="button rey-widgetRangePoints-add"><?php _e('Add', 'rey-core'); ?></a>
					&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetRangePoints-add-start <?php echo $start_enabled ? '--inactive' : ''; ?>"><?php _e('Add start', 'rey-core'); ?></a>
					&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetRangePoints-add-end <?php echo $end_enabled ? '--inactive' : ''; ?>"><?php _e('Add end', 'rey-core'); ?></a>
				</p>

				<script type="text/html" id="tmpl-rey-<?php echo $field_id; ?>">
					<p class="rey-widgetRangePoints-list --default" data-key="{{data.int}}">
						<?php if( $supports_label ): ?>
							<input type="text" class="widefat __label" name="<?php echo $field_name; ?>[{{data.int}}][label]" value="" placeholder="<?php _e('Label', 'rey-core'); ?>" />
						<?php endif; ?>
						<input type="text" class="widefat __min" name="<?php echo $field_name; ?>[{{data.int}}][min]" value="" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
						<input type="text" class="widefat __to" name="<?php echo $field_name; ?>[{{data.int}}][to]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
						<input type="text" class="widefat __max" name="<?php echo $field_name; ?>[{{data.int}}][max]" value="" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
						<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
					</p>
				</script>

			</div>
			<?php
		}


		/**
		 * Generate widget repeater
		 *
		 * @since 1.6.7
		 **/
		function repeater__fields( $field_name, $fields, $key = 0, $item = [] )
		{
			$output = '';

			foreach ($fields as $k => $field){

				$classes = [
					'widefat',
					'__field-' . $k,
					isset($field['size']) ? 'size-' . $field['size'] : ''
				];

				$value = !empty($item) && isset($item[$field['key']]) ? $item[$field['key']] : '';

				if( $field['type'] === 'select' ){

					$output .= sprintf( '<select class="%2$s" name="%1$s">',
						sprintf('%s[%s][%s]', $field_name, $key, $field['key']),
						implode(' ', $classes)
					);
						foreach ($field['choices'] as $choice_key => $choice_value) {
							$output .= sprintf( '<option value="%1$s" %3$s>%2$s</option>',
								$choice_key,
								$choice_value,
								selected($value, $choice_key, false)
							);
						}
					$output .= '</select>';
				}

				elseif ($field['type'] === 'text') {
					$output .= sprintf( '<input type="text" class="%4$s" name="%1$s" value="%2$s" placeholder="%3$s" />',
						sprintf('%s[%s][%s]', $field_name, $key, $field['key']),
						$value,
						$field['title'],
						implode(' ', $classes)
					);
				}
			}

			$output .= '<a href="javascript:void(0)" class="rey-widgetRepeater-remove">&times;</a>';

			return $output;
		}


		/**
		 * Generate widget repeater
		 *
		 * @since 1.6.7
		 **/
		function option_repeater( $args = [] )
		{
			$field_id = $this->widget->get_field_id($args['name']);
			$field_name = $this->widget->get_field_name($args['name']);
			?>

			<div class="rey-widgetRepeater-wrapper" >

				<p class="rey-widget-innerTitle"><?php echo $args['label']; ?></p>

				<div id="<?php echo $field_id; ?>-wrapper" data-id="<?php echo $field_id; ?>" class="rey-widgetRepeater-listWrapper">
					<?php if (isset($this->instance[ $args['name'] ]) && !empty($this->instance[ $args['name'] ])): ?>
						<?php
							$items = array_values($this->instance[ $args['name'] ]);
							foreach ($items as $key => $item): ?>
							<p class="rey-widgetRepeater-list --default" data-key="<?php echo $key ?>">
								<?php
								echo $this->repeater__fields($field_name, $args['fields'], $key, $item); ?>
							</p>
						<?php endforeach ?>
					<?php else: ?>
						<p class="rey-widgetRepeater-list --default">
							<?php
							echo $this->repeater__fields($field_name, $args['fields']); ?>
						</p>
					<?php endif ?>
				</div>

				<p class="rey-widgetRepeater-addWrapper">
					<a href="javascript:void(0)" class="button rey-widgetRepeater-add"><?php _e('Add', 'rey-core'); ?></a>
				</p>

				<script type="text/html" id="tmpl-rey-<?php echo $field_id; ?>">
					<p class="rey-widgetRepeater-list --default" data-key="{{data.int}}">
						<?php
						echo $this->repeater__fields($field_name, $args['fields'], '{{data.int}}'); ?>
					</p>
				</script>

			</div>
			<?php
		}


		/**
		 * Generate widget hidden
		 *
		 * @since 2.1.0
		 **/
		function option_hidden( $args = [] )
		{
			$attributes = [];

			$value = $args['value'];

			if( isset( $this->instance[$args['name']] ) ){
				$value = $this->instance[$args['name']];
			}

			printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">',
				$this->widget->get_field_id($args['name']),
				$this->widget->get_field_name($args['name']),
				esc_attr($value)
			);
		}

}
