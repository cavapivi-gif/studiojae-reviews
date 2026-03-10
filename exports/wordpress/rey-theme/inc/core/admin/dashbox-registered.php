<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">

		<h2 class="rey-dashBox-title">
			<span><?php printf( esc_html__('Register %s', 'rey'), rey__get_props('theme_title')) ?></span>
			<?php if( rey__get_props('branding') ){
				echo rey__get_svg_icon(['id'=>'logo', 'class'=>'rDash-logo']);
			} ?>
		</h2>

		<div class="rey-dashBox-content">

			<div class="dashBox-registrationData">

				<p><?php esc_html_e('Thank you for registering! You are now able to get updates, import demos, install premium plugins, access dashboard support and other features.', 'rey') ?></p>

				<h4><?php esc_html_e('Your API KEY:', 'rey') ?></h4>
				<pre><?php echo ReyTheme_Base::get_hidden_purchase_code(); ?></pre>

				<p>
					<a href="#" class="rey-adminBtn rey-adminBtn-line js-dashDeregister"><?php esc_html_e('Deregister purchase code', 'rey') ?></a>
				</p>
			</div>

		</div>
	</div>
</div>
