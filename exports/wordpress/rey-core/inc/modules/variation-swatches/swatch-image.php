<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchImage extends SwatchBase
{

	public $has_preview = true;

	const GROUP_KEY = 'group_616869317289a';

	const TYPE_KEY = 'rey_image';

	function __construct(){
		add_filter('acf/get_field_group', [$this, 'add_location']);
		parent::__construct();
	}

	function get_id(){
		return self::TYPE_KEY;
	}

	function get_name(){
		return 'Image [rey]';
	}

	function get_attribute_settings_support(){
		return [
			'swatch_tooltip_image',
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

		return $this->get_swatch_style($args['term']);

	}

	public function get_swatch_style( $term ){

		$settings = $this->get_term_swatch_data_by_term($term);

		$style = '';

		// Custom image
		if( $custom_image_style = Frontend::extract_custom_image($this->custom_image) ){
			return $custom_image_style;
		}

		// Swatch image
		if( isset($settings['image']) && ($image = $settings['image']) && isset($image['url']) && ($img_url = $image['url']) ){

			if( Base::$settings['image_uses_tag'] ){
				$style = $img_url;
			}
			else {
				$style = sprintf('background-image:url(%s);', $img_url);
			}

		}

		return $style;

	}

	function get_attribute_data($term){

		$swatch_settings = [];

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
			'title' => 'Swatch Settings (Image)',
			'fields' => array(
				array(
					'key' => 'field_61686932g49f4',
					'label' => esc_html_x('Image', 'Backend setting label', 'rey-core'),
					'name' => 'rey_attribute_image',
					'type' => 'image',
					'instructions' => esc_html_x('Add image to be used for swatch.', 'Backend setting description', 'rey-core'),
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

	function get_tooltip_content__before($term){

		$attribute_settings = Base::get_attributes_swatch_settings( $term->taxonomy );

		if( ! (isset($attribute_settings['swatch_tooltip_image']) && 'no' !== $attribute_settings['swatch_tooltip_image']) ){
			return;
		}

		$img_id = 0;

		$settings = $this->get_attribute_data($term);

		if( $this->custom_image && isset($this->custom_image['id']) && ($custom_img_id = $this->custom_image['id']) ){
			$img_id = $custom_img_id;
		}

		else if(
			isset($settings['image']) && ($image = $settings['image']) &&
			isset($image['id']) && $swatch_img_id = $image['id'] ){
				$img_id = $swatch_img_id;
		}

		if( $img_id ){
			return wp_get_attachment_image($img_id, Base::$settings['tooltip_image_size'], false, ['class' => '__image']);
		}

	}


}
