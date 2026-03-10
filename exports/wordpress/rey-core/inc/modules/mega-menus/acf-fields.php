<?php
namespace ReyCore\Modules\MegaMenus;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const GROUP_KEY = 'group_5c4f2dec3824e';

	public function __construct(){

		$this->add_fields();

		add_filter('acf/get_field_group', [$this, 'add_location']);

	}

	public function add_location($group){

		if( ! (isset($group['key']) && self::GROUP_KEY === $group['key']) ){
			return $group;
		}

		$location = [
			[
				[
					'param' => 'nav_menu_item',
					'operator' => '==',
					'value' => 'location/main-menu',
				],
			],
		];

		if( ! \ReyCore\ACF\Helper::prevent_export_dynamic_field() ){
			foreach (get_option(Base::SUPPORTED_MENUS, []) as $menu_id) {
				$location[] = [
					[
						'param' => 'nav_menu_item',
						'operator' => '==',
						'value' => $menu_id,
					]
				];
			}
		}

		$group['location'] = $location;

		return $group;
	}

	public function add_fields(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => self::GROUP_KEY,
			'title' => 'Menu Settings',
			'fields' => array(
				array(
					'key' => 'field_5c4f2e4b77834',
					'label' => esc_html_x('Mega Menu', 'Backend setting label', 'rey-core'),
					'name' => 'mega_menu',
					'type' => 'true_false',
					'instructions' => esc_html_x('Activate the mega menu for this menu item.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5c4f2e9f77836',
					'label' => esc_html_x('Mega Menu Type', 'Backend setting label', 'rey-core'),
					'name' => 'mega_menu_type',
					'type' => 'select',
					'instructions' => esc_html_x('Select the type of mega menu. Columns will only show submenu trees into columns, while Global Sections allows much more complex layouts.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'columns' => esc_html_x('Menu Columns', 'Backend setting label', 'rey-core'),
						'global_sections' => esc_html_x('Global Sections', 'Backend setting label', 'rey-core'),
					),
					'default_value' => 'columns',
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f31f6d86fd',
					'label' => esc_html_x('Menu Columns per row', 'Backend setting label', 'rey-core'),
					'name' => 'mega_menu_columns',
					'type' => 'select',
					'instructions' => esc_html_x('Select how many columns per row.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'columns',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
						2 => esc_html_x('2 Columns', 'Backend setting label', 'rey-core'),
						3 => esc_html_x('3 Columns', 'Backend setting label', 'rey-core'),
						4 => esc_html_x('4 Columns', 'Backend setting label', 'rey-core'),
						5 => esc_html_x('5 Columns', 'Backend setting label', 'rey-core'),
					),
					'default_value' => 2,
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f2f2277837',
					'label' => esc_html_x('Select Global Section', 'Backend setting label', 'rey-core'),
					'name' => 'menu_global_section',
					'type' => 'select',
					'instructions' => esc_html_x('Select the global section to load in this mega menu panel.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'global_sections',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
					),
					'default_value' => false,
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
					'rey_export' => 'post_id',
				),
				array(
					'key' => 'field_5c24f22778f37',
					'name' => 'mega_lazy',
					'label' => esc_html_x('Ajax Lazy load', 'Backend setting label', 'rey-core'),
					'type' => 'select',
					'instructions' => esc_html_x('Load the content via Ajax.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f2e9f77836',
								'operator' => '==',
								'value' => 'global_sections',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'choices' => array(
						'' => 'No',
						// 'yes_mo' => esc_html_x('Yes, on mouseover item', 'Backend setting label', 'rey-core'),
						// 'yes_pm' => esc_html_x('Yes, on mouseover parent menu', 'Backend setting label', 'rey-core'),
						'yes_pl' => esc_html_x('Yes', 'Backend setting label', 'rey-core'),
					),
					'default_value' => '',
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5c4f7fcc3be58',
					'label' => esc_html_x('Panel layout', 'Backend setting label', 'rey-core'),
					'name' => 'panel_layout',
					'type' => 'select',
					'instructions' => esc_html_x('Select the panel\'s layout', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'full' => esc_html_x('Window full width', 'Backend setting label', 'rey-core'),
						'boxed' => esc_html_x('Boxed (Container Width)', 'Backend setting label', 'rey-core'),
						'custom' => esc_html_x('Custom Width', 'Backend setting label', 'rey-core'),
					),
					'default_value' => 'boxed',
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_5ce2d5578c1b9',
					'label' => esc_html_x('Panel Width (px)', 'Backend setting label', 'rey-core'),
					'name' => 'panel_width',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5c4f2e4b77834',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5c4f7fcc3be58',
								'operator' => '==',
								'value' => 'custom',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => 800,
					'placeholder' => 'eg: 800',
					'prepend' => '',
					'append' => 'px',
					'min' => 200,
					'max' => 1800,
					'step' => '',
				),
				array(
					'key' => 'field_5e60c30ec556b',
					'label' => esc_html_x('Sub-Panel Styles', 'Backend setting label', 'rey-core'),
					'name' => 'panel_styles',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5e60c40ec556c',
					'label' => esc_html_x('Text Color', 'Backend setting label', 'rey-core'),
					'name' => 'panel_text_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 1,
				),
				array(
					'key' => 'field_5e60c459c556d',
					'label' => esc_html_x('Background Color', 'Backend setting label', 'rey-core'),
					'name' => 'panel_bg_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 1,
				),
				array(
					'key' => 'field_5e60c468c556e',
					'label' => esc_html_x('Padding', 'Backend setting label', 'rey-core'),
					'name' => 'panel_padding',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e60c30ec556b',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '--dependent',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 50,
					'prepend' => '',
					'append' => 'px',
					'min' => '',
					'max' => '',
					'step' => '',
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
