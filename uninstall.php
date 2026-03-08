<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

// Suppression des options
delete_option('sj_reviews_settings');

// Suppression des posts type avis (optionnel — commentez si vous voulez garder les données)
// $posts = get_posts(['post_type' => 'sj_avis', 'numberposts' => -1, 'fields' => 'ids']);
// foreach ($posts as $id) { wp_delete_post($id, true); }
