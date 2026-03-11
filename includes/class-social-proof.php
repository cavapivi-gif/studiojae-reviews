<?php
/**
 * Social proof toast — floating notification for recent reviews.
 *
 * Auto-injected in wp_footer when enabled in settings.
 * Shows a subtle toast when a recent review exists (< 48h).
 * Non-intrusive: shows once per session, dismissible, respects user preference.
 */

namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

class SocialProof {

    public function init(): void {
        if (is_admin()) return;

        $settings = Settings::all();
        if (($settings['toast_enabled'] ?? '0') !== '1') return;

        add_action('wp_footer', [$this, 'render_toast']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'sj-toast',
            SJ_REVIEWS_URL . 'front/assets/sj-toast.css',
            [],
            SJ_REVIEWS_VERSION
        );
        wp_enqueue_script(
            'sj-toast',
            SJ_REVIEWS_URL . 'front/assets/sj-toast.js',
            [],
            SJ_REVIEWS_VERSION,
            true
        );

        $settings = Settings::all();
        wp_localize_script('sj-toast', 'sjToastConfig', [
            'restUrl'  => esc_url_raw(rest_url('sj-reviews/v1/')),
            'nonce'    => wp_create_nonce('wp_rest'),
            'position' => $settings['toast_position'] ?? 'bottom-left',
            'delay'    => (int) ($settings['toast_delay'] ?? 5000),
            'reviewsUrl' => $settings['toast_reviews_url'] ?? '',
        ]);
    }

    /**
     * Render the empty toast container (populated via JS + REST).
     */
    public function render_toast(): void {
        $settings = Settings::all();
        $position = esc_attr($settings['toast_position'] ?? 'bottom-left');
        ?>
        <div class="sj-toast sj-toast--<?php echo $position; ?>" id="sj-social-proof-toast" role="status" aria-live="polite" hidden>
            <button type="button" class="sj-toast__close" aria-label="Fermer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="sj-toast__content">
                <div class="sj-toast__avatar" id="sj-toast-avatar"></div>
                <div class="sj-toast__body">
                    <p class="sj-toast__text" id="sj-toast-text"></p>
                    <p class="sj-toast__meta" id="sj-toast-meta"></p>
                </div>
            </div>
            <div class="sj-toast__stars" id="sj-toast-stars"></div>
        </div>
        <?php
    }
}
