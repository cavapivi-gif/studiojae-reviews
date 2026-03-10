<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

reycore__ajax_load_more_pagination([
	'class' => 'btn js-rey-ajaxLoadMore ' . get_theme_mod('loop_pagination_btn_style', 'btn-line-active'),
	'target' => 'div.rey-postList',
]);
