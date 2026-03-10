<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">

		<h2 class="rey-dashBox-title">
			<span><?php printf( esc_html__('Register %s', 'rey'), rey__get_props('theme_title')) ?></span>
			<?php if( rey__get_props('branding') ){
				echo rey__get_svg_icon(['id'=>'logo', 'class'=>'rDash-logo']);
			} ?>
		</h2>

		<div class="rey-dashBox-content">

			<p><?php printf( esc_html__('Register %s to enable its features, importing pre-made designs and templates, and install the latest updates and premium plugins.', 'rey'), rey__get_props('theme_title') ) ?></p>

			<?php if( ! ReyTheme_Base::get_purchase_code() ): ?>
			<div class="rey-dashBox-connectionTest">
				<p>
					<span class="__text"><?php esc_html_e('Testing connection..', 'rey') ?></span> &nbsp;&nbsp;
					<span class="rey-spinnerIcon"></span>
				</p>
			</div>
			<?php endif; ?>

			<form class="rey-adminForm js-dashBox-registerForm" method="post" action="#">

				<?php include __DIR__ . '/tpl-register-form.php'; ?>

				<button type="submit" class="rey-adminBtn rey-adminBtn-secondary"><?php esc_html_e( 'REGISTER', 'rey' ); ?></button>
			</form>

		</div>
	</div>
</div>
