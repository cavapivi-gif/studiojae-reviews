<?php
namespace SJ_Reviews\Admin\Backoffice;

defined('ABSPATH') || exit;

class Backoffice {

    public function init(): void {
        add_action('admin_menu',            [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_enqueue_scripts', [$this, 'disable_heartbeat']);
    }

    public function disable_heartbeat(string $hook): void {
        if ($hook !== 'toplevel_page_sj-reviews') return;
        wp_deregister_script('heartbeat');
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
                /* Masquer les éléments WP autour de notre app */
                #wpfooter, #screen-meta, #screen-meta-links,
                .update-nag, .notice, .notice-warning, .notice-error, .notice-success,
                .wp-header-end { display: none !important; }

                /*
                 * position:fixed = s\'arrache totalement du flux WP admin
                 * (float, padding, overflow ne peuvent plus interférer)
                 * top:32px = hauteur de la barre admin WP (desktop)
                 * left:160px = largeur du menu WP (non replié)
                 */
                #sj-reviews-root {
                    position: fixed !important;
                    top: 32px;
                    left: 160px;
                    right: 0;
                    bottom: 0;
                    overflow: hidden;
                    z-index: 99;
                    background: #fff;
                }
                /* Menu WP replié manuellement */
                .folded #sj-reviews-root {
                    left: 36px;
                }
                /* Mobile WP : barre admin = 46px, pas de menu latéral */
                @media (max-width: 782px) {
                    #sj-reviews-root {
                        left: 0 !important;
                        top: 46px;
                    }
                }

                /* SVG inline — stroke/fill en attribut HTML, pas de class WP à override */
                #sj-reviews-root svg {
                    display: inline-block !important;
                    overflow: visible !important;
                    flex-shrink: 0;
                    vertical-align: middle;
                }
            </style>';
        });
    }
}
