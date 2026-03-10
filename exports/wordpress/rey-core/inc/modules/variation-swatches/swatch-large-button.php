<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchLargeButton extends SwatchBase
{

	public $has_preview = true;

	const GROUP_KEY = 'group_61686363d8870';

	const TYPE_KEY = 'rey_large_button';

	function __construct(){

		add_filter('reycore/variation_swatches/attribute_fields', [$this, 'add_custom_fields']);
		add_filter('reycore/variation_swatches/item_styles', [$this, 'add_custom_css_props']);
		add_filter('acf/get_field_group', [$this, 'add_location']);

		parent::__construct();
	}

	function get_id(){
		return self::TYPE_KEY;
	}

	function get_name(){
		return 'Buttons - Large [rey]';
	}

	function get_content__text($term){
		return $term->name;
	}

	public function add_location($group){

		if( ! (isset($group['key']) && self::GROUP_KEY === $group['key']) ){
			return $group;
		}

		$group['location'] = $this->get_term_settings_location();

		return $group;
	}

	function add_custom_fields($fields){

		$fields['swatch_per_row'] = [
			'type'          => 'number',
			'label'         => esc_html__( 'Items per row (default 1)', 'rey-core' ),
			'value'         => '',
			'field_class'   => 'small-text',
			'suffix'        => 'px',
			'options'       => [
				'min'  => 1,
				'max'  => 20,
				'step' => 1,
			],
		];

		$fields['swatch_align'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Text alignment', 'rey-core' ),
			'value'         => 'center',
			'field_class'   => '',
			'options'       => [
				'start'  => esc_html__('Start', 'rey-core'),
				'center' => esc_html__('Center', 'rey-core'),
				'end'    => esc_html__('End', 'rey-core'),
			],
		];

		$fields['swatch_show_desc'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Show description', 'rey-core' ),
			'value'         => 'no',
			'field_class'   => '',
			'options'       => [
				'no'       => esc_html__('No', 'rey-core'),
				'yes'      => esc_html__('Yes', 'rey-core'),
			],
		];

		$fields['swatch_direction'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Direction', 'rey-core' ),
			'value'         => 'column',
			'field_class'   => '',
			'options'       => [
				'column'       => esc_html__('Vertical', 'rey-core'),
				'row'      => esc_html__('Horizontal', 'rey-core'),
			],
		];

		$fields['swatch_fallback'] = [
			'type'          => 'select',
			'label'         => esc_html__( 'Catalog Fallback', 'rey-core' ),
			'value'         => 'column',
			'field_class'   => '',
			'options'       => [
				\ReyCore\Modules\VariationSwatches\SwatchButton::TYPE_KEY => esc_html__('Button', 'rey-core'),
				\ReyCore\Modules\VariationSwatches\SwatchColor::TYPE_KEY => esc_html__('Color', 'rey-core'),
				\ReyCore\Modules\VariationSwatches\SwatchImage::TYPE_KEY => esc_html__('Image', 'rey-core'),
			],
		];

		// $fields['swatch_show_price'] = [
		// 	'type'          => 'select',
		// 	'label'         => esc_html__( 'Show price', 'rey-core' ),
		// 	'value'         => 'no',
		// 	'field_class'   => '',
		// 	'options'       => [
		// 		'no'       => esc_html__('No', 'rey-core'),
		// 		'yes'      => esc_html__('Yes', 'rey-core'),
		// 	],
		// ];

		return $fields;
	}

	function add_custom_css_props( $props ){

		$props['swatch_per_row'] = '--item-per-row:%s';
		$props['swatch_align'] = '--item-alignment:%s';
		$props['swatch_direction'] = '--item-direction:%s';
		// $props['swatch_font_size'] = '--item-font-size:%s';

		return $props;
	}

	function get_attribute_settings_support(){
		return [
			'swatch_height',
			'swatch_radius',
			'swatch_padding',
			'swatch_spacing',
			'swatch_font_size',
			'swatch_per_row',
			'swatch_align',
			'swatch_show_desc',
			'swatch_direction',
			'swatch_fallback',
			// 'swatch_show_price',
		];
	}

	public function get_item_content($term, $style){

		if( empty($term) ){
			return;
		}

		if( ! isset($term->taxonomy) && isset($term->name) ){
			return sprintf('<span class="rey-swatchList-itemContent">%s</span>', $term->name );
		}

		$output = '';

		$text = $this->get_content__text( $term );

		if( $style ){
			$output .= sprintf('<span class="__swatch" style="%s"></span>', esc_attr($style) );
		}

		$output .= '<span class="__content">';

		// if( isset($this->attribute_settings['swatch_show_price']) && 'no' !== $this->attribute_settings['swatch_show_price'] ){
		// 	$text = sprintf('<span class="__title">%s</span><span class="__price">%s</span>', $text, $this->get_variation_price($term) );
		// }

		$output .= sprintf('<span class="__title">%s</span>', $text );

		if(
			isset($this->attribute_settings['swatch_show_desc']) &&
			'no' !== $this->attribute_settings['swatch_show_desc'] &&
			($desc = $term->description)
		){
			$output .= sprintf('<span class="__desc">%s</span>', $desc);
		}

		$output .= '</span>';

		if( isset($this->attribute_settings['swatch_tooltip']) && 'no' !== $this->attribute_settings['swatch_tooltip'] ){
			$output .= $this->render_tooltip($term);
		}

		return $output;
	}

	public function get_content__style($args = []){

		$this->custom_image = false;

		if( $args['custom_image'] && ! empty($args['custom_image']) ){
			$this->custom_image = $args['custom_image'];
		}

		return $this->get_swatch_style($args['term']);

	}

	public function get_swatch_style( $term ){

		$settings = $this->get_term_swatch_data_by_term($term);

		$style = '';

		// Custom image
		if( $custom_image_style = Frontend::extract_custom_image($this->custom_image) ){
			return $custom_image_style;
		}

		// prioritize image
		if( isset($settings['image']) && ($image = $settings['image']) && isset($image['url']) && $img_url = $image['url'] ){
			$style = sprintf('background-image:url(%s);', $img_url);
		}

		// check colors
		elseif( isset($settings['color']) && $color = $settings['color']) {

			if( isset($settings['secondary_color']) && $secondary_color = $settings['secondary_color'] ){
				$style = sprintf('background: %1$s; background: linear-gradient(%3$s, %1$s 50%%, %2$s 50%%);', $color, $secondary_color, 'var(--rey-var-swatch-gradient-angle, 90deg)');
			}

			else {
				$style = sprintf('background: %1$s;', $color);
			}

		}

		return $style;
	}

	public function get_attribute_data($term){

		$swatch_settings = [];

		if( $color = get_field('rey_attribute_color', $term) ){
			$swatch_settings['color'] = $color;
		}

		if( $secondary_color = get_field('rey_attribute_color_secondary', $term) ){
			$swatch_settings['secondary_color'] = $secondary_color;
		}

		if( $image = get_field('rey_attribute_image', $term) ){
			$swatch_settings['image'] = [
				'id' => $image,
				'url' => wp_get_attachment_url($image)
			];
		}

		return $swatch_settings;
	}

	public function add_terms_settings(){

		acf_add_local_field_group(array(
			'key' => self::GROUP_KEY,
			'title' => 'Swatch Settings (Large Button)',
			'fields' => array(
				array(
					'key' => 'field_618537985fca8',
					'label' => esc_html_x('Color', 'Backend setting label', 'rey-core'),
					'name' => 'rey_attribute_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 0,
					'return_format' => 'string',
				),
				array(
					'key' => 'field_618537b35fca9',
					'label' => esc_html_x('Secondary Color', 'Backend setting label', 'rey-core'),
					'name' => 'rey_attribute_color_secondary',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 0,
					'return_format' => 'string',
				),
				array(
					'key' => 'field_618537c15fcaa',
					'label' => esc_html_x('Image', 'Backend setting label', 'rey-core'),
					'name' => 'rey_attribute_image',
					'type' => 'image',
					'instructions' => esc_html_x('Override the color with an image.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'id',
					'preview_size' => 'thumbnail',
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
			'location' => [],
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

}
