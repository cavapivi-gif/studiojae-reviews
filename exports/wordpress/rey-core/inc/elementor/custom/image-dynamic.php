<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ImageDynamic extends \Elementor\Skin_Base {

	public function __construct( \Elementor\Widget_Base $parent ) {
		parent::__construct( $parent );
		// add_filter( 'elementor/widget/print_template', array( $this, 'skin_print_template' ), 10, 2 );
	}

	public function get_id() {
		return 'dynamic_image';
	}

	public function get_title() {
		return __( 'Dynamic Image', 'rey-core' );
	}

	public function get_image(){

		if( class_exists('\WooCommerce') && is_tax() ){
			$queried_object = get_queried_object();
			return get_woocommerce_term_meta( $queried_object->term_id, 'thumbnail_id', true );
		}

		elseif( is_singular() ) {
			if( function_exists('rey__can_show_post_thumbnail') && rey__can_show_post_thumbnail() ) {
				return get_post_thumbnail_id();
			}
		}

		return false;
	}

	/**
	 * Retrieve image widget link URL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $settings
	 *
	 * @return array|string|false An array/string containing the link URL, or false if no link.
	 */
	private function get_link_url( $settings, $image_id ) {
		if ( 'none' === $settings['link_to'] ) {
			return false;
		}

		if ( 'custom' === $settings['link_to'] ) {
			if ( empty( $settings['link']['url'] ) ) {
				return false;
			}
			return $settings['link'];
		}

		return [
			'url' => wp_get_attachment_url( $image_id ),
		];
	}

	/**
	 * Get the caption for current widget.
	 *
	 * @access private
	 * @since 2.3.0
	 * @param $settings
	 *
	 * @return string
	 */
	private function get_caption( $settings, $image_id ) {
		$caption = '';
		if ( ! empty( $settings['caption_source'] ) ) {
			switch ( $settings['caption_source'] ) {
				case 'attachment':
					$caption = wp_get_attachment_caption( $image_id );
					break;
				case 'custom':
					$caption = ! empty( $settings['caption'] ) ? $settings['caption'] : '';
			}
		}
		return $caption;
	}

	/**
	 * Check if the current widget has caption
	 *
	 * @access private
	 * @since 2.3.0
	 *
	 * @param array $settings
	 *
	 * @return boolean
	 */
	private function has_caption( $settings ) {
		return ( ! empty( $settings['caption_source'] ) && 'none' !== $settings['caption_source'] );
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();

		$image_id = $this->get_image();

		if ( ! $image_id ) {
			return;
		}

		$has_caption = $this->has_caption( $settings );

		$this->parent->add_render_attribute( 'wrapper', 'class', 'elementor-image' );

		if ( ! empty( $settings['shape'] ) ) {
			$this->parent->add_render_attribute( 'wrapper', 'class', 'elementor-image-shape-' . $settings['shape'] );
		}

		$link = $this->get_link_url( $settings, $image_id );

		if ( $link ) {
			$this->parent->add_render_attribute( 'link', [
				'href' => $link['url'],
				'data-elementor-open-lightbox' => $settings['open_lightbox'],
			] );

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$this->parent->add_render_attribute( 'link', [
					'class' => 'elementor-clickable',
				] );
			}

			if ( ! empty( $link['is_external'] ) ) {
				$this->parent->add_render_attribute( 'link', 'target', '_blank' );
			}

			if ( ! empty( $link['nofollow'] ) ) {
				$this->parent->add_render_attribute( 'link', 'rel', 'nofollow' );
			}
		} ?>

		<div <?php echo $this->parent->get_render_attribute_string( 'wrapper' ); ?>>

			<?php if ( $has_caption ) : ?>
				<figure class="wp-caption">
			<?php endif; ?>

			<?php if ( $link ) : ?>
					<a <?php echo $this->parent->get_render_attribute_string( 'link' ); ?>>
			<?php endif; ?>

				<?php
					echo wp_get_attachment_image( $image_id, $settings['image_size'] );
				?>

			<?php if ( $link ) : ?>
					</a>
			<?php endif; ?>

			<?php if ( $has_caption ) : ?>
					<figcaption class="widget-image-caption wp-caption-text"><?php echo $this->get_caption( $settings, $image_id ); ?></figcaption>
				</figure>
			<?php endif; ?>

		</div>
		<?php
	}

	public function content_template() {}

	public function skin_print_template( $content, $image ) {
		if( 'image' == $image->get_name() ) {
			ob_start();
			$this->content_template();
			$content = ob_get_clean();
		}
		return $content;
	}
}
