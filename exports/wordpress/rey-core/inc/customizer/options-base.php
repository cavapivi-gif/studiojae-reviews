<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

abstract class OptionsBase {

	protected $sections = [];

	public function __construct(){

		if( $this->can_load() ){

			$this->create_panel();
			$this->create_sections();
			$this->misc_controls();

		}

		return $this;
	}

	public function can_load(){
		return true;
	}

	abstract public static function get_id();
	abstract public function get_title();

	public function get_priority(){
		return 10;
	}

	/**
	 * CSS Classes
	 *
	 * @return string
	 */
	public function get_css_class(){}

	/**
	 * Adds a separator before or after the panel item
	 *
	 * @return string
	 */
	public function get_separator(){}

	/**
	 * Adds an icon to the panel
	 *
	 * @return string
	 */
	public function get_icon(){}

	/**
	 * Adds a title before the panel item in the list
	 *
	 * @return string
	 */
	public function get_title_before(){}

	/**
	 * Adds a title after the panel item in the list
	 *
	 * @return string
	 */
	public function get_title_after(){}


	/**
	 * Outputs a title before a section
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_title_before( $args ){
		$this->add_panel_title( 'before', $args);
	}

	/**
	 * Outputs a title afte a section
	 *
	 * @param array $args
	 * @return void
	 */
	public function add_title_after( $args ){
		$this->add_panel_title( 'after', $args);
	}

	/**
	 * Prepares the title to be added before or after a panel
	 *
	 * @param string $position
	 * @param array $panel_args
	 * @return void
	 */
	private function add_panel_title( $position, $panel_args ){

		// Title section
		if( ! ($title = call_user_func([$this, "get_title_" . $position])) ){
			return;
		}

		$_panel_id = $panel_args['id'] . "_title_$position";

		$_panel_args              = $panel_args;
		$_panel_args['title']     = $title;
		$_panel_args['css_class'] = "cannot-expand --title --title-{$position}";

		unset($_panel_args['icon']);
		unset($_panel_args['title_before']);
		unset($_panel_args['title_after']);
		unset($_panel_args['description']);

		\Kirki::add_panel( $_panel_id, $_panel_args );

		$_section_id = $_panel_id . '_section';

		\Kirki::add_section( $_section_id, [
			'panel' => $_panel_id
		] );

		\Kirki::add_field( Controls::CONFIG_KEY, [
			'type'        => 'custom',
			'settings'    => $_section_id . '_placeholder',
			'default'     => '',
			'section'     => $_section_id,
		] );

	}

	/**
	 * Add a panel description
	 *
	 * @return string
	 */
	public function get_description(){}

	/**
	 * Run code on `customize_register` hook
	 *
	 * @return void
	 */
	public function customize_register(){

		foreach ($this->sections as $section) {
			$section->customize_register();
		}

	}

	/**
	 * Eplicitly prevent creation of a panel
	 *
	 * @return void
	 */
	public function prevent_creating_panel(){}

	public function create_panel() {

		if( ! class_exists('\Kirki') ){
			return;
		}

		if( ! ($panel_id = static::get_id()) ){
			return;
		}

		if( ! $this->prevent_creating_panel() ):

			$panel_args = [
				'id'          => $panel_id,
				'title'       => $this->get_title(),
				'priority'    => absint($this->get_priority()),
				'type'        => 'kirki-rey-panel',
				'css_class'   => $this->get_css_class(),
				'separator'   => $this->get_separator(),
				'icon'        => $this->get_icon(),
				'description' => $this->get_description(),
			];

			$this->add_title_before($panel_args);

			\Kirki::add_panel( $panel_id, $panel_args );

			$this->add_title_after($panel_args);

		endif;

		$this->register_default_sections( $panel_id );

		do_action("reycore/customizer/panel=$panel_id", $this);

	}

	public function get_default_sections(){}

	public function register_default_sections( $panel_id ){

		foreach ( (array) $this->get_default_sections() as $section_id) {

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $section_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Customizer\Options\\' . ucfirst( $panel_id ) );

			if( ! class_exists($class_name) ){
				continue;
			}

			$this->register_section( new $class_name() );

		}
	}

	protected function create_sections(){

		if( ! class_exists('\Kirki') ){
			return;
		}

		foreach ( $this->sections as $section_id => $section) {

			// meet dependency
			if( ! $section->can_load() ){
				continue;
			}

			$panel_id = static::get_id();

			// it's a part of a section
			if( $main_section_id = $section->part_of() ){
				$section->print_controls( $main_section_id, $section_id );
				continue;
			}

			$section_args = [
				'id'           => $section_id,
				'title'        => $section->get_title(),
				'priority'     => absint($section->get_priority()),
				'panel'	       => static::get_id(),
				'css_class'    => $section->get_css_class(),
				'separator'    => $section->get_separator(), // before/after
				'icon'         => $section->get_icon(),
				'breadcrumbs'  => $section->get_breadcrumbs(),
				'type'         => 'rey-section',
				'help'         => [
					'url' => $section->help_link(),
					'end'     => ($end = $section->help_extra_text()) ? $end : '',
				],
			];

			// just help links for existing sections
			if( empty($section_args['title']) && ! empty($section_args['help']['url']) ){
				$section->add_help_link([
					'url'     => $section_args['help']['url'],
					'section' => $section_id,
					'end'     => $section_args['help']['end'],
				]);
				continue;
			}

			if( $section->prevent_creating_section() ){
				continue;
			}

			$section->add_title_before( $section_args );

			\Kirki::add_section( $section_id, $section_args );

			if( isset( $section_args['help'] ) ){
				$section_args['help']['section'] = $section_id;
				$section->add_help_link( $section_args['help'] );
			}

			$section->print_controls( $section_id );

			$section->add_title_after( $section_args );

		}

	}

	public function register_section( $section_class ){
		if( $section_id = $section_class::get_id() ){
			$this->sections[ $section_id ] = $section_class;
		}
	}

	public function misc_controls(){}

}
