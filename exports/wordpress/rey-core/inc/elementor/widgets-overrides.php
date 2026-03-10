<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WidgetsOverrides
{

	public function __construct(){
		$this->load_elements_overrides();
	}

	/**
	 * Load custom Elementor elements overrides
	 *
	 * @since 1.0.0
	 */
	public function load_elements_overrides()
	{

		$elements = [
			'Accordion',
			'Button',
			'Column',
			'Common',
			'Container',
			'Document',
			'GlobalSettings',
			'Heading',
			'Icon',
			'IconBox',
			'ImageCarousel',
			'ImageGallery',
			'Image',
			'Kit',
			'Section',
			'Sidebar',
			'Text',
			'Video',
		];

		foreach ($elements as $element) {
			$class_name = \ReyCore\Helper::fix_class_name($element, 'Elementor\Custom');
			if( class_exists($class_name) ){
				new $class_name();
			}
			else {
				error_log(var_export( sprintf('class not found in Elementor widgets overrides %s', $class_name ), true));
			}
		}
	}


	/**
	 * Render Custom CSS control in Section & Container
	 *
	 * @param object $element
	 * @return void
	 */
	public static function custom_css_controls( $element ){

		$element->start_controls_section(
			'section_rey_custom_CSS',
			[
				'label' => sprintf( '<span>%s</span><span class="rey-hasStylesNotice">%s</span>', __( 'Custom CSS', 'rey-core' ) , __( 'Has Styles!', 'rey-core' ) ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
				'hide_in_inner' => 'container' === $element->get_unique_name() ? false : true,
			]
		);

		$element->add_control(
			'rey_custom_css',
			[
				'type' => \Elementor\Controls_Manager::CODE,
				'label' => esc_html__('Custom CSS', 'rey-core'),
				'language' => 'css',
				'render_type' => 'ui',
				'show_label' => false,
				'separator' => 'none',
			]
		);

		$uid = 'SELECTOR';

		$css_desc = sprintf('<p>%s</p>', esc_html__('Click to insert selector:', 'rey-core'));

		$css_desc .= sprintf('<div class="rey-selectorCss">
			<button class="elementor-button elementor-button-default" data-selector="%1$s {}">%1$s</button>
			<button class="elementor-button elementor-button-default" data-selector="%2$s {}">%2$s</button>
		</div>', $uid, 'SELECTOR-INNER' );

		$css_desc .= sprintf('<p>%s</p>', esc_html__('Insert media query snippet:', 'rey-core'));

		$css_desc .= sprintf('<div class="rey-selectorCss">
			<button class="elementor-button elementor-button-default" data-selector="@media (max-width:767px){%1$s}" data-tooltip="Smaller than 767px">Mobile only</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (max-width:1024px){%1$s}" data-tooltip="Smaller than 1024px">Mobile & Tablet</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (min-width:768px) and (max-width:1024px){%1$s}" data-tooltip="From 768px to 1024px">Tablet only</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (min-width:768px){%1$s}" data-tooltip="Larger than 768px">Tablet & Desktop</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (min-width:1025px){%1$s}" data-tooltip="Larger than 1025px">Laptop & Desktop</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (min-width:1025px) and (max-width:1440px){%1$s}" data-tooltip="From 1025px to 1440px">Laptop</button>
			<button class="elementor-button elementor-button-default" data-selector="@media (min-width:1441px){%1$s}" data-tooltip="Larger than 1441px">Desktop</button>
		</div>', "\n  $uid {\n    \n  }\n" );

		$element->add_control(
			'rey_custom_css_desc',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => $css_desc,
				'content_classes' => 'rey-raw-html rey-customCssSel',
			]
		);

		$element->end_controls_section();

	}

	/**
	* Render Custom CSS control in Section & Container
	*
	* @param object $element
	* @return void
	*/
   public static function hide_element_on( $element ){

		$element->add_control(
			'rey_hide_on',
			[
				'label' => esc_html__( 'Hide for:', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Don\'t hide', 'rey-core' ),
					'logged-in'  => esc_html__( 'Logged IN users', 'rey-core' ),
					'logged-out'  => esc_html__( 'Logged OUT users', 'rey-core' ),
					'custom-script'  => esc_html__( 'Custom Script', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'rey_hide_on_script_id',
			[
				'label' => esc_html__( 'Custom Script ID', 'rey-core' ),
				'description' => __( 'This ID will be used inside a unique WordPress filter name, such as <code>reycore/elementor/hide_element/my_script_id</code>, which if returning true, will hide the element or widget. Here\'s a <a href="https://d.pr/n/0tcLL0" target="_blank">code example</a>.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'ex: my_script_id',
				'condition' => [
					'rey_hide_on' => 'custom-script',
				],
			]
		);
   }

	/**
	* Render Custom CSS control in Section & Container
	*
	* @param object $element
	* @return void
	*/
   public static function horizontal_offset_for_mobile( $element, $extra_desc = '' ){

	$element->add_control(
		'rey_mobile_offset',
		[
			'label' => __( 'Mobile Horizontal Scroll', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
			'description' => __( 'You can force this element\'s container to stretch on mobiles and display a horizontal scrollbar. ', 'rey-core' ) . $extra_desc,
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'rey-mobiOffset',
			'default' => '',
			'prefix_class' => '',
			'separator' => 'before'
		]
	);

		$element->add_control(
			'rey_mobile_offset_width',
			[
				'label' => esc_html__( 'Stretch width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 3000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--mobi-offset: {{SIZE}}px;',
				],
				'condition' => [
					'rey_mobile_offset!' => '',
				],
			]
		);

		$element->add_control(
			'rey_mobile_offset_gutter',
			[
				'label' => esc_html__( 'Include Side Gap', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'rey-mobiOffset--gap',
				'default' => '',
				'prefix_class' => '',
				'condition' => [
					'rey_mobile_offset!' => '',
				],
			]
		);

	}

	public static function maybe_hide_element($element){

		if( $hide_on = $element->get_settings('rey_hide_on') ){

			$is_logged_in = is_user_logged_in();

			if( $hide_on === 'logged-in' && $is_logged_in ){
				return true;
			}

			else if( $hide_on === 'logged-out' && ! $is_logged_in ){
				return true;
			}

			elseif( $hide_on === 'custom-script' && ($script_id = $element->get_settings('rey_hide_on_script_id')) && apply_filters("reycore/elementor/hide_element/$script_id", false, $element) ){
				return true;
			}

		}

		return false;
	}

	public static function should_render_element_or_widget( $should_render, $element ){

		if( reycore__elementor_edit_mode() ) {
			return $should_render;
		}

		if( self::maybe_hide_element($element) ){
			$should_render = false;
		}

		return $should_render;
	}

}
