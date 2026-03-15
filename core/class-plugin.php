<?php
namespace SJ_Reviews\Core;

defined('ABSPATH') || exit;

class Plugin {

    public function init(): void {
        // CPT + ACF fields
        require_once SJ_REVIEWS_DIR . 'post-types/class-avis-cpt.php';
        require_once SJ_REVIEWS_DIR . 'post-types/class-lieu-metabox.php';
        (new \SJ_Reviews\PostTypes\AvisCpt())->init();
        (new \SJ_Reviews\PostTypes\LieuMetabox())->init();

        // Helpers
        require_once SJ_REVIEWS_DIR . 'includes/class-settings.php';
        require_once SJ_REVIEWS_DIR . 'includes/class-labels.php';
        require_once SJ_REVIEWS_DIR . 'includes/helpers.php';

        // Cron — auto-sync
        require_once SJ_REVIEWS_DIR . 'includes/class-cron.php';
        (new \SJ_Reviews\Includes\Cron())->init();

        // Email digest (cron)
        require_once SJ_REVIEWS_DIR . 'includes/class-email-digest.php';
        (new \SJ_Reviews\Includes\EmailDigest())->init();

        // Social proof toast (front-end)
        require_once SJ_REVIEWS_DIR . 'includes/class-social-proof.php';
        (new \SJ_Reviews\Includes\SocialProof())->init();

        // Classic WP Widget
        require_once SJ_REVIEWS_DIR . 'includes/class-widget.php';
        add_action('widgets_init', function () {
            register_widget(\SJ_Reviews\Includes\ReviewsWidget::class);
        });

        // REST API — enregistrée une seule fois, accessible admin et front.
        // Les endpoints admin sont protégés par permission_callback => is_manager().
        // Les endpoints publics (front/) sont ouverts mais rate-limitéa par check_public_rate_limit().
        require_once SJ_REVIEWS_DIR . 'admin/backoffice/class-rest-api.php';
        (new \SJ_Reviews\Admin\Backoffice\RestApi())->init();

        // Admin backoffice (UI React — page WP admin seulement)
        if (is_admin()) {
            require_once SJ_REVIEWS_DIR . 'admin/backoffice/class-backoffice.php';
            (new \SJ_Reviews\Admin\Backoffice\Backoffice())->init();
        }

        // Shortcodes
        require_once SJ_REVIEWS_DIR . 'front/class-shortcode.php';
        require_once SJ_REVIEWS_DIR . 'front/class-rating-shortcode.php';
        require_once SJ_REVIEWS_DIR . 'front/class-summary-shortcode.php';
        require_once SJ_REVIEWS_DIR . 'front/class-inline-rating-shortcode.php';
        require_once SJ_REVIEWS_DIR . 'front/class-form-shortcode.php';
        require_once SJ_REVIEWS_DIR . 'front/class-coup-de-coeur-shortcode.php';
        (new \SJ_Reviews\Front\Shortcode())->init();

        (new \SJ_Reviews\Front\SummaryShortcode())->init();
        new \SJ_Reviews\Front\InlineRatingShortcode();
        (new \SJ_Reviews\Front\FormShortcode())->init();
        (new \SJ_Reviews\Front\CoupDeCoeurShortcode())->init();

        // Elementor
        add_action('elementor/widgets/register', function ($manager) {
            require_once SJ_REVIEWS_DIR . 'elementor/class-elementor-manager.php';
            require_once SJ_REVIEWS_DIR . 'elementor/class-widget-base.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-typography-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-box-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-interactive-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-media-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-data-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-shared-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-reviews-style-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/traits/trait-summary-style-controls.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-reviews-widget.php';
            require_once SJ_REVIEWS_DIR . 'front/class-rating-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'front/class-summary-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'front/class-inline-rating-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-rating-badge-widget.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-summary-widget.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-inline-rating-widget.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-coup-de-coeur-widget.php';
            $manager->register(new \SJ_Reviews\Elementor\Widgets\ReviewsWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\RatingBadgeWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\SummaryWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\InlineRatingWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\CoupDeCoeurWidget());
        });

        // Enregistrement global (pas de chargement) — les shortcodes/widgets enqueue à la demande.
        add_action('wp_enqueue_scripts', [$this, 'register_front_assets']);
        // Elementor recharge ses styles après rendu — on s'assure que les assets sont bien enregistrés.
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'register_front_assets']);
    }

    /**
     * Enregistre (sans charger) tous les assets front.
     * Les shortcodes et widgets Elementor appellent sj_enqueue_asset() pour charger
     * uniquement ce dont ils ont besoin sur la page courante.
     */
    public function register_front_assets(): void {
        wp_register_style('sj-reviews-front', SJ_REVIEWS_URL . 'front/assets/sj-front.css',   [], SJ_REVIEWS_VERSION);
        wp_register_style('sj-rating-badge',  SJ_REVIEWS_URL . 'front/assets/sj-badge.css',   [], SJ_REVIEWS_VERSION);
        wp_register_style('sj-summary',       SJ_REVIEWS_URL . 'front/assets/sj-summary.css', [], SJ_REVIEWS_VERSION);
        wp_register_style('sj-form',          SJ_REVIEWS_URL . 'front/assets/sj-form.css',    [], SJ_REVIEWS_VERSION);
        wp_register_style('sj-coup-de-coeur', SJ_REVIEWS_URL . 'front/assets/sj-coup-de-coeur.css', [], SJ_REVIEWS_VERSION);
        wp_register_style('sj-toast',         SJ_REVIEWS_URL . 'front/assets/sj-toast.css',   [], SJ_REVIEWS_VERSION);

        wp_register_script('sj-reviews-front', SJ_REVIEWS_URL . 'front/assets/sj-front.js',   ['swiper'], SJ_REVIEWS_VERSION, true);
        wp_register_script('sj-summary',       SJ_REVIEWS_URL . 'front/assets/sj-summary.js', [], SJ_REVIEWS_VERSION, true);
        wp_register_script('sj-badge',         SJ_REVIEWS_URL . 'front/assets/sj-badge.js',   [], SJ_REVIEWS_VERSION, true);
        wp_register_script('sj-toast',         SJ_REVIEWS_URL . 'front/assets/sj-toast.js',   [], SJ_REVIEWS_VERSION, true);
    }

    /**
     * Charge un asset enregistré et injecte la config REST si c'est un script JS dynamique.
     * Appelé par chaque shortcode/widget dans son render().
     *
     * @param string $handle  Handle wp_register_style/script
     * @param bool   $is_script
     */
    public static function enqueue_asset(string $handle, bool $is_script = false): void {
        if ($is_script) {
            if (!wp_script_is($handle, 'enqueued')) {
                wp_enqueue_script($handle);
                // Injecte la config REST une seule fois par handle JS dynamique
                if (in_array($handle, ['sj-summary', 'sj-badge', 'sj-toast'], true)) {
                    wp_localize_script($handle, 'sjReviewsConfig', [
                        'restUrl' => esc_url_raw(rest_url('sj-reviews/v1/')),
                        'nonce'   => wp_create_nonce('wp_rest'),
                    ]);
                }
            }
        } else {
            wp_enqueue_style($handle);
        }
    }
}
