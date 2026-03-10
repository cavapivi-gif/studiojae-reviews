<?php
namespace ReyCore\Modules\Cards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Basic extends CardBase
{
	public function __construct() {
		parent::__construct();
	}

	public function get_id(){
		return 'basic';
	}

	public function get_name(){
		return 'Basic';
	}

	public function get_supports(){
		return [
			'background',
			'media-width',
		];
	}


	public function get_css(){
		return [
			$this->asset_key => [
				'src'     => $this->template_path . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		];
	}

	public function get_card_defaults(){
		return [
			'button_style' => 'btn-line-active'
		];
	}

	public function __item_content(){

		$this->__video();

		if( ! $this->_item['video'] ){
			$this->__image();
		}

		$this->__captions();
	}

}
