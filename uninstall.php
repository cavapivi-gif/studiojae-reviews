<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

// Suppression des options
delete_option('sj_reviews_settings');
delete_option('sj_lieux');
delete_option('sj_reviews_last_sync');

// Nettoyage crons
wp_clear_scheduled_hook('sj_reviews_auto_sync');

// Nettoyage transients
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_sj_%'
     OR option_name LIKE '_transient_timeout_sj_%'"
);

// Suppression du cache dashboard
delete_transient('sj_dashboard_cache');

// Suppression des posts type avis (optionnel — commentez si vous voulez garder les données)
// $posts = get_posts(['post_type' => 'sj_avis', 'numberposts' => -1, 'fields' => 'ids']);
// foreach ($posts as $id) { wp_delete_post($id, true); }
