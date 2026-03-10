<?php
namespace ReyCore\Modules\Cards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Manhattan extends CardBase
{
	public function __construct() {
		parent::__construct();
	}

	public function get_id(){
		return 'manhattan';
	}

	public function get_name(){
		return 'Manhattan';
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
			'button_style' => 'btn-line-active',
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

		echo '<div class="__wrapper">';

			$this->__title();

			echo '<div class="__inner-content js-get-height">';

				ob_start();
				$this->__subtitle();
				$this->__button();
				$inner_content_output = ob_get_clean();

				if( $inner_content_output ){
					echo '<div class="__spacer"></div>';
					echo $inner_content_output;
				}

			echo '</div>';

		echo '</div>';

	}
}
