<?php
namespace ReyCore\Modules\OffcanvasPanels;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	public $added = [];

	private $offcanvas_panels;

	private static $prevent_cached_ids = [];

	const GSTYPE = 'offcanvas';

	const ASSET_HANDLE = 'reycore-offcanvas-panels';

	const AJAX_LAZY_ACTION = 'get_offcanvas_panel';

	public function __construct()
	{
		add_action( 'init', [$this, 'init'] );
		add_filter( 'reycore/global_sections/types', [$this, 'add_gs_support']);
		add_filter( 'reycore/acf/global_section_icons', [$this, 'add_icon'], 20);
		add_filter( 'reycore/acf/global_section_descriptions', [$this, 'add_description'], 20);
		add_action( 'reycore/elementor/document_settings/gs', [$this, 'gs_settings'], 10, 3);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'save_post', [$this, 'flush_offcanvas_transient_ids'], 20, 2 );
		add_action( 'delete_post', [$this, 'flush_offcanvas_transient_ids'], 20, 2 );
	}

	public function init()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/elementor/btn_trigger', [ $this, 'init_content'] );
		add_action( 'reycore/elementor/header_nav/offcanvas', [ $this, 'init_content'] );
		add_action( 'wp_enqueue_scripts', [ $this, 'force_init_content'] );
		add_action(	'wp_footer', [$this, 'prevent_panel_caching'], 20);
		add_filter( 'reycore/global_section_template/attributes', [$this, 'editor_gs_attribute'], 10, 2 );

	}

	public function add_gs_support( $gs ){
		$gs[self::GSTYPE]  = __( 'Off-Canvas Panel', 'rey-core' );
		return $gs;
	}

	public function add_description( $gs ){
		$gs[self::GSTYPE]  = sprintf( _x('Create and display any type of content into animated side panels. <a href="%s" target="_blank">Learn More</a>.', 'Global section description', 'rey-core'), reycore__support_url('/kb/off-canvas-global-sections/') );
		return $gs;
	}

	public function add_icon( $gs ){
		$gs[self::GSTYPE]  = 'woo-pdp-components-in-summary';
		return $gs;
	}

	public function force_init_content(){

		if( ! get_theme_mod('perf__offcanvas_load_always', false) ){
			return;
		}

		$this->init_content();
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => ['rey-simple-scrollbar'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
				'lazy_assets' => "a[href^='#offcanvas-']",
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['animejs', 'rey-simple-scrollbar', 'reycore-elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'lazy_assets' => "a[href^='#offcanvas-']",
			]
		]);
	}

	public function init_content(){

		reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE, 'rey-simple-scrollbar']);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

		// run only if triggers are inside the page, or set to force
		add_action(	'wp_footer', [$this, 'add_panels']);

	}

	public function editor_gs_attribute($attributes, $type){

		if( $type === self::GSTYPE ){

			$page_id = get_the_ID();

			if( get_post_type() === 'revision' && ($rev_id = wp_get_post_parent_id($page_id)) && $rev_id !== 0 ){
				$page_id = $rev_id;
			}

			$attributes['data-position'] = ($pos = \ReyCore\Elementor\Helper::get_elementor_option( $page_id, 'offcanvas_position' )) ? $pos : 'left';
			$attributes['data-disable-padding'] = '';
		}

		return $attributes;
	}

	public function panel_settings_defaults( $setting = '' ){

		$settings = [
			'offcanvas_position'             => 'left',
			'offcanvas_close_position'       => 'inside',
			'offcanvas_close_text'           => '',
			'offcanvas_close_outside_rotate' => '',
			'offcanvas_transition'           => '',
			'offcanvas_transition_duration'  => 700,
			'offcanvas_animate_cols'         => 'yes',
			'offcanvas_shift_site'           => 'yes',
			'offcanvas_lazyload'             => '',
			'offcanvas_lazyload_cache'       => 'yes',
		];

		if( isset($settings[$setting]) ){
			return $settings[$setting];
		}

		return $settings;
	}

	/**
	 * Publish all available and non-lazy loaded panels
	 *
	 * @return void
	 */
	public function add_panels(){

		foreach ($this->get_offcanvas_panels() as $id => $gs):

			if( reycore__is_multilanguage() ){
				$id = apply_filters('reycore/translate_ids', $id, \ReyCore\Elementor\GlobalSections::POST_TYPE);
			}

			if( in_array($id, $this->added, true) ){
				continue;
			}

			if( ! apply_filters("reycore/module/offcanvas_panels/load_panel={$id}", false) ){
				continue;
			}

			if( ! ( $settings = $this->get_settings($id) ) ){
				continue;
			}

			$this->added[] = $id;

			// markup will be loaded through Ajax
			if( isset($settings['offcanvas_lazyload']) && $settings['offcanvas_lazyload'] !== '' ){
				if( isset($settings['offcanvas_lazyload_cache']) && $settings['offcanvas_lazyload_cache'] === '' ){
					self::$prevent_cached_ids[] = $id;
				}
				continue;
			}

			$this->make_markup( $id, $settings );

		endforeach;
	}

	/**
	 * Create offcanvas panel's markup and print the Global section
	 *
	 * @param int $id
	 * @param array $settings
	 * @return void
	 */
	public function make_markup($id, $settings){

		if( get_post_type() === \ReyCore\Elementor\GlobalSections::POST_TYPE ){
			return;
		}

		$attributes = [
			'data-gs-id'               => $id,
			'data-transition'          => $settings['offcanvas_transition'],
			'data-transition-duration' => $settings['offcanvas_transition_duration'],
			'data-position'            => $settings['offcanvas_position'],
			'data-close-position'      => $settings['offcanvas_close_position'],
			'data-close-rotate'        => $settings['offcanvas_close_outside_rotate'],
			'data-animate-cols'        => $settings['offcanvas_animate_cols'],
			'data-shift'               => $settings['offcanvas_shift_site'],
		]; ?>

		<div class="rey-offcanvas-wrapper --hidden" <?php echo reycore__implode_html_attributes($attributes) ?> >
			<div class="rey-offcanvas-contentWrapper">
				<button class="rey-offcanvas-close" aria-label="<?php esc_html_e('Close', 'rey-core') ?>" >
					<span class="rey-offcanvas-closeText"><?php echo $settings['offcanvas_close_text'] ?></span>
					<?php echo reycore__get_svg_icon(['id' => 'close', 'class' => 'icon-close']) ?>
				</button>
				<div class="rey-offcanvas-content">
					<?php
						reycore_assets()->defer_page_styles('elementor-post-' . $id, true);
						echo \ReyCore\Elementor\GlobalSections::do_section( $id, false, true );
					?>
				</div>
			</div>
			<div class="rey-lineLoader"></div>
		</div>
		<?php
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( self::AJAX_LAZY_ACTION, [$this, 'ajax__get_offcanvas_panel_content'], [
			'auth'      => 3,
			'nonce'     => false,
			'assets'    => true, // in case cache is disabled
			'transient' => [
				'expiration'         => 2 * WEEK_IN_SECONDS,
				'unique_id'          => 'gs',
				'unique_id_sanitize' => 'absint',
			],
		] );
	}

	/**
	 * Retrieve the panel's content via Ajax
	 *
	 * @param array $data
	 * @return void
	 */
	public function ajax__get_offcanvas_panel_content( $data ){

		if( ! (isset($data['panel_id']) && ($id = absint($data['panel_id']))) ){
			return ['errors'=> esc_html__('Missing Global Section.', 'rey-core')];
		}

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return ['errors'=> esc_html__('Elementor is disabled?', 'rey-core')];
		}

		if( reycore__is_multilanguage() ){
			$id = apply_filters('reycore/translate_ids', $id, \ReyCore\Elementor\GlobalSections::POST_TYPE);
		}

		if( ! ( $settings = $this->get_settings($id) ) ){
			return ['errors'=> esc_html__('Cannot retrieve settings!', 'rey-core')];
		}

		ob_start();
		$this->make_markup($id, $settings);
		return ob_get_clean();

	}

	public function get_settings($id){

		if( ($settings = get_post_meta( $id, \Elementor\Core\Settings\Page\Manager::META_KEY, true )) === false ){
			return;
		}

		return wp_parse_args($settings, $this->panel_settings_defaults());
	}

	public function prevent_panel_caching(){

		if( empty(self::$prevent_cached_ids) ){
			return;
		}
		printf('<script type="text/javascript">var offcanvasUncached = [%s];</script>', implode(',', self::$prevent_cached_ids));
	}

	/**
	 * Add page settings into Elementor
	 *
	 * @since 1.7.0
	 */
	function gs_settings( $page, $gs_type, $page_id )
	{

		$page->add_control(
			'offcanvas_panel_heading',
			[
				'label' => esc_html__( 'OFFCANVAS SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'gs_type' => self::GSTYPE,
				],
			]
		);

		$panel_selector = sprintf('.rey-offcanvas-wrapper[data-gs-id="%s"]', $page_id);

		$page->add_responsive_control(
			'offcanvas_width',
			[
				'label' => esc_html__( 'Panel Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', 'vh' ],
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
					'vh' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 400,
					'unit' => 'px',
				],
				'selectors' => [
					'(desktop)' . $panel_selector => '--panel-width: {{SIZE}}{{UNIT}};',
					'(tablet)' . $panel_selector => '--panel-width-tablet: {{SIZE}}{{UNIT}};',
					'(mobile)' . $panel_selector => '--panel-width-mobile: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}' => '--gs-preview-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'position_notice_x',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Set the panel <strong>WIDTH</strong>.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'offcanvas_position' => ['left', 'right'],
				],
			]
		);

		$page->add_control(
			'position_notice_y',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Set the panel <strong>HEIGHT</strong>.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'offcanvas_position' => ['top', 'bottom'],
				],
			]
		);

		$page->add_control(
			'offcanvas_bgcolor',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					$panel_selector => '--panel-color:{{VALUE}}',
					'{{WRAPPER}}' => '--gs-preview-bg:{{VALUE}}',
				],
				'separator' => 'before',
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_position',
			[
				'label' => esc_html__( 'Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => esc_html__( 'Left', 'rey-core' ),
					'right'  => esc_html__( 'Right', 'rey-core' ),
					'top'  => esc_html__( 'Top', 'rey-core' ),
					'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		// -----

		$page->add_control(
			'offcanvas_transition',
			[
				'label' => esc_html__( 'Transition', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default (Slide)', 'rey-core' ),
					'slideskew'  => esc_html__( 'Slide Skew', 'rey-core' ),
					'curtain'  => esc_html__( 'Curtain', 'rey-core' ),
					'basic'  => esc_html__( 'Basic', 'rey-core' ),
				],
				'separator' => 'before',
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 700,
				'min' => 0,
				'max' => 2000,
				'step' => 10,
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
				'selectors' => [
					$panel_selector => '--transition-duration: {{VALUE}}ms;',
				],
			]
		);

		$page->add_control(
			'offcanvas_animate_cols',
			[
				'label' => esc_html__( 'Animate Inside', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_shift_site',
			[
				'label' => esc_html__( 'Shift Site Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'gs_type' =>self::GSTYPE,
					'offcanvas_position' => ['left', 'right'],
				],
			]
		);

		$page->add_control(
			'offcanvas_curtain__m1_color',
			[
				'type' => \Elementor\Controls_Manager::COLOR,
				'label' => esc_html__( 'Curtain - Mask #1 Color', 'rey-core' ),
				'selectors' => [
					$panel_selector . ' .rey-offcanvas-mask.--m1' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
					'offcanvas_transition' => 'curtain',
				],
			]
		);

		$page->add_control(
			'offcanvas_curtain__m2_color',
			[
				'type' => \Elementor\Controls_Manager::COLOR,
				'label' => esc_html__( 'Curtain - Mask #2 Color', 'rey-core' ),
				'selectors' => [
					$panel_selector . ' .rey-offcanvas-mask.--m2' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
					'offcanvas_transition' => 'curtain',
				],
			]
		);

		$page->add_control(
			'offcanvas_lazyload',
			[
				'label' => esc_html__( 'Lazy Load Content', 'rey-core' ),
				'description' => esc_html__( 'Enabling this option will force the content to load only on demand (when button is clicked), via Ajax.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_lazyload_cache',
			[
				'label' => esc_html__( 'Cache Ajax Response?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => $this->panel_settings_defaults('offcanvas_lazyload_cache'),
				'condition' => [
					'gs_type' =>self::GSTYPE,
					'offcanvas_lazyload!' =>'',
				],
			]
		);

		// Close

		$page->add_control(
			'close_heading',
			[
			   'label' => esc_html__( 'Close Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$page->add_control(
			'offcanvas_close_position',
			[
				'label' => esc_html__( 'Close Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'inside',
				'options' => [
					'inside'  => esc_html__( 'Inside', 'rey-core' ),
					'outside'  => esc_html__( 'Outside', 'rey-core' ),
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_close_text',
			[
				'label' => esc_html__( 'Close text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: Close', 'rey-core' ),
				'selectors' => [
					$panel_selector => '--close-text: "{{VALUE}}";',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_close_outside_rotate',
			[
				'label' => esc_html__( 'Rotate Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'gs_type' =>self::GSTYPE,
					'offcanvas_position' => ['left', 'right'],
					'offcanvas_close_position' => 'outside',
					'offcanvas_close_text!' => '',
				]
			]
		);

		$page->add_control(
			'offcanvas_close_size',
			[
				'label' => esc_html__( 'Close Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 8,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					$panel_selector => '--close-size: {{VALUE}}px',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$panel_selector_close = $panel_selector . ' .rey-offcanvas-close';

		$page->add_control(
			'offcanvas_close_distance',
			[
				'label' => esc_html__( 'Close Distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 8,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					$panel_selector_close => '--distance: {{VALUE}}px',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		$page->add_control(
			'offcanvas_close_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					$panel_selector_close => 'color: {{VALUE}}',
				],
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
			]
		);

		// NOTE

		$page->add_control(
			'offcanvas_custom_trigger',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'content_classes' => 'rey-raw-html',
				'raw' => sprintf( _x( 'If you want to use a custom link anywhere in the site, make use of this anchor <strong class="js-copy-content"><u>#offcanvas-%s</u></strong>.', 'Elementor control label', 'rey-core' ), get_the_ID() ),
				'condition' => [
					'gs_type' =>self::GSTYPE,
				],
				'separator' => 'before',
			]
		);
	}

	public function get_offcanvas_panels(){

		if( $this->offcanvas_panels ){
			return $this->offcanvas_panels;
		}

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return false;
		}

		return $this->offcanvas_panels = \ReyCore\Elementor\GlobalSections::get_global_sections(self::GSTYPE);
	}

	public function flush_offcanvas_transient_ids( $post_id, $post ){

		if ( ! isset($post->post_type) ) {
			return;
		}

		if( $post->post_type !== \ReyCore\Elementor\GlobalSections::POST_TYPE ){
			return;
		}

		if( self::GSTYPE !== get_field('gs_type', $post_id) ){
			return;
		}

		delete_transient( implode('_', [\ReyCore\Ajax::AJAX_TRANSIENT_NAME, self::AJAX_LAZY_ACTION, $post_id] ) );
	}

	/**
	 * Checks if there are published Off-canvas panel global sections
	 */
	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Offcanvas Panels (Global Sections)', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds custom Elementor built side panels which can be triggered from everywhere.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/off-canvas-global-sections/'),
			'video' => true
		];
	}

	public function module_in_use(){
		return ! empty($this->get_offcanvas_panels());
	}

}
