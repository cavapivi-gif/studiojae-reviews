<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class IconBoxLink extends \Elementor\Skin_Base {

	public function __construct( \Elementor\Widget_Base $parent ) {
		parent::__construct( $parent );
	}

	public function get_id() {
		return 'icon_box_link';
	}

	public function get_title() {
		return __( 'Linked Box', 'rey-core' );
	}

	public function render() {

		$settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute( 'icon', 'class', [ 'elementor-icon', 'elementor-animation-' . $settings['hover_animation'] ] );

		$icon_tag = 'div';

		if ( ! isset( $settings['icon'] ) && ! \Elementor\Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['icon'] = 'fa fa-star';
		}

		$has_icon = ! empty( $settings['icon'] );

		if ( ! empty( $settings['link']['url'] ) ) {
			$icon_tag = 'a';
			$this->parent->add_link_attributes( 'link', $settings['link'] );
		}

		if ( $has_icon ) {
			$this->parent->add_render_attribute( 'i', 'class', $settings['icon'] );
			$this->parent->add_render_attribute( 'i', 'aria-hidden', 'true' );
		}

		$this->parent->add_render_attribute( 'description_text', 'class', 'elementor-icon-box-description' );

		// $this->parent->add_inline_editing_attributes( 'title_text', 'none' );
		// $this->parent->add_inline_editing_attributes( 'description_text' );

		if ( ! $has_icon && ! empty( $settings['selected_icon']['value'] ) ) {
			$has_icon = true;
		}

		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$is_new = ! isset( $settings['icon'] ) && \Elementor\Icons_Manager::is_migration_allowed();

		?>
		<<?php \Elementor\Utils::print_validated_html_tag( $icon_tag ); ?> <?php $this->parent->print_render_attribute_string( 'link' ); ?> class="elementor-icon-box-wrapper">

			<?php if ( $has_icon ) : ?>
			<div class="elementor-icon-box-icon">
				<span <?php $this->parent->print_render_attribute_string( 'icon' ); ?>>
				<?php
				if ( $is_new || $migrated ) {
					echo \ReyCore\Elementor\Helper::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
				} elseif ( ! empty( $settings['icon'] ) ) {
					?><i <?php $this->parent->print_render_attribute_string( 'i' ); ?>></i><?php
				}
				?>
				</span>
			</div>
			<?php endif; ?>

			<div class="elementor-icon-box-content">

				<<?php \Elementor\Utils::print_validated_html_tag( $settings['title_size'] ); ?> class="elementor-icon-box-title">
					<span <?php $this->parent->print_render_attribute_string( 'link' ); ?> <?php $this->parent->print_render_attribute_string( 'title_text' ); ?>>
						<?php $this->parent->print_unescaped_setting( 'title_text' ); ?>
					</span>
				</<?php \Elementor\Utils::print_validated_html_tag( $settings['title_size'] ); ?>>

				<?php if ( ! \Elementor\Utils::is_empty( $settings['description_text'] ) ) : ?>
					<p <?php $this->parent->print_render_attribute_string( 'description_text' ); ?>>
						<?php $this->parent->print_unescaped_setting( 'description_text' ); ?>
					</p>
				<?php endif; ?>

			</div>

		</<?php \Elementor\Utils::print_validated_html_tag( $icon_tag ); ?>>
		<?php
	}

	protected function content_template() {}
}
