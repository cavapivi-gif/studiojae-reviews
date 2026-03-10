<?php
namespace ReyCore\Modules\Cards;

use \ReyCore\Modules\Cards\Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Soho extends CardBase
{
	public function __construct() {
		parent::__construct();
	}

	public function get_id(){
		return 'soho';
	}

	public function get_name(){
		return 'Soho';
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
			'button_show' => 'no',
			'subtitle_show' => 'no',
		];
	}

	public function get_supports(){
		return [
			'background',
			'media-width',
		];
	}

	public function __item_content(){

		echo '<div class="__media-wrapper">';

			$this->__image();
			$this->__overlay();

		echo '</div>';

		echo '<div class="__wrapper">';

			$this->__title();
			$this->__subtitle();
			$this->__button();

		echo '</div>';

	}
}
