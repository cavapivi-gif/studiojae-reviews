<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Controls {

	public static $controls = [];

	const CONFIG_KEY = 'rey_core_kirki';

	public function __construct(){

		add_action( 'customize_controls_print_scripts', [$this, 'add_customizer_templates'] );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public static function add_field( $args ){

		if( ! class_exists('\Kirki') ){
			return;
		}

		if( empty($args) ){
			return;
		}

		$control_id = $args['settings'];

		/**
		 * Deprecated hooks
		 * @remove in 2.4.0
		 */
		if( apply_filters("reycore/kirki_fields/disable_field={$control_id}", false) ){
			do_action("reycore/kirki_fields/replace_field", $control_id, $args);
			return;
		}
		$args = apply_filters("reycore/kirki_fields/field={$control_id}", $args);
		$args = apply_filters_deprecated("reycore/kirki_fields/field_type={$args['type']}", [$args], '2.3.0');

		do_action_deprecated("reycore/kirki_fields/before_field", [$control_id, $args], '2.3.0', "reycore/customizer/control");
		do_action_deprecated("reycore/kirki_fields/before_field={$control_id}", [$args], '2.3.0', "reycore/customizer/control={$control_id}");

		if(
			($legacy_sections = \ReyCore\Customizer\Base::legacy_sections()) &&
			isset( $legacy_sections[ $args['section'] ] ) &&
			($legacy_section = $legacy_sections[ $args['section'] ])
		){
			$args['section'] = $legacy_section;
		}

		$json = [];
		$css_classes = [];

		// add JS tooltip
		if( isset($args['help_tooltip']) && ($help = $args['help_tooltip']) ){
			$json['help'] = $help;
			$css_classes[] = '--has-tooltip';
		}

		// add css classes
		if( isset($args['css_class']) && ($css_class = $args['css_class']) ){
			$css_classes[] = $css_class;
		}

		// add css classes
		if( isset($args['deprecated']) && $args['deprecated'] ){
			$css_classes[] = '--deprecated';
		}

		// add separator
		if( isset($args['separator']) && ($separator = $args['separator']) ){
			$json['separator'] = $separator;
		}

		if( ! empty( $css_classes ) ){
			$json['css_class'] = implode(' ', $css_classes);
		}

		if( !empty($json) ){
			add_action( "customize_render_control_{$control_id}", function($customizer) use ($json){
				foreach ($json as $key => $value) {
					$customizer->json[$key] = $value;
				}
			} );
		}

		\Kirki::add_field( self::CONFIG_KEY, $args );

		self::$controls[$control_id] = $args;

		/**
		 * Deprecated hooks
		 * @remove in 2.4.0
		 */
		do_action_deprecated("reycore/kirki_fields/after_field={$control_id}", [$args], '2.3.0', "reycore/customizer/control={$control_id}");
		do_action_deprecated("reycore/kirki_fields/after_field", [$control_id, $args], '2.3.0', "reycore/customizer/control");

	}

	public function add_tooltip_markup() {
		?>
		<script type="text/html" id="tmpl-rey-customizer-tooltips-handler">
			<div class="rey-csTitleHelp-popWrapper">
				<span class="rey-csTitleHelp-title">{{{ data.title }}}</span>
				<div class="rey-csTitleHelp-pop --pop-{{{ data.style }}}">
					<span class="rey-csTitleHelp-label">{{ data.tip }}</span>
					<# var contentStyle = data.size ? 'min-width: ' + data.size + 'px;' : ''; #>
					<# contentStyle += ! data.clickable ? 'pointer-events: none;' : ''; #>
					<span class="rey-csTitleHelp-content" style="{{contentStyle}}">{{{ data.text }}}</span>
				</div>
			</div>
		</script>
		<?php
	}

	public function add_new_page_markup() {
		?>
		<script type="text/html" id="tmpl-rey-customizer-new-page">
			<div class="rey-newContent">

				<button type="button" class="__edit-link button-link" data-id="{{data.selected_id}}" data-url="{{data.edit_link}}">
					<?php echo esc_html__('Edit selected', 'rey-core') ?>
				</button>

				<button type="button" class="__add-link button-link">{{data.new_link}}</button>

				<div class="__form">
					<span class="screen-reader-text">{{data.placeholder}}</span>
					<input type="text" id="create-input{{data.id}}" class="__input" placeholder="{{data.placeholder}} &hellip;">
					<button type="button" class="button __add">{{data.button_text}}</button>
				</div>

			</div>
		</script>
		<?php
	}

	/**
	 * Add responsive handlers
	 *
	 * @since 1.3.5
	 **/
	public function add_responsive_handlers()
	{ ?>

		<script type="text/html" id="tmpl-rey-customizer-responsive-handler">
			<div class="rey-cst-responsiveHandlers">
				<span data-breakpoint="desktop"><i class="dashicons dashicons-desktop"></i></span>
				<span data-breakpoint="tablet"><i class="dashicons dashicons-tablet"></i></span>
				<span data-breakpoint="mobile"><i class="dashicons dashicons-smartphone"></i></span>
			</div>
		</script>

		<script type="text/html" id="tmpl-rey-customizer-typo-handler">
			<div class="rey-cstTypo-wrapper">
				<span class="rey-cstTypo-btn">
					<span class="dashicons dashicons-edit"></span>
					<span class="rey-cstTypo-ff">{{ data.ff }}</span>
					<span class="rey-cstTypo-fz">{{ data.fz }}</span>
					<span class="rey-cstTypo-fw">{{ data.fw }}</span>
				</span>
			</div>
		</script>
		<?php
	}


	/**
	 * Add customizer templates
	 *
	 * @since 2.3.0
	 **/
	public function add_customizer_templates() {
		$this->add_new_page_markup();
		$this->add_tooltip_markup();
		$this->add_responsive_handlers();
		\ReyCore\Admin::add_registration_overlays(['type' => 'customizer']);
	}

	/**
	 * Custom Background option group.
	 *
	 * @since 1.0.0
	 */
	public static function bg_group($section, $args = []){

		$defaults = [
			'settings' => 'bg_option',
			'section' => 'bg_section',
			'label' => esc_html__('Background', 'rey-core'),
			'description' => esc_html__('Change background settings.', 'rey-core'),
			'output_element' => '',
			'active_callback' => [],
			'color' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-color',
				'active_callback' => [],
			],
			'image' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-image',
				'active_callback' => [],
			],
			'repeat' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-repeat',
				'active_callback' => [],
			],
			'attachment' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-attachment',
				'active_callback' => [],
			],
			'size' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-size',
				'active_callback' => [],
			],
			'positionx' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-x',
				'active_callback' => [],
			],
			'positiony' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-y',
				'active_callback' => [],
			],
			'blend' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-blend-mode',
				'active_callback' => [],
			],
		];

		$args = reycore__wp_parse_args($args, $defaults);

		$has_image = [
			'setting'  => $args['settings']. '_img',
			'operator' => '!=',
			'value'    => '',
		];

		$active_callback[] = $has_image;

		if( $args['active_callback'] ){
			$active_callback[] = array_merge($active_callback, $args['active_callback']);
		}

		$priority = isset($args['priority']) ? $args['priority'] : '';

		$section->add_title( $args['label'], [
			'description' => $args['description'],
			'priority'    => $priority,
			'separator'   => 'none',
		]);

		/**
		 * IMAGE
		 */
		$section->add_control( [
			'type'        => 'image',
			'settings'    => $args['settings']. '_img',
			'default'     => $args['image']['default'],
			'priority'   => $priority,
			// 'transport'   => 'auto',
			'output'      => [
				[
					'element' => !empty( $args['image']['output_element'] ) ? $args['image']['output_element'] : $args['output_element'],
					'property' => $args['image']['output_property'],
					'value_pattern' => 'url($)'
				]
			],
			'active_callback' => $args['active_callback']
		] );

		/**
		 * REPEAT
		 */
		if( in_array('repeat', $args['supports']) ):
			$section->add_control( [
				'type'        => 'select',
				'settings'    => $args['settings']. '_repeat',
				'label'       => __( 'Background Repeat', 'rey-core' ),
				'default'     => $args['image']['default'],
				'choices'     => [
					'' => esc_html__('Default', 'rey-core'),
					'repeat' => esc_html__('Repeat', 'rey-core'),
					'no-repeat' => esc_html__('No Repeat', 'rey-core'),
					'repeat-x' => esc_html__('Repeat Horizontally', 'rey-core'),
					'repeat-y' => esc_html__('Repeat Vertically', 'rey-core'),
					'initial' => esc_html__('Initial', 'rey-core'),
					'inherit' => esc_html__('Inherit', 'rey-core'),
				],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['repeat']['output_element'] ) ? $args['repeat']['output_element'] : $args['output_element'],
						'property' => $args['repeat']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * ATTACHMENT
		 */
		if( in_array('attachment', $args['supports']) ):
			$section->add_control( [
				'type'        => 'select',
				'settings'    => $args['settings']. '_attachment',
				'label'       => __( 'Background Attachment', 'rey-core' ),
				'default'     => $args['attachment']['default'],
				'choices'     => [
					'' => esc_html__('Default', 'rey-core'),
					'scroll' => esc_html__('Scroll', 'rey-core'),
					'fixed' => esc_html__('Fixed', 'rey-core'),
					'local' => esc_html__('Local', 'rey-core'),
					'initial' => esc_html__('Initial', 'rey-core'),
					'inherit' => esc_html__('Inherit', 'rey-core'),
				],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['attachment']['output_element'] ) ? $args['attachment']['output_element'] : $args['output_element'],
						'property' => $args['attachment']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * SIZE
		 */
		if( in_array('size', $args['supports']) ):
			$section->add_control( [
				'type'        => 'select',
				'settings'    => $args['settings']. '_size',
				'label'       => __( 'Background Size', 'rey-core' ),
				'default'     => $args['size']['default'],
				'choices'     => [
					'' => esc_html__('Default', 'rey-core'),
					'auto' => esc_html__('Auto', 'rey-core'),
					'contain' => esc_html__('Contain', 'rey-core'),
					'cover' => esc_html__('Cover', 'rey-core'),
					'initial' => esc_html__('Initial', 'rey-core'),
					'inherit' => esc_html__('Inherit', 'rey-core'),
				],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['size']['output_element'] ) ? $args['size']['output_element'] : $args['output_element'],
						'property' => $args['size']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * POSITION
		 */
		if( in_array('position', $args['supports']) ):
			/**
			 * POSITION X
			 */
			$section->add_control( [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positionx',
				'label'       => __( 'Background Horizontal Position ', 'rey-core' ),
				'default'     => $args['positionx']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positionx']['output_element'] ) ? $args['positionx']['output_element'] : $args['output_element'],
						'property' => $args['positionx']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );

			/**
			 * POSITION Y
			 */
			$section->add_control( [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positiony',
				'label'       => __( 'Background Vertical Position ', 'rey-core' ),
				'default'     => $args['positiony']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positiony']['output_element'] ) ? $args['positiony']['output_element'] : $args['output_element'],
						'property' => $args['positiony']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * COLOR
		 */
		if( in_array('color', $args['supports']) ):
			$section->add_control( [
				'type'        => 'rey-color',
				'settings'    => $args['settings']. '_color',
				'label'       => __( 'Background Color', 'rey-core' ),
				'default'     => $args['color']['default'],
				'transport'   => 'auto',
				'priority'   => $priority,
				'choices'     => [
					'alpha' => true,
				],
				'output'      => [
					[
						'element' => !empty( $args['color']['output_element'] ) ? $args['color']['output_element'] : $args['output_element'],
						'property' => $args['color']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;


		/**
		 * BLEND
		 */
		if( in_array('blend', $args['supports']) ):
			$section->add_control( [
				'type'        => 'select',
				'settings'    => $args['settings']. '_blend',
				'label'       => __( 'Background Blend', 'rey-core' ),
				'default'     => $args['blend']['default'],
				'choices'     => [
					'' => esc_html__('Default', 'rey-core'),
					'normal' => esc_html__('Normal', 'rey-core'),
					'multiply' => esc_html__('Multiply', 'rey-core'),
					'screen' => esc_html__('Screen', 'rey-core'),
					'overlay' => esc_html__('Overlay', 'rey-core'),
					'darken' => esc_html__('Darken', 'rey-core'),
					'lighten' => esc_html__('Lighten', 'rey-core'),
					'color-dodge' => esc_html__('Color Dodge', 'rey-core'),
					'saturation' => esc_html__('Saturation', 'rey-core'),
					'color-burn' => esc_html__('Color burn', 'rey-core'),
					'hard-light' => esc_html__('Hard light', 'rey-core'),
					'soft-light' => esc_html__('Soft light', 'rey-core'),
					'difference' => esc_html__('Difference', 'rey-core'),
					'exclusion' => esc_html__('Exclusion', 'rey-core'),
					'hue' => esc_html__('Hue', 'rey-core'),
					'color' => esc_html__('Color', 'rey-core'),
					'luminosity' => esc_html__('Luminosity', 'rey-core'),
					'initial' => esc_html__('Initial', 'rey-core'),
					'inherit' => esc_html__('Inherit', 'rey-core'),
				],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['blend']['output_element'] ) ? $args['blend']['output_element'] : $args['output_element'],
						'property' => $args['blend']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;

		$section->add_control( [
			'type'        => 'custom',
			'settings'    => $args['settings']. '_end',
			'priority'    => $priority,
			'default'     => '<hr>',
		] );

	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( "create_new_gs", [$this, 'ajax__create_new_gs'], 1 );
		$ajax_manager->register_ajax_action( "get_pages", [$this, 'ajax__get_pages'], 1 );
		$ajax_manager->register_ajax_action( "create_new_page", [$this, 'ajax__create_new_page'], 1 );
	}

	public function ajax__get_pages( $data ){

		$args = [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1
		];

		if( isset($data['exclude']) && $data['exclude'] ){

			$args['exclude'][] = get_option( 'page_on_front' );
			$args['exclude'][] = get_option( 'page_for_posts' );

			if( function_exists('wc_get_page_id') ){
				foreach(['shop', 'checkout', 'cart', 'myaccount',] as $wcid){
					$args['exclude'][] = wc_get_page_id($wcid);
				}
			}
		}

		$pages = get_posts($args);

		if( empty($pages) ){
			return;
		}

		$page_list[] = esc_html__('- Select page -', 'rey-core');

		foreach ( $pages as $id) {
			$page_list[ $id ] = get_the_title( $id );
		}

		return $page_list;

	}

	public function ajax__create_new_page( $data ){

		if ( ! current_user_can('install_plugins') ) {
			return [
				'errors' => [ 'Operation not allowed!' ]
			];
		}

		$page_args = [
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_name'      => sanitize_title($data['title']),
			'post_title'     => $data['title'],
			'comment_status' => 'closed',
		];

		$page_id   = wp_insert_post( $page_args );

		if( ! $page_id ){
			return [
				'errors' => [ 'Couldn\'t create page!' ]
			];
		}

		return $page_id;

	}

	public function ajax__create_new_gs( $data ){

		if ( ! current_user_can('install_plugins') ) {
			return [
				'errors' => [ 'Operation not allowed!' ]
			];
		}

		if( ! in_array( $data['type'], ['header', 'footer'], true ) ){
			return [
				'errors' => [ 'Incorrect global section type!' ]
			];
		}

		$gs_data = [
			'post_status'    => 'publish',
			'post_type'      => \ReyCore\Elementor\GlobalSections::POST_TYPE,
			'post_name'      => sanitize_title($data['title']),
			'post_title'     => $data['title'],
			'comment_status' => 'closed',
		];

		$page_id   = wp_insert_post( $gs_data );

		if( ! $page_id ){
			return [
				'errors' => [ 'Couldn\'t create global section!' ]
			];
		}

		if( ! update_field('gs_type', $data['type'], $page_id) ){
			return [
				'errors' => [ 'Couldn\'t set the global section type!' ]
			];
		}

		return $page_id;

	}

	public function get_controls(){
		return self::$controls;
	}
}
