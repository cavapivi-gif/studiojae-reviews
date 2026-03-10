<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GlobalSection extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'global-section',
			'title' => __( 'Global Section', 'rey-core' ),
			'icon' => 'eicon-section',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
		];
	}

	public function __construct( $data = [], $args = null ) {

		if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
			if( isset($settings['section_type']) && $section = $settings['section_type'] ){
				$this->load_css($section);
			}
		}

		parent::__construct( $data, $args );
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['generic'],
            $element['settings']['header'],
            $element['settings']['cover'],
            $element['settings']['footer'],
            $element['settings']['megamenu']
		);

        return $element;
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#global-section');
	}

	/**
	 * Load Sections custom CSS to prevent FOUC
	 *
	 * @since 1.0.0
	 */
	private function load_css( $section = false ) {

		if ( $section && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $section );

			if( !empty($css_file) ){
				$css_file->enqueue();
			}
		}
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		// Section type
		$this->add_control(
			'section_type',
			[
				'label' => __( 'Global Section Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'generic',
				'options' => [
					'generic'  => __( 'Generic Section', 'rey-core' ),
					'header'  => __( 'Header', 'rey-core' ),
					'cover'  => __( 'Page Cover', 'rey-core' ),
					'footer'  => __( 'Footer', 'rey-core' ),
					'megamenu'  => __( 'Mega Menu', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'generic',
			[
				'label' => __( 'Generic Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'generic',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'section_type' => ['generic'],
				],
			]
		);

		$this->add_control(
			'cover',
			[
				'label' => __( 'Cover Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'cover',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'section_type' => ['cover'],
				],
			]
		);

		$this->add_control(
			'header',
			[
				'label' => __( 'Header Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'header',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'section_type' => ['header'],
				],
			]
		);

		$this->add_control(
			'footer',
			[
				'label' => __( 'Footer Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'footer',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'section_type' => ['footer'],
				],
			]
		);

		$this->add_control(
			'megamenu',
			[
				'label' => __( 'Mega-Menu Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'megamenu',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'section_type' => ['megamenu'],
				],
			]
		);


		$this->end_controls_section();

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'rey-element reyEl-gs' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
				if( $settings['section_type'] && $section = $settings[$settings['section_type']] ){
					reycore_assets()->defer_page_styles('elementor-post-' . $section);
					echo \ReyCore\Elementor\GlobalSections::do_section($section, false, true);
				}
			?>
		</div>
		<!-- .reyEl-gs -->
		<?php
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
