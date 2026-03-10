<?php
namespace ReyCore\Elementor\Widgets\Instagram;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinShuffle extends \Elementor\Skin_Base
{
	public $_settings = [];

	public function get_id() {
		return 'shuffle';
	}

	public function get_title() {
		return __( 'Shuffle', 'rey-core' );
	}

	public function rey_get_script_depends() {
		return [ 'masonry', 'reycore-widget-instagram-scripts' ];
	}

	public function render_items(){

		if( empty($this->parent->_items['items']) ){
			return;
		}

		$anim_class =  ! \Elementor\Plugin::$instance->editor->is_edit_mode() ? 'rey-elInsta-item--animated': '';

		if( 'yes' === $this->parent->_settings['enable_box'] ){

			// box url
			if( !empty($this->parent->_settings['box_url']) ){
				$box_url = $this->parent->_settings['box_url'];
			}
			else {
				$box_url = 'https://www.instagram.com/' . $this->parent->_items['username'];
			}
			// box text
			if( !empty($this->parent->_settings['box_text']) ){
				$box_text = $this->parent->_settings['box_text'];
			}
			else {
				$box_text = $this->parent->_items['username'];
			}

			$hide_mobile = $this->parent->_settings['hide_box_mobile'] === 'yes' ? '--hide-mobile' : '';

			$shuffle_item = '<div class="rey-elInsta-item rey-elInsta-shuffleItem '. $anim_class .' '. $hide_mobile .'" style="--td:{{FACTOR}}">';
				$shuffle_item .= '<div>';
				$shuffle_item .= '<a href="'. $box_url .'" rel="noreferrer" class="rey-instaItem-link" target="_blank"><span>'.$box_text.'</span></a>';
				$shuffle_item .= '</div>';
			$shuffle_item .= '</div>';

			$box_position = absint($this->parent->_settings['box_position']);

			$this->parent->_items['items'] = array_values(
				array_merge(
					array_slice($this->parent->_items['items'], 0, $box_position, true),
					[$shuffle_item],
					array_slice($this->parent->_items['items'], $box_position, null, true)
				)
			);

		}

		foreach ($this->parent->_items['items'] as $key => $item) {

			$map = [
				1 => [0],
				2 => [0, 0.5],
				3 => [0.5, 1, 0],
				4 => [0.5, 1, 0, 0.5],
				5 => [1, 0.5, 1, 0.5, 0],
				6 => [1, 0.5, 1, 0.5, 0, 0.5]
			];

			$per_row = ! empty($this->parent->_settings['per_row'] ) ? $this->parent->_settings['per_row'] : 6;

			$distance_factor = 0;

			if( ! empty($map[$per_row]) && ($key + 1) <= $per_row ){
				$distance_factor = $map[$per_row][$key % $per_row];
			}

			if( ! isset($item['link']) ){
				echo str_replace('{{FACTOR}}', $distance_factor, $item);
				continue;
			}

			$link = $this->parent->get_url($item);

			echo '<div class="rey-elInsta-item '. $anim_class .'" style="--td:'. $distance_factor .'">';
				echo '<a href="'. $link['url'] .'" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';

					$img_attributes = [
						'src' => $item['image-url'] ? $item['image-url'] : $item['original-image-url'],
						'alt' => $item['image-caption'],
					];

					// Add `loading` attribute.
					if ( wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ) {
						$img_attributes['loading'] = 'lazy';
					}

					printf( '<img class="rey-instaItem-img" %s>', reycore__implode_html_attributes($img_attributes) );

				echo '</a>';
			echo '</div>';
		}

	}

	public function render() {

		$this->parent->_settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute( 'wrapper', 'data-gap', $this->parent->_settings['gap'] ?? 30 );
		$this->parent->add_render_attribute( 'wrapper', 'data-per-row', $this->parent->_settings['per_row'] ?? 6 );

		if( $this->parent->lazy_start() ){
			return;
		}

		reycore_assets()->add_styles($this->parent->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->parent->query_items();
		$this->parent->render_start();
		$this->render_items();
		$this->parent->render_end();

		$this->parent->lazy_end();

	}
}
