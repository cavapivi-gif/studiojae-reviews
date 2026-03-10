<?php
namespace ReyCore\Elementor\Controls;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ReyAjaxList extends \Elementor\Control_Select2 {

	public function get_type() {
		return 'rey-ajax-list';
	}

}
