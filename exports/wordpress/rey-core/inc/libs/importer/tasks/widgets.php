<?php
namespace ReyCore\Libs\Importer\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReyCore\Libs\Importer\Base;
use ReyCore\Libs\Importer\Helper;

class Widgets extends TaskBase
{

	public function get_id(){
		return 'widgets';
	}

	public function get_status(){
		return esc_html__('Process widgets ...', 'rey-core');
	}

	public function run(){

		if( ! ($data = get_transient('rey_demo_data')) ){
			return $this->add_error( 'Cannot retrieve content data.' );
		}

		if( ! ( isset($data['widgets']) && ($widgets_data = $data['widgets']) ) ){
			return $this->add_notice( 'Cannot retrieve widgets data. Most likely not added in this demo.' );
		}

		if( $this->maybe_skip('widgets') ){
			return $this->add_notice( 'Skipping widgets as requested.' );
		}

		global $wp_registered_sidebars;

		$available_widgets = $this->available_widgets();

		$stored_widgets = [];

		foreach ( $available_widgets as $widget_data ) {
			$stored_widgets[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		$sidebars_widgets = get_option( 'sidebars_widgets', [] );

		foreach ( $widgets_data as $sidebar_id => $widgets ) {

			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			if( ! isset( $wp_registered_sidebars[ $sidebar_id ] ) ){
				$sidebar_id = 'wp_inactive_widgets';
			}

			foreach ( $widgets as $widget_instance_id => $widget ) {

				$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$maybe_continue = true;

				// Check if widget already exists
				if (
					isset( $available_widgets[ $id_base ], $stored_widgets[ $id_base ], $sidebars_widgets[ $sidebar_id ] )
					&& ($sidebar_widgets = $sidebars_widgets[ $sidebar_id ])
				) {
					foreach ( (array) $stored_widgets[ $id_base ] as $stored_widget_id => $stored_widget_data ) {
						if ( in_array( implode('-', [$id_base, $stored_widget_id]), $sidebar_widgets, true ) && wp_json_encode((array) $widget) === wp_json_encode($stored_widget_data) ) {
							$maybe_continue = false;
							break;
						}
					}
				}

				if ( ! $maybe_continue ) {
					continue;
				}

				$single = get_option( 'widget_' . $id_base );
				$single = ! empty( $single ) ? $single : ['_multiwidget' => 1];
				$single[] = $widget;

				end( $single );
				$new_instance_id_number = key( $single );

				if ( 0 === absint( $new_instance_id_number ) ) {
					$new_instance_id_number = 1;
					$single[ $new_instance_id_number ] = $single[0];
					unset( $single[0] );
				}

				$widget_instance_id = $id_base . '-' . $new_instance_id_number;
				$sidebars_widgets[ $sidebar_id ][] = $widget_instance_id;

				update_option( 'widget_' . $id_base, $single, false );
				update_option( 'sidebars_widgets', $sidebars_widgets, false );

				$this->map[uniqid('widget_')] = implode(':', [$sidebar_id, $widget_instance_id]);

			}
		}

		Base::update_map($this->map);

	}

	public function available_widgets() {

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = [];

		foreach ( $widget_controls as $widget ) {
			// No duplicates.
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}
		}

		return $available_widgets;
	}
}
