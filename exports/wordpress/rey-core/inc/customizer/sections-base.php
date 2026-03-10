<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

abstract class SectionsBase {

	protected $controls = [];

	protected $priorities = [];

	public function __construct(){
		$this->controls();
	}

	/**
	 * Section ID
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Section ID
	 *
	 * @return string
	 */
	public static function part_of(){}

	/**
	 * Section Title
	 *
	 * @return string
	 */
	abstract public function get_title();

	/**
	 * Controls to add
	 *
	 * @return void
	 */
	abstract public function controls();

	/**
	 * Controls to add (at the end of the section)
	 *
	 * @return void
	 */
	public function end_controls(){}

	/**
	 * Help URL
	 *
	 * @return string
	 */
	public function help_link(){}

	/**
	 * CSS Classes
	 *
	 * @return string
	 */
	public function get_css_class(){}

	/**
	 * Adds a separator before or after the section item
	 *
	 * @return string
	 */
	public function get_separator(){}

	/**
	 * Adds an icon to the section
	 *
	 * @return string
	 */
	public function get_icon(){}

	/**
	 * Adds an icon to the section
	 *
	 * @return string
	 */
	public function get_breadcrumbs(){
		return [];
	}

	/**
	 * Adds a title before the section item in the list
	 *
	 * @return string
	 */
	public function get_title_before(){}

	/**
	 * Adds a title after the section item in the list
	 *
	 * @return string
	 */
	public function get_title_after(){}

	/**
	 * Outputs a title before a section
	 *
	 * @param array $section_args
	 * @return void
	 */
	public function add_title_before( $section_args ){
		$this->add_section_title( 'before', $section_args);
	}

	/**
	 * Outputs a title afte a section
	 *
	 * @param array $section_args
	 * @return void
	 */
	public function add_title_after( $section_args ){
		$this->add_section_title( 'after', $section_args);
	}

	/**
	 * Prepares the title to be added before or after a section
	 *
	 * @param string $position
	 * @param array $section_args
	 * @return void
	 */
	private function add_section_title( $position, $section_args ){

		// Title section
		if( ! ($title = call_user_func([$this, "get_title_" . $position])) ){
			return;
		}

		$_section_id = $section_args['id'] . "_title_$position";

		$_section_args              = $section_args;
		$_section_args['title']     = $title;
		$_section_args['css_class'] = "cannot-expand --title --title-{$position}";

		unset($_section_args['title_before']);
		unset($_section_args['title_after']);
		unset($_section_args['help']);
		unset($_section_args['icon']);

		\Kirki::add_section( $_section_id, $_section_args );

		\Kirki::add_field( Controls::CONFIG_KEY, [
			'type'        => 'custom',
			'settings'    => $_section_id . '_placeholder',
			'default'     => '',
			'section'     => $_section_id,
		] );

	}

	/**
	 * Help extra description
	 *
	 * @return string
	 */
	public function help_extra_text(){}

	/**
	 * When trying to only append controls
	 *
	 * @return bool
	 */
	public function prevent_creating_section(){}

	/**
	 * Section meets requirements
	 *
	 * @return boolean
	 */
	public function can_load(){
		return true;
	}

	/**
	 * Run code on `customize_register` hook
	 *
	 * @return void
	 */
	public function customize_register(){}

	/**
	 * Section's priority
	 *
	 * @return int
	 */
	public function get_priority(){
		return 100;
	}

	public function get_section_id(){
		return ($section_part = static::part_of()) ? $section_part : static::get_id();
	}

