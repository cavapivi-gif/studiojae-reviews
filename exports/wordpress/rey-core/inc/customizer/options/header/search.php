<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Search extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'header-search';
	}

	public function get_title(){
		return esc_html__('Search (Button & Panel)', 'rey-core');
	}

	public function get_priority(){
		return 30;
	}

	public function get_icon(){
		return 'header-search';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-header-settings/#search');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_enable_search',
			'label'       => esc_html__( 'Enable Search?', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$this->add_notice([
			'default'     => __('<strong>Heads up!</strong><br> You\'re using a Header Global Section, built with Elementor. It\'s likely these settings below won\'t work properly because the "Header - Search" element might have the Override option enabled eg: <a href="https://d.pr/i/npGOxT" target="_blank">https://d.pr/i/npGOxT</a>.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'default',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_search_style',
			'label'       => esc_html__( 'Search Style', 'rey-core' ),
			'default'     => 'wide',
			'choices'     => [
				'button' => esc_html__( 'Simple Form', 'rey-core' ),
				'wide' => esc_html__( 'Wide Panel', 'rey-core' ),
				'side' => esc_html__( 'Side Panel', 'rey-core' ),
			],
		] );

		$this->start_controls_group( [
			'group_id' => 'header_search_styles_options_title',
			'label'    => esc_html__( 'Panel options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_search_style',
					'operator' => 'in',
					'value'    => ['wide' , 'side'],
				],
			],
		]);

			$this->add_control( [
				'type'      => 'rey-color',
				'settings'  => 'search_bg_color',
				'label'     => __( 'Background Color', 'rey-core' ),
				'default'   => '',
				'transport' => 'auto',
				'choices'   => [
					'alpha' => true,
				],
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--search-bg-color',
					],
				],
			] );


			$this->add_control( [
				'type'      => 'rey-color',
				'settings'  => 'search_text_color',
				'label'     => __( 'Text Color', 'rey-core' ),
				'default'   => '',
				'transport' => 'auto',
				'choices'   => [
					'alpha' => true,
				],
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--search-text-color',
					],
				],
			] );


			/* ------------------------------------ Suggestions ------------------------------------ */

			$this->add_title( esc_html__('Extra content', 'rey-core'), [
				'description' => esc_html__('Add extra content near the search form.', 'rey-core'),
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '!=',
						'value'    => 'button',
					],
				],
			]);


			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'search_complementary',
				'label'       => esc_html__( 'Suggestions content', 'rey-core' ),
				'default'     => 'menu',
				'multiple'    => 1,
				'choices'     => [
					'menu' => esc_html__( 'Menu', 'rey-core' ),
					'keywords' => esc_html__( 'Keyword suggestions', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '!=',
						'value'    => 'button',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'search_menu',
				'label'       => esc_html__( 'Search menu source', 'rey-core' ),
				'default'     => '',
				'choices'     => ['' => esc_html__('- Select -', 'rey-core')],
				'ajax_choices' => 'get_menus_list',
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '!=',
						'value'    => 'button',
					],
					[
						'setting'  => 'search_complementary',
						'operator' => '==',
						'value'    => 'menu',
					],
				],
			] );

			$this->add_control( [
				'type'     => 'textarea',
				'settings'    => 'search_suggestions',
				'label'       => esc_html__( 'Keywords', 'rey-core' ),
				'default'  => '',
				'description' => esc_html__( 'Add keyword suggestions, separated by comma ",".', 'rey-core' ),
				'input_attrs'     => [
					'placeholder' => esc_html__( 'eg: t-shirt, pants, trousers', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '!=',
						'value'    => 'button',
					],
					[
						'setting'  => 'search_complementary',
						'operator' => '==',
						'value'    => 'keywords',
					],
				],
			] );

			/* ------------------------------------ Wide panel settings ------------------------------------ */

			$this->add_title( esc_html__('Wide Style options', 'rey-core'), [
				'description' => esc_html__('Options for Wide panel search style.', 'rey-core'),
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '==',
						'value'    => 'wide',
					],
				],
			]);

			$this->add_control( [
				'type'        => 'image',
				'settings'    => 'search_wide_logo',
				'label'       => esc_html__( 'Custom Logo Image', 'rey-core' ),
				'description' => esc_html__( 'This logo will be shown when the search panel is opened.', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'save_as' => 'id',
				],
				'active_callback' => [
					[
						'setting'  => 'header_search_style',
						'operator' => '==',
						'value'    => 'wide',
					],
				],
			] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'header_search__input_placeholder',
			'label'       => esc_html__( 'Text Placeholder', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: type to search..', 'rey-core'),
			],
		] );


		$this->add_title( esc_html__('Advanced', 'rey-core') );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_enable_ajax_search',
			'label'       => esc_html__( 'Enable Ajax Search?', 'rey-core' ),
			'help' => [
				__( 'Displayes the results live as the visitor types.', 'rey-core' )
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'header_search_style',
					'operator' => '!=',
					'value'    => 'button',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_enable_categories',
			'label'       => esc_html__( 'Enable Categories List?', 'rey-core' ),
			'help' => [
				__( 'This option will output a select list of categories inside the Search form.', 'rey-core' )
			],
			'default'     => false,
		] );

	}
}
