<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class ProductPageLayout extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-product-page-layout';
	}

	public function get_title(){
		return esc_html__('Layout', 'rey-core');
	}

	public function get_title_before(){
		return esc_html__('PRODUCT PAGE', 'rey-core');
	}

	public function get_priority(){
		return 60;
	}

	public function get_icon(){
		return 'woo-pdp-layout';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Product Page'];
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-woocommerce/#product-page-layout');
	}

	public function controls(){

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'single_skin',
			'label'       => esc_html__('Product Page Skin', 'rey-core'),
			'description' => __('Select the product page\'s skin (layout).', 'rey-core'),
			'default'     => 'default',
			'choices'     => [],
			'rey_preset' => 'page',
			'ajax_choices' => 'get_pdp_skins_list',
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Default Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'single_skin__default__sidebar',
			'label'       => esc_html__( 'Choose Sidebar', 'rey-core' ),
			'description' => __('If enabled, make sure to add widgets in the "Product Page" sidebar.', 'rey-core'),
			'default'     => '',
			'choices'     => [
				'' => esc_html__('Disabled', 'rey-core'),
				'left' => esc_html__('Left', 'rey-core'),
				'right' => esc_html__('Right', 'rey-core'),
			],
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'single_skin__default__sidebar_size',
			'label'       => esc_html__( 'Sidebar Size', 'rey-core' ),
			'default'     => 16,
			'choices'     => [
				'min'  => 10,
				'max'  => 60,
				'step' => 1,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-pp-sidebar-size',
					'units'    		=> '%',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'single_skin__default__sidebar',
					'operator' => '!=',
					'value'    => '',
				],
			]
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin__default__sidebar_mobile',
			'label'       => esc_html__( 'Hide sidebar on tablet/mobile', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'single_skin__default__sidebar',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$this->end_controls_group();



		/* ------------------------------------ Fullscreen Options ------------------------------------ */

		$this->start_controls_group( [
			'label'    => esc_html__( 'Fullscreen Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
			],
		]);

		// Stretch Gallery (for fullscreen & Cascade gallery)
		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin_fullscreen_stretch_gallery',
			'label'       => esc_html__( 'Stretch Gallery (Cascade)', 'rey-core' ),
			'description' => __('This option will stretch the gallery.', 'rey-core'),
			'default'     => false,
			'rey_preset' => 'page',
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => '==',
					'value'    => 'cascade',
				],
			],
		] );


		$this->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'single_skin_fullscreen_gallery_color',
			'label'           => __( 'Gallery Background Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'       => 'single_skin',
					'operator'      => '==',
					'value'         => 'fullscreen',
				],
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-single-fs-gallery-color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'single_skin_fullscreen_valign',
			'label'       => esc_html__( 'Summary vertical alignment', 'rey-core' ),
			'default'     => 'flex-start',
			'choices'     => [
				'flex-start' => esc_html__( 'Top', 'rey-core' ),
				'center' => esc_html__( 'Center', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
			],
			'output' => [
				[
					'element'  => ':root',
					'property' => '--woocommerce-fullscreen-summary-valign',
				],
			],
		] );


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin_fullscreen_custom_height',
			'label'       => esc_html__( 'Custom Summary Height', 'rey-core' ),
			'description' => __('This option will allow setting a custom summary height.', 'rey-core'),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'single_skin_fullscreen_summary_height',
			'label'       => esc_html__( 'Summary Min. Height (vh)', 'rey-core' ),
			'default'     => 100,
			'choices'     => [
				'min'  => 35,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
				[
					'setting'  => 'single_skin_fullscreen_custom_height',
					'operator' => '==',
					'value'    => true,
				],
			],
			'output'      		=> [
				[
					'media_query'	=> '@media (min-width: 1025px)',
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-fullscreen-gallery-height',
					'units'    		=> 'vh',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin_fullscreen__header_rel_abs',
			'label'       => esc_html__( 'Force "Absolute" header position', 'rey-core' ),
			'help' => [
				esc_html__( 'This option forces the header to overlap the content.', 'rey-core' )
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
				[
					'setting'  => 'header_position',
					'operator' => '==',
					'value'    => 'rel',
				],
			],
		] );


		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_skin_fullscreen__top_padding',
			'label'       => esc_html__( 'Top Spacing', 'rey-core' ) . ' (px)',
			'help' => [
				esc_html__( 'Customize the top padding.', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'max'  => 400,
				'step' => 1,
			],
			'transport' => 'auto',
			'output'    => [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-fullscreen-top-padding',
					'units'    		=> 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '==',
					'value'    => 'fullscreen',
				],
			],
			// 'responsive' => true,
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_skin__top_spacing',
			'label'       => esc_html__( 'Top Spacing', 'rey-core' ) . ' (px)',
			'help' => [
				esc_html__( 'Customize the top padding.', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'max'  => 400,
				'step' => 1,
			],
			'transport' => 'auto',
			'output'    => [
				[
					'element'  => ':root',
					'property' => '--woocommerce-pdp-top',
					'units'    => 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '!=',
					'value'    => 'fullscreen',
				],
			],
			'responsive' => true,
		] );


		/* ------------------------------------ Product summary ------------------------------------ */

		$this->add_title( esc_html__('Product Summary', 'rey-core'), [
			'description' => esc_html__('Customize the product summary block\'s layout.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '!=',
					'value'    => 'compact',
					],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin_default_flip',
			'label'       => esc_html__( 'Flip Gallery & Summary', 'rey-core' ),
			'description' => __('This option will flip the positions of product summary (title, add to cart button) with the images gallery.', 'rey-core'),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => 'in',
					'value'    => ['default', 'fullscreen'],
				],
			],
			'rey_preset' => 'page',
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'summary_size',
			'label'       => esc_html__( 'Summary Size', 'rey-core' ),
			'description' => __('Control the product summary content size.', 'rey-core'),
			'rey_preset' => 'page',
			'default'     => 36,
			'choices'     => [
				'min'  => 20,
				'max'  => 60,
				'step' => 1,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-summary-size',
					'units'    		=> '%',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '!=',
					'value'    => 'compact',
				],
			],
		] );


		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'summary_padding',
			'label'       => esc_html__( 'Summary Padding', 'rey-core' ),
			'description' => __('Control the product summary content padding.', 'rey-core'),
			'default'     => 0,
			'rey_preset' => 'page',
			'choices'     => [
				'min'  => 0,
				'max'  => 150,
				'step' => 1,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					// 'media_query'	=> '@media (min-width: 1025px)',
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-summary-padding',
					'units'    		=> 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => 'in',
					'value'    => ['default', 'fullscreen'],
				],
			],
			'responsive' => true,
		] );

		$this->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'summary_bg_color',
			'label'           => __( 'Background Color', 'rey-core' ),
			'rey_preset' => 'page',
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => 'in',
					'value'    => ['default', 'fullscreen'],
				],
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-summary-bgcolor',
				],
			],
		] );

		$this->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'summary_text_color',
			'label'           => __( 'Text Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => 'in',
					'value'    => ['default', 'fullscreen'],
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce div.product div.summary, .woocommerce div.product div.summary a, .woocommerce div.product .rey-postNav .nav-links a,  .woocommerce div.product .rey-productShare h5, .woocommerce div.product form.cart .variations label, .woocommerce div.product .rey-pdp-meta, .woocommerce div.product .rey-pdp-meta a',
					'property' 		   => 'color',
				],
			],
		] );



		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_summary_fixed',
			'label'       => esc_html__( 'Fixed Summary', 'rey-core' ),
			'help' => [
				esc_html__( 'This option will make the product summary fixed upon page scrolling, until the product gallery images are outside viewport.', 'rey-core' )
			],
			'default'     => false,
			'rey_preset' => 'page',
			'active_callback' => [
				[
					'setting'  => 'single_skin',
					'operator' => '!=',
					'value'    => 'compact',
				],
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Fixed summary options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'product_page_summary_fixed',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'product_page_summary_fixed__offset',
			'label'       => esc_html__( 'Summary top distance', 'rey-core' ) . ' (px)',
			'help' => [
				esc_html__( 'Customize the summary top margin.', 'rey-core' )
			],
			'default'     => '',
			'rey_preset' => 'page',
			'choices'     => [
				'max'  => 400,
				'step' => 1,
			],
			'output'          => [
				[
					'element'  => '.--fixed-summary',
					'property' => '--woocommerce-fixedsummary-offset',
					'units'    => 'px'
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'product_page_summary_fixed__offset_active',
			'label'       => esc_html__( 'Offset', 'rey-core' ) . ' (px)',
			'help' => [
				esc_html__( 'Customize the top sticky offset when page has scrolled and sticky is active.', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				'max'  => 400,
				'step' => 1,
			],
			'output'          => [
				[
					'element'  => ':root',
					'property' => '--woocommerce-fullscreen-top-padding-anim',
					'units'    => 'px'
				],
			],
			'active_callback' => [
				[
					'setting'  => 'product_page_summary_fixed',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_summary_fixed__gallery',
			'default'     => false,
			'label'       => esc_html__( 'Sticky Gallery', 'rey-core' ),
			'help' => [
				esc_html__( 'If enabled, the gallery will stick to top while summary is scrolling. Useful for large summaries. Enabled for vertical or horizontal galleries.', 'rey-core' )
			],
			'active_callback' => [
				[
					'setting'  => 'product_page_summary_fixed',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->end_controls_group();

		$this->add_title( esc_html__('Typography', 'rey-core') );

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_pdp__product_title',
			'label'       => esc_attr__('Product Title', 'rey-core'),
			'default'     => [
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'font-weight' => '',
				'variant' => '',
				'color' => '',
			],
			'output' => [
				[
					'element' => '.woocommerce div.product .product_title',
				]
			],
			'load_choices' => true,
			'transport' => 'auto',
			'responsive' => true,
		));

		/* ------------------------------------ MISC ------------------------------------ */

		$this->add_title( esc_html__('MISC.', 'rey-core') );


		$demos = \ReyCore\Customizer\Helper::demo_presets();
		$choices = [ '' => esc_html__( 'Default', 'rey-core' ) ];
		$presets = [];

		foreach ($demos as $key => $demo) {
			$choices[$key] = $demo['title'];
			$presets[$key]['settings'] = $demo['settings']['page'];
		}

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wc_product_layout_presets',
			'label'       => esc_html__( 'Layout Presets', 'rey-core' ),
			'description' => esc_html__( 'These are product page layout presets from each demo.', 'rey-core' ),
			'default'     => '',
			'choices'     => $choices,
			'preset' => $presets,
		] );

	}
}
