<?php
namespace ReyCore\Modules\ElementorSectionSlideshow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PgSkinCarouselSection extends \Elementor\Skin_Base
{
	public $images = [];

	public $_settings = [];

	public function get_id() {
		return 'carousel-section';
	}

	public function get_title() {
		return __( 'Carousel Section', 'rey-core' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
	}

	public function register_carousel_controls( $element ){

		$element->start_controls_section(
			'section_carousel_section_settings',
			[
				'label' => __( 'Carousel Settings', 'rey-core' ),
				'condition' => [
					'_skin' => 'carousel-section',
				],
			]
		);

		$element->add_control(
			'cs_autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no' => __( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'cs_autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [
					'cs_autoplay' => 'yes',
				],
			]
		);

		$element->add_control(
			'cs_speed',
			[
				'label' => __( 'Animation Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$element->add_control(
			'cs_dots',
			[
				'label' => __( 'Dots Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Disabled', 'rey-core' ),
					'before'  => __( 'Before', 'rey-core' ),
					'after'  => __( 'After', 'rey-core' ),
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Prints dots HTML
	 *
	 * @since 1.0.0
	 */

	public function show_dots($position, $total = 0){
		$dots = $this->_settings['cs_dots'];
		if( $dots == $position ){
			printf('<div class="__pagination-%s reyEl-productGrid-cs-dots reyEl-productGrid-cs-dots--%s">', esc_attr( $this->parent->get_id()), $position );
			for($i = 0; $i < $total; $i++){
				printf('<button data-go="%1$d" aria-label="%2$s %1$d"></button>', $i, esc_html__('Go to ', 'rey-core'));
			}
			echo '</div>';
		}
	}



	public function loop_start($products)
	{

		if( 'no' !== $this->_settings['hide_thumbnails'] ){
			remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail');
		}

		echo '<div class="splide">';

			// Show dots before
			$this->show_dots('before', $products->total);

			echo '<div class="splide__track">';

				echo sprintf('<ul class="products %s">', implode(' ', [
					'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
					'--prevent-scattered', // make sure scattered is not applied
					'--prevent-masonry', // make sure masonry is not applied
					'splide__list'
				]) );
	}

	public function loop_end($products){

			echo '</ul></div>';

			// Show dots after
			$this->show_dots('after', $products->total);

		echo '</div>';

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {

		$this->_settings = $this->parent->get_settings_for_display();

		if( 'no' !== $this->_settings['hide_thumbnails'] ){
			$this->_settings['hide_thumbnails'] = 'yes';
		}

		// Hide thumbnails
		$this->_settings['hide_thumbnails_second'] = 'yes';
		$this->_settings['hide_thumbnails_slideshow'] = 'yes';

		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		$args = [
			'name'        => 'product_grid_element',
			'filter_name' => 'product_grid',
			'main_class'  => 'reyEl-productGrid',
			'el_instance' => $this->parent,
			'attributes'  => [
				'data-carousel-section-settings' => wp_json_encode([
					'autoplay' => esc_attr($this->_settings['cs_autoplay']),
					'autoplaySpeed' => esc_attr($this->_settings['cs_autoplay_speed']),
					'animationDuration' => esc_attr($this->_settings['cs_speed']),
					'customPagination' => $this->_settings['cs_dots'] !== '' ? '.__pagination-' . $this->parent->get_id() : ''
				])
			]
		];

		$product_archive = new \ReyCore\WooCommerce\Tags\ProductArchive( $args, $this->_settings );

		if (
			($query_results = (array) $product_archive->get_query_results()) &&
			isset($query_results['ids']) && ! empty($query_results['ids'])
		) {

			reycore_assets()->add_styles( ['rey-splide', Base::ASSET_HANDLE, $this->parent->get_style_name(), $this->parent->get_style_name('carousel'), $this->parent->get_style_name('carousel-section')] );

			reycore_assets()->add_scripts( [
				'reycore-woocommerce',
				Base::ASSET_HANDLE,
				Base::ASSET_HANDLE . '-cs',
				'splidejs',
				'rey-splide',
			] );

			foreach ( $product_archive->_products->ids as $product_id ) {
				// assign images
				$this->images[] = get_the_post_thumbnail_url( $product_id, 'full' );
			}

			$product_archive->render_start();

				$product_archive->__loop_hooks_start();

				$this->loop_start($product_archive->_products);

					$product_archive->render_products();

				$this->loop_end($product_archive->_products);

				$product_archive->__loop_hooks_end();

			$product_archive->render_end();

			// slideshow markup
			if( !empty($this->images) ){

				$slideshow_html = sprintf(
					'<div class="rey-section-slideshow--template splide" data-cs-id="%1$s" id="tmpl-slideshow-tpl-%1$s">',
					esc_attr( $this->parent->get_id() )
				);

					$slideshow_html .= '<div class="splide__track">';
						$slideshow_html .= '<div class="splide__list">';

						foreach ($this->images as $index => $item) {
							$slideshow_html .= sprintf( '<div class="splide__slide rey-section-slideshowItem rey-section-slideshowItem--%s"><img class="rey-section-slideshowItem-img" src="%s" alt=""/></div>', $index, $item);
						}

						$slideshow_html .= '</div>';
					$slideshow_html .= '</div>';
				$slideshow_html .= '</div><!-- .rey-section-slideshow--template -->';

				echo $slideshow_html;
			}

		}
		else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}

	}

}
