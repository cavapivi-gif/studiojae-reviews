<?php
namespace ReyCore\Modules\CustomTemplates;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	const POST_TYPE = REY_CORE_THEME_NAME . '-templates';
	const POST_TYPE_TAXONOMY = REY_CORE_THEME_NAME . '-templates-cat';
	const OPTION = 'rey_templates_data';

	const ASSET_HANDLE = 'reycore-custom-templates';

	private $has_taxonomy = false;

	public $template = [];

	public $singular_group_key = 'group_5c4ad0bd35b33';
	public $singular_excluded_options = [];

	public $elementor;

	public $saved_templates;

	private $mods = [];

	private static $_instance = null;

	public function __construct()
	{

		$this->includes();

		new AcfPopulate();
		new Elementor();

		add_action( 'init', [$this, 'initialize'] );
		add_filter( "manage_". self::POST_TYPE ."_posts_columns", [$this, 'add_columns'] );
		add_action( "manage_". self::POST_TYPE ."_posts_custom_column" , [$this, 'manage_column'], 10, 2 );
		add_filter( 'acf/load_field_group', [$this, 'load_singular_settings']);
		add_action( 'admin_init', [$this, 'refresh_stored_templates']);
		add_action( 'reycore/customizer/section', [$this, 'print_customizer_notices'], 10, 2);
	}

	public function includes() {
		require_once __DIR__ . '/functions.php';
		require_once __DIR__ . '/acf-fields.php';
	}

	public function initialize()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		$this->saved_templates = get_option(self::OPTION, []);

		$this->register_post_type();
		$this->set_settings();

		add_action( 'template_redirect', [ $this, 'block_frontend' ] );
		add_action( 'admin_menu', [$this, 'register_admin_menu'], 50 );
		add_action( 'wp', [$this, 'set_active_template'], 5 );
		add_action( 'wp', [$this, 'apply_conditions'], 9 );
		add_filter( 'reycore/admin_bar_menu/nodes', [$this, 'admin_menu_link'] );
		add_action( 'reycore/admin_bar_menu/page_components/nodes', [$this, 'add_page_component_node'], 10, 2 );
		add_action( 'admin_footer', [$this, 'add_back_button']);
		add_action( 'save_post_' . self::POST_TYPE, [$this, 'save_template'], 20, 3 );
		add_action( 'delete_post', [$this, 'delete_template'], 20 );
		add_action( 'wp_trash_post', [$this, 'delete_template'], 20 );
		add_filter( 'views_edit-' . self::POST_TYPE, [$this, 'add_pt_links']);
		add_filter( 'body_class', [ $this, 'body_class'], 30 );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter('reycore/woocommerce/products/minimal_tpl', [$this, 'prevent_filters_minimal_template']);
	}

	/**
	 * Add Columns
	 *
	 * @since 1.0.0
	 **/
	public function add_columns( $columns )
	{

		$n_columns = [];
		$before = 'date';

		foreach($columns as $key => $value) {

			if ( $key == $before ){

				$n_columns['reycore_status_column'] = __( 'Status', 'rey-core' );
				$n_columns['reycore_type_column'] = __( 'Type', 'rey-core' );
				$n_columns['reycore_priority_column'] = __( 'Priority', 'rey-core' );

				if( class_exists('\Elementor\Plugin') && is_callable( '\Elementor\Plugin::instance' ) ){
					$n_columns['reycore_elem_column'] = __( 'Elementor', 'rey-core' );
				}
			}

			$n_columns[$key] = $value;
		}



		return $n_columns;
	}

	/**
	 * Add Columns
	 *
	 * @since 1.0.0
	 **/
	public function manage_column( $column, $post_id ) {

		switch ( $column ) {

			case 'reycore_status_column' :
				if( $status = get_post_status($post_id) ):
					$status_type = 'publish' === $status ? esc_html__('Active', 'rey-core') : esc_html__('Inactive', 'rey-core');
					printf('<strong class="col-%1$s">%2$s</strong>', $status, ucwords( $status_type ));
				endif;
			break;

			case 'reycore_type_column' :
				if( $type = (string) get_field('template_type', $post_id) ):
					printf('<strong>%s</strong>', ucwords( $type ));
				endif;
			break;

			case 'reycore_priority_column' :

				if( $priority = absint(get_field('template_priority', $post_id)) ):
					printf('<strong>%s</strong>', ucwords( $priority ));
				endif;

			break;

			case 'reycore_elem_column' :

				$text = '-';

				if( ( $document = \Elementor\Plugin::$instance->documents->get( $post_id ) ) && $document->is_built_with_elementor() ){
					$text = esc_html__('Yes', 'rey-core');
				}

				printf('<strong>%s</strong>', $text);

			break;
		}

	}

	public function register_assets( $assets ){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);
	}

	public function set_active_template(){

		// append ?no-template=1 to the url to disable the template.
		if( current_user_can('administrator') && isset($_REQUEST['no-template']) && 1 === absint($_REQUEST['no-template']) ){
			return;
		}

		$saved_templates = is_null($this->saved_templates) ? get_option(self::OPTION, []) : $this->saved_templates;
		$active_template = Conditions::getInstance()->check_conditions( $saved_templates );

		if( $active_template ){
			$this->template = $active_template;
		}

	}

	function get_template_data($template_id, $template_type){

		$data = [
			'id'                         => $template_id,
			'template_type'              => $template_type,
			'template_priority'          => get_field( 'template_priority', $template_id ),
		];

		if( 'product' === $template_type ){
			$data['product_conditions'] = get_field( 'product_conditions', $template_id );
		}

		elseif( 'product-archive' === $template_type ){
			$data['product_archive_conditions'] = get_field( 'product_archive_conditions', $template_id );
		}

		elseif( 'archive' === $template_type ){
			$data['archive_conditions'] = get_field( 'archive_conditions', $template_id );
		}

		elseif( 'single' === $template_type ){
			$data['general_conditions'] = get_field( 'general_conditions', $template_id );
		}

		elseif( 'page' === $template_type ){
			$data['pages'] = get_field( 'pages', $template_id );
			$data['pages_operator'] = get_field( 'pages_operator', $template_id );
			$data['page_types'] = get_field( 'page_types', $template_id );
		}

		return $data;
	}

	function add_pt_links( $links ){

		$links['refresh'] = sprintf('<a href="%s" class="rey-ct-refresh"><span>%s</span></a>', admin_url('edit.php?post_type='. self::POST_TYPE .'&tpl_action=refresh_templates'), esc_html__('Refresh data', 'rey-core') );
		$links['help'] = sprintf('<a href="%s" class="rey-ct-help" target="_blank"><span>%s</span> <span class="dashicons dashicons-editor-help"></span></a>', reycore__support_url('kb/custom-templates'), esc_html__('Help', 'rey-core') );

		return $links;
	}

	function refresh_stored_templates(){

		if( ! (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === self::POST_TYPE) ){
			return;
		}

		if( ! ( isset($_REQUEST['tpl_action']) && $_REQUEST['tpl_action'] === 'refresh_templates' ) ){
			return;
		}

		$template_posts_ids = get_posts([
			'posts_per_page' => $this->settings['max'],
			'orderby'        => 'date',
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'fields'         => 'ids'
		]);

		$templates = [];

		foreach ($template_posts_ids as $template_id) {

			$template_type = get_field( 'template_type', $template_id );
			$templates[$template_type][$template_id] = $this->get_template_data($template_id, $template_type);

		}

		update_option(self::OPTION, $templates);
	}

	function save_template($template_id, $template, $update){

		// Avoiding autosave, auto-draft, and revisions
		if (
			defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
			$template->post_status == 'auto-draft' ||
			$template->post_status == 'trash' ||
			wp_is_post_revision($template_id)) {
			return;
		}

		// not checking for $update because the template type might be changed

		$templates = get_option(self::OPTION, []);

		$template_type = get_field( 'template_type', $template_id );

		if( 'publish' !== $template->post_status ){
			unset($templates[$template_type][$template_id]);
		}
		else {
			$templates[$template_type][$template_id] = $this->get_template_data($template_id, $template_type);
		}

		update_option(self::OPTION, $templates);
	}

	function delete_template($template_id){

		global $post;

		if( is_null($post) ){
			return;
		}

		if( ! isset($post->post_type) ){
			return;
		}

		if( self::POST_TYPE !== $post->post_type ){
			return;
		}

		$templates = (array) get_option(self::OPTION, []);

		foreach ($templates as $type => $templateItems) {
			if (is_array($templateItems)) {
				unset($templateItems[$template_id]);
				$templates[$type] = $templateItems; // Reassign back to ensure the changes are saved
			}
		}

		update_option(self::OPTION, $templates);
	}

	public function get_active_template(){
		return $this->template;
	}

	/**
	 * Apply conditions to matches
	 */
	function apply_conditions(){

		if( empty($this->template) ){
			return;
		}

		$excluded_options = $this->singular_excluded_options;

		$options = acf_get_fields($this->singular_group_key);
		$options = array_filter( wp_list_pluck($options, 'name'), function( $option ) use ($excluded_options){
			return $option !== '' && ! in_array($option, $excluded_options, true);
		} );

		foreach ($options as $option) {

			$filter = function( $mod, $post_id = null ) use ($option) {

				if( isset($this->mods[$option]) ){
					return $this->mods[$option];
				}

				if( $template_option = get_field($option, $this->template['id']) ){

					// default value, just override
					$mod_override = $template_option;

					// don't override individual settings (unless specified)
					if( ! get_field('template_override_individual', $this->template['id']) && $mod){
						$mod_override = $mod;
					}

					// set the template options
					$mod = $mod_override;
				}

				// append extra class to body
				if( $option === 'rey_body_class' ){
					$mod .= ' rey-template-type rey-template-' . absint( $this->template['id'] );
				}

				return $this->mods[$option] = $mod;
			};

			// add_filter('theme_mod_'. $option, $filter);
			add_filter('rey_acf_option_'. $option, $filter, 100, 2);
		}

		add_filter('reycore/page_styles/cache', '__return_false');

	}

	function body_class($classes){

		if( empty($this->template) ){
			return $classes;
		}

		if( ! isset($this->template['id']) ){
			return $classes;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $this->template['id'] );

		if ( $document && $document->is_built_with_elementor() ) {
			unset($classes['pdp_skin']);
		}

		return $classes;
	}

	public function register_post_type() {

		$labels = array(
			'name'                  => _x( 'Custom Templates', 'Post Type General Name', 'rey-core' ),
			'singular_name'         => _x( 'Custom Template', 'Post Type Singular Name', 'rey-core' ),
			'menu_name'             => __( 'Custom Templates', 'rey-core' ),
			'name_admin_bar'        => __( 'Custom Template', 'rey-core' ),
			'archives'              => __( 'List Archives', 'rey-core' ),
			'parent_item_colon'     => __( 'Parent List:', 'rey-core' ),
			'all_items'             => __( 'All Custom Templates', 'rey-core' ),
			'add_new_item'          => __( 'Add New Custom Template', 'rey-core' ),
			'add_new'               => __( 'Add New', 'rey-core' ),
			'new_item'              => __( 'New Custom Template', 'rey-core' ),
			'edit_item'             => __( 'Edit Custom Template', 'rey-core' ),
			'update_item'           => __( 'Update Custom Template', 'rey-core' ),
			'view_item'             => __( 'View Custom Template', 'rey-core' ),
			'search_items'          => __( 'Search Custom Template', 'rey-core' ),
			'not_found'             => __( 'Not found', 'rey-core' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'rey-core' )
		);

		$args = [
			'labels'              => $labels,
			'public'              => true,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'elementor' ],
			'register_meta_box_cb' => [$this, 'remove_meta_box']
		];

		if( $this->has_taxonomy ) {
			$args['taxonomies'][] = self::POST_TYPE_TAXONOMY;
		}

		register_post_type( self::POST_TYPE, $args );

		if( $this->has_taxonomy ) {
			register_taxonomy(
				self::POST_TYPE_TAXONOMY,
				self::POST_TYPE,
				[
					'public'              => false,
					'rewrite'             => false,
					'hierarchical'        => true,
					'show_ui'             => true,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,
					'exclude_from_search' => true,
					'show_admin_column'   => true,
					'labels'              => [
						'name'          => _x( 'Custom Template Categories', 'Custom Templates', 'rey-core' ),
						'singular_name' => _x( 'Custom Template Category', 'Custom Templates', 'rey-core' ),
						'all_items'     => _x( 'All Custom Template Categories', 'Custom Templates', 'rey-core' ),
					],

				]
			);
		}
	}

	function remove_meta_box(){
		remove_meta_box( 'pageparentdiv', self::POST_TYPE, 'side' );
	}

	/**
	 * Block custom template in frontend
	 *
	 * Don't display the single view of the post type in the frontend for users that don't have the proper permissions.
	 *
	 * Fired by `template_redirect` action.
	 *
	 * @since 3.0.1
	 * @access public
	 */
	public function block_frontend() {
		if ( ! empty( $_GET[ self::POST_TYPE ] ) && ! is_admin() && ! current_user_can( 'edit_posts' ) ) {
			wp_safe_redirect( site_url(), 301 );
			die;
		}
	}

	/**
	 * Register the admin menu.
	 *
	 * @since  1.0.0
	 */
	public function register_admin_menu() {

		if( $dashboard_id = reycore__get_dashboard_page_id() ){
			add_submenu_page(
				$dashboard_id,
				__( 'Custom Templates', 'rey-core' ),
				__( 'Custom Templates', 'rey-core' ),
				'edit_pages',
				'edit.php?post_type=' . self::POST_TYPE
			);

			if( $this->has_taxonomy ) {
				add_submenu_page(
					$dashboard_id,
					__( 'Custom Templates Categories', 'rey-core' ),
					__( 'Custom Templates Categories', 'rey-core' ),
					'edit_pages',
					sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s', self::POST_TYPE_TAXONOMY, self::POST_TYPE ),
					null
				);
			}
		}
	}

	function load_singular_settings($field_group){

		if( \ReyCore\ACF\Helper::prevent_export_dynamic_field() ){
			return $field_group;
		}

		if( ! (isset($field_group['key']) && $field_group['key'] === $this->singular_group_key) ){
			return $field_group;
		}

		$field_group['location'][] = [
			[
				'param' => 'post_type',
				'operator' => '==',
				'value' => self::POST_TYPE,
			]
		];

		return $field_group;

	}

	private function set_settings(){
		$this->settings = apply_filters('reycore/module/rey_templates', [
			'max' => 100
		]);
	}

	public function prevent_filters_minimal_template( $status ){

		if( empty( $this->template ) ){
			return $status;
		}

		if( !isset($this->template['id']) ){
			return $status;
		}

		return false;
	}

	function admin_menu_link( $nodes ){

		if( ! is_array($nodes) ){
			return $nodes;
		}

		if( empty( $this->template ) ){
			return $nodes;
		}

		if( !isset($this->template['id']) ){
			return $nodes;
		}

		$nodes['rey_template'] = [
			'title'  => sprintf(__('Custom Template: <strong>%s</strong>', 'rey-core'), get_the_title($this->template['id'])),
			'href'  => get_edit_post_link($this->template['id']),
			'top'  => true,
			'new'  => true,
			'class' => 'rey-abQuickMenu-tplTitle'

		];

		if( isset($nodes['main']['class']) ){
			$nodes['main']['class'] .= ' --has-rt';
		}

		return $nodes;
	}


	public function add_page_component_node($admin_bar, $slug){

		if( empty( $this->template ) ){
			return;
		}

		if( !isset($this->template['id']) ){
			return;
		}

		$admin_bar->add_node(
			[
				'id'     => 'edit-rey-cover',
				'title'  => sprintf(esc_html__('Edit Custom Template (%s)', 'rey-core'), get_the_title($this->template['id'])),
				'href'   => get_edit_post_link($this->template['id']),
				'parent' => 'edit',
			]
		);

	}

	function add_back_button(){

		\ReyCore\Admin::render_top_buttons([
			'post_type' => self::POST_TYPE,
			'export_label' => 'Custom Template',
		]);

	}

	function print_customizer_notices( $section_id, $section_manager ){

		if( is_null($this->saved_templates) ){
			$this->saved_templates = (array) get_option(self::OPTION, []);
		}

		if( empty($this->saved_templates) ){
			return;
		}

		$sections = [
			'product' => [
				\ReyCore\Customizer\Options\Woocommerce\ProductPageLayout::get_id(),
				\ReyCore\Customizer\Options\Woocommerce\ProductPageGallery::get_id(),
				\ReyCore\Customizer\Options\Woocommerce\ProductPageSummaryComponents::get_id(),
				\ReyCore\Customizer\Options\Woocommerce\ProductPageComponents::get_id(),
				\ReyCore\Customizer\Options\Woocommerce\ProductPageTabs::get_id(),
			]
		];

		if( isset($this->saved_templates['product']) && empty($sections['product']) ){
			foreach ($sections['product'] as $section) {

				if( in_array($section_id, $sections, true) ){
					continue;
				}

				$section_manager->add_notice([
					'section'     => $section,
					'default'     => esc_html_x('Heads up! There are Custom Templates assigned for product pages. Therefore some of these settings might not work correctly because they\'re overriden by the elements in those templates.', ' Customizer control label', 'rey-core')
				] );
			}
		}

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Custom Templates for Elementor', 'Module name', 'rey-core'),
			'description' => esc_html_x('Override the content of any page with custom Elementor templates, assigned to a specific target location.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Elementor', 'Product Page', 'Product catalog'],
			'help'        => reycore__support_url('kb/custom-templates/'),
			'video'        => true,
		];
	}

	public function module_in_use(){
		return ! empty( get_option(self::OPTION, []) );
	}

}
