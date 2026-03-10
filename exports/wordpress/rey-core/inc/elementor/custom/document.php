<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Document {

	private static $params = [
		'preview_width' => [
			'generic',
		]
	];

	function __construct(){

		add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'page_settings']);
		add_action( 'elementor/element/wp-post/document_settings/after_section_end', [$this, 'extra_page_settings']);
		add_action( 'elementor/element/wp-post/section_page_style/before_section_end', [$this, 'page_styles']);
		add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'gs_settings']);

		add_action( 'elementor/element/wp-page/document_settings/before_section_end', [$this, 'page_settings']);
		add_action( 'elementor/element/wp-page/document_settings/after_section_end', [$this, 'extra_page_settings']);
		add_action( 'elementor/element/wp-page/section_page_style/before_section_end', [$this, 'page_styles']);

		add_action('elementor/db/before_save', [$this , 'reset_hide_title'], 10, 2);

	}

	public function reset_hide_title( $post_id , $is_meta ) {

		global $post;

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return;
		}

		if ( ! isset($post->post_type) ) {
			return;
		}

		if ( $post->post_type !== \ReyCore\Elementor\GlobalSections::POST_TYPE ) {
			return;
		}

		if( ! ($elementor_meta = get_post_meta( $post->ID, \Elementor\Core\Base\Document::PAGE_META_KEY, true )) ){
			return;
		}

		$elementor_meta['hide_title'] = '';

		update_post_meta( $post->ID  , \Elementor\Core\Base\Document::PAGE_META_KEY , $elementor_meta );
	}

	/**
	 * Add page settings into Elementor
	 *
	 * @since 1.0.0
	 */
	function page_settings( $page )
	{
		if( ! ( ($page_id = $page->get_id()) && $page_id != "" && $post_type = get_post_type( $page_id ) ) ) {
			return;
		}

		if ( ! ($post_type === 'page' || $post_type === 'revision') ) {
			return;
		}

		// Inject options
		$page->start_injection( [
			'of' => 'template',
		] );

		$page->add_control(
			'rey_stretch_page',
			[
				'label' => __( 'Stretch Page [deprecated]', 'rey-core' ),
				'description' => __( 'Please use a Full-width template from the Layout list instead of using this option.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'rey-stretchPage',
				'default' => '',
				'condition' => [
					'template' => 'template-builder.php',
				],
			]
		);

		$page->end_injection();

		$page->add_control(
			'rey_need_help',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					__('To learn more about these options, please visit <a href="%s" target="_blank">Rey\'s Documentation</a>.', 'rey-core'),
					reycore__support_url('kb/elementor-page-settings/')
				),
				'content_classes' => 'elementor-descriptor',
			]
		);

	}

	public function get_params(){
		return self::$params;
	}

	public function set_params( $params ){
		self::$params = $params;
	}

	/**
	 * Add page settings into Elementor
	 *
	 * @since 1.0.0
	 */
	function gs_settings( $page )
	{
		if( ! (
			class_exists('\ReyCore\Elementor\GlobalSections') &&
			($page_id = $page->get_id()) && $page_id != "" && ($post_type = get_post_type( $page_id )) &&
			($post_type === \ReyCore\Elementor\GlobalSections::POST_TYPE || $post_type === 'revision')
		) ) {
			return;
		}

		if( $post_type === 'revision' && ($rev_id = wp_get_post_parent_id($page_id)) && $rev_id !== 0 ){
			$page_id = $rev_id;
		}

		$gs_type = reycore__acf_get_field('gs_type', $page_id, 'generic');

		do_action('reycore/elementor/document_settings/gs/before', $this, $page, $gs_type, $page_id);

		$page->add_control(
			'gs_type',
			[
				'label' => __( 'Global Section Type', 'rey-core' ),
				'default' => $gs_type,
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_section_types',
					'export' => 'id',
				],
			]
		);

		$page->add_responsive_control(
			'gs_preview_width',
			[
				'label' => esc_html__( 'Preview Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 3000,
						'step' => 1,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [],
				'selectors' => [
					'{{WRAPPER}}' => '--gs-preview-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'gs_type' => self::$params['preview_width'],
				],
			]
		);

		$page->add_responsive_control(
			'gs_header_width',
			[
				'label' => esc_html__( 'Header Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', '%' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 3000,
						'step' => 1,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [],
				'selectors' => [
					'.rey-siteHeader' => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}' => '--gs-preview-width: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
				'condition' => [
					'gs_type' =>['header'],
				],
			]
		);

		$page->add_control(
			'gs_cover_contain',
			[
				'label' => esc_html__( 'Use Container Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'gs_type' => 'cover',
				],
				'selectors' => [
					// '.elementor-editor-active .rey-pbTemplate--gs-cover .elementor-top-section, .elementor-editor-preview .rey-pbTemplate--gs-cover .elementor-top-section' => 'max-width: var(--container-max-width); margin-right: auto; margin-left: auto; width: 100%;',
					'.elementor-editor-active .rey-pbTemplate--gs-cover, .elementor-editor-preview .rey-pbTemplate--gs-cover' => 'max-width: var(--container-max-width); margin-right: auto; margin-left: auto; width: 100%;',
				],
			]
		);

		$messages = [

			'cover' => sprintf( 'To assign a Page cover to a page, either access <a href="%s" target="_blank">Customizer > Page Covers</a> and set global options, or do it per page individually. <a href="%s" target="_blank">Read more</a> about page covers.',
				add_query_arg( ['autofocus[panel]' => \ReyCore\Customizer\Options\Cover::get_id() ], admin_url( 'customize.php' ) ),
				reycore__support_url('kb/how-to-create-page-covers/') ),

			'megamenu' => sprintf( 'To assign a Mega Menu to a menu item, access <a href="%s" target="_blank">Appearance > Menus</a>, edit menu items and enable Mega Menus. <a href="%s" target="_blank">Read more</a> about adding mega menus.',
				admin_url( 'nav-menus.php' ),
				reycore__support_url('kb/how-to-add-mega-menu-panels/') )


		];

		if( isset($messages[ $gs_type ])  ){
			$page->add_control(
				'rey_gs_note',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => $messages[ $gs_type ],
					'content_classes' => 'rey-raw-html',
				]
			);
		}

		do_action('reycore/elementor/document_settings/gs', $page, $gs_type, $page_id, $this);
	}


	/**
	 * Add page settings into Elementor
	 *
	 * @since 1.0.0
	 */
	function extra_page_settings( $page )
	{
		if( ! (($page_id = $page->get_id()) && isset($page) && $page_id != "" && $post_type = get_post_type( $page_id )) ) {
			return;
		}

		if ( ! ($post_type === 'page' || $post_type === 'revision') ) {
			return;
		}

		$page->start_controls_section(
			'rey_utilities',
			[
				'label' => __( 'Utilities', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		$page->add_control(
			'rey_body_class',
			[
				'label' => esc_html__( 'Body CSS Classes', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true
			]
		);

		$page->end_controls_section();

	}

	function page_styles( $stack )
	{
		if(! (isset($stack) && $stack->get_id() != "")) {
			return;
		}

		if ( in_array( get_post_type( $stack->get_id() ), apply_filters('reycore/elementor/page_styles/supported_post_types', [
			'page',
			'revision',
			'rey-templates',
		]), true) ) {

			// Update padding
			$p_padding = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'padding' );
			if( $p_padding && ! is_wp_error($p_padding) ) {
				$p_padding['selectors'] = [
					':root' => '--page-padding-top: {{TOP}}{{UNIT}}; --page-padding-right: {{RIGHT}}{{UNIT}}; --page-padding-bottom: {{BOTTOM}}{{UNIT}}; --page-padding-left: {{LEFT}}{{UNIT}}',
				];
				$stack->update_control( 'padding', $p_padding );
			}

			// Add Container Padding
			$stack->add_responsive_control(
				'rey_container_spacing',
				[
					'label'       => esc_html__( 'Container Horizontal Margins', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'default'     => '',
					'step'        => 1,
					'placeholder' => get_theme_mod('container_spacing', 15) . 'px',
					'selectors' => [
						'[data-page-el-selector="{{WRAPPER}}"]' => '--main-gutter-size: {{VALUE}}px;',
					],
				]
			);

		}

	}

}
