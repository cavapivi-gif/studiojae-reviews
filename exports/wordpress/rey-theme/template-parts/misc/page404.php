<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
rey_assets()->add_styles('rey-buttons'); ?>

<header class="rey-pageHeader">
	<h1 class="rey-pageTitle"><?php esc_html_e('404', 'rey'); ?></h1>
	<span class="rey-pageSubtitle"><?php esc_html_e('Page Not Found', 'rey'); ?></span>
</header>

<div class="rey-pageContent">
	<p><?php esc_html_e('It looks like nothing was found at this location. Try another link or click the button below.', 'rey'); ?></p>
	<a class="btn btn-line-active" href="#" onclick="window.history.back();"><?php esc_html_e('GO BACK', 'rey'); ?></a>
	<a class="btn btn-line-active" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('HOME', 'rey'); ?></a>
</div>
