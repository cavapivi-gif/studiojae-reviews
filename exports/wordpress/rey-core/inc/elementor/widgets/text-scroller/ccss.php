<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action('reycore/critical_css/before_render', function($ccss){

	$ccss->add_css([
		'.rey-textScroller-item p {margin-bottom:0}'
	]);

});