	public function print_controls( $section_id, $sub_section_id = '' ){

		if( $sub_section_id ){
			$this->section_hooks( $sub_section_id );
		}
		else {
			$this->section_hooks( $section_id );
		}

		$active_group_conditionals = [];
		$is_accordion_item = false;

		foreach ($this->controls as $control_id => $control_args) {

			if( $control_args['section'] !== $section_id ){
				continue;
			}

			// set priorities
			if( ! isset($control_args['priority']) || (isset($control_args['priority']) && '' === $control_args['priority'] ) ){

				$priorities_section = $control_args['section'];

				// start with 20
				if( ! isset( $this->priorities[ $priorities_section ] ) ){
					$this->priorities[ $priorities_section ] = 20;
				}
				// add 10 for each control
				else {
					$this->priorities[ $priorities_section ] += 10;
				}

				$control_args['priority'] = $this->priorities[ $priorities_section ];

			}

			/**
			 * Grouping
			 */
			if( 'rey_group_start' === $control_args['type'] ){
				if( isset($control_args['active_callback']) ){
					$active_group_conditionals = $control_args['active_callback'];
				}
			}
			else if( 'rey_group_end' === $control_args['type'] ){
				// assign conditionals
				$control_args['active_callback'] = $active_group_conditionals;
				// cleanup
				$active_group_conditionals = [];
			}
			else {
				if( ! empty($active_group_conditionals) ){
					if( isset($control_args['active_callback']) ){
						$control_args['active_callback'] = array_merge($control_args['active_callback'], $active_group_conditionals);
					}
					else {
						$control_args['active_callback'] = $active_group_conditionals;
					}
				}
			}

			/**
			 * Accordions
			 */

			$classes = [];

			if( 'rey_accordion_start' === $control_args['type'] ){
				$is_accordion_item = true;
				$classes[] = '--is-accordion';
				if( isset($control_args['open']) && $control_args['open'] ){
					$is_accordion_item = 'open';
					$classes[] = '--acc-open';
				}
			}
			else if( 'rey_accordion_end' === $control_args['type'] ){
				$is_accordion_item = false;
			}
			else {
				if( $is_accordion_item ){
					$classes[] = '--is-accordion';
					if( 'open' !== $is_accordion_item ){
						$classes[] = '--acc-hidden';
					}
				}
			}

			if( ! empty($classes) && ($the_classes = implode(' ', $classes)) ){
				if( isset($control_args['css_class']) ){
					$control_args['css_class'] .= ' ' . $the_classes;
				}
				else {
					$control_args['css_class'] = $the_classes;
				}
			}

			Controls::add_field( $control_args );

		}
	}

	public function add_control( $control_args ){

		if( ! isset($control_args['section']) ){
			$control_args['section'] = $this->get_section_id();
		}

		if( isset($control_args['section']) && '' === $control_args['section'] ){
			$control_args['section'] = $this->get_section_id();
		}

		$control_id = $control_args['settings'];

		// Parse tooltips
		if( isset($control_args['help'][0]) ){

			$control_args['help_tooltip'] = [
				'tip'       => '',
				// 'tip'       => '?',
				'style'     => 'qmark',
				'size'      => '',
				'clickable' => false,
			];

			$control_args['help_tooltip']['title'] = $control_args['label'];
			$control_args['help_tooltip']['text'] = $control_args['help'][0];

			$control_args['help_tooltip'] = wp_parse_args( $control_args['help'], $control_args['help_tooltip']);

			unset($control_args['help_tooltip'][0]);

		}

		/**
		 * Responsive
		 */
		if( isset($control_args['responsive']) && $control_args['responsive'] === true ){

			add_action( 'customize_render_control_' . $control_id, function( $customizer ) {
				$customizer->json['is_responsive'] = 'desktop';
			} );

			// avoid loophole
			unset($control_args['responsive']);

			$mq = [
				'tablet'	=> '@media (min-width: 768px) and (max-width: 1025px)',
				'mobile'	=> '@media (max-width: 767px)',
			];

			$devices = [
				'desktop' => $control_args,
			];

			foreach([
				'tablet',
				'mobile'
			] as $breakpoint){

				$device_control = $control_args;

				// assign media queries
				if( isset($control_args['output']) ){
					$output = $control_args['output'];
					unset($device_control['output']);
					foreach($output as $i => $rule){
						$rule['media_query'] = $mq[$breakpoint];
						$device_control['output'][$i] = $rule;
					}
				}

				// create setting name per device
				$device_control['settings'] = sprintf( '%s_%s', $control_id, $breakpoint);

				if( isset($control_args['default_' . $breakpoint]) ){
					$device_control['default'] = $control_args['default_' . $breakpoint];
				}

				// pass JSON attribute
				add_action( 'customize_render_control_' . $device_control['settings'], function( $customizer ) use ($breakpoint) {
					$customizer->json['is_responsive'] = $breakpoint;
				} );

				$devices[ $breakpoint ] = $device_control;

			}

			foreach ($devices as $breakpoint => $device_control_args) {
				$device_control_id = $device_control_args['settings'];
				$this->controls[ $device_control_id ] = $device_control_args;
				$this->run_controls_hooks($device_control_id, $device_control_args);
			}

		}
		else {
			$this->controls[ $control_id ] = $control_args;
			$this->run_controls_hooks($control_id, $control_args);
		}

	}

