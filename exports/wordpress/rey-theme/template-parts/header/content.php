<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rey-siteHeader-container">
    <div class="rey-siteHeader-row">

		<?php
		/**
		 * Prints content inside header's main row
		 * @hook rey/header/row
		 */
		rey_action__header_row(); ?>

	</div>
	<!-- .rey-siteHeader-row -->

	<?php

	if( get_theme_mod('header_separator_bar', true) ){
		printf('<div class="header--separator-bar %s" style="height:var(--header-bar-size, 1vh);background-color:var(--header-bar-color,HSL(var(--neutral-1)));"></div>', (get_theme_mod('header_separator_bar_mobile', false) ? '--dnone-lg' : ''));
	}

	?>
</div>
