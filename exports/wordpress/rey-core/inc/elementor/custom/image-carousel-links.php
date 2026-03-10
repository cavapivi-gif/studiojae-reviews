<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ImageCarouselLinks extends \Elementor\Skin_Base {

	public function __construct( \Elementor\Widget_Base $parent ) {
		parent::__construct( $parent );
	}

	public function get_id() {
		return 'carousel_links';
	}

	public function get_title() {
		return __( 'Carousel with links', 'rey-core' );
	}

	public function render() {

		$settings = $this->parent->get_settings_for_display();

		if ( empty( $settings['rey_items'] ) ) {
			return;
		}

		$lazyload = 'yes' === $settings['lazyload'];

		$slides = [];

		foreach ( $settings['rey_items'] as $index => $item ) {

			$image_url = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $item['image']['id'], 'thumbnail', $settings );

			if ( $lazyload ) {
				$image_html = '<img class="swiper-slide-image swiper-lazy" data-src="' . esc_attr( $image_url ) . '" alt="' . esc_attr(  \Elementor\Control_Media::get_image_alt( $item['image'] ) ) . '" />';
			} else {
				$image_html = '<img class="swiper-slide-image" src="' . esc_attr( $image_url ) . '" alt="' . esc_attr(  \Elementor\Control_Media::get_image_alt( $item['image'] ) ) . '" />';
			}

			$link_tag = '';

			$link = $item['link'];

			if ( $link ) {
				$link_key = 'link_' . $index;

				$this->parent->add_link_attributes( $link_key, $link );

				$link_tag = '<a ' . $this->parent->get_render_attribute_string( $link_key ) . '>';
			}

			$image_caption = $this->__get_image_caption( $item['image'] );

			$slide_count = $index + 1;
			$slide_setting_key = 'swiper_slide_' . $index;

			$this->parent->add_render_attribute( $slide_setting_key, [
				'class' => 'swiper-slide',
				'role' => 'group',
				'aria-roledescription' => 'slide',
				'aria-label' => sprintf(
					/* translators: 1: Slide count, 2: Total slides count. */
					esc_html__( '%1$s of %2$s', 'elementor' ),
					$slide_count,
					count( $settings['rey_items'] )
				),
			] );

			$slide_html = '<div ' . $this->parent->get_render_attribute_string( $slide_setting_key ) . '>' . $link_tag . '<figure class="swiper-slide-inner">' . $image_html;

			if ( $lazyload ) {
				$slide_html .= '<div class="swiper-lazy-preloader"></div>';
			}

			if ( ! empty( $image_caption ) ) {
				$slide_html .= '<figcaption class="elementor-image-carousel-caption">' . $image_caption . '</figcaption>';
			}

			$slide_html .= '</figure>';

			if ( $link ) {
				$slide_html .= '</a>';
			}

			$slide_html .= '</div>';

			$slides[] = $slide_html;
		}

		if ( empty( $slides ) ) {
			return;
		}


		$swiper_class = \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' ) ? 'swiper' : 'swiper-container';
		$has_autoplay_enabled = 'yes' === $this->parent->get_settings_for_display( 'autoplay' );

		$this->parent->add_render_attribute( [
			'carousel' => [
				'class' => 'elementor-image-carousel swiper-wrapper',
				'aria-live' => $has_autoplay_enabled ? 'off' : 'polite',
			],
			'carousel-wrapper' => [
				'class' => 'elementor-image-carousel-wrapper ' . $swiper_class,
				'dir' => $settings['direction'],
			],
		] );

		$show_dots = ( in_array( $settings['navigation'], [ 'dots', 'both' ] ) );
		$show_arrows = ( in_array( $settings['navigation'], [ 'arrows', 'both' ] ) );

		if ( 'yes' === $settings['image_stretch'] ) {
			$this->parent->add_render_attribute( 'carousel', 'class', 'swiper-image-stretch' );
		}

		$slides_count = count( $settings['rey_items'] );
		?>
		<div <?php $this->parent->print_render_attribute_string( 'carousel-wrapper' ); ?>>
			<div <?php $this->parent->print_render_attribute_string( 'carousel' ); ?>>
				<?php // PHPCS - $slides contains the slides content, all the relevent content is escaped above. ?>
				<?php echo implode( '', $slides ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php if ( 1 < $slides_count ) : ?>
				<?php if ( $show_arrows ) : ?>
					<div class="elementor-swiper-button elementor-swiper-button-prev" role="button" tabindex="0">
						<?php $this->render_swiper_button( 'previous' ); ?>
					</div>
					<div class="elementor-swiper-button elementor-swiper-button-next" role="button" tabindex="0">
						<?php $this->render_swiper_button( 'next' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_dots ) : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
		reycore_assets()->add_scripts('reycore-elementor-elem-carousel-links');
	}


	/**
	 * Retrieve image carousel caption.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param array $attachment
	 *
	 * @return string The caption of the image.
	 */
	public function __get_image_caption( $attachment ) {
		$caption_type = $this->parent->get_settings_for_display( 'caption_type' );

		if ( empty( $caption_type ) ) {
			return '';
		}

		$attachment_post = get_post( $attachment['id'] );

		if ( 'caption' === $caption_type ) {
			return $attachment_post->post_excerpt;
		}

		if ( 'title' === $caption_type ) {
			return $attachment_post->post_title;
		}

		return $attachment_post->post_content;
	}

	private function render_swiper_button( $type ) {
		$direction = 'next' === $type ? 'right' : 'left';
		$icon_settings = $this->parent->get_settings_for_display( 'navigation_' . $type . '_icon' );

		if ( empty( $icon_settings['value'] ) ) {
			$icon_settings = [
				'library' => 'eicons',
				'value' => 'eicon-chevron-' . $direction,
			];
		}

		\Elementor\Icons_Manager::render_icon( $icon_settings, [ 'aria-hidden' => 'true' ] );
	}

}
