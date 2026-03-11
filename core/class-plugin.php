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

        // Admin backoffice
        if (is_admin()) {
            require_once SJ_REVIEWS_DIR . 'admin/backoffice/class-backoffice.php';
            require_once SJ_REVIEWS_DIR . 'admin/backoffice/class-rest-api.php';
            (new \SJ_Reviews\Admin\Backoffice\Backoffice())->init();
            (new \SJ_Reviews\Admin\Backoffice\RestApi())->init();
        }

        // REST API aussi accessible côté front (nonce wp_rest)
        if (!is_admin()) {
            require_once SJ_REVIEWS_DIR . 'admin/backoffice/class-rest-api.php';
            (new \SJ_Reviews\Admin\Backoffice\RestApi())->init();
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

        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_front_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
    }

    public function enqueue_front_assets(): void {
        wp_enqueue_style(
            'sj-reviews-front',
            SJ_REVIEWS_URL . 'front/assets/sj-front.css',
            [],
            SJ_REVIEWS_VERSION
        );
        wp_enqueue_style(
            'sj-rating-badge',
            SJ_REVIEWS_URL . 'front/assets/sj-badge.css',
            [],
            SJ_REVIEWS_VERSION
        );
        wp_enqueue_style(
            'sj-summary',
            SJ_REVIEWS_URL . 'front/assets/sj-summary.css',
            [],
            SJ_REVIEWS_VERSION
        );
        wp_enqueue_style(
            'sj-form',
            SJ_REVIEWS_URL . 'front/assets/sj-form.css',
            [],
            SJ_REVIEWS_VERSION
        );
        wp_enqueue_style(
            'sj-coup-de-coeur',
            SJ_REVIEWS_URL . 'front/assets/sj-coup-de-coeur.css',
            [],
            SJ_REVIEWS_VERSION
        );

        wp_enqueue_script(
            'sj-summary',
            SJ_REVIEWS_URL . 'front/assets/sj-summary.js',
            [],
            SJ_REVIEWS_VERSION,
            true
        );
        wp_localize_script('sj-summary', 'sjReviewsConfig', [
            'restUrl' => esc_url_raw(rest_url('sj-reviews/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'sj-badge',
            SJ_REVIEWS_URL . 'front/assets/sj-badge.js',
            [],
            SJ_REVIEWS_VERSION,
            true
        );
        wp_localize_script('sj-badge', 'sjBadgeConfig', [
            'restUrl' => esc_url_raw(rest_url('sj-reviews/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'sj-reviews-front',
            SJ_REVIEWS_URL . 'front/assets/sj-front.js',
            ['swiper'],
            SJ_REVIEWS_VERSION,
            true
        );
    }
}
