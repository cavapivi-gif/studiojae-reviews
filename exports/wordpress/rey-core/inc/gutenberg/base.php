<?php
namespace ReyCore\Gutenberg;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base
{
	public $excerpt_length;

	public $slug = 'reycore';
	public $category = 'rey-blocks';
	public $path = 'inc/gutenberg/';

	public function __construct()
	{

		add_action( 'init', [$this, 'init'] );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_acf_block_scripts']);

	}

	function init(){

		$this->register_js_blocks_assets();
		$this->register_js_blocks();
		$this->register_acf_blocks();

		add_action( 'enqueue_block_assets', [ $this, 'enqueue_js_blocks_assets' ] );

	}

	public function get_asset_file( $filepath ) {

		$asset_path = REY_CORE_DIR . $this->path . $filepath . '.asset.php';

		return file_exists( $asset_path )
			? include $asset_path
			: [
				'dependencies' => [],
				'version'      => REY_CORE_VERSION,
			];
	}

	function register_js_blocks_assets(){

		// Styles.
		$filepath   = 'dist/blocks';
		$asset_file = $this->get_asset_file( $filepath );
		$rtl        = ! is_rtl() ? '' : '-rtl';

		wp_register_style(
			$this->slug . '-blocks-editor',
			REY_CORE_URI . $this->path . $filepath . $rtl . '.css',
			[],
			$asset_file['version']
		);


		wp_register_script(
			$this->slug . '-blocks-editor',
			REY_CORE_URI . $this->path . $filepath . '.js',
			array_merge( $asset_file['dependencies'], ['wp-api'] ),
			$asset_file['version'],
			true
		);

	}

	/**
	 * Enqueue block assets for use within Gutenberg.
	 *
	 * @access public
	 */
	public function enqueue_js_blocks_assets() {

		if ( is_admin() || ! is_singular('post') ) {
			return;
		}

		// Styles.
		$filepath   = 'dist/style-blocks';
		$asset_file = $this->get_asset_file( $filepath );
		$rtl        = ! is_rtl() ? '' : '-rtl';

		wp_enqueue_style(
			$this->slug . '-blocks-frontend',
			REY_CORE_URI . $this->path . $filepath . $rtl . '.css',
			[],
			$asset_file['version']
		);
	}

	function register_js_blocks(){

		// Return early if this function does not exist.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$blocks = [
			'container-v1',
			'spacer-v1',
		];

