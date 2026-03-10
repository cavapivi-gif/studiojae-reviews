<?php
namespace ReyCore\Modules\CardLoop;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LoopTemplates extends \ReyCore\Elementor\LoopTemplatesBase
{

	public function get_key(){
		return 'card_pdp_loop';
	}

	public function get_title(){
		return esc_html__('Loop Template (Card Global Section)', 'rey-core');
	}

	public function get_option_name(){
		return 'pdp_loop_template';
	}

	public function get_option_title(){
		return esc_html__( 'Select Card Global Section', 'rey-core' );
	}

	public function get_option_help(){
		return sprintf(__( 'Choose a Card Global section or <a href="%s" target="_blank">visit all</a>.', 'rey-core' ), admin_url('edit.php?post_status=all&post_type=rey-global-sections&rey_gs_type=card'));
	}

	public function get_ajax_method(){
		return 'get_pdp_loop_templates';
	}

	public function get_css_class(){
		return 'r-loop-item-';
	}

	public function get_query_pt_args(){
		return [
			'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
			'meta_key' => 'gs_type',
			'meta_value' => 'card',
		];
	}

	public function get_content( $template_id ){
		echo \ReyCore\Elementor\GlobalSections::do_section($template_id, false, true);
	}

	public function register_actions( $ajax_manager ) {

		$ajax_manager->register_ajax_action( $this->get_ajax_method(), function(){

			$items = [
				0 => 'Disabled'
			];

			foreach (\ReyCore\Elementor\GlobalSections::get_global_sections('card') as $template_id => $name) {
				$items[$template_id] = sprintf('%s (%s)', $name, get_post_status($template_id));
			}

			return $items;

		}, 1 );
	}

}
