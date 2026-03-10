<?php

/**
 * Before Header
 *
 * @since 1.0.0
 **/
function rey_action__before_header()
{
	do_action('rey/before_header');
}

/**
 * Header
 *
 * @since 1.0.0
 **/
function rey_action__header()
{
	do_action('rey/header');
}

/**
 * After Header
 *
 * @since 1.0.0
 **/
function rey_action__after_header()
{
	do_action('rey/after_header');
}

/**
 * After Header (Outside of header)
 *
 * @since 1.0.0
 **/
function rey_action__after_header_outside()
{
	do_action('rey/after_header_outside');
}

/**
 * Inside Header's content
 *
 * @since 1.0.0
 **/
function rey_action__header_content()
{
	do_action('rey/header/content');
}

/**
 * Header's main row content
 *
 * @since 1.0.0
 **/
function rey_action__header_row()
{
	do_action('rey/header/row');
}

/**
 * Adds Icons to Header Nav.
 *
 * @since 1.0.0
 **/
function rey_action__header_icons_nav()
{
	do_action('rey/header/icons_nav');
}


/**
 * Before Footer
 *
 * @since 1.0.0
 **/
function rey_action__before_footer()
{
	do_action('rey/before_footer');
}

/**
 * Footer
 *
 * @since 1.0.0
 **/
function rey_action__footer()
{
	do_action('rey/footer');
}

/**
 * After Footer
 *
 * @since 1.0.0
 **/
function rey_action__after_footer()
{
	do_action('rey/after_footer');
}

/**
 * Inside Footer's content
 *
 * @since 1.0.0
 **/
function rey_action__footer_content()
{
	do_action('rey/footer/content');
}

/**
 * Inside Footer's main row
 *
 * @since 1.0.0
 **/
function rey_action__footer_row()
{
	do_action('rey/footer/row');
}


/**
 * Before Site Wrapper
 *
 * @since 1.0.0
 **/
function rey_action__before_site_wrapper()
{
	do_action('rey/before_site_wrapper');
}

/**
 * After Site Wrapper Starts
 *
 * @since 1.0.0
 **/
function rey_action__after_site_wrapper_start()
{
	do_action('rey/after_site_wrapper_start');
}

/**
 * After Site Wrapper
 *
 * @since 1.0.0
 **/
function rey_action__after_site_wrapper()
{
	do_action('rey/after_site_wrapper');
}

/**
 * Before Site container
 *
 * @since 1.0.0
 **/
function rey_action__before_site_container()
{
	do_action('rey/before_site_container');
}

/**
 * After Site container
 *
 * @since 1.0.0
 **/
function rey_action__after_site_container()
{
	do_action('rey/after_site_container');
}


/**
 * After Posts list
 *
 * @since 1.0.0
 **/
function rey_action__post_list()
{
	do_action('rey/post_list');
}

/**
 * Single post content hook
 *
 * @since 1.0.0
 **/
function rey_action__single_post_content()
{
	do_action('rey/single_post/content');
}

/**
 * Single post after content hook
 *
 * @since 1.0.0
 **/
function rey_action__single_post_after_content()
{
	do_action('rey/single_post/after_content');
}
