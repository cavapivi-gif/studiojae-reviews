<?php

if ( class_exists('\ReyCore\Elementor\GlobalSections') && ! class_exists( 'acf_field_global_sections' ) ) :

	class acf_field_global_sections extends acf_field {

		public function __construct() {

			// vars
			$this->name     = 'global_sections';
			$this->label    = __( 'Global Sections', 'rey-core' );
			$this->category = 'content';
			// $this->defaults = [
			// 	'default_value' => '',
			// ];

			$this->settings = [
				'version' => '1.0.0',
				'url'     => plugin_dir_url( __DIR__ )
			];

			$this->l10n = [];

			parent::__construct();

		}

		public function render_field( $field ) {

			// Change Field into a select
			$field['type'] = 'select';
			$field['ui'] = 0;
			$field['ajax'] = 0;
			$field['allow_null'] = true;
			$field['multiple'] = false;

			if( empty($field['choices']) ){
				$field['choices'] = [];
			}

			if( isset($field['gs_type']) ){

				$sections = \ReyCore\Elementor\GlobalSections::get_global_sections( $field['gs_type'] );

				if( is_array($sections) && !empty($sections) ) {
					$field['choices'] = $field['choices'] + $sections;
				}

			}
			// If GS_TYPE not provided, just show all
			else {
				foreach (\ReyCore\Elementor\GlobalSections::get_global_section_types() as $type => $name) {
					$sections = \ReyCore\Elementor\GlobalSections::get_global_sections( $type );
					if( is_array($sections) && !empty($sections) ) {
						$field['choices'][ $name ] = $sections;
					}
				}
			}

			// render
			acf_render_field( $field );
		}

	}

	// initialize
	acf_register_field_type( new acf_field_global_sections() );

endif; // class_exists check
