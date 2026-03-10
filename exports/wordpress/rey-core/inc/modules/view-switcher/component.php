<?php
namespace ReyCore\Modules\ViewSwitcher;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Component extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function __construct(){
		add_action( 'customize_controls_print_scripts', [$this, 'customizer_print_js'], 30 );
	}

	public function status(){
		return Base::instance()->is_enabled();
	}

	public function get_id(){
		return 'view_selector';
	}

	public function get_name(){
		return 'View Selector';
	}

	public function loop_type(){
		return 'grid';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/woocommerce/loop/before_grid',
			'priority'      => 29,
		];

	}

	public function render(){

		if( ! wc_get_loop_prop( 'is_paginated' ) ){
			return;
		}

		if( is_singular('product') ){
			return;
		}

		if( ! $this->maybe_render() ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/view-selector');

		reycore_assets()->add_styles( [Base::ASSET_HANDLE, 'rey-wc-loop-inlinelist'] );
		reycore_assets()->add_scripts( Base::ASSET_HANDLE );
	}

	/**
	 * Print JS script in Customizer Preview.
	 * Clean localstorage
	 *
	 * @since 1.0.0
	 */
	function customizer_print_js()
	{ ?>
		<script type="text/javascript">
			(function ( api ) {

				if( ! api ){
					return;
				}

				var lsName = "rey-active-view-selector-" + <?php echo is_multisite() ? absint( get_current_blog_id() ) : 0 ?>;

				var removeLs = function(){
						localStorage.removeItem( lsName );
						localStorage.removeItem( lsName + '-mobile' );
					};

				api.bind( "ready", function () {
					removeLs();
				});

				api.bind( "saved", function () {
					removeLs();
				});

				api( 'woocommerce_catalog_columns', function( value ) {
					value.bind( function( to ) {
						removeLs();
					} );
				} );
			})( wp.customize );
		</script>
		<?php
	}

}
