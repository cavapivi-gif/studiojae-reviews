<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class ProductPageGallery extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-product-page-gallery';
	}

	public function get_title(){
		return esc_html__('Image Gallery', 'rey-core');
	}

	public function get_priority(){
		return 70;
	}

	public function get_icon(){
		return 'woo-pdp-image-gallery';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Product Page'];
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-woocommerce/#product-images');
	}

	public function customize_register(){

		global $wp_customize;

		$wp_customize->get_control( 'woocommerce_single_image_width' )->priority = 10;
		$wp_customize->get_control( 'woocommerce_single_image_width' )->section = self::get_id();

	}

	public function controls(){

		/* ------------------------------------ CUSTOM MAIN IMAGE HEIGHT ------------------------------------ */

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'custom_main_image_height',
			'label'       => __( 'Main Image <u>Container</u> height', 'rey-core' ),
			'description' => __( 'This will force the main image\'s container height, therefore make the image constrain inside.', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Main Image-Container Height', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'custom_main_image_height',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'custom_main_image_height_size',
			'label'       => esc_html__( 'Height (px)', 'rey-core' ),
			'default'     => 540,
			// 'priority'    => 10,
			'choices'     => [
				'min'  => 100,
				'max'  => 1000,
				'step' => 1,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-custom-main-image-height',
					'units'    		=> 'px',
				],
			],
		] );

		$this->end_controls_group();


		/**
		 * GALLERY SETTINGS
		 */

		$this->add_title( esc_html__('Product Gallery', 'rey-core'), [
			'description' => esc_html__('Customize the product page gallery style.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'product_gallery_preview_ratio',
			'label'       => esc_html__( 'Images Ratio (Optional)', 'rey-core' ),
			'help' => [
				__('Force images to have an aspect ratio. "1" means a square, while increasing the value will make the image taller.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				'min'  => 0.3,
				'max'  => 2,
				'step' => 0.1,
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-gallery-aspect-ratio',
				],
			],
			'responsive' => true,
		] );


		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'product_gallery_layout',
			'label'       => esc_html__( 'Gallery layout', 'rey-core' ),
			'help' => [
				__('Select the gallery layout.', 'rey-core')
			],
			'default'     => 'vertical',
			'rey_preset' => 'page',
			'choices'     => [],
			'ajax_choices' => 'get_gallery_types_list',
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_skin_cascade_bullets',
			'label'       => esc_html__( 'Bullets Navigation (Cascade gallery)', 'rey-core' ),
			'description' => __('This option will add bullets (dots) navigation for the Cascade gallery.', 'rey-core'),
			'default'     => true,
			'rey_preset' => 'page',
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => '==',
					'value'    => 'cascade',
				],
				[
					'setting'  => 'single_skin',
					'operator' => '!=',
					'value'    => 'compact',
				],
			],
		] );

		$this->add_control( [
			'type'            => 'toggle',
			'settings'        => 'grid_stretch_odd',
			'label'           => esc_html__( 'Stretch uneven items in Grid layouts', 'rey-core' ),
			// 'help'            => [
			// 	__('Stretch the items which are uneven?', 'rey-core')
			// ],
			'default'         => false,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['cascade-grid', 'grid'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'product_gallery_grid_pattern',
			'label'       => esc_html__( 'Grid Pattern', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('ex: 2 3', 'rey-core'),
			],
			'help'            => [
				__('Add a pattern how to list the images. For example "2 3 4" will list 2 on the first row, 3 on the 2nd row and 4 on the 3rd row, and so on.', 'rey-core')
			],
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => '==',
					'value'    => 'grid-pattern',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_zoom',
			'label'       => esc_html__( 'Enable Hover Zoom', 'rey-core' ),
			'help' => [
				__('This option will enable zooming the main image by hovering it.', 'rey-core')
			],
			'default'     => true,
			'rey_preset' => 'page',
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_lightbox',
			'label'       => esc_html__( 'Enable Lightbox', 'rey-core' ),
			'help' => [
				__('This option enables the lightbox for images when clicking on the icon or images.', 'rey-core')
			],
			'default'     => true,
		] );

			$this->start_controls_group( [
				'label'    => esc_html__( 'Lightbox options', 'rey-core' ),
				'active_callback' => [
					[
						'setting'  => 'product_page_gallery_lightbox',
						'operator' => '==',
						'value'    => true,
					],
				],
			]);

				$this->add_control( [
					'type'        => 'toggle',
					'settings'    => 'product_page_gallery_lightbox_theme',
					'label'       => esc_html__( 'Enable "White" Skin for Lightbox?', 'rey-core' ),
					'help' => [
						__('Either use white or black lightbox.', 'rey-core')
					],
					'default'     => true,
					'active_callback' => [
						[
							'setting'  => 'product_page_gallery_lightbox',
							'operator' => '==',
							'value'    => true,
						],
					],
				] );

				$this->add_control( [
					'type'        => 'toggle',
					'settings'    => 'product_page_gallery__btn__enable',
					'label'       => esc_html__( 'Open Button', 'rey-core' ),
					'default'     => true,
				] );

				$this->add_control( [
					'type'        => 'select',
					'settings'    => 'product_page_gallery__btn__icon',
					'label'       => esc_html__( 'Button Icon', 'rey-core' ),
					'default'     => 'reycore-icon-plus-stroke',
					'choices'     => [
						'reycore-icon-plus-stroke' => esc_html__( 'Plus icon', 'rey-core' ),
						'reycore-icon-zoom' => esc_html__( 'Zoom Icon', 'rey-core' ),
					],
					'active_callback' => [
						[
							'setting'  => 'product_page_gallery__btn__enable',
							'operator' => '==',
							'value'    => true,
						],
						[
							'setting'  => 'product_page_gallery_lightbox',
							'operator' => '==',
							'value'    => true,
						],
					],
				] );

				$this->add_control( [
					'type'        => 'toggle',
					'settings'    => 'product_page_gallery__btn__text_enable',
					'label'       => esc_html__( 'Enable Button Text', 'rey-core' ),
					'default'     => false,
					'active_callback' => [
						[
							'setting'  => 'product_page_gallery__btn__enable',
							'operator' => '==',
							'value'    => true,
						],
						[
							'setting'  => 'product_page_gallery_lightbox',
							'operator' => '==',
							'value'    => true,
						],
					],
				] );

				$this->add_control( [
					'type'        => 'text',
					'settings'    => 'product_page_gallery__btn__text',
					'label'       => esc_html__( 'Button Text', 'rey-core' ),
					'default'     => '',
					'input_attrs'     => [
						'placeholder' => esc_html__('eg: OPEN GALLERY', 'rey-core'),
					],
					'active_callback' => [
						[
							'setting'  => 'product_page_gallery__btn__enable',
							'operator' => '==',
							'value'    => true,
						],
						[
							'setting'  => 'product_page_gallery__btn__text_enable',
							'operator' => '==',
							'value'    => true,
						],
						[
							'setting'  => 'product_page_gallery_lightbox',
							'operator' => '==',
							'value'    => true,
						],
					],
				] );

			$this->end_controls_group();

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery__lazy_load',
			'label'       => esc_html__( 'Lazy-load Images', 'rey-core' ),
			'help' => [
				__('By default, images are loaded based on browser lazy behavior. Enabling this option will agressively lazy load them during user actions. Recommended to be disabled.', 'rey-core')
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_gallery_counter_data',
			'label'       => esc_html__( 'Counter', 'rey-core' ),
			'help' => [
				__('Will add a counter for "current / total" image, in the top left corner.', 'rey-core')
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_autoheight_desktop',
			'label'       => esc_html__( 'Auto-height transition', 'rey-core' ),
			'help' => [
				__('This option will enable an a automatic height adjustment between unequally images.', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_arrow_nav',
			'label'       => esc_html__( 'Arrows Navigation', 'rey-core' ),
			'help' => [
				__('This option will enable arrows navigation.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->add_title( esc_html__('THUMBNAILS', 'rey-core'), [
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		]);

		$this->start_controls_group( [
			'label'    => esc_html__( 'Thumbs options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'product_gallery_thumbs_max',
			'label'       => esc_html__( 'Max. visible thumbs', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'max'  => 10,
				'step' => 1,
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-gallery-max-thumbs',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'product_gallery_thumbs_nav_style',
			'label'       => esc_html__( 'Thumbs nav. style', 'rey-core' ),
			'default'     => 'boxed',
			'choices'     => [
				'boxed' => esc_html__( 'Boxed', 'rey-core' ),
				'minimal' => esc_html__( 'Minimal', 'rey-core' ),
				'edges' => esc_html__( 'Edges', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );


		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'product_gallery_thumbs_event',
			'label'       => esc_html__( 'Thumbs trigger', 'rey-core' ),
			'default'     => 'click',
			'choices'     => [
				'click' => esc_html__( 'Click', 'rey-core' ),
				'mouseenter' => esc_html__( 'Hover', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_gallery_thumbs_flip',
			'label'       => esc_html__( 'Flip thumbs position', 'rey-core' ),
			'help' => [
				__('This option will flip the thumbnail list on the other side of the main image.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => '==',
					'value'    => 'vertical',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_gallery_thumbs_disable_cropping',
			'label'       => esc_html__( 'Disable thumbs cropping', 'rey-core' ),
			'help' => [
				__('By default WooCommerce is cropping the gallery thumbnails. You can disable this with this option and contain the image in its natural sizes.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_gallery_layout',
					'operator' => 'in',
					'value'    => ['vertical', 'horizontal'],
				],
			],
		] );

		$this->end_controls_group();

		$this->add_title( esc_html__('MOBILE GALLERY', 'rey-core') );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Mobile gallery options', 'rey-core' ),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'product_gallery_mobile_nav_style',
			'label'       => esc_html__( 'Navigation Style', 'rey-core' ),
			'default'     => 'bars',
			'choices'     => [
				'bars' => esc_html__( 'Horizontal Bars', 'rey-core' ),
				'circle' => esc_html__( 'Circle Bullets', 'rey-core' ),
				'thumbs' => esc_html__( 'Thumbnails', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_gallery_mobile_arrows',
			'label'       => esc_html__( 'Show Arrows', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_autoheight_mobile',
			'label'       => esc_html__( 'Auto-height transition', 'rey-core' ),
			'help' => [
				__('This option will enable an a automatic height adjustment between unequally images.', 'rey-core')
			],
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_scroll_top_after_variation_change',
			'label'       => esc_html__('Scroll top on variation change', 'rey-core'),
			'help' => [
				esc_html__( 'On mobiles, after a variation is changed, the page will animate and scroll back to the gallery, so that any image swap is visible.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_gallery_mobile_peek',
			'label'       => esc_html__('Show side edge of the next image', 'rey-core'),
			'help' => [
				esc_html__( 'This will make the next gallery image "peek" to make aware there are more images.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'pdp_img_radius',
			'label'       => esc_html__( 'Corner Radius', 'rey-core' ) . ' (px)',
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'max'  => 500,
				'step' => 1,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-product-img-radius',
					'units'    		=> 'px',
				],
			],
			'responsive' => true,
		] );
	}
}
