<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TagPosts
{

	public $_settings = [];
	public $_args = [];

	function __construct( $args = [], $settings = [] )
	{

		$this->_settings = $settings;
		$this->_args = $args;

	}

	function lazy_start(){

		if( ! isset($this->_settings['lazy_load']) ){
			return;
		}

		// Initial Load (not Ajax)
		if( '' !== $this->_settings['lazy_load'] &&
			'yes' !== $this->_settings['add_pagination'] &&
			! \ReyCore\Ajax::doing_ajax() &&
			! ( reycore__elementor_edit_mode() ) ){

			$qid = (isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids'])) ? end($gs_ids) : get_queried_object_id();

			$config = [
				'element_id' => $this->_args['el_instance']->get_id(),
				'skin'       => $this->_settings['_skin'],
				'trigger'    => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger']: 'scroll',
				'qid'        => apply_filters('reycore/elementor/posts/lazy_load_qid', $qid),
				'options'    => apply_filters('reycore/elementor/posts/lazy_load_options', []),
				'cache'      => $this->_settings['lazy_load_cache'] !== '',
			];

			if( 'click' === $this->_settings['lazy_load_trigger'] ){
				$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
			}

			$this->_args['el_instance']->add_render_attribute( '_wrapper', [
				'data-lazy-load' => wp_json_encode( $config )
			] );

			if( $this->_settings['carousel'] !== '' ){
				$per_row = $this->_settings['slides_to_show'];
				$per_row_tablet = isset($this->_settings['slides_to_show_tablet']) ? $this->_settings['slides_to_show_tablet'] : 2;
				$per_row_mobile = isset($this->_settings['slides_to_show_mobile']) ? $this->_settings['slides_to_show_mobile'] : 1;
			}
			else {
				$per_row = $this->_settings['per_row'];
				$per_row_tablet = isset($this->_settings['per_row_tablet']) ? $this->_settings['per_row_tablet'] : 2;
				$per_row_mobile = isset($this->_settings['per_row_mobile']) ? $this->_settings['per_row_mobile'] : 1;
			}

			echo reycore__lazy_placeholders([
				'class'              => 'placeholder_posts',
				'filter_title'       => 'placeholder_posts',
				'blocktitle'         => false,
				'desktop'            => absint($per_row),
				'tablet'             => absint($per_row_tablet),
				'mobile'             => absint($per_row_mobile),
				'limit'              => $this->_settings['carousel'] === '' ? $this->_settings['posts_per_page']: $per_row,
				'placeholders_class' => isset($this->_args['placeholder_class']) ? $this->_args['placeholder_class'] : '',
				// 'nowrap'             => $this->_settings['carousel'] === '',
			]);

			$scripts = ['reycore-elementor-elem-lazy-load', 'reycore-widget-basic-post-grid-scripts'];

			if( ! empty($scripts) ){
				reycore_assets()->add_scripts($scripts);
			}

			do_action('reycore/elementor/posts/lazy_load_assets', $this->_settings);

			return true;
		}

		return false;
	}

	function lazy_end(){

	}

}
