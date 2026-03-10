<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Attributes extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-attributes',
			'title'      => esc_html__( 'Product Attributes', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	public static function get_attributes_list(){
		$options = function_exists('reycore_wc__get_attributes_list') ? reycore_wc__get_attributes_list(true) : [];
		return ['' => esc_html__('- Select -', 'rey-core')] + $options;
	}

	protected function register_controls() {

		TagDynamic::woo_product_control($this);

		$this->add_control(
			'attribute',
			[
				'label' => esc_html__( 'Attribute', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_attributes_list',
				],
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => esc_html__( 'Limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
			]
		);

		$this->add_control(
			'index',
			[
				'label' => esc_html__( 'Index', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'condition' => [
					'limit' => '',
				],
			]
		);

		$this->add_control(
			'links',
			[
				'label' => esc_html__( 'Add links', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'display',
			[
				'label' => esc_html__( 'Display', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'value',
				'options' => [
					'value'  => esc_html__( 'Values', 'rey-core' ),
					'label'  => esc_html__( 'Attribute label', 'rey-core' ),
					'both'  => esc_html__( 'Both', 'rey-core' ),
				],
			]
		);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Attributes}', 'rey-core' ) );
		}

		$settings = $this->get_settings();

		echo reycore_wc__get_attributes([
			'taxonomy'   => $settings['attribute'],
			'product'    => $product,
			'link'       => $settings['links'] !== '',
			'separator'  => $settings['separator'],
			'limit'      => absint($settings['limit']),
			'display'    => $settings['display'],
			'index'      => $settings['index'],
		]);
	}

}