	public function run_controls_hooks($control_id, $control_args){
		do_action("reycore/customizer/control", $control_args, $this);
		do_action("reycore/customizer/control={$control_id}", $control_args, $this);
	}

	public function add_section_marker($marker){
		$section_id = static::get_id();
		do_action("reycore/customizer/section={$section_id}/marker={$marker}", $this);
	}

	public function add_control_before( $current_control, $new_control ){

		$current_control_id = $current_control['settings'];

		if( isset($this->controls[ $current_control_id ]) ){

			if( ! isset($new_control['section']) ){
				$new_control['section'] = $current_control['section'];
			}

			$new = [];

			foreach ($this->controls as $k => $value) {

				if ($k === $current_control_id) {
					$new[ $new_control['settings'] ] = $new_control;
				}

				$new[$k] = $value;
			}

			$this->controls = $new;
		}
	}

	public function get_control( $control_id ){

		if( is_array($control_id) ){
			$control_id = $control_id['settings'];
		}

		return $this->controls[ $control_id ];
	}

	public function remove_control( $control ){

		if( is_array($control) ){
			unset($this->controls[ $control['settings'] ]);
		}
		else {
			unset($this->controls[ $control ]);
		}

	}

	public function update_control( $control_args ){
		$this->controls[ $control_args['settings'] ] = $control_args;
	}

