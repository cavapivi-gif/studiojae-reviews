<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Init extends TaskBase
{
	public function get_id(){
		return 'init';
	}

	public function get_status(){
		return esc_html__('Initializing...', 'rey-core');
	}

	public function run(){

		if( ! ($config = get_transient('rey_demo_config')) ){
			return $this->add_error( 'Cannot retrieve configuration.' );
		}

		if( isset($config['reset']) && 1 === absint($config['reset']) ){
			$this->importer->reset_data();
		}

		Base::reset_map();

		if( ! reycore__get_purchase_code() ){
			return $this->add_error('Purchase code missing');
		}

		// Suspend bunches of stuff in WP core
		wp_suspend_cache_invalidation( true );

	}

}
