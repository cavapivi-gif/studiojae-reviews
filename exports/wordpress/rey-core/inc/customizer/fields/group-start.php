<?php
namespace ReyCore\Customizer\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

class GroupStart extends \Kirki_Control_Base {

	public $type = 'rey_group_start';

	public function render_content() {
		if( ! empty($this->label) ){
			printf('<h4>%s</h4>', $this->label);
		}
	}

}
