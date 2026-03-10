<?php
/**
 * Cron — synchronisation automatique des lieux.
 *
 * Fréquences :
 *   twice_daily  → 2× / jour
 *   daily        → 1× / jour
 *   weekly       → 1× / semaine
 *   monthly      → 1× / mois (≈ 30 jours)
 *   off          → désactivé
 */

namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

class Cron {

    public const HOOK       = 'sj_reviews_auto_sync';
    public const OPTION_KEY = 'sj_reviews_settings';

    /**
     * Intervalles personnalisés (weekly + monthly ne sont pas natifs WP).
     */
    public static array $custom_schedules = [
        'sj_weekly'  => ['interval' => WEEK_IN_SECONDS,     'display' => '1× par semaine'],
        'sj_monthly' => ['interval' => 30 * DAY_IN_SECONDS, 'display' => '1× par mois'],
    ];

    /**
     * Tableau slug front → recurrence WP.
     */
    public static array $preset_map = [
        'twice_daily' => 'twicedaily',
        'daily'       => 'daily',
        'weekly'      => 'sj_weekly',
        'monthly'     => 'sj_monthly',
        'off'         => '',
    ];

    public function init(): void {
        add_filter('cron_schedules', [$this, 'register_schedules']);
        add_action(self::HOOK, [$this, 'run_sync']);
    }

    /**
     * Enregistre les intervalles personnalisés.
     */
    public function register_schedules(array $schedules): array {
        foreach (self::$custom_schedules as $key => $def) {
            if (!isset($schedules[$key])) {
                $schedules[$key] = $def;
            }
        }
        return $schedules;
    }

    /**
     * Recalcule le cron quand la fréquence change dans les settings.
     */
    public static function reschedule(string $frequency): void {
        $recurrence = self::$preset_map[$frequency] ?? '';

        // Supprime l'ancien cron dans tous les cas
        $ts = wp_next_scheduled(self::HOOK);
        if ($ts) {
            wp_unschedule_event($ts, self::HOOK);
        }

        if ($recurrence) {
            wp_schedule_event(time() + 60, $recurrence, self::HOOK);
        }
    }

    /**
     * Exécuté par le cron : sync tous les lieux actifs ayant un place_id
     * et une source compatible (google, tripadvisor, trustpilot).
     */
    public function run_sync(): void {
        $settings = \SJ_Reviews\Includes\Settings::all();
        $lieux    = \SJ_Reviews\Includes\Settings::lieux();
        $updated  = false;

        foreach ($lieux as &$lieu) {
            if (empty($lieu['active'])) continue;

            // Google sync
            if ($lieu['source'] === 'google' && !empty($lieu['place_id'])) {
                $result = $this->sync_google_lieu($lieu, $settings);
                if ($result) {
                    $lieu = array_merge($lieu, $result);
                    $updated = true;
                }
            }

            // Trustpilot sync
            if ($lieu['source'] === 'trustpilot' && !empty($lieu['trustpilot_domain'])) {
                $result = $this->sync_trustpilot_lieu($lieu, $settings);
                if ($result) {
                    $lieu = array_merge($lieu, $result);
                    $updated = true;
                }
            }

            // TripAdvisor sync
            if ($lieu['source'] === 'tripadvisor' && !empty($lieu['tripadvisor_location_id'])) {
                $result = $this->sync_tripadvisor_lieu($lieu, $settings);
                if ($result) {
                    $lieu = array_merge($lieu, $result);
                    $updated = true;
                }
            }
        }
        unset($lieu);

        if ($updated) {
            update_option('sj_lieux', $lieux);
        }

        // Log timestamp
        update_option('sj_reviews_last_sync', current_time('Y-m-d H:i:s'), false);
    }

    /**
     * Sync un lieu Google Places (rating + count).
     */
    private function sync_google_lieu(array $lieu, array $settings): ?array {
        $api_key = $settings['google_api_key'] ?? '';
        if (empty($api_key)) return null;

        $url = add_query_arg([
            'place_id' => $lieu['place_id'],
            'fields'   => 'rating,user_ratings_total',
            'key'      => $api_key,
        ], 'https://maps.googleapis.com/maps/api/place/details/json');

        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['Referer' => trailingslashit(get_site_url())],
        ]);

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (($body['status'] ?? '') !== 'OK') return null;

        return [
            'rating'        => round((float) ($body['result']['rating'] ?? 0), 1),
            'reviews_count' => (int) ($body['result']['user_ratings_total'] ?? 0),
            'last_sync'     => current_time('Y-m-d H:i:s'),
        ];
    }

    /**
     * Sync un lieu Trustpilot (rating + count).
     */
    private function sync_trustpilot_lieu(array $lieu, array $settings): ?array {
        $api_key = $settings['trustpilot_api_key'] ?? '';
        if (empty($api_key)) return null;

        $domain = sanitize_text_field($lieu['trustpilot_domain'] ?? '');
        if (empty($domain)) return null;

        $url = 'https://api.trustpilot.com/v1/business-units/find?' . http_build_query([
            'name'   => $domain,
            'apikey' => $api_key,
        ]);

        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => true]);
        if (is_wp_error($response)) return null;

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['id'])) return null;

        // Store business unit ID for review fetching
        $bu_id = $body['id'];

        return [
            'rating'                => round((float) ($body['score']['trustScore'] ?? 0), 1),
            'reviews_count'         => (int) ($body['numberOfReviews']['total'] ?? 0),
            'trustpilot_bu_id'      => $bu_id,
            'last_sync'             => current_time('Y-m-d H:i:s'),
        ];
    }

    /**
     * Sync un lieu TripAdvisor (rating + count).
     */
    private function sync_tripadvisor_lieu(array $lieu, array $settings): ?array {
        $api_key = $settings['tripadvisor_api_key'] ?? '';
        if (empty($api_key)) return null;

        $location_id = sanitize_text_field($lieu['tripadvisor_location_id'] ?? '');
        if (empty($location_id)) return null;

        $url = "https://api.content.tripadvisor.com/api/v1/location/{$location_id}/details?" . http_build_query([
            'key'      => $api_key,
            'language' => 'fr',
        ]);

        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['accept' => 'application/json'],
        ]);
        if (is_wp_error($response)) return null;

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return [
            'rating'        => round((float) ($body['rating'] ?? 0), 1),
            'reviews_count' => (int) ($body['num_reviews'] ?? 0),
            'last_sync'     => current_time('Y-m-d H:i:s'),
        ];
    }
}
