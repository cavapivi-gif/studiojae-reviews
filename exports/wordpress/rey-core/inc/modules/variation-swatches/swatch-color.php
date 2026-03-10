<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchColor extends SwatchBase
{
	public $has_preview = true;

	const GROUP_KEY = 'group_61695673d8870';

	const TYPE_KEY = 'rey_color';

	function __construct(){

		add_filter('acf/get_field_group', [$this, 'add_location']);

		parent::__construct();
	}

	function get_id(){
		return self::TYPE_KEY;
	}

	function get_name(){
		return 'Color [rey]';
	}

	function get_attribute_settings_support(){
		return [
			'use_variation_img',
			'swatch_width',
			'swatch_height',
			'swatch_radius',
			'swatch_padding',
			'swatch_spacing',
		];
	}

	public function add_location($group){

		if( ! (isset($group['key']) && self::GROUP_KEY === $group['key']) ){
			return $group;
		}

		$group['location'] = $this->get_term_settings_location();

		return $group;
	}

	function get_content__style($args = []){

		$this->custom_image = false;

		if( $args['custom_image'] && ! empty($args['custom_image']) ){
			$this->custom_image = $args['custom_image'];
		}

		// Custom attributes
		if( ! ($args['term'] instanceof \WP_Term) ){
			if(
				($custom_attr_data = Frontend::get_custom_attributes_data())
				&& isset($args['term']->name)
				&& ! empty($custom_attr_data[ $args['attribute'] ]['items'][ $args['term']->name ])
			){
				return $this->get_style( $custom_attr_data[ $args['attribute'] ]['items'][ $args['term']->name ] );
			}
		}

		return $this->get_swatch_style($args['term']);

	}

	public function get_swatch_style( $term ){

		$settings = $this->get_term_swatch_data_by_term($term);

		// Custom image
		if( $custom_image_style = Frontend::extract_custom_image($this->custom_image) ){
			return $custom_image_style;
		}

		return $this->get_style($settings);
	}

	public function get_style($settings){

		$style = '';

		// prioritize image
		if( isset($settings['image']) && ($image = $settings['image']) ){
			$style = sprintf('background-image:url(%s);', isset($image['url']) ? $image['url'] : $image);
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

	function get_attribute_data($term){

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

	function add_terms_settings(){

		acf_add_local_field_group(array(
			'key' => self::GROUP_KEY,
			'title' => 'Swatch Settings (Color)',
			'fields' => array(
				array(
					'key' => 'field_6g6583985fca8',
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
					'key' => 'field_6g6583b35fca9',
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
					'key' => 'field_6g6583c15fcaa',
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
