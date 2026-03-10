<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class Component {

	protected $scheme = [];

	protected $status;

	protected $css_classes = [];

	public function __construct(){
	}

	public function init(){}

	abstract public function get_id();

	abstract public function get_name();

	public function maybe_render(){
		return apply_filters( 'reycore/woocommerce/pdp/render/' . $this->get_id(), true );
	}

	/**
	 * Render component output
	 *
	 * @return void
	 */
	public function render(){

	}

}
