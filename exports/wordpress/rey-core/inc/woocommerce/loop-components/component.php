<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class Component {

	protected $scheme = [];

	protected $status;

	protected $css_classes = [];

	public function __construct(){}

	/**
	 * Initialize code for component which runs
	 * before the loop is starting (only if the component is enabled)
	 *
	 * @return void
	 */
	protected function __run(){}

	/**
	 * Run code on `init`
	 *
	 * @return void
	 */
	public function init(){}

	/**
	 * Run component code on `template_redirect` (query vars ready)
	 *
	 * @return void
	 */
	public function late_init(){}

	/**
	 * Get component status, if enabled
	 *
	 * @return bool
	 */
	protected function status(){
		return true;
	}

	/**
	 * Programatically set the component status
	 *
	 * @param bool $status
	 * @return bool
	 */
	public function set_status( $status = null ){
		$this->status = ! is_null($status) ? $status : $this->status();
	}

	/**
	 * Retrieve the component status
	 *
	 * @return void
	 */
	public function get_status(){
		return ! is_null($this->status) ? $this->status : $this->status();
	}

	/**
	 * Component unique ID
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Component title
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Set a group for the component variant (eg: multiple positions of the same component)
	 *
	 * @return string
	 */
	public function get_group(){
		return '';
	}

	public function group_default(){
		return false;
	}

	/**
	 * Internal refernce of the component,
	 * whether it's a grid or product item component
	 *
	 * @supports `product` and `grid`
	 *
	 * @return string
	 */
	public function loop_type(){
		return 'product';
	}

	/**
	 * Scheme of the component.
	 * What hook to run and its callback.
	 *
	 * @return void
	 */
	public function scheme(){
		return [];
	}

	public function set_scheme( $scheme ){
		$this->scheme = wp_parse_args( $scheme, $this->scheme() );
	}

	public function get_scheme(){

		if( ! $this->get_status() ){
			return [];
		}

		return ! empty($this->scheme) ? $this->scheme : $this->scheme();
	}

	public function css_classes(){
		return [];
	}

	public function set_css_classes( $classes ){
		$this->css_classes = $classes;
	}

	public function get_css_classes(){
		return ! empty($this->css_classes) ? $this->css_classes : $this->css_classes();
	}

	/**
	 * Prevent or force a hook to display
	 *
	 * @return bool
	 */
	public function maybe_render(){
		return apply_filters( 'reycore/woocommerce/loop/render/' . $this->get_id(), $this->get_status() );
	}

	/**
	 * Render component output
	 *
	 * @return void
	 */
	public function render(){}

}
