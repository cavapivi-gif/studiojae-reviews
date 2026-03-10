<?php
namespace ReyCore\Modules\BuyNowButton;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct($acf_fields){

		foreach ($this->fields() as $field) {
			$acf_fields->set_group_fields( 'product_settings', $field, 'single_specifications_block' );
		}

	}

	public function fields(){
		return [
			[
				'key' => 'field_60063680a1046',
				'label' => 'Buy Now button',
				'name' => 'buynow_pdp__enable',
				'type' => 'true_false',
				'instructions' => esc_html_x('Show the "Buy now" button for this product only.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => 'Show',
				'ui_off_text' => 'Hide',
				'parent' => self::FIELDS_GROUP_KEY,
				// 'menu_order' => 5,
			],
		];
	}
}
