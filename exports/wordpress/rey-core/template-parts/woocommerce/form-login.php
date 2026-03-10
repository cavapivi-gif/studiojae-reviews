<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Commented, might not be needed.
//do_action( 'woocommerce_before_customer_login_form' );

$user_value = ( ! empty( $_POST['username'] ) ) ? wp_unslash( $_POST['username'] ) : '';
$action = !reycore_wc__get_account_panel_args('ajax_forms') && wc_get_page_id( 'myaccount' ) ? sprintf('action="%s"', esc_attr(get_permalink(wc_get_page_id( 'myaccount' )))) : '';
$account_panel = isset($args['account_panel']) && ! $args['account_panel'] ? false : true;

?>

<?php if( $account_panel ): ?>
<div class="rey-accountPanel-form rey-loginForm --active">
	<<?php echo reycore_wc__account_heading_tags('login') ?> class="rey-accountPanel-title"><?php esc_html_e( 'Login', 'woocommerce' ); ?></<?php echo reycore_wc__account_heading_tags('login') ?>>
<?php endif; ?>

	<form <?php echo $action ?> class="woocommerce-form woocommerce-form-login js-rey-woocommerce-form-login login" method="post">

		<?php do_action( 'woocommerce_login_form_start' ); ?>

		<p class="rey-form-row rey-form-row--text <?php echo ($user_value ? '--has-value' : ''); ?>">
			<input type="text" class="rey-input rey-input--text" name="username" id="username" autocomplete="username" value="<?php echo esc_attr($user_value); ?>" required <?php echo reycore__input_has_value(); ?> /><?php // @codingStandardsIgnoreLine ?>
			<label for="username" class="rey-label"><?php esc_html_e( 'Username or email address', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
		</p>

		<p class="rey-form-row rey-form-row--text">
			<input class="rey-input rey-input--text --suports-visibility" type="password" name="password" id="password" autocomplete="current-password" required <?php echo reycore__input_has_value(); ?>/>
			<label for="password" class="rey-label"><?php esc_html_e( 'Password', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
		</p>

		<?php do_action( 'woocommerce_login_form' ); ?>

		<div class="rey-form-row rey-form-row--reset-mobile">
			<p class="col">
				<label class="rey-label rey-label--checkbox" for="rememberme">
					<input class="rey-input rey-input--checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever"  />
					<span></span>
					<span class="rey-label-text"><?php esc_html_e( 'Remember me', 'rey-core' ); ?></span>
				</label>
			</p>

			<p class="col text-right">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="btn btn-line-active submit-btn" name="login" value="<?php echo esc_attr__( 'SIGN IN', 'rey-core' ); ?>"  aria-label="<?php esc_html_e('SIGN IN', 'rey-core') ?>"><?php esc_html_e( 'SIGN IN', 'rey-core' ); ?></button>
			</p>
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
				echo apply_filters('reycore/woocommerce/account_links/forget_btn', sprintf( '<button class="btn btn-line" %s>%s</button>' ,
						apply_filters('reycore/woocommerce/account_links/forget_btn_attributes', 'data-location="rey-forgetForm"'),
						esc_html__( 'Forgot password', 'rey-core' )
					)
				); ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_login_form_end' ); ?>

	</form>

<?php if( $account_panel ): ?>
</div>
<?php endif; ?>
