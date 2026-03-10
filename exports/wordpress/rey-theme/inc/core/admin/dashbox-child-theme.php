<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_child_theme = is_child_theme();

// No need to show box
if ( $is_child_theme ){
	return;
}

$theme = wp_get_theme(); ?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">
		<h2 class="rey-dashBox-title">
			<span><?php printf(esc_html__('Setup %s Child theme', 'rey'), $theme->Name) ?></span>
		</h2>
		<div class="rey-dashBox-content">
			<p> <?php printf( esc_html__(  "Use this button below to install and activate %s Child theme, and finally copy settings from the parent theme.", 'rey' ), $theme->Name ); ?> </p>
			<p>

			<a href="#" class="rey-adminBtn rey-adminBtn-primary rey-installChild js-installChild" ><?php esc_html_e('INSTALL CHILD THEME', 'rey') ?> </a>

		</div>
	</div>
</div>
