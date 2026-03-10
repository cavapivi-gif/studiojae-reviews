<?php
namespace ReyCore\Modules\Cards;

use \ReyCore\Modules\Cards\Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Harlem extends CardBase
{
	public function __construct() {
		parent::__construct();
	}

	public function get_id(){
		return 'harlem';
	}

	public function get_name(){
		return 'Harlem';
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
			// 'media-width',
		];
	}

	public function __item_content(){

		echo '<div class="__wrapper">';

			echo reycore__get_svg_icon([ 'id' => 'arr-top-right', 'class' => '__icon' ]);

			$this->__title();

		echo '</div>';

		echo '<div class="__media-wrapper">';

			$this->__image();
			$this->__overlay();

			ob_start();
			$this->__label();
			$this->__subtitle();
			$this->__button();
			if( $hover_content = ob_get_clean() ){
				printf('<div class="__hover-wrapper">%s</div>', $hover_content);
			}

		echo '</div>';
	}
}
