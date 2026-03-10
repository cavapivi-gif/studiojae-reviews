<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-dashBox --loading">
	<div class="rey-dashBox-inner">

		<h2 class="rey-dashBox-title">
			<span class="rey-spinnerIcon"></span>
			<a href="#" class="rDash-link js-check-updates rey-genericBtn">
				<?php echo esc_html__('Check for updates', 'rey') ?>
			</a>
			<span class="__title"><?php esc_html_e('Versions Status', 'rey') ?><span id="rey-updates-count" data-count=""></span></span>
		</h2>

		<div class="rey-dashBox-content">
			<table class="rey-systemStatus rey-versionsStatus" data-supports-updates="no">
				<?php do_action('rey/dashboard/box/versions'); ?>
			</table>
		</div>

	</div>
</div>
