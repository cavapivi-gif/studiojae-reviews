<?php
namespace ReyCore\Modules\ProductVideo;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct(){

		if( ! function_exists('acf_add_local_field') ){
			return;
		}

		foreach ($this->fields() as $key => $field) {
			acf_add_local_field($field);
		}

	}

	public function fields(){
		return [
			[
				'key' => 'field_5e92dd2e2be4f',
				'label' => esc_html_x('Video', 'Backend setting label', 'rey-core'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'placement' => 'top',
				'endpoint' => 0,
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5e92dd4f2be50',
				'label' => esc_html_x('Video URL', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_url',
				'type' => 'text',
				'instructions' => esc_html_x('Supports YouTube and Vimeo urls. For self-hosted videos, paste in the video path.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: https://www.youtube.com/watch?v=L6P3nI6VnlY',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5e92dde22be51',
				'label' => esc_html_x('Gallery - Show Play icon button over main image?', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_main_image',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 1,
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5f678ebf2c933',
				'label' => esc_html_x('Gallery - Video Image', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_gallery_image',
				'type' => 'image',
				'instructions' => esc_html_x('Show this image in product gallery. For gallery with thumbnails, it\'s mandatory to set an image.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92dde22be51',
							'operator' => '!=',
							'value' => '1',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'return_format' => 'array',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_615c464aa9fb2',
				'label' => esc_html_x('Gallery - Show Inline?', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_inline',
				'type' => 'true_false',
				'instructions' => esc_html_x('Enable if you want to show the video inside the gallery (without modal).', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92dde22be51',
							'operator' => '!=',
							'value' => '1',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5e92de0d2be53',
				'label' => esc_html_x('Product Summary - Add button link?', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_summary',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'choices' => [
					'disabled' => esc_html_x('Don\'t add', 'Backend setting label', 'rey-core'),
					'before_product_meta' => esc_html_x('Before Product Meta', 'Backend setting label', 'rey-core'),
					'after_product_meta' => esc_html_x('After Product Meta', 'Backend setting label', 'rey-core'),
					'after_share' => esc_html_x('After Sharing buttons', 'Backend setting label', 'rey-core'),
					'before_add_to_cart' => esc_html_x('Before Add to cart button', 'Backend setting label', 'rey-core'),
					'after_title' => esc_html_x('After Title', 'Backend setting label', 'rey-core'),
				],
				'default_value' => 'disabled',
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5f71b8be0bc81',
				'label' => esc_html_x('Product Summary - Link Text', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_link_text',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5e92de0d2be53',
							'operator' => '!=',
							'value' => 'disabled',
						],
						[
							'field' => 'field_5e92de0d2be53',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: PLAY PRODUCT VIDEO',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			// [
			// 	'key' => 'field_5e92dead5526d',
			// 	'label' => esc_html_x('Display - Modal Width', 'Backend setting label', 'rey-core'),
			// 	'name' => 'product_video_modal_size',
			// 	'type' => 'number',
			// 	'instructions' => '',
			// 	'required' => 0,
			// 	'conditional_logic' => 0,
			// 	'wrapper' => [
			// 		'width' => '',
			// 		'class' => '',
			// 		'id' => '',
			// 	],
			// 	'default_value' => '',
			// 	'placeholder' => 'eg: 600',
			// 	'prepend' => '',
			// 	'append' => 'px',
			// 	'min' => '',
			// 	'max' => '',
			// 	'step' => '',
			// 	'parent' => self::FIELDS_GROUP_KEY,
			// ],
			[
				'key' => 'field_5ead2de65529d',
				'label' => esc_html_x('Display - Video Ratio (h/w]', 'Backend setting label', 'rey-core'),
				'name' => 'product_video_modal_ratio',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => 'eg: 56.25',
				'prepend' => '',
				'append' => '%',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],

		];
	}
}
