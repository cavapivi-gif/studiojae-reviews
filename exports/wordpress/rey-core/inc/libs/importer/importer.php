<?php
namespace ReyCore\Libs\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Importer
{

	public $upload_dir;
	public $upload_dir_url;
	public $upload_dir_path;
	public $upload_dir_path_tr;
	protected $current_task;
	protected $tasks = [];
	public $task_data = [];
	public $demo_config = [];

	const ITEMS_PER_TASK = 15;

	const TRANSIENT_EXPIRATION = 10 * MINUTE_IN_SECONDS;

	public function __construct(){

		$this->upload_dir      = wp_upload_dir();
		$this->upload_dir_url  = $this->upload_dir['baseurl'];

		$this->upload_dir_path = $this->upload_dir['basedir'];
		$this->upload_dir_path_tr = trailingslashit($this->upload_dir_path);

		$this->setup();
	}

	public function setup(){

		foreach (self::base_tasks() as $task) {

			// get the task class name
			$class_name =  '\\ReyCore\\Libs\\Importer\\Tasks\\' . implode('', array_map(function($item){
				return ucfirst($item);
			}, explode('-', $task)));

			// check if exists
			if( ! class_exists($class_name) ){
				continue;
			}

			$the_task = new $class_name( $this );

			$this->tasks[ $the_task->get_id() ] = $the_task;
		}

	}

	public static function get_items_per_task(){

		if( defined('REY_IMPORTER_ITEMS_PER_TASK') && REY_IMPORTER_ITEMS_PER_TASK ){
			return REY_IMPORTER_ITEMS_PER_TASK;
		}

		return apply_filters('reycore/importer/items_per_task', self::ITEMS_PER_TASK);
	}

	public static function base_tasks(){
		return [
			'init',
			'install-plugins',
			'download',
			'unzip',
			'get-data',
			'attachments',
			'attributes',
			'terms',
			'posts',
			'revolution',
			'terms-meta',
			'posts-meta',
			'comments',
			'widgets',
			'customizer',
			'config',
			'cleanup',
		];
	}

	public function run( $data ){

		if( empty($this->tasks) ){
			return ['error' => 'Something went wrong (no_tasks).'];
		}

		$this->task_data = $data;

		// in the begining
		if( ! (isset($this->task_data['task']) && ($task = $this->task_data['task'])) ){
			$task = 'init';
		}

		if( ! isset($this->tasks[$task]) ){
			return ['error' => 'Wrong task.'];
		}

		if( apply_filters('reycore/importer/maybe_fix_transients', false) ) {
			wp_using_ext_object_cache( false );
		}

		if( isset($this->task_data['config']) ){
			$this->demo_config = wp_parse_args($this->task_data['config'], [
				'content' => [],
				'plugins' => [],
				'reset'   => 0,
				'demo'    => '',
			]);
			set_transient('rey_demo_config', $this->demo_config, self::TRANSIENT_EXPIRATION);
		}

		$this->current_task = $this->tasks[$task];

		$this->current_task->run();

		do_action('reycore/import/task/' . $this->current_task->get_id(), $this);

		if( $error = $this->current_task->get_errors() ){
			return [
				'error' => $error,
				'revert' => true,
			];
		}

		$return = [
			'status' => $this->current_task->get_status(),
		];

		// set fractions
		if( 'init' === $task && $fractions = $this->get_fractions() ){
			$return['fractions'] = $fractions;
		}

		// repeat task
		if( ($offset = $this->current_task->get_offset()) && ! empty($offset['task']) ){
			$return['task'] = $offset['task'];
			$return['offset'] = $offset['offset'];
			if( ! empty($offset['status']) ){
				$return['status'] = $offset['status'];
			}
		}

		// get next task
		else {
			if( $next = $this->get_task() ){
				$return['task'] = $next->get_id();
				$return['status'] = $next->get_status();
			}
		}

		// get notices
		if( $notices = $this->current_task->get_notices() ){

			foreach ($notices as $n) {
				$notice_output[] = sprintf('Notice: %s.', $n, $this->current_task->get_id() );
			}

			$return['notices'] = implode("\n", $notice_output);
		}

		return $return;

	}

	public function get_task( $next = true ){

		$tasks = self::base_tasks();
		$flip = array_flip( $tasks );

		$index = $flip[ $this->current_task->get_id() ];

		$next ? $index++ : $index--;

		if( isset($tasks[$index]) && isset($this->tasks[ $tasks[$index] ]) ){
			return $this->tasks[ $tasks[$index] ];
		}
	}

	public function get_fractions(){

		$count = count(array_keys($this->tasks));

		if( isset($this->demo_config['plugins']) && ! empty($this->demo_config['plugins']) ){
			$count += count(array_keys($this->demo_config['plugins']));
		}

		$count--; // minus the plugins task (or init?)

		return $count;
	}

	public function reset_data(){

		if( ! ($data = Base::get_map()) ){
			return ['error' => 'Demo data is empty'];
		}

		$map = [
			'att'  => 'wp_delete_attachment',
			'term' => 'wp_delete_term',
			'post' => 'wp_delete_post',
			'attr' => 'wc_delete_attribute',
			'widget' => '',
		];

		$deleted = 0;

		foreach ($data as $uid => $id) {

			$uid_parts = explode('_', $uid);
			$type = $uid_parts[0];

			if( ! isset($map[$type]) ){
				continue;
			}

			if( $map[$type] ){
				if( ! function_exists($map[$type]) ){
					continue;
				}
			}

			$delete = false;

			if( 'widget' === $type ){

				$w_data = explode(':', $id);
				$sidebars_widgets = get_option( 'sidebars_widgets', [] );

				foreach ($sidebars_widgets as $sidebar_id => $widgets) {
					if( $w_data[0] === $sidebar_id){
						foreach ($widgets as $i => $widget_instance_id) {
							if( $w_data[1] === $widget_instance_id){
								unset($sidebars_widgets[$sidebar_id][$i]);
								$delete = true;
							}
						}
					}
				}

				update_option( 'sidebars_widgets', $sidebars_widgets );
			}

			// needs taxonomy
			elseif( 'term' === $type ){
				$term = get_term_by('term_taxonomy_id', $id);
				if( ! is_wp_error($term) && isset($term->taxonomy) ){
					$delete = call_user_func($map[$type], $id, $term->taxonomy);
				}
			}

			else {
				$delete = call_user_func($map[$type], $id);
			}

			if( $delete && ! is_wp_error($delete) ){
				$deleted++;
			}
		}

		Base::reset_map();

		return sprintf('Deleted %d from a total of %d items', $deleted, count($data));
	}
}
