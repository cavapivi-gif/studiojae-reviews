<?php
namespace SJ_Reviews\Admin\Backoffice;

defined('ABSPATH') || exit;

class Backoffice {

    public function init(): void {
        add_action('admin_menu',            [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function add_menu(): void {
        add_menu_page(
            'StudioJae Reviews',
            'SJ Reviews',
            'manage_options',
            'sj-reviews',
            [$this, 'render_app'],
            'dashicons-star-filled',
            25
        );

        // Un seul slug WP — React (HashRouter) gère toute la navigation interne
        add_submenu_page('sj-reviews', 'Tableau de bord', 'Tableau de bord', 'manage_options', 'sj-reviews', [$this, 'render_app']);
    }

    public function render_app(): void {
        if (!current_user_can('manage_options')) return;
        echo '<div id="sj-reviews-root"></div>';
    }

    public function enqueue(string $hook): void {
        if ($hook !== 'toplevel_page_sj-reviews') return;

        $build_dir = SJ_REVIEWS_DIR . 'admin/backoffice/build/assets/';
        $build_url = SJ_REVIEWS_URL . 'admin/backoffice/build/assets/';

        if (!is_dir($build_dir)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning"><p><strong>SJ Reviews :</strong> Le build admin n\'existe pas encore. Lancez <code>cd wp-content/plugins/studiojae-reviews/admin/backoffice && npm install && npm run build</code></p></div>';
            });
            return;
        }

        $css_ver = filemtime($build_dir . 'index.css') ?: SJ_REVIEWS_VERSION;
        $js_ver  = filemtime($build_dir . 'index.js')  ?: SJ_REVIEWS_VERSION;

        wp_enqueue_style('sj-reviews-admin', $build_url . 'index.css', [], $css_ver);
        wp_enqueue_script('sj-reviews-admin', $build_url . 'index.js', [], $js_ver, true);

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
