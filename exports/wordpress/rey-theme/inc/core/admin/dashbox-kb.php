<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">
		<h2 class="rey-dashBox-title">
			<span><?php esc_html_e('HELP', 'rey') ?></span>
		</h2>
		<div class="rey-dashBox-content">

			<h3><?php esc_html_e('Search in Knowledgebase:', 'rey') ?></h3>
			<div class="dashbox-searchForm rey-adminForm">
				<input id="dashbox-search-kb" class="reyAdmin-input" type="text" placeholder="<?php esc_attr_e('eg: getting started', 'rey') ?>" />
			</div>

			<h3><?php esc_html_e('Explore knowledge base topics:', 'rey') ?></h3>
			<ul class="ul-square list-2-cols">
				<li><a href="<?php echo rey__support_url('kbtopic/getting-started/'); ?>" target="_blank"><?php esc_html_e('Getting started', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/settings/'); ?>" target="_blank"><?php esc_html_e('Settings', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/elementor/'); ?>" target="_blank"><?php esc_html_e('Elementor', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/woocommerce/'); ?>" target="_blank"><?php esc_html_e('WooComerce', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/global-sections/'); ?>" target="_blank"><?php esc_html_e('Global Sections', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/customization-faqs/'); ?>" target="_blank"><?php esc_html_e('Customization FAQ\'s', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('kbtopic/troubleshooting/'); ?>" target="_blank"><?php esc_html_e('Troubleshooting', 'rey') ?></a></li>
			</ul>

			<h3><?php esc_html_e('Useful links:', 'rey') ?></h3>
			<ul class="ul-square list-2-cols">
				<li><a href="<?php echo rey__support_url(); ?>" target="_blank"><?php esc_html_e('Knowledge Base', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('new/'); ?>" target="_blank"><?php esc_html_e('Get Support', 'rey') ?></a></li>
				<li><a href="<?php echo rey__support_url('changelog/'); ?>" target="_blank"><?php esc_html_e('See changelog', 'rey') ?></a></li>
			</ul>

		</div>
	</div>
</div>
