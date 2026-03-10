<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SwatchRadio extends SwatchBase
{
	const TYPE_KEY = 'rey_radio';

	function __construct(){
		parent::__construct();
	}

	function get_id(){
		return self::TYPE_KEY;
	}

	function get_name(){
		return 'Radio [rey]';
	}

	function get_item_generic_class(){
		return 'rey-swatchList-item--radio';
	}

	function get_content__text($term){
		return sprintf('<span class="__point"></span><span class="__text">%s</span>', $term->name);
	}

}
