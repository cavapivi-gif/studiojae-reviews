<?php
namespace ReyCore\WooCommerce\PdpSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class Skin
{
	const ASSET_HANDLE = 'reycore-pdp-skin';

	public function __construct() {}

	abstract public function get_id();

	abstract public function get_name();

	abstract public function init();

	public function get_asset_key(){
		return self::ASSET_HANDLE . '-' . $this->get_id();
	}

	public function register_scripts( $assets ){ }

	public function get_styles(){}

	public function product_page_classes() {}
}
