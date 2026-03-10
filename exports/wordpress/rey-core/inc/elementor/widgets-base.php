<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WidgetsBase extends \Elementor\Widget_Base
{
	private $widget_data = [];

	public function get_title() {
		return $this->rey__widget_data( 'title' );
	}

	public function get_name() {
		return Widgets::PREFIX . $this->rey__widget_data( 'id' );
	}

	public function get_icon() {
		return $this->rey__widget_data( 'icon' );
	}

	public function get_categories() {
		return $this->rey__widget_data( 'categories' );
	}

	public function get_keywords() {
		return $this->rey__widget_data( 'keywords' );
	}

	public function get_asset_name($name){
		return sprintf('%s%s-%s', Widgets::ASSET_PREFIX, $this->rey__widget_data( 'id' ), esc_attr($name));
	}

	public function get_style_name($name = 'style'){
		return $this->get_asset_name($name);
	}

	public function get_script_name($name = 'script'){
		return $this->get_asset_name($name);
	}

	public function show_in_panel() {
		return (bool) reycore__get_purchase_code();
	}

	public static function get_rey_config(){}

	protected function rey__widget_data( $key = '' ){

		if( ! empty( $this->widget_data ) ){
			return $this->widget_data[$key];
		}

		$config = static::get_rey_config();

		if( ! ( is_array($config) && ! empty( $config ) ) ){
			return;
		}

		$this->widget_data = $config;

		return $config[ $key ];

	}

}
