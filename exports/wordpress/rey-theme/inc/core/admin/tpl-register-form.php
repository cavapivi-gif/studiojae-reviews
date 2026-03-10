<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rey_purchase_code = isset($_POST['rey_purchase_code']) ? trim( $_POST['rey_purchase_code'] ) : '';
$rey_email_address = isset($_POST['rey_email_address']) ? sanitize_email( $_POST['rey_email_address'] ) : '';

$rey_subscribe_newsletter = '';

if( isset($_POST['rey_subscribe_newsletter']) && $_POST['rey_subscribe_newsletter'] != 1 ){
	$rey_subscribe_newsletter = '';
}

?>

<div class="reyAdmin-formRow">
	<label for="rey-purchase-code" class="reyAdmin-label --required"><?php esc_html_e( 'Your Purchase Code', 'rey' ); ?></label><br>
	<input id="rey-purchase-code" class="reyAdmin-input" name="rey_purchase_code" type="text" value="<?php echo esc_attr($rey_purchase_code) ?>" placeholder="<?php echo esc_attr( 'e.g. cb0e057f-a05d-4758-b314-024db98eff85', 'rey' ); ?>" required pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$" autocomplete="<?php echo esc_attr(apply_filters('rey/register_form/autocomplete', '')); ?>" />

	<?php if( rey__get_props('branding') ): ?>
	<p class="reyAdmin-inputDesc"><?php _e('Visit your <a href="https://support.reytheme.com/user-dashboard/" target="_blank">User Dashboard</a>, log in with your Envato account, and manage all your purchase codes and their active locations. Or, <a href="https://reytheme.com/buy" target="_blank">purchase a new Rey license</a>.', 'rey') ?></p>
	<?php endif; ?>

</div>

<div class="reyAdmin-formRow">
	<label for="rey-email-address" class="reyAdmin-label"><?php esc_html_e( 'Your Email Address', 'rey' ); ?></label><br>
	<input id="rey-email-address" class="reyAdmin-input" name="rey_email_address" type="text" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Invalid email address." value="<?php echo esc_attr($rey_email_address) ?>" />
	<p class="reyAdmin-inputDesc"><?php esc_html_e('Not mandatory, but recommended to keep an open communication channel for very important announcements.', 'rey') ?></p>
</div>

<div class="reyAdmin-formRow">
	<div class="reyAdmin-checkboxWrapper">
		<input id="rey-subscribe-newsletter" name="rey_subscribe_newsletter" type="checkbox" value="1" <?php echo esc_attr($rey_subscribe_newsletter) ?> />
		<label for="rey-subscribe-newsletter" class="reyAdmin-label --checkbox"><?php esc_html_e( 'Subscribe to our newsletter?', 'rey' ); ?></label>
	</div>
	<p class="reyAdmin-inputDesc"><?php esc_html_e('We also dislike spam and prefer to spend our time enhancing this theme rather than sending bothersome emails. However, occasionally we might share valuable tips, freebies, and exclusive offers. You are free to unsubscribe at any time.', 'rey') ?></p>
</div>
