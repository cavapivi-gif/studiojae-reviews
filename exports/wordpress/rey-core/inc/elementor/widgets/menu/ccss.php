<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action('reycore/critical_css/before_render', function($ccss){

	$css[] = '.reyEl-menu-nav {
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-wrap: wrap;
	}';
	$css[] = '.reyEl-menu--horizontal {--distance: 0.5em }';
	$css[] = '.reyEl-menu--horizontal .reyEl-menu-nav { flex-direction: row }';
	$css[] = '.reyEl-menu--horizontal .reyEl-menu-nav > li a { display: block; }';
	$css[] = '.reyEl-menu .reyEl-menu-nav {gap: var(--distance, 0px)}';

	$ccss->add_css($css);

});
