<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class ProductPageTabs extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-product-page-tabs';
	}

	public function get_title(){
		return esc_html__('Tabs / Blocks', 'rey-core');
	}

	public function get_priority(){
		return 90;
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Product Page'];
	}

	public function get_icon(){
		return 'woo-pdp-tabs-blocks';
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'product_content_layout',
			'label'       => esc_html__( 'Layout', 'rey-core' ),
			'default'     => 'blocks',
			'rey_preset' => 'page',
			'choices'     => [
				'blocks' => esc_html__( 'As Blocks', 'rey-core' ),
				'tabs' => esc_html__( 'Tabs', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_content_tabs_disable_titles',
			'label'       => esc_html__( 'Disable titles inside Tabs', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'tabs',
				],
			],
		] );

		// $this->add_control( [
		// 	'type'        => 'select',
		// 	'settings'    => 'product_content_tabs_mobile_layout',
		// 	'label'       => esc_html__( 'Mobile layout', 'rey-core' ),
		// 	'default'     => get_theme_mod('product_content_layout', 'blocks'),
		// 	'choices'     => [
		// 		'blocks' => esc_html__( 'As Blocks', 'rey-core' ),
		// 		'tabs' => esc_html__( 'As Accordion', 'rey-core' ),
		// 	],
		// ] );


		/* ------------------------------------ DESCRPTION ------------------------------------ */


		$this->add_title( esc_html__('Description', 'rey-core') );

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'product_tab_description',
			'label'       => esc_html__('Enable Description', 'rey-core'),
			'default'     => true,
		));

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_content_blocks_desc_toggle',
			'label'       => esc_html__( 'Toggle long description', 'rey-core' ),
			'default'     => false,
			'help' => [
				__('Makes the text shorter and adds a "view more" button.', 'rey-core')
			],
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'product_content_blocks_title',
			'label'       => esc_html__('Description Title', 'rey-core'),
			'help' => [
				__('If you want to completely hide the title, please add "0".', 'rey-core')
			],
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Description', 'rey-core'),
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_description_priority',
			'label'       => esc_html__('Priority', 'rey-core'),
			'help' => [
				__('Choose the order of the blocks/tabs.', 'rey-core')
			],
			'default'     => 10,
			'choices'     => [
				'min'  => 10,
				'max'  => 200,
				'step' => 5,
			],
		]);


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_content_blocks_desc_stretch',
			'label'       => esc_html__( 'Stretch Description Block', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'blocks',
				],
			],
		] );

		/* ------------------------------------ INFORMATION ------------------------------------ */

		$this->add_title( esc_html__('Information', 'rey-core'), [
			// 'description' => esc_html__('You can add a block of custom content right after the product summary.', 'rey-core'),
		]);

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'product_info',
			'label'       => esc_html__('Custom Information', 'rey-core'),
			'description' => __('Select if you want to add a tab with text content. You can override or disable per product.', 'rey-core'),
			'default'     => '',
		));

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'single__product_info_title',
			'label'       => esc_html__( 'Title', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Information', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'product_info',
					'operator' => '!=',
					'value'    => '',
				],
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'tabs',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_custom_info_priority',
			'label'       => esc_html__('Priority', 'rey-core'),
			'help' => [
				__('Choose the order of the blocks/tabs.', 'rey-core')
			],
			'default'     => 15,
			'choices'     => [
				'min'  => 10,
				'max'  => 200,
				'step' => 5,
			],
			'active_callback' => [
				[
					'setting'  => 'product_info',
					'operator' => '!=',
					'value'    => '',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'editor',
			'settings'    => 'product_info_content',
			'label'       => esc_html__( 'Add Content', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'product_info',
					'operator' => '!=',
					'value'    => '',
				],
			],
			'partial_refresh'    => [
				'product_info_content' => [
					'selector'        => '.rey-wcPanel--information',
					'render_callback' => function() {
						return get_theme_mod('product_info_content', '');
					},
				],
			],
		] );


		/* ------------------------------------ Specs ------------------------------------ */

		$this->add_title( esc_html__('Additional Info. / SPECIFICATIONS', 'rey-core'), [
			'description' => __('Content inside Additional Information / Specifications block/tab.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_specifications_block',
			'label'       => esc_html__('Enable tab/block', 'rey-core'),
			'help' => [
				__('Select the visibility of Specifications (Additional Information) block/tab', 'rey-core')
			],
			'default'     => true
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'single_specifications_title',
			'label'       => esc_html__('Title', 'rey-core'),
			'help' => [
				__('If you want to completely hide the title, please add "0".', 'rey-core')
			],
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Specifications', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_specifications_block_dimensions',
			'label'       => esc_html__('Enable Dimensions Info', 'rey-core'),
			'help' => [
				__('Select the visibility of Weight/Dimensions rows.', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'single_specifications_position',
			'label'       => esc_html__('Spec. Position', 'rey-core'),
			'help' => [
				__('Select if you want to move the Specifications block in product summary.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'Default (as block/tab)', 'rey-core' ),
				'29' => esc_html__( 'After short description', 'rey-core' ),
				'39' => esc_html__( 'After Add to cart button', 'rey-core' ),
				'49' => esc_html__( 'After Meta', 'rey-core' ),
				'499' => esc_html__( 'Last one', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_specs_priority',
			'label'       => esc_html__('Priority', 'rey-core'),
			'help' => [
				__('Choose the order of the blocks/tabs.', 'rey-core')
			],
			'default'     => 20,
			'choices'     => [
				'min'  => 10,
				'max'  => 200,
				'step' => 5,
			],
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'woocommerce_product_page_attr_desc',
			'label'       => esc_html__( 'Show attributes descriptions', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_content_blocks_specs_toggle',
			'label'       => esc_html__( 'Toggle long list', 'rey-core' ),
			'default'     => false,
			'help' => [
				__('Makes the table shorter and adds a "view more" button.', 'rey-core')
			],
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'custom',
			'settings'    => 'single_specs_customize_desc',
			'default'     => sprintf(__('Here\'s a <a href="%s" target="_blank">quick article</a> on how to add or remove rows inside.', 'rey-core'), reycore__support_url('kb/customize-the-attributes-inside-specifications-additional-information/')),
			'active_callback' => [
				[
					'setting'  => 'single_specifications_block',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		/* ------------------------------------ Review ------------------------------------ */

		$this->add_title( esc_html__('REVIEWS', 'rey-core'), [
			'description' => esc_html__('Content inside reviews block/tab.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'star_rating_color',
			'label'       => esc_html__( 'Stars Color', 'rey-core' ),
			'default'     => '#ff4545',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--star-rating-color',
				],
			],
			'transport' => 'auto'
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_reviews_start_opened',
			'label'       => esc_html_x('Start Opened', 'Customizer control label', 'rey-core'),
			'help' => [
				_x('By default the review block is hidden and can be opened clicking the large Reviews button. If this option is enabled though, the reviews block will always load opened first.', 'Customizer control description', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'blocks',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_reviews_ajax',
			'label'       => esc_html_x('Ajax load reviews', 'Customizer control label', 'rey-core'),
			'help' => [
				_x('This option will make the reviews to load dynamically on demand.', 'Customizer control description', 'rey-core')
			],
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_reviews_info',
			'label'       => esc_html_x('Rating Infographic', 'Customizer control label', 'rey-core'),
			'help' => [
				_x('Will show an infographic summary of the reviews and ratings.', 'Customizer control description', 'rey-core')
			],
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_reviews_avatar',
			'label'       => esc_html_x('Show avatars?', 'Customizer control label', 'rey-core'),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_reviews_layout',
					'operator' => '!=',
					'value'    => 'minimal',
					],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'single_reviews_layout',
			'label'       => esc_html__( 'Reviews layout', 'rey-core' ),
			'default'     => 'default',
			'choices'     => [
				'default' => esc_html__( 'Default', 'rey-core' ),
				'minimal' => esc_html__( 'Minimal', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_tabs__reviews_outside',
			'label'       => esc_html__( 'Make Reviews tab as block', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'tabs',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_reviews_priority',
			'label'       => esc_html__('Priority', 'rey-core'),
			'help' => [
				__('Choose the order of the blocks/tabs.', 'rey-core')
			],
			'default'     => 30,
			'choices'     => [
				'min'  => 5,
				'max'  => 200,
				'step' => 5,
			],
		]);


	}
}
