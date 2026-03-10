<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Attributes extends TaskBase
{

	public function get_id(){
		return 'attributes';
	}

	public function get_status(){
		return esc_html__('Process attributes ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['attributes']) && ($attributes = $data['attributes']) ) ){
			return $this->add_notice( 'Cannot retrieve attributes. Most likely not added in this demo.' );
		}

		if( $this->maybe_skip('terms') ){
			return $this->add_notice( 'Skipping terms (attributes) as requested.' );
		}

		if( ! function_exists('wc_update_attribute') ){
			return $this->add_notice( 'WooCommerce is not installed.' );
		}

		foreach ($attributes as $taxonomy => $attribute) {

			$attr_id = wc_update_attribute(0, [
				'id'           => 0,
				'name'         => $attribute['attribute_label'],
				'slug'         => $taxonomy,
				'order_by'     => $attribute['attribute_orderby'],
				'type'         => $attribute['attribute_type'],
				'has_archives' => (bool) $attribute['attribute_public'],
			]);

			if( is_wp_error($attr_id) ){
				continue;
			}

			$this->map['attr_' . $taxonomy] = $attr_id;
		}

		Base::update_map($this->map);

	}

}
