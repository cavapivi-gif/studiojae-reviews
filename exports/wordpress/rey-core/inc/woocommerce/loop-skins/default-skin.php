<?php
namespace ReyCore\WooCommerce\LoopSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DefaultSkin extends Skin
{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_id(){
		return 'default';
	}

	public function get_name(){
		return esc_html__('Default', 'rey-core');
	}

	/**
	 * Exclusion template
	 */
	// function exclude_components( $components ){
	// 	unset($components['brands']);
	// 	unset($components['wishlist']);
	// 	return $components;
	// }

}
