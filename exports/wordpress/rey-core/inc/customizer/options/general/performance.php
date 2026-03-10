<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Performance extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-performance';
	}

	public function get_title(){
		return esc_html__('Site Performance', 'rey-core');
	}

	public function get_priority(){
		return 25;
	}

	public function get_icon(){
		return 'site-performance';
	}

	public function controls(){

		$this->add_title( esc_html__('WordPress Editor Optimisations', 'rey-core'), [
			'separator' => 'none'
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'perf__disable_wpblock',
			'label'       => esc_html_x( 'Disable WordPress Block Styles', 'Customizer control title', 'rey-core' ),
			'help' => [
				esc_html_x( 'This will disable WordPress\'s built-in Gutenberg editor styles. If you don\'t use WordPress blocks throughout the site (and you are likely not using), than please enable this option. You can also override this option in any page\'s Advanced settings.', 'Customizer control description', 'rey-core')
			],
			'default'     => false,
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'perf__disable_wpblock',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'perf__disable_wpblock__posts',
				'label'       => esc_html_x( 'Keep in blog posts', 'Customizer control title', 'rey-core' ),
				'default'     => true,
			] );

		$this->end_controls_group();

		if( class_exists('\WooCommerce') ):
			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'perf__disable_wcblock',
				'label'       => esc_html_x( 'Disable WooCommerce Block Styles', 'Customizer control title', 'rey-core' ),
				'help' => [
					esc_html_x( 'This will disable WooCommerce\'s built-in Gutenberg editor styles. If you don\'t use WooCommerce blocks throughout the site (and you are likely not using), than please enable this option. You can also override this option in any page\'s Advanced settings.', 'Customizer control description', 'rey-core')
				],
				'default'     => false,
			] );
		endif;

		$this->add_title( esc_html__('Site Assets', 'rey-core'), [ ]);

		// $this->add_control( [
		// 	'type'        => 'select',
		// 	'settings'    => 'perf__css',
		// 	'label'       => esc_html_x( 'CSS Delivery', 'Customizer control title', 'rey-core' ),
		// 	'help' => [
		// 		esc_html_x( 'This option specifies how the CSS styles are being loaded. If selecting "Inline", please make sure to run tests.', 'Customizer control description', 'rey-core')
		// 	],
		// 	'default'     => 'defer',
		// 	'choices' => [
		// 		'block' => esc_html_x( 'Render blocking', 'Customizer control choice', 'rey-core' ),
		// 		'defer' => esc_html_x( 'Optimal (Async)', 'Customizer control choice', 'rey-core' ),
		// 		'inline' => esc_html_x( 'Inline', 'Customizer control choice', 'rey-core' ),
		// 	],
		// ] );

		$this->add_section_marker('performance_toggles');

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'perf__disable_emoji',
			'label'       => esc_html_x( 'Disable Emoji Scripts', 'Customizer control title', 'rey-core' ),
			'help' => [
				esc_html_x( 'Will disable WordPress\'s built-in emoji scripts. This option is recommended to be enabled.', 'Customizer control description', 'rey-core')
			],
			'default'     => true,
		] );


		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'perf__preload_assets',
			'label'       => _x( 'Preload Assets', 'Customizer control title', 'rey-core' ),
			'help' => [
				_x( 'Preload assets that are important to your site\'s top area to load them faster. Make sure not to add more than 1-2 items as otherwise it would make more harm. More about preloading at <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content" target="_blank">Mozilla docs - Preloading content</a>.', 'Customizer control description', 'rey-core'),
				'clickable' => true,
				// 'size'      => 290,
			],
			'row_label' => [
				'type' => 'text',
				'value' => esc_html_x('Preloaded asset', 'Customizer control title', 'rey-core'),
				'field' => 'type',
			],
			'button_label' => esc_html_x('New Asset', 'Customizer control title', 'rey-core'),
			'default'      => [],
			'fields' => [
				'type' => [
					'type'        => 'text',
					'label'       => esc_html_x('Type (eg: image, font, video etc.)', 'Customizer control title', 'rey-core'),
				],
				'path' => [
					'type'        => 'text',
					'label'       => esc_html_x('URL', 'Customizer control title', 'rey-core'),
				],
				'mime' => [
					'type'        => 'text',
					'label'       => esc_html_x('MIME-type (eg: image/jpeg etc.)', 'Customizer control title', 'rey-core'),
				],
				'media' => [
					'type'        => 'text',
					'label'       => esc_html_x( 'Media (eg: (max-width: 600px))', 'Customizer control title', 'rey-core' ),
				],
				'crossorigin' => [
					'type'        => 'select',
					'label'       => esc_html_x( 'Cross-Origin', 'Customizer control title', 'rey-core' ),
					'default'     => 'no',
					'choices'     => [
						'no' => esc_html__( 'No', 'rey-core' ),
						'yes' => esc_html__( 'Yes', 'rey-core' ),
					],
				],
			],

		] );

		$this->add_control( [
			'type'     => 'textarea',
			'settings'    => 'perf__exclude_styles',
			'label'       => esc_html__( 'Exclude Styles', 'rey-core' ),
			'default'  => '',
			'help' => [
				_x( 'Exclude styles from loading in the frontend by adding their WP style ID, or filename, filepath etc. One per line', 'Customizer control description', 'rey-core'),
				'clickable' => true,
			],
			'input_attrs'     => [
				'placeholder' => esc_html__( 'eg: some-style-id' . "\n" . 'another-style.js', 'rey-core' ),
			],
			'css_class' => '--block-label',
		] );

		$this->add_control( [
			'type'     => 'textarea',
			'settings'    => 'perf__exclude_scripts',
			'label'       => esc_html__( 'Exclude Scripts', 'rey-core' ),
			'default'  => '',
			'help' => [
				_x( 'Exclude scripts from loading in the frontend by adding their WP script ID, or filename, filepath etc. One per line', 'Customizer control description', 'rey-core'),
				'clickable' => true,
			],
			'input_attrs'     => [
				'placeholder' => esc_html__( 'eg: some-script-id' . "\n" . 'another-script.js', 'rey-core' ),
			],
			'css_class' => '--block-label',
		] );

	}
}
