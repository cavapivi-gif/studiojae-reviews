<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Helper {

	public static $breakpoints = [];

	public function __construct(){}

	/**
	 * Get major Elementor version
	 *
	 * @since 1.6.12
	 */
	public static function get_elementor_major_version(){

		if( defined('ELEMENTOR_VERSION') ){
			$version = explode( '.', ELEMENTOR_VERSION);
			if( isset($version[0]) ){
				return absint($version[0]);
			}
		}

		return false;
	}

	public static function legacy_mode( $mode_name = null ) {
		if (class_exists('\Elementor\Plugin')) {
			$elementor = \Elementor\Plugin::instance();
			if (method_exists($elementor, 'get_legacy_mode')) {
				return $elementor->get_legacy_mode( $mode_name );
			}
		}
	}

	public static function is_pushback_fallback_enabled(){
		return apply_filters('reycore/elementor/pushback_fallback', false);
	}

	public static function is_experiment_active( $feature ){

		if( ! isset(\Elementor\Plugin::$instance->experiments) ){
			return false;
		}

		return \Elementor\Plugin::$instance->experiments->is_feature_active( $feature );
	}

	public static function is_optimized_dom() {

		// feature was removed in Elementor 3.19.0 and enabled by default
		return true;

		if( self::get_elementor_major_version() < 3 ){
			return false;
		}

		static $is_active;

		if( is_null($is_active) ){
			$is_active = self::is_experiment_active('e_dom_optimization');
		}

		return $is_active;
	}

	public static function button_styles(){
		$style['simple'] = __( 'REY - Link', 'rey-core' );
		$style['primary'] = __( 'REY - Primary', 'rey-core' );
		$style['secondary'] = __( 'REY - Secondary', 'rey-core' );
		$style['primary-outline'] = __( 'REY - Primary Outline', 'rey-core' );
		$style['secondary-outline'] = __( 'REY - Secondary Outline', 'rey-core' );
		$style['underline'] = __( 'REY - Underlined', 'rey-core' );
		$style['underline-hover'] = __( 'REY - Hover Underlined', 'rey-core' );
		$style['dashed --large'] = __( 'REY - Large Dash', 'rey-core' );
		$style['dashed'] = __( 'REY - Normal Dash', 'rey-core' );
		$style['underline-1'] = __( 'REY - Underline 1', 'rey-core' );
		$style['underline-2'] = __( 'REY - Underline 2', 'rey-core' );

		return $style;
	}

	public static function get_breakpoints(){

		if( ! empty(self::$breakpoints) ){
			return self::$breakpoints;
		}

		$breakpoints = [];

		if ( isset(\Elementor\Plugin::$instance->experiments) && \Elementor\Plugin::$instance->experiments->is_feature_active( 'additional_custom_breakpoints' ) ) {
			$custom_breakpoints = \Elementor\Plugin::$instance->breakpoints->get_breakpoints_config();
			foreach ($custom_breakpoints as $key => $value) {
				if( $value['is_enabled'] ){
					$breakpoints[] = $key;
				}
			}
		}

		if( empty($breakpoints) ){
			return [
				'',
				'_tablet',
				'_mobile'
			];
		}

		array_walk($breakpoints, function(&$bp) {
			$bp = $bp ? '_' . $bp : $bp;
		});

		// desktop
		$breakpoints[] = '';

		return self::$breakpoints = $breakpoints;
	}

	/**
	 * Method to get Elementor compatibilities.
	 *
	 * @since 1.0.0
	 */
	public static function get_compatibilities( $support = '' )
	{
		$supports = [
			/**
			 * If Elementor adds video support on columns,
			 * i'll need to add ELEMENTOR_VERSION < x.x.x .
			 */
			'column_video' => true,
			/**
			 * Currently disabled by default. Needs implementation. However still,
			 * i'll need to add support on ELEMENTOR_VERSION > 2.7.0
			 */
			'video_bg_play_on_mobile' => true,
		];

		if( $support && isset($supports[$support]) ){
			return $supports[$support];
		}

		return $supports;
	}


	static public function get_elementor_option( $post_id = null, $option = false ){

		if( !class_exists('\Elementor\Core\Settings\Page\Manager') ){
			return;
		}

		if( ! $post_id ){
			return;
		}

		$elementor_meta = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

		if ( $option ) {
			if( isset($elementor_meta[ $option ]) ){
				return $elementor_meta[ $option ];
			}
			else {
				return false;
			}
		}

		return $elementor_meta;
	}

	/**
	 * Inject HTML into an element/widget output
	 *
	 * @since 1.0.0
	 */
	public static function el_inject_html( $content, $injection, $query )
	{
		// checks
		if ( ( class_exists( '\DOMDocument' ) && class_exists( '\DOMXPath' ) && function_exists( 'libxml_use_internal_errors' ) ) ) {

			// We have to go through DOM, since it can load non-well-formed XML (i.e. HTML).
			$dom = new \DOMDocument();

			// The @ is not enough to suppress errors when dealing with libxml,
			// we have to tell it directly how we want to handle errors.
			libxml_use_internal_errors( true );

			if ( apply_filters('reycore/helper/el_inject_html/entity_fix', function_exists( 'mb_encode_numericentity' ) ) ) {
				$content = mb_encode_numericentity( $content, array( 0x80, 0x10FFFF, 0, ~0 ), 'UTF-8' );
			}

			@$dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR ); // suppress parser warnings

			libxml_clear_errors();
			libxml_use_internal_errors( false );

			// Get parsed document
			$xpath = new \DOMXPath($dom);
			$container = $xpath->query($query);

			if( $container ) {
				$container_node = $container->item(0);

				// Create new node
				$newNode = $dom->createDocumentFragment();
				// add the slideshow html into the newly node
				if ( $newNode->appendXML( $injection ) && $container_node ) {
					// insert before the first child
					$container_node->insertBefore($newNode, $container_node->firstChild);
					// fixed extra html & body tags
					// on some hostings these tags are added even though LIBXML_HTML_NOIMPLIED is added
					$clean_tags = ['<html>', '<body>', '</html>', '</body>'];
					// save the content
					return str_replace($clean_tags, '', $dom->saveHTML());
				}
			}
		}
		else {
			return __( "PHP's DomDocument is not available. Please contact your hosting provider to enable PHP's DomDocument extension." );
		}

		return $content;
	}


	/**
	 * Display a notice in widgets, in edit mode.
	 * For example requirements of a widget or a simple warning
	 *
	 * @since 1.0.0
	 */
	public static function edit_mode_widget_notice( $presets = [], $args = [] )
	{
		if( reycore__elementor_edit_mode() ) {

			$default_presets = [
				'full_viewport' => [
					'type' => 'warning',
					'title' => __('Requirement!', 'rey-core'),
					'text' => __('This widget is full viewport only. Please access this widget\'s parent section/container, and <strong>enable Stretch</strong> and also select <strong>Content Width to "Full width"</strong>.', 'rey-core'),
					'class' => 'coverEl-notice--needStretch',
				],
				'tabs_modal' => [
					'type' => 'warning',
					'title' => __('Not using properly!', 'rey-core'),
					'text' => __('Please don\'t use this widget into a section with <strong>Tabs</strong> or <strong>Modal</strong> enabled. Please disable those settings.', 'rey-core'),
					'class' => 'coverEl-notice--noTabs',
				]
			];

			$markup = '<div class="rey-elementorNotice rey-elementorNotice--%s %s"><h4>%s</h4><p>%s</p></div>';

			if( !empty($args) ){

				$defaults = [
					'type' => 'warning',
					'text' => __('Warning!', 'rey-core'),
					'text' => __('Text', 'rey-core'),
					'class' => '',
				];

				// Parse args.
				$args = wp_parse_args( $args, $defaults );

				printf( $markup, $args['type'], $args['class'], $args['title'], $args['text'] );
			}

			if( !empty($presets) ){
				foreach ($presets as $preset) {
					if( isset($default_presets[$preset]) ){
						printf(
							$markup,
							$default_presets[$preset]['type'],
							$default_presets[$preset]['class'],
							$default_presets[$preset]['title'],
							$default_presets[$preset]['text']
						);
					}
				}
			}

		}
	}


	/**
	 * Get Document settings
	 *
	 * @since 1.0.0
	 */
	public static function get_document_settings( $setting = '' ){

		// Get the current post id
		$post_id = get_the_ID();

		// Get the page settings manager
		$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

		// Get the settings model for current post
		$page_settings_model = $page_settings_manager->get_model( $post_id );

		return $page_settings_model->get_settings( $setting );
	}

	public static function common_toggle_text($content, $all_settings){

		if( ! isset($all_settings['rey_toggle_text']) ){
			return $content;
		}

		if( $all_settings['rey_toggle_text'] === '' ){
			return $content;
		}

		$strip_tags = isset($all_settings['rey_toggle_text_tags']) && $all_settings['rey_toggle_text_tags'] !== '';

		$more_text = esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core');
		$less_text = esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core');

		if( isset($all_settings['rey_toggle_text_more']) && $custom_more_text = $all_settings['rey_toggle_text_more'] ){
			$more_text = $custom_more_text;
		}

		if( isset($all_settings['rey_toggle_text_less']) && $custom_less_text = $all_settings['rey_toggle_text_less'] ){
			$less_text = $custom_less_text;
		}

		$height_attr = '';
		if( isset($all_settings['rey_toggle_text_height']) && $custom_height = $all_settings['rey_toggle_text_height'] ){
			$height_attr = sprintf('data-height="%s"', $custom_height);
		}

		if( isset($all_settings['rey_toggle_text_height_tablet']) && $custom_height_tablet = $all_settings['rey_toggle_text_height_tablet'] ){
			$height_attr .= sprintf(' data-height-tablet="%s"', $custom_height_tablet);
		}

		if( isset($all_settings['rey_toggle_text_height_mobile']) && $custom_height_mobile = $all_settings['rey_toggle_text_height_mobile'] ){
			$height_attr .= sprintf(' data-height-mobile="%s"', $custom_height_mobile);
		}

		if( $strip_tags ){

			$intro = wp_strip_all_tags($content);
			$limit = 50;

			if ( strlen($intro) > $limit) {
				reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
				reycore_assets()->add_scripts('reycore-text-toggle');
				$content = sprintf('<div class="u-toggle-text u-toggle-text-wrapper --collapsed" %s>', $height_attr);
					$content .= '<div class="u-toggle-content">';
					$content .= $intro;
					$content .= '</div>';
					$content .= '<button class="btn u-toggle-btn" aria-label="Toggle" data-read-more="'. $more_text .'" data-read-less="'. $less_text .'"></button>';
				$content .= '</div>';

				return $content;
			}
		}
		// keep tags
		else{
			$full_content = $content;
			if( $full_content ):
				reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
				reycore_assets()->add_scripts('reycore-text-toggle');
				$content = sprintf('<div class="u-toggle-text-next-btn u-toggle-text-wrapper --short" %s>', $height_attr);
				$content .= $full_content;
				$content .= '</div>';
				$content .= '<button class="btn btn-minimal" aria-label="Toggle"><span data-read-more="'. $more_text .'" data-read-less="'. $less_text .'"></span></button>';
			endif;
		}

		return $content;
	}

	public static function unprefixed_widget_name( $id ){
		return str_replace( Widgets::PREFIX, '', $id );
	}

	public static function px_badge( $text = 'px' ){
		return sprintf('<span class="rey-pxBadge">%s</span>', $text);
	}

	public static function rey_badge( $text = '' ){

		if( ! reycore__get_props('branding') ){
			return;
		}

		if( empty($text) && defined('REY_CORE_THEME_NAME') ){
			$text = REY_CORE_THEME_NAME;
		}

		return sprintf('<span class="rey-elementorBadge">%s</span>', $text);
	}

	public static function scan_content_in_site( $type = 'element', $element = '' ){

		if( empty( $element ) ){
			return [];
		}

		$args = [
			'numberposts' => -1,
			'post_type'   => 'any',
			'post_status' => 'publish',
			'fields'      => 'ids',
		];

		if( $post_types = get_post_types(['public' => true]) ){
			if( ! empty($post_types) && is_array($post_types) ){
				$args['post_type'] = array_keys($post_types);
			}
		}

		$_elementor_data = '_elementor_data';

		if( $type === 'element' ){
			$value = '"widgetType":"%s"';
		}
		elseif( $type === 'content' ){
			$value = '%s';
		}

		if( is_array($element) ){

			$args['meta_query']['relation'] = 'OR';

			foreach ($element as $el) {
				$args['meta_query'][] = [
					'key'     => $_elementor_data,
					'value'   => sprintf($value, reycore__clean($el)),
					'compare' => 'LIKE'
				];
			}
		}
		else {
			$args['meta_query'][] = [
				'key'     => $_elementor_data,
				'value'   => sprintf($value, reycore__clean($element)),
				'compare' => 'LIKE'
			];
		}

		return get_posts($args);

	}

	 /**
     * Import an Elementor Template from an local file
     *
     * @param string $filepath
     */
    public static function importTemplateFromFile( $filepath, $download = false ) {

		if( $download ){

			if ( ! function_exists( 'download_url' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}

			$template_url = $filepath;

			// Download file to temporary location.
			$filepath = download_url( $template_url );

			// Make sure there were no errors.
			if ( is_wp_error( $filepath ) ) {
				return sprintf('Cannot download template file from URL %s.', $template_url);
			}
		}

		/**
		 * @var Elementor\TemplateLibrary\Source_Local
		 */
		if( ! ($source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' )) ){
			return 'Cannot find Elementor Local Template Library.';
		}

        $filename = pathinfo( $filepath, PATHINFO_BASENAME );

        return $source->import_template( $filename, $filepath );
    }

	/**
	 * Wrapper for box styles controls
	 *
	 * @param array $args
	 * @return void
	 */
	public static function widgets_box_styles_controls( $args = [] ){

		$args = wp_parse_args($args, [
			'element'       => null,
			'selectors'     => ['active'=> '{{WRAPPER}}', 'hover'=>'{{WRAPPER}}:hover'],
			'section_title' =>  'Box styles',
		]);

		if( ! ($element = $args['element']) ){
			return;
		}

		$element->start_controls_section(
			'section_box_styles',
			[
				'label'      => $args['section_title'],
				'tab'        => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,

			]
		);

			$selectors = $args['selectors'];

			$element->add_control(
				'box_border_width',
				[
					'label' => esc_html__( 'Border Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'selectors' => [
						$args['selectors']['active'] => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$element->add_control(
				'box_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
						],
					],
					'selectors' => [
						$args['selectors']['active'] => 'border-radius: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$element->add_responsive_control(
				'box_padding',
				[
					'label' => esc_html__( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'selectors' => [
						$args['selectors']['active'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$element->start_controls_tabs( 'box_style_tabs' );

				$element->start_controls_tab( 'box_style_normal', [
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$element->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'box_shadow',
							'selector' => $args['selectors']['active'],
						]
					);

					$element->add_control(
						'box_bg_color',
						[
							'label' => esc_html__( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$args['selectors']['active'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'box_border_color',
						[
							'label' => esc_html__( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$args['selectors']['active'] => 'border-color: {{VALUE}}',
							],
						]
					);

				$element->end_controls_tab();

				$element->start_controls_tab( 'box_style_hover', [
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$element->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'box_shadow_hover',
							'selector' => $args['selectors']['hover'],
						]
					);

					$element->add_control(
						'box_bg_color_hover',
						[
							'label' => esc_html__( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$args['selectors']['hover'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'box_border_color_hover',
						[
							'label' => esc_html__( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$args['selectors']['hover'] => 'border-color: {{VALUE}}',
							],
						]
					);

				$element->end_controls_tab();

			$element->end_controls_tabs();

		$element->end_controls_section();

	}

	public static function render_dynamic_controls($element, $args = []){

		$args = wp_parse_args($args, [
			'source_key' => '',
			'condition_skin' => '',
		]);

		$element->add_control(
			$args['source_key'],
			[
				'label' => __( 'Text Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'title',
				'options' => [
					'title'  => __( 'Post Title', 'rey-core' ),
					'excerpt'  => __( 'Post excerpt', 'rey-core' ),
					'content'  => __( 'Post Content', 'rey-core' ),
					'archive_title'  => __( 'Archive Title', 'rey-core' ),
					'desc'  => __( 'Archive Description', 'rey-core' ),
				],
				'condition' => [
					'_skin' => $args['condition_skin'],
				],
			]
		);

		$element->add_control(
			'rey_source_before',
			[
				'label' => esc_html__( 'Text Before', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'_skin' => $args['condition_skin'],
				],
			]
		);

		$element->add_control(
			'rey_source_after',
			[
				'label' => esc_html__( 'Text After', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'_skin' => $args['condition_skin'],
				],
			]
		);

		$element->add_control(
			'rey_source_placeholder',
			[
				'label' => esc_html__( 'Placeholder', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => '{{ ... }}',
				'condition' => [
					'_skin' => $args['condition_skin'],
				],
			]
		);

	}


	public static function render_dynamic_text($args = []){

		$args = wp_parse_args($args, [
			'settings' => [],
			'fallback' => '',
			'source_key' => '',
		]);

		$text = (isset($args['settings']['rey_source_placeholder']) && ($placeholder = $args['settings']['rey_source_placeholder'])) ? $placeholder : $args['fallback'];

		$args['before'] = isset($args['settings']['rey_source_before']) ? $args['settings']['rey_source_before'] : '';
		$args['after'] = isset($args['settings']['rey_source_after']) ? $args['settings']['rey_source_after'] : '';

		if( get_post_type() !== \ReyCore\Elementor\GlobalSections::POST_TYPE ){

			switch( $args['settings'][$args['source_key']] ):

				case 'excerpt':
					$text = wp_kses_post( get_the_excerpt() );
					break;

				case 'content':
					$text = wp_kses_post( get_the_content() );
					break;

				case 'desc':
					$text = wp_kses_post( get_the_archive_description() );
					break;

				default:
					$text = wp_kses_post( reycore__get_page_title() );
			endswitch;

		}

		if( $custom_text = apply_filters('reycore/elementor/dynamic_text/render_text', '', $args['settings'], $args) ){
			return $custom_text;
		}

		return $args['before'] . $text . $args['after'];

	}

	public static function render_icon( $icon, $attributes = [], $tag = 'i' ){

		ob_start();
		\Elementor\Icons_Manager::render_icon( $icon, $attributes, $tag );
		$icon_html = ob_get_clean();

		if( 'svg' === $icon['library'] ){
			return sprintf('<span class="rey-wicon">%s</span>', $icon_html);
		}

		return $icon_html;
	}

	/**
	 * @deprecated 2.7.1
	 *
	 * @param string $prop
	 * @return void
	 */
	public static function get_props( $prop = '' ){
		return false;
	}

}
