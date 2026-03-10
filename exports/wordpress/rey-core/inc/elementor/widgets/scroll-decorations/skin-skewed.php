<?php
namespace ReyCore\Elementor\Widgets\ScrollDecorations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinSkewed extends \Elementor\Skin_Base
{

	public function get_id() {
		return 'skewed';
	}

	public function get_title() {
		return __( 'Skewed style', 'rey-core' );
	}

	public function render() {

		$settings = $this->parent->get_settings_for_display();

		$this->parent->render_start($settings);
		?>
		<span class="rey-scrollDeco-line"></span>
		<?php
		$this->parent->render_end();

		reycore_assets()->add_styles('reycore-elementor-scroll-deco');
		reycore_assets()->add_scripts('reycore-elementor-scroll-deco');
	}

}
