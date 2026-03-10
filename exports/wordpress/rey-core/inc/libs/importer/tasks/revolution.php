<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Revolution extends TaskBase
{

	private $path;

	public function get_id(){
		return 'revolution';
	}

	public function get_status(){
		return esc_html__('Process Revolution sliders ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! isset($data['revolution']) ){
			return $this->add_notice( 'Cannot retrieve Revolution slider data. Most likely not added in this demo.' );
		}

		if( ! ($this->path = get_transient('rey_demo_temp_path')) ){
			return $this->add_notice( 'Cannot retrieve data path.' );
		}

		if( ! defined('RS_PLUGIN_PATH') ){
			return;
		}

		$this->iterate_data($data['revolution'], function( $uid, $id ){
			$this->add_rev($id, $uid);
		});

		Base::update_map($this->map);

	}

	public function add_rev($id, $uid){

		$file_path = sprintf('%s%s.zip', $this->path, $uid);

		// check new path
		if( ! Helper::fs()->exists( $file_path ) ){
			return $this->add_notice('Slider filepath doesnt exist.');
		}

		if( ! class_exists('\RevSliderSliderImport') ){
			require_once(RS_PLUGIN_PATH . 'admin/includes/template.class.php');
			require_once(RS_PLUGIN_PATH . 'admin/includes/functions-admin.class.php');
			require_once(RS_PLUGIN_PATH . 'admin/includes/folder.class.php');
			require_once(RS_PLUGIN_PATH . 'admin/includes/import.class.php');
		}

		$i = new \RevSliderSliderImport();
		$import_slider = $i->import_slider(true, $file_path);

		if( isset($import_slider['success'], $import_slider['sliderID']) && $import_slider['success'] ){

			$_slider = new \RevSliderSlider();
			$_slider->init_by_id($import_slider['sliderID']);

			$this->map[$uid] = $_slider->get_alias();
		}

	}

}
