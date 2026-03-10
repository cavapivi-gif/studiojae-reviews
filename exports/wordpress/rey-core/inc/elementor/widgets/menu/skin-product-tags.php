<?php
namespace ReyCore\Elementor\Widgets\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinProductTags extends \Elementor\Skin_Base
{

	private $_settings = [];
	private $_terms = [];

	const TAX_NAME = 'product_tag';

	public function get_id() {
		return 'product-tags';
	}

	public function get_title() {
		return __( 'Product Tags (Collections)', 'rey-core' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'register_items_controls' ] );
	}

	public function register_items_controls( $element ){

		$element->add_control(
			'pt_type',
			[
				'label' => esc_html__( 'Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'list',
				'options' => [
					'list'  => esc_html__( 'List of tags', 'rey-core' ),
					'common'  => esc_html__( 'Tags with common taxonomies', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'product-tags',
				],
			]
		);

		$element->add_control(
			'pt_product_tags',
			[
				'label' => esc_html__( 'Select Tags', 'rey-core' ),
				'placeholder' => esc_html__('- Select-', 'rey-core'),
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => self::TAX_NAME,
				],
				'multiple' => true,
				'label_block' => true,
				'default' => [],
				'condition' => [
					'_skin' => 'product-tags',
					'pt_type' => 'list',
				],
			]
		);

		$element->add_control(
			'pt_common_type',
			[
				'label' => esc_html__( 'Common Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'cat',
				'options' => [
					'cat'  => esc_html__( 'Categories', 'rey-core' ),
					'attr'  => esc_html__( 'Attributes', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'product-tags',
					'pt_type' => 'common',
				],
			]
		);


		$element->add_control(
			'pt_common_cat',
			[
				'label' => esc_html__( 'Select categories', 'rey-core' ),
				'description' => esc_html__( 'Leave empty to grab the current category ID.', 'rey-core' ),
				'placeholder' => esc_html__('- Select-', 'rey-core'),
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
				],
				'multiple' => true,
				'label_block' => true,
				'default' => [],
				'condition' => [
					'_skin' => 'product-tags',
					'pt_type' => 'common',
					'pt_common_type' => 'cat',
				],
			]
		);

		$terms = function_exists('reycore_wc__get_attributes_list') ? reycore_wc__get_attributes_list() : [];

		$element->add_control(
			'pt_attr_id',
			[
				'label' => __( 'Select Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $terms,
				'condition' => [
					'_skin' => 'product-tags',
					'pt_type' => 'common',
					'pt_common_type' => 'attr',
				],
			]
		);

		foreach($terms as $term => $term_label):

			$element->add_control(
				'pt_attr_custom_' . $term,
				[
					'label' => sprintf( esc_html__( 'Select one or more %s attributes', 'rey-core' ), $term_label ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'description' => esc_html__( 'Leave empty to grab the current attribute ID.', 'rey-core' ),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => wc_attribute_taxonomy_name( $term ),
					],
					'multiple' => true,
					'default' => [],
					'label_block' => true,
					'condition' => [
						'_skin' => 'product-tags',
						'pt_type' => 'common',
						'pt_common_type' => 'attr',
						'pt_attr_id' => $term,
					],
				]
			);

		endforeach;

	}

	public static function get_attributes_list(){
		$options = function_exists('reycore_wc__get_attributes_list') ? reycore_wc__get_attributes_list() : [];
		return ['' => esc_html__('- Select -', 'rey-core')] + $options;
	}

	public function render_menu()
	{
		if( $this->_settings['pt_type'] === 'list' && ($tags = $this->_settings['pt_product_tags']) ){

			$this->_terms = \ReyCore\Helper::get_terms([
				'include' => $tags,
				'taxonomy' => self::TAX_NAME
			]);

		}

		if( $this->_settings['pt_type'] === 'common' ){

			if( $this->_settings['pt_common_type'] === 'cat' ){

				$categories = $this->_settings['pt_common_cat'];

				if( empty($categories) ){
					$categories = [
						get_queried_object_id()
					];
				}

				$this->_terms = reycore__get_terms_by_common_posts(
					$categories,
					'product_cat',
					self::TAX_NAME
				);

			}
			elseif( $this->_settings['pt_common_type'] === 'attr' && ($attr_id = $this->_settings['pt_attr_id']) ){

				$attributes_ids = $this->_settings['pt_attr_custom_' . $attr_id];

				if( empty($attributes_ids) ){
					$attributes_ids = [
						get_queried_object_id()
					];
				}

				$this->_terms = reycore__get_terms_by_common_posts(
					$attributes_ids,
					wc_attribute_taxonomy_name( $attr_id ),
					self::TAX_NAME
				);

			}

		}

		$this->render_terms();
	}

	public function render_terms()
	{
		if( empty($this->_terms) ){
			return;
		}

		echo '<nav class="reyEl-menu-navWrapper" role="navigation">';

			printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $this->_settings['hover_style']);

			foreach ($this->_terms as $term) {

				if( !(is_object($term) && ! empty($term)) ){
					continue;
				}

				printf(
					'<li class="menu-item %3$s"><a class="" href="%2$s"><span>%1$s</span></a></li>',
					$term->name,
					get_term_link($term, self::TAX_NAME),
					(is_tax($term->term_id, 'product_cat') ? 'current-menu-item' : '')
				);
			}

			echo '</ul>';

		echo '</nav>';

	}

	public function render() {

		$this->_settings = $this->parent->get_settings_for_display();

		if( empty($this->_settings) ){
			return;
		}

		reycore_assets()->add_styles( $this->parent->get_style_name('style') );

		$this->parent->render_start();
		$this->parent->render_title();
		$this->render_menu();
		$this->parent->render_end();
	}
}
