<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$user_value = ( ! empty( $_POST['username'] ) ) ? wp_unslash( $_POST['username'] ) : '';
$email_value = ( ! empty( $_POST['email'] ) ) ? wp_unslash( $_POST['email'] ) : '';
$action = !reycore_wc__get_account_panel_args('ajax_forms') && wc_get_page_id( 'myaccount' ) ? sprintf('action="%s"', esc_attr(get_permalink(wc_get_page_id( 'myaccount' )))) : '';
$account_panel = isset($args['account_panel']) && ! $args['account_panel'] ? false : true;

?>
<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	<?php if( $account_panel ): ?>
	<div class="rey-accountPanel-form rey-registerForm ">
		<<?php echo reycore_wc__account_heading_tags('register') ?> class="rey-accountPanel-title"><?php esc_html_e( 'Create an account', 'rey-core' ); ?></<?php echo reycore_wc__account_heading_tags('register') ?>>
	<?php endif; ?>

		<form  <?php echo $action ?> method="post" class="register woocommerce-form woocommerce-form-register js-rey-woocommerce-form-register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="rey-form-row rey-form-row--text <?php echo ($user_value ? '--has-value' : ''); ?>">
					<label class="rey-label" for="reg_username"><?php esc_html_e( 'Username', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="rey-input rey-input--text" name="username" id="reg_username" autocomplete="username" value="<?php echo esc_attr($user_value); ?>" required <?php echo reycore__input_has_value(); ?> /><?php // @codingStandardsIgnoreLine ?>
				</p>

			<?php endif; ?>

			<p class="rey-form-row rey-form-row--text <?php echo ($email_value ? '--has-value' : ''); ?>">
				<label class="rey-label" for="reg_email"><?php esc_html_e( 'Email address', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="rey-input rey-input--text" name="email" id="reg_email" autocomplete="email" value="<?php echo esc_attr($email_value); ?>" required pattern="[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" <?php echo reycore__input_has_value(); ?> /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="rey-form-row rey-form-row--text">
					<label class="rey-label" for="reg_password"><?php esc_html_e( 'Password', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="rey-input rey-input--text --suports-visibility" name="password" id="reg_password" autocomplete="new-password" required <?php echo reycore__input_has_value(); ?> />
				</p>

			<?php endif; ?>

			<div class="rey-form-row rey-form-row--text --small-text">

				<?php if ( 'no' !== get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
				<?php endif; ?>

			</div>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="btn btn-line-active submit-btn" name="register" value="<?php echo esc_attr__( 'Register', 'rey-core' ); ?>" aria-label="<?php esc_html_e('Register', 'rey-core') ?>"><?php esc_html_e( 'CREATE ACCOUNT', 'rey-core' ); ?></button>
			</p>

			<?php if( $account_panel ): ?>
				<div class="rey-accountForms-notice"></div>
				<div class="rey-accountPanel-links rey-accountForms-links">
					<?php
					echo apply_filters('reycore/woocommerce/account_links/login_btn', sprintf( '<button class="btn btn-line" %s>%s</button>' ,
							apply_filters('reycore/woocommerce/account_links/login_btn_attributes', 'data-location="rey-loginForm"'),
							esc_html__( 'Login', 'rey-core' )
						)
					);
					echo apply_filters('reycore/woocommerce/account_links/forget_btn', sprintf( '<button class="btn btn-line" %s>%s</button>' ,
							apply_filters('reycore/woocommerce/account_links/forget_btn_attributes', 'data-location="rey-forgetForm"'),
							esc_html__( 'Forgot password', 'rey-core' )
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	<?php if( $account_panel ): ?>
	</div>
	<?php endif; ?>

<?php endif; ?>
