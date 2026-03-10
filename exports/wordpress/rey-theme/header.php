<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package rey
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="//gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php rey__render_attributes('body'); ?>>

<?php
rey__body_open();

/**
 * Prints content before site wrapper
 * @hook rey/before_site_wrapper
 */
rey_action__before_site_wrapper(); ?>

	<a href="#content" class="skip-link screen-reader-text"><?php esc_html_e('Skip to content', 'rey') ?></a>

	<div id="page" class="rey-siteWrapper <?php echo esc_attr(implode(' ', apply_filters('rey/site_wrapper_classes', []))) ?>">

		<?php
		/**
		 * Prints content after site wrapper starts
		 * @hook rey/after_site_wrapper_start
		 */
		rey_action__after_site_wrapper_start(); ?>

		<?php
		/**
		 * Prints Header
		 */
		rey_action__header();

		/**
		 * Prints content after header (outside of header)
		 * @hook rey/after_header_outside
		 */
		rey_action__after_header_outside(); ?>

		<div id="content" class="rey-siteContent <?php echo esc_attr(implode(' ', apply_filters('rey/site_content_classes', []))) ?>">
