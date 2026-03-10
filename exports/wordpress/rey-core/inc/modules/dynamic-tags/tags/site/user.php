<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class User extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'site-user',
			'title'      => esc_html__( 'User Data', 'rey-core' ),
			'categories' => [ 'text', 'url' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'data',
			[
				'label' => esc_html__( 'User Data', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''                => esc_html__( '- Select -', 'rey-core' ),
					'ID'              => esc_html__( 'ID', 'rey-core' ),
					'user_login'      => esc_html__( 'Username', 'rey-core' ),
					'user_nicename'   => esc_html__( 'Nicename', 'rey-core' ),
					'user_email'      => esc_html__( 'Email', 'rey-core' ),
					'user_url'        => esc_html__( 'URL', 'rey-core' ),
					'user_registered' => esc_html__( 'Registered', 'rey-core' ),
					'display_name'    => esc_html__( 'Display Name', 'rey-core' ),
				],
			]
		);

	}

	public function render()
	{

		$current_user = wp_get_current_user();

		if( ! isset($current_user->data) ){
			return;
		}

		$dt = $this->get_settings('data');

		if( ! isset($current_user->data->{$dt}) ){
			return;
		}

		echo $current_user->data->{$dt};
	}

}
