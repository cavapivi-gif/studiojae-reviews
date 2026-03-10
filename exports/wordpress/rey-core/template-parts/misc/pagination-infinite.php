<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

reycore__ajax_load_more_pagination([
	'class' => 'btn btn-line-active rey-infiniteLoadMore js-rey-infiniteLoadMore',
	'target' => 'div.rey-postList',
]);
