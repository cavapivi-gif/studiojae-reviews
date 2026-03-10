<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action('reycore/critical_css/before_render', function($ccss){

	$css[] = '.reyajfilter-layered-nav ul, .woocommerce-widget-layered-nav ul {
		list-style: none;
		margin: 0;
		padding: 0;
	}';

	$ccss->add_css($css);

});
