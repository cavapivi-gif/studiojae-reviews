<?php
namespace ReyCore\Modules\GridList;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	public function __construct()
	{
		// NOT RUNNING
		return;

		parent::__construct();

		add_action( 'reycore/customizer/control=loop_grid_layout', [ $this, 'add_customizer_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=loop_grid_layout', [$this, 'add_grid_types'], 20, 2);
		add_action( 'reycore/customizer/control=loop_gap_size_v2', [$this, 'remove_grid_gap'], 20, 2);
		add_action( 'wp', [$this, 'init'] );
	}

	public function init()
	{
		$this->get_grid_type();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );

		// force short desc
		// force default product skin
		// force compare / wishlist positions

		$this->settings = apply_filters('reycore/module/grid_list', [
			'force_short_description' => true
		]);
	}

	public function enqueue_scripts(){

	}

	public function script_params($params)
	{
		return $params;
	}

	function add_grid_types($control_args, $section){
		$current_control = $section->get_control($control_args['settings']);
		$current_control['choices']['list'] = esc_html__( 'List', 'rey-core' );
		$current_control['choices']['list_full'] = esc_html__( 'List (product summary)', 'rey-core' );
		$section->update_control( $current_control );
	}

	function remove_grid_gap($control_args, $section){

		$current_control = $section->get_control($control_args['settings']);

		$current_control['active_callback'] = [
			[
				'setting'  => 'loop_grid_layout',
				'operator' => '!=',
				'value'    => 'list',
			],
			[
				'setting'  => 'loop_grid_layout',
				'operator' => '!=',
				'value'    => 'list_full',
			],
		];

		$section->update_control( $current_control );

	}

	function add_customizer_options($control_args, $section){

		$section->add_control( [
			'type'        => 'custom',
			'settings'    => 'loop_grid_layout__list_notice',
			'default'     => esc_html__('All "Products per row" settings will be overriden in List grid layout.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'loop_grid_layout',
					'operator' => 'in',
					'value'    => ['list', 'list_full'],
				],
			],
		] );

	}

	public function get_grid_type(){
		$this->type = get_theme_mod('loop_grid_layout', 'default');
	}

	public function is_enabled() {
		return in_array($this->type, ['list', 'list_full'], true);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Support for List catalog display', 'Module name', 'rey-core'),
			'description' => esc_html_x('Transforms a regular product grid, into a one column vertical list of products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
