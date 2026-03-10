<?php
namespace ReyCore\Customizer\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

class AccordionStart extends \Kirki_Control_Base {

	public $type = 'rey_accordion_start';

	public function render_content() {
		if( ! empty($this->label) ){
			printf('<h4 tabindex="-1">%s</h4>%s', $this->label, reycore__get_svg_icon(['id'=>'arrow']) );
		}
	}

}
