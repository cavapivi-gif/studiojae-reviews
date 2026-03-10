<?php
namespace ReyCore\Modules\GsVisibility;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	protected $date_format = 'd/m/Y g:i a';

	public function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		$this->add_fields();

		add_filter( 'reycore/elementor/gs_id', [$this, 'hide_gs'], 99);

	}

	private function fields_visibility( $gs_id ){

		$status = get_field('gs_enable_visibility', $gs_id);

		// if null means it's new (legacy)
		if( is_null( $status ) ){

			$status = 0;

			// check for values of the other fields.
			// If one of them has data, means the visibility option should be enabled (but not necesarily in use)
			if(
				get_field('start_date', $gs_id) ||
				get_field('end_date', $gs_id) ||
				get_field('show_per_login_status', $gs_id)
			){
				$status = 1;
			}

			update_field('gs_enable_visibility', $status, $gs_id);

		}

		return $status;
	}

	private function maybe_hide_gs( $gs_id ){

		if( ! $this->fields_visibility( $gs_id ) ){
			return;
		}

		$hide = [];

		$date_now_ob = new \DateTime( "now", wp_timezone() );
		$date_now = $date_now_ob->getTimestamp();

		if( $start_date = get_field('start_date', $gs_id) ){

			if( $startMakeFormat = \DateTime::createFromFormat( $this->date_format, $start_date, wp_timezone() ) ):

				$hide['start_date'] = true;

				$start_timestamp = $startMakeFormat->getTimestamp();

				if( $date_now > $start_timestamp ){
					$hide['start_date'] = false;
				}
			endif;

		}

		if( $end_date = get_field('end_date', $gs_id) ){

			if( $endMakeFormat = \DateTime::createFromFormat ($this->date_format, $end_date, wp_timezone() ) ):

				$hide['end_date'] = true;
				$end_timestamp = $endMakeFormat->getTimestamp();

				if( $date_now < $end_timestamp ){
					$hide['end_date'] = false;
				}
			endif;
		}

		if( $status = get_field('show_per_login_status', $gs_id) ){

			$hide['status'] = true;
			$logged_in = is_user_logged_in();

			if( $logged_in && 'logged' === $status ){
				$hide['status'] = false;
			}

			else if( ! $logged_in && 'logged_out' === $status ){
				$hide['status'] = false;
			}

		}

		return in_array(true, $hide, true);
	}

	public function hide_gs( $gs_id ){

		if( ! class_exists('\ACF') ){
			return $gs_id;
		}

		if( $this->maybe_hide_gs( $gs_id ) ){
			return false;
		}

		return $gs_id;
	}

	public function add_fields(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => 'group_5f058db5d6559',
			'title' => 'Global Section Visibility',
			'fields' => array(

				array(
					'key' => 'field_5f1520058dfb4',
					'label' => esc_html_x('Enable Visibility controls?', 'Backend setting label', 'rey-core'),
					'name' => 'gs_enable_visibility',
					'type' => 'true_false',
					'instructions' => esc_html_x('Enabling these options will allow you to control this global section visibility, per date or login status.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),

				array(
					'key' => 'field_5f058df1520b4',
					'label' => esc_html_x('Start Date', 'Backend setting label', 'rey-core'),
					'name' => 'start_date',
					'type' => 'date_time_picker',
					'instructions' => esc_html_x('Automatically show this global section when this date has started.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => [
						[
							[
								'field' => 'field_5f1520058dfb4',
								'operator' => '!=',
								'value' => 0,
							]
						]
					],
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'display_format' => $this->date_format,
					'return_format' => $this->date_format,
					'first_day' => 1,
				),

				array(
					'key' => 'field_5f058e27520b5',
					'label' => esc_html_x('End Date', 'Backend setting label', 'rey-core'),
					'name' => 'end_date',
					'type' => 'date_time_picker',
					'instructions' => esc_html_x('Automatically hide this global section after this date.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => [
						[
							[
								'field' => 'field_5f1520058dfb4',
								'operator' => '!=',
								'value' => 0,
							]
						]
					],
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'display_format' => $this->date_format,
					'return_format' => $this->date_format,
					'first_day' => 1,
				),

				[
					'key' => 'field_5f058ec8e3781',
					'label' => esc_html_x('Show per login status', 'Backend setting label', 'rey-core'),
					'name' => 'show_per_login_status',
					'type' => 'select',
					'instructions' => esc_html_x('Select if you want to show this section to a specific group of users', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => [
						[
							[
								'field' => 'field_5f1520058dfb4',
								'operator' => '!=',
								'value' => 0,
							]
						]
					],
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'choices' => [
						'logged' => esc_html_x('Logged-in users', 'Backend setting label', 'rey-core'),
						'logged_out' => esc_html_x('Logged-out users (guests)', 'Backend setting label', 'rey-core'),
					],
					'default_value' => array(
					),
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				],
			),
			'location' => [
				[
					[
						'param' => 'post_type',
						'operator' => '==',
						'value' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
					]
				]
			],
			'menu_order' => 10,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Global Sections Visibility', 'Module name', 'rey-core'),
			'description' => esc_html_x('Control a Global Section\'s visibility, per date range or login status.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Global Sections'],
			// 'help'        => reycore__support_url('kb/custom-templates/'),
		];
	}

	public function module_in_use(){

		$post_ids = get_posts([
			'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'gs_enable_visibility',
					'value'   => '0',
					'compare' => '!='
				],
			]
		]);

		return ! empty($post_ids);
	}
}
