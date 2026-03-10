<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class TaskBase
{
	protected $args = [];

	protected $errors = [];

	protected $importer;

	protected $offset_data = [
		'task'   => '',
		'offset' => 0,
		'status' => null,
	];

	protected $notices = [];

	protected $map = [];

	public function __construct($importer){
		$this->importer = $importer;
	}

	public function get_id(){}

	public function get_status(){}

	public function run(){}

	public function get_errors(){
		return implode(', ', $this->errors);
	}

	public function add_error($error){
		return $this->errors[] = $error;
	}

	public function get_notices(){
		return $this->notices;
	}

	public function add_notice($data){
		$this->notices[] = $data;
	}

	public function get_offset(){
		return $this->offset_data;
	}

	public function set_offset( $task, $offset, $status = null ){
		$this->offset_data = [
			'task' => $task,
			'offset' => $offset,
			'status' => $status,
		];
	}

	public function maybe_skip( $type ){

		$config = get_transient('rey_demo_config');

		if( false !== $config && isset($config['content']) && ! in_array($type, $config['content'], true) ){
			return true;
		}

		return false;
	}

	public function iterate_data( $data, $callback ){

		$offset = isset($this->importer->task_data['offset']) ? absint($this->importer->task_data['offset']) : 0;

		$i = 0;

		foreach ($data as $key => $value) {

			if( $i++ < $offset ) {
				continue;
			}

			if( $i > ( $offset + $this->importer::get_items_per_task() ) ) {
				$status = sprintf('%s (%s / %s)', static::get_status(), ($offset + $this->importer::get_items_per_task()), count($data));
				static::set_offset( static::get_id(), $offset + $this->importer::get_items_per_task(), $status );
				break;
			}

			call_user_func( $callback, $key, $value );

		}
	}

}
