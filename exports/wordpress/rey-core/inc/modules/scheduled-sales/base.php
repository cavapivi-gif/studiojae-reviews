<?php
namespace ReyCore\Modules\ScheduledSales;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public $defaults = [];
	public $sale_products = [];

	public $data = [];

	const ASSET_HANDLE = 'reycore-scheduled-sales';

	public function __construct()
	{
		add_action('init', [$this, 'init']);
		add_action( 'reycore/acf/fields', [$this, 'add_acf_fields']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_loop_component']);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_filter( 'reycore/elementor/tag_archive/components', [$this, 'product_grid_components'], 10, 2);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
	}

	public function init(){

		new Customizer();

		$this->data = [
			'loop' => [
				'type' => get_theme_mod('scheduled_sale__loop_type', '')
			],
			'pdp' => [
				'type' => get_theme_mod('scheduled_sale__pdp_type', '')
			],
		];

		if( ! ($this->data['loop']['type'] || $this->data['pdp']['type']) ){
			return;
		}

		$this->data['loop'] = array_merge($this->data['loop'], [
			'position' => get_theme_mod('scheduled_sale__loop_pos', 'top_left'),
			'text'     => get_theme_mod('scheduled_sale__loop_text', ''),
			'bg'       => (bool) get_theme_mod('scheduled_sale__loop_bg_color', ''),
			'stretch'  => get_theme_mod('scheduled_sale__loop_stretch', false),
			'center'   => get_theme_mod('scheduled_sale__loop_center', false),
		]);

		$this->data['pdp'] = array_merge($this->data['pdp'], [
			'position' => get_theme_mod('scheduled_sale__pdp_pos', 'after_price'),
			'text'     => get_theme_mod('scheduled_sale__pdp_text', ''),
			'bg'       => (bool) get_theme_mod('scheduled_sale__pdp_bg_color', ''),
			'stretch'  => get_theme_mod('scheduled_sale__pdp_stretch', false),
			'center'   => get_theme_mod('scheduled_sale__pdp_center', false),
		]);

		new Pdp();

	}

	public function add_acf_fields( $acf_fields ){
		new AcfFields($acf_fields);
	}

	public function register_loop_component($base){
		$base->register_component( new Catalog );
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	function set_defaults(){

		if( ! empty($this->defaults) ){
			return;
		}

		$this->defaults = apply_filters('reycore/woocommerce/scheduled_sales/defaults', [
			'strings' => [
				'm' => [
					'singular' => esc_html_x('month', 'Scheduled sale badge "month" text', 'rey-core'),
					'plural' => esc_html_x('months', 'Scheduled sale badge "months" text', 'rey-core'),
					'abbr' => esc_html_x('mon.', 'Scheduled sale badge "month" short text', 'rey-core'),
				],
				'd' => [
					'singular' => esc_html_x('day', 'Scheduled sale badge "day" text', 'rey-core'),
					'plural' => esc_html_x('days', 'Scheduled sale badge "days" text', 'rey-core'),
				],
				'h' => [
					'singular' => esc_html_x('hour', 'Scheduled sale badge "hour" text', 'rey-core'),
					'plural' => esc_html_x('hours', 'Scheduled sale badge "hours" text', 'rey-core'),
				],
				'i' => [
					'singular' => esc_html_x('minute', 'Scheduled sale badge "minute" text', 'rey-core'),
					'plural' => esc_html_x('minutes', 'Scheduled sale badge "minutes" text', 'rey-core'),
					'abbr' => esc_html_x('min.', 'Scheduled sale badge "minutes" short text', 'rey-core'),
				],
				's' => [
					'singular' => esc_html_x('second', 'Scheduled sale badge "second" text', 'rey-core'),
					'plural' => esc_html_x('seconds', 'Scheduled sale badge "seconds" text', 'rey-core'),
					'abbr' => esc_html_x('sec.', 'Scheduled sale badge "seconds" short text', 'rey-core'),
				],
			],
			'hide_out_of_stock'   => true,
			'badge_use_icon'      => true,
			'badge_use_short'     => false,
			'countdown_use_short' => true,
			'badge_icon' => '<svg class="rey-icon" width="100%" height="100%" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M7.72461 0.0374277C7.48398 0.0131834 7.24229 0.0011146 7.00048 0.00122141H6.99322C6.59303 0.00784321 6.27305 0.335943 6.27646 0.736241V3.62164C6.2755 3.71883 6.29526 3.81506 6.33435 3.90402C6.45963 4.20222 6.76808 4.38058 7.08892 4.34053C7.45952 4.28499 7.73145 3.96341 7.72461 3.58885V1.49764C10.4284 1.85255 12.4746 4.11806 12.553 6.844C12.6314 9.56993 10.7189 11.9494 8.04 12.4591C5.36105 12.9688 2.70838 11.458 1.78026 8.89376C0.852143 6.32953 1.92317 3.47083 4.30776 2.14775C4.62475 1.96822 4.7574 1.57988 4.61642 1.24398L4.61557 1.24195C4.53653 1.05088 4.37975 0.902747 4.18451 0.8345C3.98938 0.76636 3.77439 0.78473 3.59357 0.884911C0.576484 2.56599 -0.763363 6.19656 0.43828 9.43451C1.63982 12.6726 5.02377 14.5501 8.40708 13.8559C11.7904 13.1618 14.1617 10.1036 13.9913 6.65399C13.8209 3.20446 11.1598 0.394791 7.72461 0.0374277Z" fill="currentColor"/> <path d="M4.35554 4.67456C4.64679 5.44151 5.59969 7.25235 6.31249 8.02336C6.7507 8.51807 7.49896 8.58429 8.01728 8.17438C8.27659 7.95682 8.43263 7.64047 8.44759 7.30234C8.46243 6.9643 8.33459 6.63546 8.09546 6.39601C7.34997 5.65063 5.45796 4.66302 4.66463 4.36184C4.57577 4.32841 4.47548 4.35041 4.40862 4.41791C4.34187 4.48552 4.32104 4.58602 4.35554 4.67456Z" fill="currentColor"/> </svg>',
			// the product must be on sale (have a sale price)
			'sale_is_mandatory' => true,
		]);

		$this->sale_products = wc_get_product_ids_on_sale();
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
		]);

	}

	public function get_env(){

		if( ! ($product = wc_get_product()) ){
			return;
		}

		$product_id = $product->get_id();

		return ($page_product_id = get_queried_object_id()) && $product_id === $page_product_id ? 'pdp' : 'loop';

	}

	public function render( $custom_args = [] ){

		if( ! ( $product = reycore_wc__get_product() ) ){
			return;
		}

		$target_product = null;
		$sale_to = null;
		$now = new \DateTime( "now", wp_timezone() );

		$this->set_defaults();

		// Evergreen sale
		if(
			class_exists('\ACF') &&
			($evergreen = get_field('evergreen_sale', $product->get_id() ))
			&& ! empty($evergreen['starting_from'])
			&& ! empty($evergreen['duration'])
		){
			$sale_to = \ReyCore\Libs\Countdown::get_evergreen(array_merge($evergreen, [
				'now' => $now
			]));
			if( $sale_to ){
				$target_product = $product;
			}
		}

		// Default scheduled sale on product
		else {

			if( 'simple' === $product->get_type() ){

				if( ! in_array( $product->get_id(), $this->sale_products, true ) && $this->defaults['sale_is_mandatory'] ){
					return;
				}

				if( ! ( $sale_to = $product->get_date_on_sale_to() ) ){
					return;
				}

				$target_product = $product;
			}
			// parent products
			else {

				if( $children = $product->get_children() ){

					foreach ($children as $_product_id) {

						if( ! in_array( $_product_id, $this->sale_products, true ) && $this->defaults['sale_is_mandatory']){
							continue;
						}

						if( $_product = reycore_wc__get_product($_product_id) ){

							if( ! ( $v_sale_to = $_product->get_date_on_sale_to() ) ){
								continue;
							}

							$sale_to = $v_sale_to;
							$target_product = $_product;
							break; // stop the loop
						}

					}
				}
			}

		}

		if( ! $target_product ){
			return;
		}

		if( ! $sale_to ){
			return;
		}

		if( $this->defaults['hide_out_of_stock'] && ! $target_product->is_in_stock() ){
			return;
		}

		$env = $this->get_env();
		$env_class = esc_attr('--' . $env);

		$args = [
			'to'   => $sale_to,
			'now'  => $now,
			'classes' => [
				$env_class
			]
		];

		if( $args['now']->getTimestamp() > $args['to']->getTimestamp() ){
			return;
		}

		$data = wp_parse_args($custom_args, $this->data[$env]);

		$type = $data['type'];
		$position = $data['position'];

		if( $bg = $data['bg'] ){
			$args['classes'][] = '--bg';
		}

		if( in_array($type, ['countdown', 'inline-countdown']) ){

			$countdown_config = array_merge($args, [
				'use_short' => $this->defaults['countdown_use_short'],
				'use_icon'  => $this->defaults['badge_use_icon'],
				'strings'   => $this->defaults['strings'],
				'icon'      => $this->defaults['badge_icon'],
			]);

			$countdown = new \ReyCore\Libs\Countdown($countdown_config);
		}

		$output = '';

		if( $type === 'badge' )
		{
			$output = $this->render_badge($args, $data);
		}

		elseif( $type === 'countdown' )
		{
			$output = $countdown->render($args);

			// override the data-attribute
			if( in_array( $position, ['top_left', 'top_right', 'bottom_left', 'bottom_right'] ) && 'loop' === $env ){
				$position = 'before_title';
			}
		}
		elseif( $type === 'inline-countdown' )
		{
			$args['inline'] = true;
			$output = $countdown->render($args);
		}

		if( $output )
		{
			$classes = [
				$env_class
			];

			if( $data['stretch'] ){
				$classes[] = '--stretch';
			}

			if( $data['center'] ){
				$classes[] = '--center';
			}

			printf('<div class="rey-schedSale %s" data-position="%s">%s</div>', esc_attr(implode(' ', $classes)), esc_attr( $position ), $output );

			reycore_assets()->add_styles(Base::ASSET_HANDLE);
		}
	}

	function render_badge( $args, $data ){

		$text = '';
		$text_strings = $this->defaults['strings'];
		$remaining = date_diff($args['now'], $args['to']);

		foreach(['m', 'd', 'h', 'i'] as $d){

			if( $remaining->invert ){
				break;
			}

			if( isset($remaining->{$d}) && ($count = $remaining->{$d}) && isset($text_strings[$d]['plural']) ){

				// plural or singular text
				$item_text = $count > 1 ? $text_strings[$d]['plural'] : $text_strings[$d]['singular'];

				if( $this->defaults['badge_use_short'] && isset($text_strings[$d]['abbr']) ){
					$item_text = $text_strings[$d]['abbr'];
				}

				$text .= sprintf(' <strong>%s%d %s</strong>',
					in_array($d, ['d', 'h']) ? '+' : '',
					$count,
					$item_text
				);

				// bail
				break;
			}

		}

		if( ! $text ){
			return;
		}

		$text_start = esc_html_x('Ends in', 'Scheduled sale badge default text', 'rey-core');

		if( $custom_text = $data['text'] ){
			$text_start = $custom_text;
		}

		$badge_icon = $this->defaults['badge_use_icon'] ? $this->defaults['badge_icon'] : '';

		return sprintf('<div class="rey-badgeSale %s">%s<span class="__text">%s</span></div>',
			esc_attr(implode(' ', $args['classes'])),
			$badge_icon,
			$text_start . $text
		);

	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_scheduled_sale',
			[
				'label' => esc_html__( 'Scheduled Sales', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$stack->end_injection();

	}

	public function product_grid_components( $components, $element ){

		// 'inherits' will bail
		if( isset( $element->_settings['hide_scheduled_sale'] ) && ($setting = $element->_settings['hide_scheduled_sale']) ){
			$components['scheduled_sale'] = $setting === 'no';
		}

		return $components;
	}


	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Scheduled Sales', 'Module name', 'rey-core'),
			'description' => esc_html_x('Will display a timer or countdown inside products, based on the scheduled pricing scheme.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/scheduling-sales-and-showing-countdowns-or-limited-sale-badge/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return ! ($this->data['loop']['type'] || $this->data['pdp']['type']);
	}
}
