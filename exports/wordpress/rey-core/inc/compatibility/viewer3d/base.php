<?php
namespace ReyCore\Compatibility\Viewer3d;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct() {

		add_action( 'acf/init', [$this, 'add_fields']);
		add_action('reycore/woocommerce/product_image/after_gallery_wrapper', [$this, 'render_under_gallery']);

	}

	function render_under_gallery(){

		if( ! apply_filters('reycore/compat/3dviewer/show_under_gallery', true) ){
			return;
		}

		if( ! class_exists('\ACF') ){
			return;
		}

		if( !($file_id = get_field('3d_viewer_files')) ){
			return;
		}

		$shortcode = sprintf('[3d_viewer id="%s"]', $file_id);

		echo do_shortcode($shortcode);
	}


	function add_fields(){

		if( ! function_exists('acf_add_local_field_group') ){
			return;
		}

		acf_add_local_field_group(array(
			'key' => 'group_611bee54164f8',
			'title' => '3d viewer files',
			'fields' => array(
				array(
					'key' => 'field_611bee5fe1ae2',
					'label' => esc_html_x('3D Viewer files', 'Backend setting label', 'rey-core'),
					'name' => '3d_viewer_files',
					'type' => 'post_object',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'bp3d-model-viewer',
					),
					'taxonomy' => '',
					'allow_null' => 1,
					'multiple' => 0,
					'return_format' => 'id',
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'product',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}
}
