<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/source-wpzoom.php';
require_once __DIR__ . '/source-rey.php';

use \ReyCore\Elementor\Widgets\WPZoomInsta\Base as ZoomInsta;
use \ReyCore\Elementor\Widgets\ReyInsta\Base as ReyInsta;

class Instagram extends \ReyCore\Elementor\WidgetsBase {

	public $_items = [];
	public $_errors = [];
	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'instagram',
			'title' => __( 'Instagram Feed', 'rey-core' ),
			'icon' => 'eicon-instagram-gallery',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [];
	}

	protected function register_skins() {

		foreach ([
			'SkinShuffle',
		] as $skin) {
			$skin_class = __CLASS__ . '\\' . $skin;
			$this->add_skin( new $skin_class( $this ) );
		}

	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#instagram');
	}

	public function get_token_help(){
		return sprintf( __( 'Please read <strong><a href="%s" target="_blank">this article</a></strong> to find out how to generate an Instagram Access Token.', 'rey-core' ) , reycore__support_url('kb/add-instagram-feeds/#generate-access-tokens') );
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
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$supports_zoom_insta = ZoomInsta::getInstance()->add_control( $this );

		if( ! $supports_zoom_insta ) {

			if( ! ReyInsta::get_token() ){

				$this->add_control(
					'connect_msg',
					[
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => sprintf(__('Instagram Access Token is missing. Go to <a href="%s" target="_blank">Rey Settings > Integrations > Instagram</a> to add the token. %s', 'rey-core'), admin_url( 'admin.php?page=' . REY_CORE_THEME_NAME . '-settings'), $this->get_token_help() ),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
					]
				);

			}

		}

		$this->add_control(
			'limit',
			[
				'label' => __( 'Limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 20,
				'step' => 1,
			]
		);

		$this->add_control(
			'offset',
			[
				'label' => __( 'Offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
			]
		);

		$this->add_responsive_control(
			'hide_last',
			[
				'label' => __( 'Hide Last Nth items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 10,
				'step' => 1,
			]
		);

		$this->add_responsive_control(
			'per_row',
			[
				'label' => __( 'Items per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 7,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta' => '--items-per-row: {{VALUE}};',
				],
				'tablet_default' => 4,
				'mobile_default' => 3,
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => esc_html__( 'Gap', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta' => '--gap: {{VALUE}}px;',
				],
				'tablet_default' => 20,
				'mobile_default' => 10,
			]
		);

		$this->add_control(
			'img_size',
			[
				'label' => __( 'Image Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'low_resolution',
				'options' => [
					'thumbnail'  => __( 'Thumbnail ( 150x150px )', 'rey-core' ),
					'low_resolution'  => __( 'Low Resolution ( 320x320px )', 'rey-core' ),
					'standard_resolution'  => __( 'Standard Resolution ( 640x640px )', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link To', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'insta',
				'options' => [
					'insta'  => __( 'Instagram Page', 'rey-core' ),
					'image'  => __( 'Image Lightbox', 'rey-core' ),
					'url'  => __( 'Caption URL', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'caption_url_target',
			[
				'label' => __( 'Caption URL Target', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '_self',
				'options' => [
					'_self'  => __( 'Same window', 'rey-core' ),
					'_blank'  => __( 'New window', 'rey-core' ),
				],
				'condition' => [
					'link' => ['url'],
				],
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label' => esc_html__( 'Lazy Load', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
			] );

		$this->add_control(
			'lazy_load_trigger',
			[
				'label' => esc_html__( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'scroll',
				'options' => [
					'scroll'  => esc_html__( 'On Scroll', 'rey-core' ),
					'click'  => esc_html__( 'On Click', 'rey-core' ),
					'mega-menu'  => esc_html__( 'On Mega Menu display', 'rey-core' ),
				],
				'condition' => [
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_click_trigger',
			[
				'label' => esc_html__( 'Click Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: .custom-unique-selector', 'rey-core' ),
				'condition' => [
					'lazy_load!' => '',
					'lazy_load_trigger' => 'click',
				],
				'wpml' => false,
			]
		);

		$this->add_control(
			'demo_items',
			[
				'label' => esc_html__( 'Items JSON (Fallback)', 'rey-core' ),
				'description' => esc_html__( 'Mostly used for demo purposes. This control is used when you don\'t have an Instagram account connected', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => '{ .. }',
				'separator' => 'before',
				'wpml' => false,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'top_spacing',
			[
				'label'       => esc_html__( 'Top-Spacing Items', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 2, 3, 4',
				'description' => __( 'Adds a top-spacing margin for specific items. Add item index number separated by comma.', 'rey-core' ),
				'condition' => [
					'_skin' => [''],
				],
				'wpml' => false,
			]
		);

		$this->add_responsive_control(
			'radius',
			[
				'label' => __( 'Corner Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Shuffled
		$this->add_control(
			'enable_box',
			[
				'label' => __( 'Display Username Box', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text',
			[
				'label'       => __( 'Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for username.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_url',
			[
				'label'       => __( 'URL', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for your Instagram profile.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_position',
			[
				'label' => __( 'Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 20,
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'hide_box_mobile',
			[
				'label' => __( 'Hide Username Box on Mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->end_controls_section();
	}

	public function query_items() {

		$def_items = [];

		if(
			isset($this->_settings['demo_items']) &&
			!empty($this->_settings['demo_items']) &&
			( $settings_demo_items = json_decode($this->_settings['demo_items'], true) )
		){
			$def_items = $settings_demo_items;
		}

		// used for demos
		$this->_items = apply_filters('reycore/elementor/instagram/data', $def_items, $this->get_id() );

		// if WPZoom Insta is active,
		// just get items
		if( ($zoominsta = ZoomInsta::getInstance()) && $zoominsta->is_active() ){
			$result = $zoominsta->query_items( array_merge($this->_settings, [
				'limit' => $this->_settings['limit'] + absint($this->_settings['offset'] ?? 0),
			]) );
		}

		// rely on Rey's token
		else {
			$result = ReyInsta::getInstance()->query_items( array_merge($this->_settings, [
				'limit' => $this->_settings['limit'] + absint($this->_settings['offset'] ?? 0),
			]), $this->get_id() );
		}

		if( isset( $result['items'] ) ){
			$this->_items = $result['items'];
		}

		if( ! empty($this->_items['items']) ){
			$this->_items['items'] = array_slice($this->_items['items'], absint($this->_settings['offset'] ?? 0), $this->_settings['limit']);
		}

		if( isset( $result['errors'] ) ){
			$this->_errors = $result['errors'];
		}

	}

	/**
	 * Output errors if widget is misconfigured and current user can manage options (plugin settings).
	 *
	 * @return void
	 */
	protected function display_errors( $message ) {

		if ( current_user_can( 'edit_theme_options' ) ) {

			?>
			<p class="text-center">
				<?php echo $message ?>
			</p>

            <?php if ( ! empty( $this->_errors ) ): ?>
                <ul>
					<?php foreach ( $this->_errors as $error ): ?>
                        <li class="text-center"><?php echo $error; ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
		<?php
		} else {
			echo "&#8230;";
		}
	}

	public function get_insta_items() {
		return $this->_items;
	}

	public function get_url($item = []) {
		if( empty($item) ) {
			return;
		}

		// Default Instagram URL
		$url = [
			'url' => $item['link'],
			'attr' => 'target="_blank"'
		];

		// Instagram IMAGE
		if( 'image' == $this->_settings['link'] ){
			$url = [
				'url' => $item['image-url'],
				'attr' => 'data-elementor-open-lightbox="yes"'
			];
		}

		// Instagram Caption URL
		// gets first link, if not, get default
		elseif( 'url' == $this->_settings['link'] ){
			$matches = [];

			$regex = '/https?\:\/\/[^\" ]+/i';

			if( isset($item['image-caption']) && ($caption = $item['image-caption']) ){
				preg_match($regex, $caption, $matches);
			}

			if( !empty($matches) ){
				$url = [
					'url' => $matches[0],
					'attr' => 'data-caption-url target="'. $this->_settings['caption_url_target'] .'"'
				];
			}
		}

		return $url;
	}

	public function render_items(){

		if( empty($this->_items['items']) ){
			return;
		}

		$top_spacing = array_map( 'trim', explode( ',', $this->_settings['top_spacing'] ) );
		$anim_class =  '';

		foreach ($this->_items['items'] as $key => $item) {

			$css_classes = [
				'rey-elInsta-item',
				in_array( ($key + 1), $top_spacing) ? '--spaced' : '',
				$anim_class
			];

			foreach ([
				'lg' => '',
				'md' => '_tablet',
				'sm' => '_mobile',
			] as $device => $control_key) {

				$hide_last = ! empty($this->_settings['hide_last' . $control_key]) ? $this->_settings['hide_last' . $control_key] : 0;

				if( $hide_last && $key >= (count($this->_items['items']) - $hide_last) ){
					$css_classes[] = '--dnone-' . $device;
				}
			}

			$link = $this->get_url($item);

			printf('<div class="%s">', implode(' ', $css_classes) );
				echo '<a href="'. $link['url'] .'" rel="noreferrer" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';

				$img_attributes = [
					'src' => $item['image-url'] ? $item['image-url'] : $item['original-image-url'],
					'alt' => $item['image-caption'],
				];

				// Add `loading` attribute.
				if ( wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ) {
					$img_attributes['loading'] = 'lazy';
				}

				printf( '<img class="rey-instaItem-img" %s>', reycore__implode_html_attributes($img_attributes) );

				echo '</a>';
			echo '</div>';
		}

	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-elInsta',
			'rey-elInsta--skin-' . ($this->_settings['_skin'] ? $this->_settings['_skin'] : 'default'),
		] );

		$this->add_render_attribute( 'wrapper', 'data-image-size', $this->_settings['img_size'] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php

		if ( empty( $this->_items ) && current_user_can('administrator') ) {

			$error_message = '';

			if( ($zoominsta = ZoomInsta::getInstance()) && $zoominsta->is_active() ){
				$error_message = $zoominsta->get_misconfigured_message();
			}

			else {
				if( isset($this->_settings['rey_access_token']) && ! $this->_settings['rey_access_token'] ){
					$error_message = esc_html__('Missing Instagram Access Token.', 'rey-core') . ' ' . $this->get_token_help();
				}
			}

			$this->display_errors($error_message);

		}

	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		do_action('reycore/elementor/instagram/before_render');

		if( $this->lazy_start() ){
			return;
		}

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->query_items();
		$this->render_start();
		$this->render_items();
		$this->render_end();

		$this->lazy_end();
	}

	public function lazy_start(){

		if( ! isset($this->_settings['lazy_load']) ){
			return;
		}

		// Initial Load (not Ajax)
		if( '' !== $this->_settings['lazy_load'] &&
			! wp_doing_ajax() &&
			! ( reycore__elementor_edit_mode() ) ){

			$qid = (isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids'])) ? end($gs_ids) : get_queried_object_id();

			$config = [
				'element_id' => $this->get_id(),
				'skin' => $this->_settings['_skin'],
				'trigger' => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger'] : 'scroll',
				'qid' => apply_filters('reycore/elementor/instagram/lazy_load_qid', $qid),
				'options' => apply_filters('reycore/elementor/instagram/lazy_load_options', []),
				'cache' => false,
			];

			if( 'click' === $this->_settings['lazy_load_trigger'] ){
				$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
			}

			$this->add_render_attribute( '_wrapper', 'data-lazy-load', wp_json_encode( $config ) );

			echo '<div class="__lazy-loader"></div>';

			reycore_assets()->add_scripts(['reycore-elementor-elem-lazy-load', 'reycore-widget-instagram-scripts']);

			do_action('reycore/elementor/instagram/lazy_load_assets', $this->_settings);

			return true;
		}

		return false;
	}

	public function lazy_end(){

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
