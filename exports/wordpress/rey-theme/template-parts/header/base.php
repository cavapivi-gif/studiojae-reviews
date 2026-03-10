<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Prints content before header
 * @hook rey/before_header
 */
rey_action__before_header(); ?>

<header class="rey-siteHeader <?php echo esc_attr(implode(' ', apply_filters('rey/header/header_classes', []))) ?>" <?php rey__render_attributes('header'); ?>>

	<?php
	/**
	 * Prints content inside header
	 * @hook rey/header/content
	 */
	rey_action__header_content(); ?>

</header>
<!-- .rey-siteHeader -->

<?php
/**
 * Prints content after header
 * @hook rey/after_header
 */
rey_action__after_header(); ?>
