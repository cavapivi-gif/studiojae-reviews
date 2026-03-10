<?php
/**
 * Template part for displaying a big text in posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package rey
 */

$category = get_the_category();

if( get_theme_mod('post_cat_text_visibility', true) && isset($category[0]) && $category[0]->cat_name ){
	printf( '<div class="rey-postItem-catText">%s</div>', $category[0]->cat_name);
}
