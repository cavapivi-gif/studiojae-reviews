<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Helper {

	/**
	 * Get WooCommerce taxonomies
	 *
	 * @since 1.6.x
	 **/
	public static function wc_taxonomies( $args = [] )
	{
		if( ! function_exists('wc_get_attribute_taxonomies') ){
			return [];
		}

		$args = wp_parse_args($args, [
			'exclude' => [],
			'label_formatting' => '%s'
		]);

		$wc_taxonomy_attributes = [
			'product_cat' => esc_html__( 'Product Catagories', 'rey-core' ),
			'product_tag' => esc_html__( 'Product Tags', 'rey-core' ),
		];

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = wc_attribute_taxonomy_name( $attribute->attribute_name );

			$wc_taxonomy_attributes[$attribute_name] = sprintf( $args['label_formatting'], $attribute->attribute_label, $attribute_name );

		}

		if( !empty($args['exclude']) ){
			foreach ($args['exclude'] as $to_exclude) {
				unset($wc_taxonomy_attributes[$to_exclude]);
			}
		}

		return $wc_taxonomy_attributes;
	}

	/**
	 * Get Global sections
	 *
	 * @since 2.2.0
	 **/
	public static function global_sections( $type, $default = [] )
	{

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return [];
		}

		return \ReyCore\Elementor\GlobalSections::get_global_sections($type, $default);
	}


	/**
	 * Retrieves a theme's mods
	 *
	 * @param string $theme_name
	 * @return array
	 */
	public static function get_theme_settings( $theme_name ){

		if ( false === ($mods = get_option( "theme_mods_$theme_name" )) ) {
			$mods = get_option( "mods_$theme_name" ); // Deprecated location.
			if ( is_admin() && false !== $mods ) {
				update_option( "theme_mods_$theme_slug", $mods );
				delete_option( "mods_$theme_name" );
			}
		}

		$data = [
			'template' => $theme_name,
			'mods'     => $mods,
			'options'  => []
		];

		global $wp_customize;

		if( $wp_customize ):

			// Get options from the Customizer API.
			$settings = $wp_customize->settings();

			foreach ( $settings as $key => $setting ) {

				if ( 'option' == $setting->type ) {

					// Don't save widget data.
					if ( 'widget_' === substr( strtolower( $key ), 0, 7 ) ) {
						continue;
					}

					// Don't save sidebar data.
					if ( 'sidebars_' === substr( strtolower( $key ), 0, 9 ) ) {
						continue;
					}

					// Don't save core options.
					if ( in_array( $key, [
						'blogname',
						'blogdescription',
						'show_on_front',
						'page_on_front',
						'page_for_posts',
					] ) ) {
						continue;
					}

					$data['options'][ $key ] = $setting->value();
				}
			}

		endif;

		// Plugin developers can specify additional option keys to export.
		$option_keys = apply_filters( 'reycore/transfer_mods_option_keys', [] );

		foreach ( $option_keys as $option_key ) {
			$data['options'][ $option_key ] = get_option( $option_key );
		}

		if( function_exists( 'wp_get_custom_css_post' ) ) {
			$data['wp_css'] = wp_get_custom_css( $theme_name );
		}

		return $data;
	}

	/**
	 * Presets
	 *
	 * @since 1.9.4
	 **/
	public static function demo_presets()
	{
		return [
			'london' => [
				'title' => esc_html__( 'London Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'fullscreen',
						'single_skin_default_flip' => false,
						'summary_size' => 45,
						'product_page_summary_fixed' => true,
						'product_gallery_layout' => 'cascade',
						'product_page_gallery_zoom' => true,
						'single_skin_fullscreen_stretch_gallery' => true,
						'single_skin_cascade_bullets' => true,
					],
					'catalog' => [],
				],
			],
			'valencia' => [
				'title' => esc_html__( 'Valencia Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'compact',
						'single_skin_default_flip' => false,
						'summary_size' => 36,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'grid',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'amsterdam' => [
				'title' => esc_html__( 'Amsterdam Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'fullscreen',
						'single_skin_default_flip' => false,
						'summary_size' => 40,
						'product_page_summary_fixed' => true,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'newyork' => [
				'title' => esc_html__( 'New York Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 40,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'grid',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'tokyo' => [
				'title' => esc_html__( 'Tokyo Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 44,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'beijing' => [
				'title' => esc_html__( 'Beijing Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 44,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'horizontal',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'milano' => [
				'title' => esc_html__( 'Milano Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 38,
						'product_page_summary_fixed' => true,
						'product_gallery_layout' => 'cascade-scattered',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'melbourne' => [
				'title' => esc_html__( 'Melbourne Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 45,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'paris' => [
				'title' => esc_html__( 'Paris Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'fullscreen',
						'single_skin_default_flip' => false,
						'summary_size' => 40,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
					],
					'catalog' => [],
				],
			],
			'stockholm' => [
				'title' => esc_html__( 'Stockholm Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'compact',
						'single_skin_default_flip' => false,
						'summary_size' => 36,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'cascade',
						'product_page_gallery_zoom' => true,
						'product_content_layout' => 'blocks',
					],
					'catalog' => [],
				],
			],
			'frankfurt' => [
				'title' => esc_html__( 'Frankfurt Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 45,
						'summary_padding' => 50,
						'summary_bg_color' => '#ebf5fb',
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
						'product_content_layout' => 'blocks',
					],
					'catalog' => [],
				],
			],
			'athens' => [
				'title' => esc_html__( 'Athens Demo', 'rey-core' ),
				'settings' => [
					'page' => [
						'single_skin' => 'default',
						'single_skin_default_flip' => false,
						'summary_size' => 36,
						'summary_padding' => 0,
						'product_page_summary_fixed' => false,
						'product_gallery_layout' => 'vertical',
						'product_page_gallery_zoom' => true,
						'product_content_layout' => 'blocks',
					],

				],
			],
		];
	}

}
