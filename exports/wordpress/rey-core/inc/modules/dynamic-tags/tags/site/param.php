<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Param extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'site-param',
			'title'      => esc_html__( 'URL Request Parameter', 'rey-core' ),
			'categories' => [ 'text', 'url' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'request_key',
			[
				'label' => esc_html__( 'Request Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]
		);

	}

	public function render()
	{
		if( ! ($key = $this->get_settings('request_key')) ){
			return;
		}

		if( ! (isset($_REQUEST[$key]) && ($param = reycore__clean($_REQUEST[$key])) ) ){
			return;
		}

		echo $param;
	}

}
