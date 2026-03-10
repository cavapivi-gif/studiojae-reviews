<?php
namespace ReyCore\Modules\CardCode;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const LAYOUT_TYPE = 'code';

	public function __construct()
	{
		add_action( 'elementor/element/reycore-carousel/section_content_style/before_section_end', [$this, 'add_controls']);
		add_action( 'elementor/element/reycore-grid/section_content_style/before_section_end', [$this, 'add_controls']);
		add_action( 'reycore/cards/not_existing', [$this, 'render'], 10, 2);
	}

	public function add_controls( $stack ){

		if( ! ($card_module = reycore__get_module('cards')) ){
			return;
		}

		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $stack->get_unique_name();

		// add to Layout list
		$card_control = $controls_manager->get_control_from_stack( $unique_name, $card_module::CARD_KEY );
		if( $card_control && ! is_wp_error($card_control) ) {
			$card_control['options'][self::LAYOUT_TYPE] = esc_html__('Custom Code', 'rey-core');
			$stack->update_control( $card_module::CARD_KEY, $card_control );
		}

		$stack->add_control(
			'card_code_name',
			[
				'label' => esc_html__( 'Unique Name', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'ex: mark1', 'rey-core' ),
				'condition' => [
					$card_module::CARD_KEY => self::LAYOUT_TYPE,
				],
				'ai' => [
					'active' => false,
				],
			]
		);

	}

	public function render($card_id, $element){

		if( self::LAYOUT_TYPE !== $card_id ){
			return;
		}

		if( ! ( isset($element->_settings['card_code_name']) && ($card_name = $element->_settings['card_code_name']) ) ){
			echo 'Please add a Unique name.';
			return;
		}

		if( ! empty($element->_items[$element->item_key]['video']) ){
			// no need to load the script if the widget is not a grid
			if( 'reycore-grid' === $element->get_unique_name() ){
				reycore_assets()->add_scripts( 'reycore-widget-grid-videos' );
			}
		}

		/**
		 * @hook reycore/cards/render/custom_code={$card_name}
		 * @param array Item Content
		 * @param bool Is first item
		 * @param object Elementor Element
		 */
		do_action('reycore/cards/render/custom_code=' . $card_name, $element->_items[$element->item_key], 0 === $element->item_key, $element);

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Card (Custom Code)', 'Module name', 'rey-core'),
			'description' => esc_html_x('Build custom coded templates which are used for various elements (Carousel, Grid)', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['elementor', 'carousel', 'grid'],
			// 'video'       => true,
			// 'help'        => reycore__support_url('kb/card-global-section/'),
		];
	}

	public function module_in_use(){
		return true;
	}

}