		foreach ($blocks as $block) {
			register_block_type( $this->slug . '/' . $block, [
				'editor_script' => $this->slug . '-blocks-editor',
				'editor_style'  => $this->slug . '-blocks-editor',
				'style'         => $this->slug . '-blocks-frontend',
			]);
		}
	}

	function register_acf_blocks() {

		require REY_CORE_DIR . $this->path . 'acf-fields/blocks-fields.php';

		// Return early if this function does not exist.
		if( ! function_exists('acf_register_block_type') ) {
			return;
		}

		$blocks = [
			'posts-v1' => [
				'title'				=> __('Posts [rey]'),
				'description'		=> __('A custom block for posts items.'),
				'category'			=> $this->category,
				'icon'				=> '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd"><path d="M4,4 C2.34314575,4 1,5.34314575 1,7 L1,17 C1,18.6568542 2.34314575,20 4,20 L20,20 C21.6568542,20 23,18.6568542 23,17 L23,7 C23,5.34314575 21.6568542,4 20,4 L4,4 Z" stroke="#CD2323" strokeWidth="2"></path><path d="M7,11 L9,11 L9,9 L7,9 L7,11 Z M7,15 L9,15 L9,13 L7,13 L7,15 Z M10,11 L17,11 L17,9 L10,9 L10,11 Z M10,15 L17,15 L17,13 L10,13 L10,15 Z" fill="#000000" fillRule="nonzero"></path></g></svg>',
				'keywords'			=> ['posts', 'blog', 'related'],
				'render_callback'	=> [$this, 'acf_block__postsv1'],
			],
			'elementor-global-sections' => [
				'title'				=> __('Elementor Global Section [rey]'),
				'description'		=> __('A black that can hold a global section.'),
				'category'			=> $this->category,
				'icon'				=> '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd"><path d="M4,4 C2.34314575,4 1,5.34314575 1,7 L1,17 C1,18.6568542 2.34314575,20 4,20 L20,20 C21.6568542,20 23,18.6568542 23,17 L23,7 C23,5.34314575 21.6568542,4 20,4 L4,4 Z" stroke="#CD2323" strokeWidth="2"></path><path d="M10,13 L17,13 L17,11 L10,11 L10,13 Z M7,16 L9,16 L9,8 L7,8 L7,16 Z M10,10 L17,10 L17,8 L10,8 L10,10 Z M10,16 L17,16 L17,14 L10,14 L10,16 Z" fill="#000000" fillRule="nonzero"></path></g></svg>',
				'keywords'			=> ['elementor', 'section'],
				'render_callback'	=> [$this, 'acf_block__elementor_global_section'],
			],
		];

		foreach ($blocks as $block_slug => $block_data) {

			$args = [
				'name' => $this->slug . '/' . $block_slug,
			];

			foreach ($block_data as $key => $value) {
				$args[$key] = $value;
			}

			acf_register_block_type($args);
		}

	}

	function acf_block__postsv1( $block, $content = '', $is_preview = false, $post_id = 0){

		if( $is_preview ){
			printf('<div class="reyBlock-noPreview">%s<span>%s</span></div>', $block['icon'], esc_html__('Please preview in frontend.', 'rey-core'));
			return;
		}

		$args = [
			'columns'         => ($columns = get_field('columns')) ? absint($columns) : 1,
			'show_image'      => !! get_field('show_image'),
			'show_date'       => !! get_field('show_date'),
			'show_categories' => get_field('show_categories'),
			'show_excerpt'    => get_field('show_excerpt'),
			'vertical_sep'    => get_field('vertical_separator'),
			'image_alignment' => ($image_alignment = get_field('image_alignment')) ? $image_alignment : 'left',
			'image_size' => ($image_size = get_field('image_size')) ? $image_size : 'medium',
			'image_width' => ($image_width = get_field('image_width')) ? $image_width : false,
			'image_height' => ($image_height = get_field('image_height')) ? $image_height : false,
			'title_size' => ($title_size = get_field('title_size')) ? $title_size : 'sm',
			'title_numerotation' => ($numerotation = get_field('numerotation')) ? $numerotation : false,
			'image_roundness' => ($image_roundness = get_field('image_roundness')) ? absint($image_roundness) : 0,
			'gap_size' => ($gap_size = get_field('gap_size')) ? absint($gap_size) : 30,
		];

		if( $args['columns'] !== 1 ){
			$args['title_numerotation'] = false;
		}

		$query_args = [
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'posts_per_page' => ($limit   = get_field('limit')) ? absint($limit)     : 4,
			'orderby'        => ($orderby = get_field('order_by')) ? $orderby: 'date',
			'order'          => ($order   = get_field('order_direction')) ? $order : 'desc',
		];

		$query_type = ($qt = get_field('query_type')) ? $qt : 'related';

		if( 'related' === $query_type ){

			if( $cats_from_same = wp_get_post_categories( get_the_ID() ) ){
				$query_args['category__in'] = $cats_from_same;
			}

			// find better solution than `post__not_in`
			$query_args['post__not_in'][] = get_the_ID();

		}

		elseif( 'custom' === $query_type ){

			if( $categories = get_field('categories') ){
				$query_args['category__in'] = $categories;
			}
			if( $tags = get_field('tags') ){
				$query_args['tag__in'] = $tags;
			}

			$query_args['post__not_in'][] = get_the_ID();

		}

		elseif( 'manual' === $query_type ){

			if( $manual_posts = get_field('manual_posts') ){
				$query_args['post__in'] = (array) $manual_posts;
				unset($query_args['posts_per_page']);
			}

		}

		$query = new \WP_Query($query_args);
		$args['posts'] = $query->query($query_args);

		if( $args['show_excerpt'] && $excerpt_length = get_field('excerpt_length') ){
			$this->excerpt_length = $excerpt_length;
		}

		if( $this->excerpt_length ){
			add_filter( 'excerpt_length', [$this, '__excerpt_length'], 999 );
		}

		reycore_assets()->add_styles( $this->slug . '-blocks-posts-v1' );

		reycore__get_template_part('template-parts/blocks/posts-v1', false, false, $args);

		if( $this->excerpt_length ){
			remove_filter( 'excerpt_length', [$this, '__excerpt_length'], 999 );
		}
	}

	function acf_block__elementor_global_section( $block, $content = '', $is_preview = false, $post_id = 0){

		if( $is_preview ){
			printf('<div class="reyBlock-noPreview">%s<span>%s</span></div>', $block['icon'], esc_html__('Please preview in frontend.', 'rey-core'));
			return;
		}

		if( class_exists('\ReyCore\Elementor\GlobalSections') && isset($block['data']['global_section']) && $gs = $block['data']['global_section'] ){
			echo \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);
		}

	}

	function __excerpt_length( $length ){

		if( $this->excerpt_length ){
			$length = absint($this->excerpt_length);
		}

		return $length;
	}

	function register_acf_block_scripts($assets)
	{
		$assets->register_asset('styles', $this->blocks_styles());
		$assets->register_asset('scripts', $this->blocks_scripts());
	}

	function blocks_styles(){

		$rtl = reycore_assets()::rtl();

		$filepath   = 'dist/blocks';
		$asset_file = $this->get_asset_file( $filepath );

		$styles = [
			$this->slug . '-blocks-posts-v1' => [
				'src'     => REY_CORE_URI . 'assets/css/blocks/posts-v1/posts-v1' . $rtl . '.css',
			],
		];

		foreach ($styles as $key => $style) {

			if( ! isset($style['deps']) ){
				$styles[$key]['deps'] = [ REY_CORE_STYLESHEET_HANDLE ];
			}

			if( ! isset($style['version']) ){
				$styles[$key]['version'] = REY_CORE_VERSION;
			}
		}

		return $styles;
	}

	function blocks_scripts(){
		return [];
	}

}
