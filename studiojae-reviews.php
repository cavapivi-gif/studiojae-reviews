<?php
/**
 * Plugin Name: StudioJae Reviews
 * Plugin URI:  https://studiojae.fr
 * Description: Gestion et affichage d'avis clients — CPT natif, widget Elementor (Slider I/II, Badge, Grid, List) et shortcode. Design system noir/blanc.
 * Version:     1.0.0
 * Author:      StudioJae
 * Text Domain: sj-reviews
 * Requires at least: 6.3
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('SJ_REVIEWS_VERSION', '1.0.0');
define('SJ_REVIEWS_DIR',     plugin_dir_path(__FILE__));
define('SJ_REVIEWS_URL',     plugin_dir_url(__FILE__));
define('SJ_REVIEWS_SLUG',    'sj-reviews');

require_once SJ_REVIEWS_DIR . 'core/class-loader.php';
require_once SJ_REVIEWS_DIR . 'core/class-plugin.php';

function sj_reviews_run(): void {
    $plugin = new SJ_Reviews\Core\Plugin();
    $plugin->init();
}
sj_reviews_run();
