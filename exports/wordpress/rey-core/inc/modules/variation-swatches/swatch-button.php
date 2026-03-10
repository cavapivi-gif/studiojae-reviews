<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchButton extends SwatchBase
{

	public $is_default = true;
	public $has_preview = true;

	const GROUP_KEY = 'group_68365463d8870';

	const TYPE_KEY = 'rey_button';

	function __construct(){
		add_filter('acf/get_field_group', [$this, 'add_location']);

		parent::__construct();
	}

	function get_id(){
		return self::TYPE_KEY;
	}

	function get_name(){
		return 'Buttons - Inline [rey]';
	}

	function get_content__text($term){
		return $term->name;
	}

	function get_attribute_settings_support(){
		return [
			'swatch_width',
			'swatch_height',
			'swatch_radius',
			'swatch_padding',
			'swatch_spacing',
			'swatch_font_size',
		];
	}

	public function add_location($group){

		if( ! (isset($group['key']) && self::GROUP_KEY === $group['key']) ){
			return $group;
		}

		$group['location'] = $this->get_term_settings_location();

		return $group;
	}

	function get_item_content($term, $style){

		if( empty($term) ){
			return;
		}

		if( ! isset($term->taxonomy) && isset($term->name) ){
			return sprintf('<span class="rey-swatchList-itemContent">%s</span>', $term->name );
		}

		$output = '';

		if( $style ){
			$output .= sprintf('<span class="__swatch" style="%s"></span>', esc_attr($style) );
		}

		$output .= sprintf('<span class="rey-swatchList-itemContent">%s</span>', $this->get_content__text( $term ) );

		if( isset($this->attribute_settings['swatch_tooltip']) && 'no' !== $this->attribute_settings['swatch_tooltip'] ){
			$output .= $this->render_tooltip($term);
		}

		return $output;
	}

	function get_content__style($args = []){

		$this->custom_image = false;

		if( $args['custom_image'] && ! empty($args['custom_image']) ){
			$this->custom_image = $args['custom_image'];
		}

		return $this->get_swatch_style($args['term']);

	}

	public function get_swatch_style( $term ){

		$settings = $this->get_term_swatch_data_by_term($term);

		$style = '';

		// // Custom image
		// if( $this->custom_image && isset($this->custom_image['url']) && $img_url = $this->custom_image['url'] ){
		// 	return sprintf('background-image:url(%s);', $img_url);
		// }

		// // prioritize image
		// if( isset($settings['image']) && ($image = $settings['image']) && isset($image['url']) && $img_url = $image['url'] ){
		// 	$style = sprintf('background-image:url(%s);', $img_url);
		// }

		// check colors
		if( isset($settings['color']) && $color = $settings['color']) {

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

		// if( $image = get_field('rey_attribute_image', $term) ){
		// 	$swatch_settings['image'] = [
		// 		'id' => $image,
		// 		'url' => wp_get_attachment_url($image)
		// 	];
		// }

		return $swatch_settings;
	}

	function add_terms_settings(){

		acf_add_local_field_group(array(
			'key' => self::GROUP_KEY,
			'title' => 'Swatch Settings (Button)',
			'fields' => array(
				array(
					'key' => 'field_616863985fca8',
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
					'key' => 'field_616863b35fca9',
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
				// array(
				// 	'key' => 'field_616863c15fcaa',
				// 	'label' => esc_html_x('Image', 'Backend setting label', 'rey-core'),
				// 	'name' => 'rey_attribute_image',
				// 	'type' => 'image',
				// 	'instructions' => 'Override the color with an image.',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array(
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'return_format' => 'id',
				// 	'preview_size' => 'thumbnail',
				// 	'library' => 'all',
				// 	'min_width' => '',
				// 	'min_height' => '',
				// 	'min_size' => '',
				// 	'max_width' => '',
				// 	'max_height' => '',
				// 	'max_size' => '',
				// 	'mime_types' => '',
				// ),
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
