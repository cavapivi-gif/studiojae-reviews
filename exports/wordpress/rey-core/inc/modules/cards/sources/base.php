<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base {

	public $base;

	public function __construct( $base ){
		$this->base = $base;
	}

	public function get_id(){}

	public function get_title(){}

	public function controls($element){}

	public function query($element){
		return [];
	}

	public function parse_item($element){
		return [];
	}

	public function load_more_button_per_page($element){
		return false;
	}

}
