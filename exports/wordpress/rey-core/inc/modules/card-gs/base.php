<?php
namespace ReyCore\Modules\CardGs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const GSTYPE = 'card';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);

		add_filter( 'reycore/global_sections/types', [$this, 'add_support'], 20);
		add_filter( 'reycore/acf/global_section_icons', [$this, 'add_icon'], 20);
		add_filter( 'reycore/acf/global_section_descriptions', [$this, 'add_description'], 20);
		add_action( 'elementor/element/reycore-carousel/section_content_style/before_section_end', [$this, 'add_card_gs_control_options']);
		add_action( 'elementor/element/reycore-grid/section_content_style/before_section_end', [$this, 'add_card_gs_control_options']);
		add_action( 'reycore/elementor/document_settings/gs/before', [$this, 'gs_settings']);
		add_action( 'reycore/cards/not_existing', [$this, 'render_gs'], 10, 2);

	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

	}

	public function add_support( $gs ){
		$gs[self::GSTYPE]  = __( 'Card', 'rey-core' );
		return $gs;
	}

	public function add_description( $gs ){
		$gs[self::GSTYPE]  = esc_html_x('Create custom templates with Elementor to be used in widgets such as Grid and Carousel, making great use of Rey Dynamic Tags module. This will allow you to build dynamic listings based on various content sources.', 'Global section description', 'rey-core');
		return $gs;
	}

	public function add_icon( $gs ){
		$gs[self::GSTYPE]  = 'woo-pdp-tabs-blocks';
		return $gs;
	}

	public function add_card_gs_control_options( $stack ){

		if( ! ($card_module = reycore__get_module('cards')) ){
			return;
		}

		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $stack->get_unique_name();

		// add to Layout list
		$card_control = $controls_manager->get_control_from_stack( $unique_name, $card_module::CARD_KEY );
		if( $card_control && ! is_wp_error($card_control) ) {
			$card_control['options'][self::GSTYPE] = esc_html__('Card Template (Global Section)', 'rey-core');
			$stack->update_control( $card_module::CARD_KEY, $card_control );
		}

		// Choose template
		$stack->add_control(
			'card_gs_template',
			[
				'label_block' => true,
				'label'       => __( 'Choose Card Template', 'rey-core' ),
				'type'        => 'rey-query',
				'default'     => '',
				'placeholder' => esc_html__('- Select -', 'rey-core'),
				'query_args'  => [
					'type'      => 'posts',
					'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
					'meta'      => [
						'meta_key'   => 'gs_type',
						'meta_value' => self::GSTYPE,
					],
					'edit_link' => true,
				],
				'condition' => [
					$card_module::CARD_KEY => self::GSTYPE,
				],
			]
		);

	}

	public function render_gs($card_id, $element){

		if( self::GSTYPE !== $card_id ){
			return;
		}

		$_settings = $element->_settings;

		if( ! ( isset($_settings['card_gs_template']) && ($card_gs_id = $_settings['card_gs_template']) ) ){
			return;
		}

		$item_settings = $element->_items[$element->item_key];

		/**
		 * POST DATA
		 */
		if( isset($item_settings['post_id']) && ($post_id = $item_settings['post_id']) ){

			$GLOBALS['post'] = get_post( $post_id ); // WPCS: override ok.
			setup_postdata( $GLOBALS['post'] );

				self::render_the_global_section($card_gs_id);

			wp_reset_postdata();

		}

		/**
		 * TERM DATA
		 */
		else if( isset($item_settings['term']) && ($term = $item_settings['term']) && isset($term->taxonomy) ){

			global $wp_query, $wp_the_query;

			// Backup the original objects.
			$original_wp_query = clone $wp_query;
			$original_wp_the_query = clone $wp_the_query;

			// Mimic a term archive state.
			$wp_query->init();
			$wp_query->is_tax = true;
			$wp_query->queried_object = $term;
			$wp_query->queried_object_id = $term->term_id;
			$wp_query->set('taxonomy', $term->taxonomy);
			$wp_query->set('term', $term->slug);

				reycore_assets()->defer_page_styles('elementor-post-' . $card_gs_id);
				echo \ReyCore\Elementor\GlobalSections::do_section($card_gs_id, false, true);

			// Restore the original query objects.
			$wp_query = clone $original_wp_query;
			$wp_the_query = clone $original_wp_the_query;

		}

		/**
		 * CUSTOM CONTENT DATA
		 * as fake post
		 */
		else if( isset($item_settings['custom_content']) && $item_settings['custom_content'] ){

			// Create a new stdClass object
			$fake_post = (object) [];

			// Set the properties for the fake post
			$fake_post->ID                    = -1;
			$fake_post->post_author           = 1; // ID of the author
			$fake_post->post_content          = $item_settings['subtitle'];
			$fake_post->post_title            = $item_settings['title'];
			$fake_post->post_excerpt          = $item_settings['label'];
			$fake_post->post_status           = 'publish';
			$fake_post->post_parent           = 0;
			$fake_post->post_type             = 'post';
			$fake_post->filter                =  'raw';

			// $fake_post->post_date             = current_time('mysql');
			// $fake_post->post_date_gmt         = current_time('mysql', 1);
			// $fake_post->comment_status        = 'open';
			// $fake_post->ping_status           = 'closed';
			// $fake_post->post_password         = '';
			// $fake_post->post_name             = 'fake-post';
			// $fake_post->to_ping               = '';
			// $fake_post->pinged                = '';
			// $fake_post->post_modified         = current_time('mysql');
			// $fake_post->post_modified_gmt     = current_time('mysql', 1);
			// $fake_post->post_content_filtered = '';
			// $fake_post->guid                  = '';
			// $fake_post->menu_order            = 0;
			// $fake_post->post_mime_type        = '';
			// $fake_post->comment_count         = 0;

			// Convert the stdClass object to a WP_Post object
			$GLOBALS['post'] = new \WP_Post($fake_post);

			setup_postdata( $GLOBALS['post'] );

				$post_link = function() use ($item_settings){
					return $item_settings['button_url']['url'];
				};

				$post_thumb_id = function(){
					return -1;
				};

				$post_thumb_url = function() use ($item_settings){
					return $item_settings['image']['url'];
				};

				add_filter( 'post_link', $post_link );
				add_filter( 'post_thumbnail_id', $post_thumb_id );
				add_filter( 'post_thumbnail_url', $post_thumb_url );

					self::render_the_global_section($card_gs_id);

				remove_filter( 'post_link', $post_link );
				remove_filter( 'post_thumbnail_id', $post_thumb_id );
				remove_filter( 'post_thumbnail_url', $post_thumb_url );

			wp_reset_postdata();

		}
	}

	public static function render_the_global_section($card_gs_id){

		reycore_assets()->defer_page_styles('elementor-post-' . $card_gs_id);
		echo \ReyCore\Elementor\GlobalSections::do_section($card_gs_id, false, true);

	}


	/**
	 * Add page settings into Elementor
	 *
	 * @since 2.4.4
	 */
	public function gs_settings( $doc )
	{

		$params = $doc->get_params();
		$params['preview_width'][] = self::GSTYPE;
		$doc->set_params($params);

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Card Global Section', 'Module name', 'rey-core'),
			'description' => esc_html_x('Build global sections which are used as templates for various elements (Carousel, Grid)', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['elementor', 'carousel', 'grid'],
			'video'       => true,
			'help'        => reycore__support_url('kb/card-global-section/'),
		];
	}

	public function module_in_use(){
		return ! empty(\ReyCore\Elementor\GlobalSections::get_global_sections(self::GSTYPE));
	}

}
