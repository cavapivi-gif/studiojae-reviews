<?php
namespace ReyCore\Elementor\Widgets\SliderNav;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinBullets extends \Elementor\Skin_Base
{

	public function get_id() {
		return 'bullets';
	}

	public function get_title() {
		return __( 'Bullets Nav', 'rey-core' );
	}

	public function render() {

		reycore_assets()->add_styles($this->parent->get_style_name());

		$this->parent->_settings = $this->parent->get_settings_for_display();

		$this->parent->render_start();
		$this->parent->render_end();

		reycore_assets()->add_scripts( $this->parent->rey_get_script_depends() );

	}

}
