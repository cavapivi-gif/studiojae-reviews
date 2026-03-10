<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once REY_CORE_DIR . 'inc/vendor/action-scheduler/action-scheduler.php';

require_once REY_CORE_DIR . 'inc/vendor/kirki/kirki.php';

if( ! REYCORE_DISABLE_ACF ){
	require_once REY_CORE_DIR . 'inc/vendor/advanced-custom-fields-pro/acf.php';
}
