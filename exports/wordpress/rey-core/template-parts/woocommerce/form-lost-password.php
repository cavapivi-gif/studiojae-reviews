<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$user_value = ( ! empty( $_POST['user_login'] ) ) ? wp_unslash( $_POST['user_login'] ) : '';
$account_panel = isset($args['account_panel']) && ! $args['account_panel'] ? false : true;
?>

<?php if( $account_panel ): ?>
<div class="rey-accountPanel-form rey-forgetForm ">
	<<?php echo reycore_wc__account_heading_tags('lost_password') ?> class="rey-accountPanel-title"><?php esc_html_e( 'Password Recovery', 'rey-core' ); ?></<?php echo reycore_wc__account_heading_tags('lost_password') ?>>
<?php endif; ?>

	<form method="post" class="woocommerce-form woocommerce-form-forgot js-rey-woocommerce-form-forgot">

		<div class="woocommerce-form-forgot-formData">

			<p><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

			<p class="rey-form-row rey-form-row--text <?php echo ($user_value ? '--has-value' : ''); ?>">
				<label class="rey-label" for="user_login"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?></label>
				<input class="rey-input rey-input--text" type="text" name="user_login" id="user_login" autocomplete="username" required value="<?php echo esc_attr($user_value); ?>" <?php echo reycore__input_has_value(); ?> />
			</p>

			<?php do_action( 'woocommerce_lostpassword_form' ); ?>

			<p class="">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="btn btn-line-active submit-btn" value="<?php echo esc_attr__( 'Reset password', 'woocommerce' ); ?>"  aria-label="<?php esc_html_e('Reset password', 'woocommerce') ?>"><?php echo esc_html__( 'Reset password', 'woocommerce' ); ?></button>
			</p>

			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

		</div>

		<?php if( $account_panel ): ?>
			<div class="rey-accountForms-notice"></div>
			<div class="rey-accountPanel-links rey-accountForms-links">
				<?php
				if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) {
					echo apply_filters('reycore/woocommerce/account_links/register_btn', sprintf( '<button class="btn btn-line" %s>%s</button>' ,
							apply_filters('reycore/woocommerce/account_links/register_btn_attributes', 'data-location="rey-registerForm"'),
							esc_html__( 'Create Account', 'rey-core' )
						)
					);
				}
				echo apply_filters('reycore/woocommerce/account_links/login_btn', sprintf( '<button class="btn btn-line" %s>%s</button>' ,
						apply_filters('reycore/woocommerce/account_links/login_btn_attributes', 'data-location="rey-loginForm"'),
						esc_html__( 'Login', 'rey-core' )
					)
				);
				?>
			</div>
			<?php endif; ?>
	</form>
<?php if( $account_panel ): ?>
</div>
<?php endif;

// Commented, might not be needed.
//do_action( 'woocommerce_after_customer_login_form' ); ?>
