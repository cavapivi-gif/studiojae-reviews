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
        require_once SJ_REVIEWS_DIR . 'includes/helpers.php';

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
        (new \SJ_Reviews\Front\Shortcode())->init();
        (new \SJ_Reviews\Front\RatingShortcode())->init();
        (new \SJ_Reviews\Front\SummaryShortcode())->init();
        new \SJ_Reviews\Front\InlineRatingShortcode();

        // Elementor
        add_action('elementor/widgets/register', function ($manager) {
            require_once SJ_REVIEWS_DIR . 'elementor/class-elementor-manager.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-reviews-widget.php';
            require_once SJ_REVIEWS_DIR . 'front/class-rating-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'front/class-summary-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'front/class-inline-rating-shortcode.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-rating-badge-widget.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-summary-widget.php';
            require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-inline-rating-widget.php';
            $manager->register(new \SJ_Reviews\Elementor\Widgets\ReviewsWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\RatingBadgeWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\SummaryWidget());
            $manager->register(new \SJ_Reviews\Elementor\Widgets\InlineRatingWidget());
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

        wp_enqueue_script(
            'sj-summary',
            SJ_REVIEWS_URL . 'front/assets/sj-summary.js',
            [],
            SJ_REVIEWS_VERSION,
            true
        );

        wp_enqueue_script(
            'sj-reviews-front',
            SJ_REVIEWS_URL . 'front/assets/sj-front.js',
            ['swiper'],
            SJ_REVIEWS_VERSION,
            true
        );
    }
}
