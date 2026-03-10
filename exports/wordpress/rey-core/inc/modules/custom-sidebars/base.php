<?php
namespace ReyCore\Modules\CustomSidebars;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $custom_sidebars = [];

	private $swidgets = null;

	private $_swidgets_checked_conditions = [];

	const KEY = 'loop_sidebars';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'woo_init']);
		add_action( 'reycore/woocommerce/sidebar/widget_init', [ $this, 'register_sidebars'] );
		add_action( 'wp_ajax_rey_custom_sidebars_links', [$this, 'custom_sidebars_links']);
	}

	public function woo_init(){

		$this->migrate_v2();

		add_filter( 'sidebars_widgets', [$this, 'sidebar_widgets']);
		add_filter( 'rey/content/sidebar_class', [ $this, 'elementor_sidebar_custom_sidebars_classes'], 10, 2 );

		new Customizer();

	}

	/**
	 * Register sidebars
	 *
	 * @since 1.0.0
	 **/
	public function register_sidebars( $default_sidebars )
	{

		if( ! ($custom_sidebars = $this->get_custom_sidebars()) ){
			return;
		}

		foreach ($custom_sidebars as $id => $custom_sidebar) {

			$the_sidebar = $default_sidebars[ $custom_sidebar['type'] ];
			$the_sidebar['name'] = $custom_sidebar['name'];
			$the_sidebar['id'] = $id;


			global $pagenow;

			if( 'widgets.php' === $pagenow ){

				$active_taxs = [];

				$terms = !empty($custom_sidebar['terms']) ? get_terms( ['include' => $custom_sidebar['terms']] ) : [];

				foreach ($terms as $term) {
					if(isset($term->name) ){
						$active_taxs[] = sprintf('<a href="#widgets-only-visible-terms--%s" target="_blank">%s</a>', $term->term_id, $term->name);
					}
				}

				if( !empty($active_taxs) ){
					$the_sidebar['description'] .= ' ' . esc_html__('Only visible for: ', 'rey-core') . implode(', ', $active_taxs);
				}

			}

			register_sidebar( $the_sidebar );
		}

	}

	function default_sidebars(){
		return [
			\ReyCore\WooCommerce\Tags\Sidebar::SHOP_SIDEBAR_ID,
			\ReyCore\WooCommerce\Tags\Sidebar::FILTER_PANEL_SIDEBAR_ID,
			\ReyCore\WooCommerce\Tags\Sidebar::FILTER_TOP_BAR_SIDEBAR_ID,
		];
	}

	function sidebar_widgets($sidebars_widgets){

		if( wp_doing_ajax() || is_admin() ){
			return $sidebars_widgets;
		}

		if( ! empty($this->swidgets) ){
			return array_merge($sidebars_widgets, $this->swidgets);
		}

		$custom_sidebars = $this->get_custom_sidebars();

		// bail if no custom sidebars
		if( empty($custom_sidebars) ){
			return $sidebars_widgets;
		}

		$current_tax = is_product_taxonomy() ? get_queried_object() : false;

		foreach ($custom_sidebars as $id => $custom_sidebar) {

			// check conditions
			$conditions = [];

			// Run in taxonomies only
			if( $current_tax ){

				// just show in all taxonomies
				if( isset($custom_sidebar['all']) && 'yes' === $custom_sidebar['all'] ){
					$conditions[] = true;
					continue;
				}

				$terms = !empty(array_filter($custom_sidebar['terms'])) ? get_terms( ['include' => $custom_sidebar['terms']] ) : [];

				foreach ($terms as $term) {
					$conditions[] = $term->taxonomy === $current_tax->taxonomy && $term->term_id === $current_tax->term_id;
				}

				if( isset($custom_sidebar['tax']) && !empty(array_filter($custom_sidebar['tax'])) ){
					foreach ( (array) $custom_sidebar['tax'] as $taxonomy) {
						if( taxonomy_exists($taxonomy) ){
							$conditions[] = $taxonomy === $current_tax->taxonomy;
						}
					}
				}

			}
			else if( ! empty($custom_sidebar['shop_page']) && ('yes' === $custom_sidebar['shop_page']) && is_shop() ){
				$conditions[] = true;
			}

			if(
				empty($conditions) &&
				! empty($sidebars_widgets[ $id ]) &&
				is_page()
			){
				$conditions[] = true;
			}

			// if sidebar inactive, remove it from display
			if( ! in_array(true, $conditions, true) ){
				unset($sidebars_widgets[ $id ]);
			}

		}

		$default_sidebars = $this->default_sidebars();

		foreach ($default_sidebars as $default_single_sidebar) {

			foreach ($sidebars_widgets as $key => $sidebars_widget) {

				if( $key === $default_single_sidebar ){
					continue;
				}

				if( strpos($key, $default_single_sidebar ) === 0 ){
					$sidebars_widgets[$default_single_sidebar] = $sidebars_widget;
					$this->swidgets[$default_single_sidebar] = $sidebars_widget;
				}
			}

		}

		return $sidebars_widgets;
	}

	private function get_custom_sidebars(){

		if( ! empty($this->custom_sidebars) ){
			return $this->custom_sidebars;
		}

		$the_custom_sidebars = [];

		if( ! ($opt = $this->option()) ){
			return;
		}

		foreach ($opt as $key => $sidebar) {

			$terms = [];

			if( isset($sidebar['categories']) && $categories = $sidebar['categories'] ){
				$terms = array_merge($categories, $terms);
			}

			if( isset($sidebar['attributes']) && ($attributes = $sidebar['attributes']) ){
				$terms = array_merge($attributes, $terms);
			}

			if( isset($sidebar['terms']) && ($terms = $sidebar['terms']) ){
				$terms = array_merge($terms, $terms);
			}

			if( ! (isset($sidebar['name']) && $name = $sidebar['name']) ){
				continue;
			}

			if( ! (isset($sidebar['type']) && $type = $sidebar['type']) ){
				continue;
			}

			$default_sidebars = $this->default_sidebars();

			if( ! in_array($type, $default_sidebars, true) ){
				continue;
			}

			$id = $type . '-' . sanitize_title($name);
			$sidebar['terms'] = array_filter($terms);
			// $sidebar['terms'] = !empty($terms) ? get_terms( ['include' => $terms] ) : [];
			$the_custom_sidebars[$id] = $sidebar;
		}

		return $this->custom_sidebars = $the_custom_sidebars;
	}

	public function custom_sidebars_links(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json( ['error' => 'Invalid security nonce.'] );
		}

		if( ! (isset($_REQUEST['term_ids']) && $term_ids = reycore__clean($_REQUEST['term_ids'])) ){
			wp_send_json( ['error' => 'Empty terms.'] );
		}

		$terms = get_terms(['include'=>$term_ids]);
		$term_links = [];

		foreach ($terms as $term) {
			$term_links[$term->term_id] = get_term_link($term);
		}

		wp_send_json( $term_links );

	}

	/**
	 * Adds CSS classes to the Sidebar Element (when used in an Elementor built page),
	 * for custom sidebars to inherit the parent css classes
	 *
	 * @param array $classes
	 * @param string $sidebar
	 * @return array
	 */
	public function elementor_sidebar_custom_sidebars_classes( $classes, $sidebar ){

		foreach ($this->default_sidebars() as $value) {
			if( $sidebar !== $value && strpos($sidebar, $value) === 0 ){
				$classes['source_sidebar_class'] = $value;
			}
		}

		return $classes;
	}

	public function migrate_v2(){

		$opt = get_theme_mod(self::KEY, []);

		if( ! $opt ){
			return;
		}

		// set backup option
		set_theme_mod( self::KEY . '_bkp', $opt );

		foreach ( $opt as $k => $ls) {

			$terms = [];

			if( isset($ls['categories']) && ! empty($ls['categories']) ){
				$terms = array_merge($terms, $ls['categories']);
				unset($opt[$k]['categories']);
			}

			if( isset($ls['attributes']) && ! empty($ls['attributes']) ){
				$terms = array_merge($terms, $ls['attributes']);
				unset($opt[$k]['attributes']);
			}

			if( ! empty($terms) ){
				$opt[$k]['terms'] = $terms;
			}
		}

		// let's get rid of the legacy mod
		remove_theme_mod(self::KEY, []);

		// set the new option
		set_theme_mod( self::KEY . '_v2', $opt );

	}

	public function option(){
		return get_theme_mod(self::KEY . '_v2', []);
	}

	public function is_enabled() {
		return ! empty( $this->option() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Custom Sidebars', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to create new Sidebar positions, and assign them throughout categories and attributes.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/custom-sidebars-for-woocommerce'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
