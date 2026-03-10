<?php
namespace ReyCore\Modules\PdpCustomTabs;

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
				'key' => 'field_5ecaea2256e70',
				'label' => esc_html_x('Custom Tabs/Blocks', 'Backend setting label', 'rey-core'),
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
				'key' => 'field_5ecae99f56e6d',
				'label' => '',
				'name' => 'product_custom_tabs',
				'type' => 'repeater',
				'instructions' => sprintf('You can pick "Custom" to create new tabs for this product, or you can pick any Global tab, to override its content (if Title and Content are filled below). Global Tabs/Blocks are created in <a href="%s" target="_blank" title="Customizer > WooCommerce > Product page - Tabs/Blocks > Custom Tabs">Customizer\'s "Custom Tabs"</a> setting.', add_query_arg([
					'autofocus[section]' => 'woo-pdp-tabs-blocks'
					], admin_url( 'customize.php' )
				)),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'class' => '--acf-repeater-row',
				],
				'collapsed' => 'field_5ecae9c356e6e',
				'min' => 0,
				'max' => 0,
				'layout' => 'row',
				'button_label' => 'Add / Override Global Tab',
				'parent' => self::FIELDS_GROUP_KEY,
				'sub_fields' => [
					[
						'key' => 'field_615b37e5b5408',
						'label' => esc_html_x('Type', 'Backend setting label', 'rey-core'),
						'name' => 'tab_type',
						'type' => 'select',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => [
							'class' => 'rey-decrease-list-size',
						],
						'choices' => [
							''         => '- Select -',
							'custom'   => 'Custom Tab',
						],
						'default_value' => '',
						'ui' => 0,
						'return_format' => 'value',
					],
					[
						'key' => 'field_5ecae9c356e6e',
						'label' => esc_html_x('Tab Title', 'Backend setting label', 'rey-core'),
						'name' => 'tab_title',
						'type' => 'text',
						'required' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'default_value' => '',
						'placeholder' => 'ex: My Custom Tab',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					],
					[
						'key' => 'field_5ecae9ef56e6f',
						'label' => esc_html_x('Tab Content', 'Backend setting label', 'rey-core'),
						'name' => 'tab_content',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'default_value' => '',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 1,
					],
					[
						'key' => 'field_649453268d134',
						'label' => esc_html_x('Priority', 'Backend setting label', 'rey-core'),
						'name' => 'custom_tab_priority',
						'aria-label' => '',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => [
							[
								[
									'field' => 'field_615b37e5b5408',
									'operator' => '==',
									'value' => 'custom',
								]
							]
						],
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'default_value' => 50,
						'min' => '',
						'max' => '',
						'placeholder' => '',
						'step' => '',
						'prepend' => '',
						'append' => '',
					],
					[
						'key' => 'field_649453638d135',
						'label' => esc_html_x('Add into Accordion', 'Backend setting label', 'rey-core'),
						'name' => 'custom_add_into_accordion',
						'aria-label' => '',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => [
							[
								[
									'field' => 'field_615b37e5b5408',
									'operator' => '==',
									'value' => 'custom',
								]
							],
						],
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'message' => '',
						'default_value' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
						'ui' => 1,
					],
					[
						'key' => 'field_649453c98d136',
						'label' => esc_html_x('Disable', 'Backend setting label', 'rey-core'),
						'name' => 'tab_disable',
						'aria-label' => '',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
					],
				],
			],

		];
	}
}
