<?php
namespace ReyCore\Compatibility\ElementorPro;

if ( ! defined( 'ABSPATH' ) ) exit;

class LoopTemplates extends \ReyCore\Elementor\LoopTemplatesBase
{

	public function get_key(){
		return 'epro_loop';
	}

	public function get_title(){
		return esc_html__('Loop Template (Elementor Pro)', 'rey-core');
	}

	public function get_option_name(){
		return 'epro_loop_template';
	}

	public function get_option_title(){
		return esc_html__( 'Select Loop Template', 'rey-core' );
	}

	public function get_option_help(){
		return sprintf(__( 'Choose a Loop template from the Theme Builder. <a href="%s" target="_blank">See all templates</a>.', 'rey-core' ), admin_url('edit.php?post_type=elementor_library&tabs_group=theme'));
	}

	public function get_ajax_method(){
		return 'get_epro_loop_templates';
	}

	public function get_css_class(){
		return 'e-loop-item-';
	}

	public function get_query_pt_args(){
		return [
			'post_type' => 'elementor_library',
			'meta_key' => '_elementor_template_type',
			'meta_value' => 'loop-item',
		];
	}

	public function get_content( $template_id ){

		if ( ! ($document = \Elementor\Plugin::$instance->documents->get( $template_id )) ) {
			return;
		}

		$document->print_content();
	}

	public function register_actions( $ajax_manager ) {

		$ajax_manager->register_ajax_action( $this->get_ajax_method(), function(){

			$quary_args = $this->get_query_pt_args();

			$templates = get_posts([
				'post_type'   => $quary_args['post_type'],
				'numberposts' => -1,
				'fields' => 'ids',
				'meta_query' => [
					[
						'meta_key'   => $quary_args['meta_key'],
						'meta_value' => $quary_args['meta_value'],
					]
				]
			]);

			$items = [
				0 => 'Disabled'
			];

			foreach ($templates as $template_id) {
				$items[$template_id] = sprintf('%s (%s)', get_the_title($template_id), get_post_status($template_id));
			}

			return $items;

		}, 1 );
	}

}
