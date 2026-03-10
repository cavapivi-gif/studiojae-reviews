<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class WebfontsBase {

	abstract function get_id();

	public function get_list(){
		return [];
	}

	public function get_css(){}

	public function preconnect_urls(){
		return [];
	}

	public function get_transients(){
		return [];
	}

}
