<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReySelectExtended extends \Kirki_Control_Base {

		public $type = 'kirki-select';

		public $placeholder = false;

		public $multiple = 1;

		public $query_args;
		public $ajax_choices;
		public $edit_preview;
		public $new_page;
		public $select2;

		/*
		'query_args' => [
			'type' => 'posts',
			'post_type' => 'product',
			'meta' => [
				'meta_key' => '',
				'meta_value' => ''
			]
		],

		'query_args' => [
			'type' => 'terms',
			'taxonomy' => 'product_cat',
			// 'taxonomy' => 'all_attributes',
			'field' => 'slug',
		],

		'ajax_choices' => 'get_global_sections',


		'ajax_choices' => [
			'action' => 'get_global_sections',
			'params' => [
				'type' => 'cover',
			]
		],


		*/

		/**
		 * Constructor.
		 * Supplied `$args` override class property defaults.
		 * If `$args['settings']` is not defined, use the $id as the setting ID.
		 *
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    {@see WP_Customize_Control::__construct}.
		 */
		public function __construct( $manager, $id, $args = [] ) {

			parent::__construct( $manager, $id, $args );

			if ( empty( $args['query_args'] ) || ! is_array( $args['query_args'] ) ) {
				$args['query_args'] = [];
			}

			$this->query_args = $args['query_args'];
			$this->ajax_choices = isset($args['ajax_choices']) ? reycore__clean($args['ajax_choices']) : '';
			$this->edit_preview = isset($args['edit_preview']) && $args['edit_preview'];
			$this->multiple = isset($args['multiple']) && absint($args['multiple']) > 1 ? $args['multiple'] : 1;
			$this->select2 = isset($args['select2']) ? $args['select2'] : true;

			if( isset($args['new_page']) && ! empty($args['new_page']) ){
				$this->new_page = reycore__clean($args['new_page']);
			}

		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {

			// Get the basics from the parent class.
			parent::to_json();

			$this->json['ajax_choices'] = $this->ajax_choices;
			$this->json['edit_preview'] = $this->edit_preview;
			$this->json['new_page']     = $this->new_page;
			$this->json['query_args']   = $this->query_args;
			$this->json['multiple']     = $this->multiple;
			$this->json['placeholder']  = $this->placeholder;
			$this->json['select2']      = $this->select2;
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['kirki-select'] = 'Kirki_Controls_ReySelectExtended';
		return $controls;
	}, 20 );


} );
