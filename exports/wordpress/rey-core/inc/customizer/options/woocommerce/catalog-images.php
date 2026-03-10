<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

class CatalogImages extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-catalog-product-images';
	}

	public function get_title(){
		return esc_html__('Product Images', 'rey-core');
	}

	public function get_priority(){
		return 30;
	}

	public function get_icon(){
		return 'woo-catalog-product-images';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Catalog Settings'];
	}

	// public function help_link(){
	// 	return reycore__support_url('kb/customizer-woocommerce/#product-images');
	// }

	public function customize_register(){

		global $wp_customize;

		$control__img_width = $wp_customize->get_control( 'woocommerce_thumbnail_image_width' );

		if( isset($control__img_width->priority) ){
			$control__img_width->priority = 5;
			$control__img_width->section = self::get_id();
			$control__img_width->description = esc_html__('Image size used for products in the catalog.', 'woocommerce');
		}

		$control__thumb_cropping = $wp_customize->get_control( 'woocommerce_thumbnail_cropping' );

		if( isset($control__thumb_cropping) ){
			$control__thumb_cropping->priority = 15;
			$control__thumb_cropping->section = self::get_id();
			$control__thumb_cropping->label = '';
		}

	}

	public function controls(){

		$this->add_title( esc_html__('THUMBNAIL CROPPING', 'rey-core'), [
			'description' => __( 'Choose how the images are <em>physically</em> sized.', 'rey-core' ),
			'priority' => 10,
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'custom_image_height',
			'label'       => esc_html__( 'Thumbnail Container Height', 'rey-core' ),
			'description' => __( 'Adding a custom image container height forces the image to fit in its parent container.<br><strong>Only works for Uncropped images!</strong>', 'rey-core' ),
			'default'     => false,
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Image-Container Height', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'custom_image_height',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'custom_image_height_size',
			'label'       => esc_html__( 'Height (px)', 'rey-core' ),
			'default'     => 350,
			'choices'     => [
				'min'  => 0,
				'max'  => 1000,
				'step' => 1,
			],
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-custom-image-height',
					'units'    		=> 'px',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'custom_image_height_size_mobile',
			'label'       => esc_html__( 'Height (Mobile)', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'max'  => 1000,
				'step' => 1,
			],
			'output'      		=> [
				[
					'media_query'	=> '@media (max-width: 767px)',
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-custom-image-height',
					'units'    		=> 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'custom_image_height',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->end_controls_group();


	/**
	 * EXTRA IMAGES
	 */

		$this->add_title( esc_html__('EXTRA IMAGES', 'rey-core'), [
			'description' => esc_html__('Choose to display assisting product images, either on hover or as slideshow.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_extra_media',
			'label'       => esc_html__( 'Extra Images Display', 'rey-core' ),
			'default'     => 'second',
			'choices'     => [
				'no' => esc_html__( 'Disabled', 'rey-core' ),
				'second' => esc_html__( '2nd image on hover', 'rey-core' ),
				'slideshow' => esc_html__( 'Slideshow', 'rey-core' ),
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra media options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_extra_media',
					'operator' => '==',
					'value'    => 'slideshow',
				],
			],
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'loop_slideshow_nav',
				'label'       => esc_html__( 'Slideshow navigation', 'rey-core' ),
				'default'     => 'dots',
				'choices'     => [
					'dots' => esc_html__( 'Show bullets only', 'rey-core' ),
					'arrows' => esc_html__( 'Show Arrows only', 'rey-core' ),
					'both' => esc_html__( 'Show Both', 'rey-core' ),
				],
			] );

			$this->add_control( [
				'type'        => 'rey-color',
				'settings'    => 'loop_slideshow_nav_color',
				'label'       => esc_html__( 'Color', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'alpha' => true,
				],
				'output'          => [
					[
						'element'  		   => ':root',
						'property' 		   => '--woocommerce-loop-nav-color',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_slideshow_nav_color_invert',
				'label'       => esc_html__( 'Adapt (invert) colors on slide', 'rey-core' ),
				'default'     => false,
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'loop_slideshow_nav_dots_style',
				'label'       => esc_html__( 'Bullets style', 'rey-core' ),
				'default'     => 'bars',
				'choices'     => [
					'bars' => esc_html__( 'Bars', 'rey-core' ),
					'dots' => esc_html__( 'Dots', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'loop_slideshow_nav',
						'operator' => 'in',
						'value'    => ['dots', 'both'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_slideshow_nav_hover_dots',
				'label'       => __('Change slide on dot hover', 'rey-core'),
				'help' => [
					__('While hovering the slideshow navigation, the slider will proceed to the next item. This option is incompatibile with Masonry grid.', 'rey-core')
				],
				'default'     => false,
				'active_callback' => [
					[
						'setting'  => 'loop_slideshow_nav',
						'operator' => 'in',
						'value'    => ['dots', 'both'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_slideshow_hover_slide',
				'label'       => __('Change image on item hover', 'rey-core'),
				'help' => [
					__('While hovering the product item, the slider will proceed to the next item. This option is incompatibile with Masonry grid.', 'rey-core')
				],
				'default'     => true,
			] );


			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_extra_media_disable_mobile',
				'label'       => esc_html__( 'Disable on mobiles?', 'rey-core' ),
				'default'     => get_theme_mod('loop_slideshow_disable_mobile', false), // legacy
				'active_callback' => [
					[
						'setting'  => 'loop_extra_media',
						'operator' => '!=',
						'value'    => 'no',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'loop_slideshow_nav_max',
				'label'       => esc_html__( 'Maximum images', 'rey-core' ),
				'default'     => 4,
				'choices'     => [
					'min'  => 1,
					'max'  => 20,
					'step' => 1,
				],
			] );

		$this->end_controls_group();

	/**
	 * OTHER SETTINGS
	 */

		$this->add_title( esc_html__('OTHER SETTINGS', 'rey-core') );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'catalog_thumbs_radius',
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
					'property' 		=> '--woocommerce-product-thumbs-radius',
					'units'    		=> 'px',
				],
			],
			'responsive' => true,
		] );

		$this->add_control( [
			'type'        => 'dimensions',
			'settings'    => 'shop_thumbnails_padding',
			'label'       => esc_html__( 'Thumbnails Inner Padding', 'rey-core' ),
			'description' => __( 'Will add padding around the <strong>thumbnails</strong>. Dont forget to include unit (eg: px, em, rem).', 'rey-core' ),
			'default'     => [
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
			'choices'     => [
				'labels' => [
					'top'  => esc_html__( 'Top', 'rey-core' ),
					'right' => esc_html__( 'Right', 'rey-core' ),
					'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
					'left' => esc_html__( 'Left', 'rey-core' ),
				],
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--woocommerce-thumbnails-padding',
					'units' => 'px'
				],
			],
			'css_class' => 'dimensions-4-cols',
			'responsive' => true
		] );


	}
}
