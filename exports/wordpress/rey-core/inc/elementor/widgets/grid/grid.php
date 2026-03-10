<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('\ReyCore\Modules\Cards\CardElement')){
	return;
}

class Grid extends \ReyCore\Modules\Cards\CardElement {

	public $_settings = [];

	public $_items = [];

	public static function get_rey_config(){
		return [
			'id' => 'grid',
			'title' => __( 'Grid', 'rey-core' ),
			'icon' => 'eicon-gallery-grid',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['list', 'cards', 'posts', 'gallery', 'categories', 'portfolio'],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
				'assets/videos.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-grid-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#grid');
	}

	public function add_element_controls() {

		$this->selectors['grid'] = '{{WRAPPER}} .rey-gridEl';

		$this->controls__content();
		$this->get_source_controls();
		$this->controls__grid_settings();
		$this->controls__load_more();
		$this->controls__teaser();
		$this->controls__content_styles();
		$this->controls__media_styles();
		$this->controls__title_styles();
		$this->controls__subtitle_styles();
		$this->controls__label_styles();
		$this->controls__button_styles();
	}

	public function controls__grid_settings(){

		$this->start_controls_section(
			'section_grid_settings',
			[
				'label' => __( 'Grid Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'masonry',
			[
				'label' => esc_html__( 'Use Masonry', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'No', 'rey-core' ),
					'js'  => esc_html__( 'Yes - JavaScript based (ordered)', 'rey-core' ),
					'css'  => esc_html__( 'Yes - CSS based (CSS Columns, unordered)', 'rey-core' ),
				],
			]
		);

		$this->add_responsive_control(
			'grid_type',
			[
				'label' => esc_html__( 'Grid Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'normal',
				'options' => [
					'normal'  => esc_html__( 'Normal', 'rey-core' ),
					'list'  => esc_html__( 'Horizontal List', 'rey-core' ),
					'vlist'  => esc_html__( 'Vertical List', 'rey-core' ),
				],
				'condition' => [
					'masonry' => '',
				],
			]
		);

		$this->add_responsive_control(
			'per_row',
			[
				'label' => __( 'Items Per Row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'selectors' => [
					$this->selectors['grid'] => '--per-row: {{VALUE}}',
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => 4,
				'tablet_default' => 2,
				'mobile_default' => 1,
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => __( 'Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					$this->selectors['grid'] => '--gap: {{VALUE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'vgap',
			[
				'label' => __( 'Vertical Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					$this->selectors['grid'] => '--vgap: {{VALUE}}px;',
				],
				'condition' => [
					'grid_type' => ['normal', 'vlist'],
					'masonry' => '',
				],
			]
		);

		$this->add_responsive_control(
			'offset',
			[
				'label' => __( 'Offset', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 300,
				'step' => 1,
				'selectors' => [
					$this->selectors['grid'] => '--offset: {{VALUE}}px;',
				],
				'condition' => [
					'grid_type' => 'list',
					'masonry' => '',
				],
			]
		);

		$this->add_control(
			'enable_drag',
			[
				'label' => esc_html__( 'Enable Drag (desktop)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'grid_type' => 'list',
					'masonry' => '',
				],
			]
		);

		$this->end_controls_section();

	}

	public function render_start(){

		$classes = [
			'rey-gridEl',
			'--' . $this->_settings['source'],
		];

		if( '' === $this->_settings['masonry'] ){

			$desktop = $this->_settings[ 'grid_type' ];
			$classes[ 'grid_type' ] = sprintf('--type-%s', $desktop);

			$tablet = isset($this->_settings[ 'grid_type_tablet' ]) ? $this->_settings[ 'grid_type_tablet' ] : $desktop;
			$classes[ 'grid_type_tablet' ] = sprintf('--type-%s-%s', 'md', $tablet );

			$mobile = isset($this->_settings[ 'grid_type_mobile' ]) ? $this->_settings[ 'grid_type_mobile' ] : $tablet;
			$classes[ 'grid_type_mobile' ] = sprintf('--type-%s-%s', 'sm', $mobile );

		}
		else {

			if( 'js' === $this->_settings['masonry'] && ! reycore__elementor_edit_mode() ){
				reycore_assets()->add_scripts( ['masonry', 'reycore-widget-grid-scripts' ]);
			}

			$classes[ 'masonry' ] = '--masonry';
			$classes[ 'masonry_type' ] = '--msnry-' . esc_attr($this->_settings['masonry']);

		}

		$this->add_render_attribute( 'wrapper', [
			'class' => $classes,
			'style' => '--total:' . count($this->_items),
			'data-layout' => $this->_settings[$this->card_key],
		] );

		?><div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>><?php
	}

	public function render_end(){
		?></div><?php
	}

	public function render_grid(){

		if( empty($this->_items) ){
			return;
		} ?>

		<div class="__items <?php echo $this->_settings[ 'enable_drag' ] !== ''  && $this->_settings[ 'grid_type' ] === 'list' ? 'js-horizontal-drag' : '' ?>">
			<?php
			for ($i=0; $i < count($this->_items); $i++) {
				$this->item_key = $i;

				$this->parse_item();
				$this->render_item__start();
				$this->render_item();
				$this->render_item__end();

			} ?>
		</div>
		<?php

		$this->render_load_more_button();
	}

	public static function default_item_classes(){
		return [
			'item' => '__item',
		];
	}

	public function render_item__start(){

		$classes = self::default_item_classes();

		if( isset($this->_items[$this->item_key]['_id']) && $_id = $this->_items[$this->item_key]['_id'] ){
			$classes['_id'] = 'elementor-repeater-item-' . $_id;
		}

		if( isset($this->_items[$this->item_key]['item_classes']) ){
			$classes = array_merge($classes, $this->_items[$this->item_key]['item_classes']);
		}

		do_action('reycore/elementor/card/before_item', $this, 'grid');

		?><div class="<?php echo esc_attr(implode(' ', $classes)) ?>"><?php
	}

	public function render_item__end(){
		?></div><?php

		do_action('reycore/elementor/card/after_item', $this, 'grid');

	}


	public function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		if( ! ($this->_items = $this->get_items_data()) ){
			return;
		}

		if( $this->_settings[ 'enable_drag' ] !== '' && $this->_settings[ 'grid_type' ] === 'list' ){
			reycore_assets()->add_scripts( 'rey-horizontal-drag' );
		}

		$this->render_start();
		$this->render_grid();
		$this->render_end();
	}

}
