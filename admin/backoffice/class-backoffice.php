<?php
namespace SJ_Reviews\Admin\Backoffice;

defined('ABSPATH') || exit;

class Backoffice {

    public function init(): void {
        add_action('admin_menu',            [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function add_menu(): void {
        // Menu principal
        add_menu_page(
            'StudioJae Reviews',
            'SJ Reviews',
            'manage_options',
            'sj-reviews',
            [$this, 'render_app'],
            'dashicons-star-filled',
            25
        );

        // Sous-menus — slugs propres sans hash (le HashRouter JS gère la navigation interne)
        add_submenu_page('sj-reviews', 'Tableau de bord', 'Tableau de bord', 'manage_options', 'sj-reviews',          [$this, 'render_app']);
        add_submenu_page('sj-reviews', 'Avis',            'Tous les avis',   'manage_options', 'edit.php?post_type=sj_avis', '');
        add_submenu_page('sj-reviews', 'Ajouter',         'Ajouter un avis', 'manage_options', 'post-new.php?post_type=sj_avis', '');
        add_submenu_page('sj-reviews', 'Réglages',        'Réglages',        'manage_options', 'sj-reviews-settings', [$this, 'render_app']);
    }

    public function render_app(): void {
        if (!current_user_can('manage_options')) return;

        // Détermine la route React à partir du slug WP courant
        $page   = sanitize_text_field($_GET['page'] ?? 'sj-reviews');
        $routes = [
            'sj-reviews'          => '/dashboard',
            'sj-reviews-settings' => '/settings',
        ];
        $route = $routes[$page] ?? '/dashboard';

        echo '<div id="sj-reviews-root" data-route="' . esc_attr($route) . '"></div>';
    }

    public function enqueue(string $hook): void {
        // Hooks valides pour nos pages : toplevel_page_sj-reviews et sj-reviews_page_sj-reviews-settings
        if (strpos($hook, 'sj-reviews') === false) return;

        $build_dir = SJ_REVIEWS_DIR . 'admin/backoffice/build/assets/';
        $build_url = SJ_REVIEWS_URL . 'admin/backoffice/build/assets/';

        if (!is_dir($build_dir)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning"><p><strong>SJ Reviews :</strong> Le build admin n\'existe pas encore. Lancez <code>cd wp-content/plugins/studiojae-reviews/admin/backoffice && npm install && npm run build</code></p></div>';
            });
            return;
        }

        wp_enqueue_style('sj-reviews-admin', $build_url . 'index.css', [], SJ_REVIEWS_VERSION);
        wp_enqueue_script('sj-reviews-admin', $build_url . 'index.js', [], SJ_REVIEWS_VERSION, true);

        wp_localize_script('sj-reviews-admin', 'sjReviews', [
            'rest_url'  => rest_url('sj-reviews/v1'),
            'nonce'     => wp_create_nonce('wp_rest'),
            'version'   => SJ_REVIEWS_VERSION,
            'admin_url' => admin_url(),
        ]);

        add_action('admin_head', function () {
            echo '<style>
                #wpcontent { padding-left: 0 !important; margin-left: 0 !important; }
                #wpfooter  { display: none !important; }
                .notice, .update-nag, #screen-meta { display: none !important; }
                #sj-reviews-root { min-height: 100vh; }
                #sj-reviews-root svg { display: inline-block !important; overflow: visible !important; flex-shrink: 0; }
            </style>';
        });
    }
}
