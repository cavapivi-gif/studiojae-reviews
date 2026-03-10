<?php
namespace ReyCore\Modules\Cards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Brooklyn extends CardBase
{
	public function __construct() {
		parent::__construct();
	}

	public function get_id(){
		return 'brooklyn';
	}

	public function get_name(){
		return 'Brooklyn';
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

	public function get_critical_css(){
		return [
			'.rey-card.--brooklyn .__captionTitle { transform: rotate(-180deg) translateY(0px); }',
		];
	}

	public function get_card_defaults(){
		return [
			'subtitle_show' => 'no',
			'button_style' => 'btn-dash-line'
		];
	}

	public function get_supports(){
		return [
			'height',
			'clip',
		];
	}

	public function __item_content(){

		$this->__image();
		$this->__overlay();

		$this->__title();
		$this->__title([
			'class' => [
				'__hover-title'
			]
		]);

		$this->__button();

	}

}