	/**
	 * Display Title in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	public function add_title( $title, $args = [] ){

		$args = wp_parse_args($args, [
			'settings'       => '',
			'type'           => 'rey-title',
			'description'    => '',
			'size'           => 'xs',
			'style_attr'     => '',
			'color'          => 'inherit',
			'upper'          => true,
			'separator'      => 'before',
			'separator_size' => 'thick',
		]);

		// Transfer
		foreach ([ 'size', 'style_attr', 'color', 'upper', 'separator', 'separator_size' ] as $value) {
			$args['title_args'][ $value ] = $args[ $value ];
			unset($args[ $value ]);
		}

		$args['label'] = $title;

		if( $args['settings'] === '' ){
			$args['settings'] = sprintf( 'title_%s', uniqid() );
			// $args['settings'] = sprintf('title_%s', str_replace('-','_', sanitize_title($args['title'])) );
		}

		$this->add_control( $args );
	}

	public function add_separator( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'section'     => '',
			'id'     => '',
		]);

		$section_id = $this->get_section_id();

		$this->add_control( [
			'type'     => 'custom',
			'settings' => sprintf('separator_%s_%s', $args['section'], uniqid() ),
			'section'  => $section_id,
			'default'  => '<hr class="--separator-simple"/>',
		] );
	}

	public function prepare_notice( $control_args ){

		$control_args = reycore__wp_parse_args($control_args, [
			'type'        => 'custom',
			'default'     => '',
			'section'     => '',
			'notice_type'     => 'warning', // raw, info
		]);

		if( $control_args['default'] === '' ){
			return;
		}

		$text = $control_args['default'];
		$slug = substr( strip_tags($text), 0, 15);

		$control_args['default'] = sprintf( '<p class="rey-cstInfo --%2$s">%1$s</p>', $text, $control_args['notice_type'] );

		if( ! isset($control_args['settings']) ){
			$control_args['settings'] = sprintf('notice_%s_%s', $control_args['section'], str_replace('-','_', sanitize_title($slug)) );
		}

		return $control_args;

	}

	public function add_notice( $control_args ){
		$this->add_control( $this->prepare_notice($control_args) );
	}

	public function add_control_bg_group( $args ){
		Controls::bg_group( $this, array_merge($args, [ 'section' =>  $this->get_section_id() ]) );
	}

	public function section_hooks( $id ){

		do_action('reycore/customizer/section', $id, $this);
		do_action("reycore/customizer/section=$id", $this);

		$this->end_controls();

	}

	public function add_help_link( $args ){

		$args = reycore__wp_parse_args($args, [
			'url'      => '',
			'priority' => 3000,
			'section'  => '',
			'end'      => ''
		]);

		$content = '<hr class="--separator --separator-top2x"><h2 class="rey-helpTitle">' . esc_html__('Need help?', 'rey-core') . '</h2>';
		$content .= '<p>'. sprintf( __('Read more about <a href="%s" target="_blank">these options from this panel</a>.', 'rey-core'), $args['url']) . '</p>' . $args['end'];

		$section_id = $this->get_section_id();

		$this->add_control( [
			'type'     => 'custom',
			'settings' => 'chelp_' . $section_id,
			'section'  => $section_id,
			'priority' => $args['priority'],
			'default'  => $content,
		] );

	}

	public function start_controls_group( $control_args ){

		$id = uniqid();

		if( isset($control_args['group_id']) && ($group_id = $control_args['group_id']) ){
			$id = $group_id;
		}

		$control_args['type'] = 'rey_group_start';
		$control_args['settings'] = 'group_start__' . $id;

		$this->add_control( $control_args );

		if( isset($GLOBALS['group_controls_start']) ){
			_doing_it_wrong( __METHOD__, sprintf('You need to end the "%s" group first, using `end_controls_group()`.', $GLOBALS['group_controls_start']), '2.3.0' );
		}

		$GLOBALS['group_controls_start'] = $id;
	}

	public function end_controls_group( $control_args = [] ){

		$control_args['type'] = 'rey_group_end';

		if( isset($GLOBALS['group_controls_start']) && ($id = $GLOBALS['group_controls_start']) ){
			$control_args['settings'] = 'group_end__' . $id;
			unset($GLOBALS['group_controls_start']);
		}
		else {
			_doing_it_wrong( __METHOD__, 'You need to start a group first, using `start_controls_group()`.', '2.3.0' );
		}

		$this->add_control( $control_args );

	}

	public function start_controls_accordion( $control_args ){

		$id = uniqid();

		if( isset($control_args['group_id']) && ($group_id = $control_args['group_id']) ){
			$id = $group_id;
		}

		$control_args['type'] = 'rey_accordion_start';
		$control_args['settings'] = 'accordion_start__' . $id;
		$control_args['default'] = 'accordion_start__' . $id;

		$this->add_control( $control_args );

		if( isset($GLOBALS['accordion_controls_start']) ){
			_doing_it_wrong( __METHOD__, sprintf('You need to end the "%s" accordion first, using `end_controls_accordion()`.', $GLOBALS['accordion_controls_start']), '2.3.0' );
		}

		$GLOBALS['accordion_controls_start'] = $id;
	}

	public function end_controls_accordion( $control_args = [] ){

		$control_args['type'] = 'rey_accordion_end';

		if( isset($GLOBALS['accordion_controls_start']) && ($id = $GLOBALS['accordion_controls_start']) ){
			$control_args['settings'] = 'accordion_end__' . $id;
			unset($GLOBALS['accordion_controls_start']);
		}
		else {
			_doing_it_wrong( __METHOD__, 'You need to start a accordion first, using `start_controls_accordion()`.', '2.3.0' );
		}

		$this->add_control( $control_args );

	}

}
