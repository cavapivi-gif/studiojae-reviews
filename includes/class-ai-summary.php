<?php
/**
 * AI-powered review summaries via Anthropic Claude API.
 *
 * - Generates a concise summary from all reviews for a given lieu.
 * - Caches result in a transient (24h per lieu).
 * - Rate-limited: 1 generation per lieu per 24h.
 * - Front-end only reads cached summaries (no API call from visitors).
 */

namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

class AiSummary {

    private const CACHE_PREFIX = 'sj_ai_summary_';
    private const CACHE_TTL    = DAY_IN_SECONDS;
    private const API_URL      = 'https://api.anthropic.com/v1/messages';

    /**
     * Get cached summary for a lieu. Returns null if not yet generated.
     */
    public static function get_cached(string $lieu_id): ?array {
        $key = self::CACHE_PREFIX . sanitize_key($lieu_id ?: 'all');
        $data = get_transient($key);
        return is_array($data) ? $data : null;
    }

    /**
     * Generate (or regenerate) summary for a lieu.
     * Returns ['summary' => string, 'generated_at' => string] or WP_Error.
     */
    public static function generate(string $lieu_id): array|\WP_Error {
        $api_key = Settings::get('anthropic_api_key', '');
        if (empty($api_key)) {
            return new \WP_Error('no_api_key', 'Clé API Anthropic non configurée.', ['status' => 400]);
        }

        // Fetch reviews
        $reviews = self::fetch_reviews($lieu_id, 50);
        if (empty($reviews)) {
            return new \WP_Error('no_reviews', 'Aucun avis disponible pour ce lieu.', ['status' => 404]);
        }

        // Build compact JSON payload (minimize tokens)
        $payload = self::build_review_payload($reviews);

        // Call Claude API
        $summary = self::call_claude($api_key, $payload);
        if (is_wp_error($summary)) {
            return $summary;
        }

        $result = [
            'summary'      => $summary,
            'generated_at' => current_time('Y-m-d H:i:s'),
            'review_count' => count($reviews),
        ];

        // Cache it
        $key = self::CACHE_PREFIX . sanitize_key($lieu_id ?: 'all');
        set_transient($key, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Fetch reviews for a lieu, ordered by rating DESC.
     */
    private static function fetch_reviews(string $lieu_id, int $limit): array {
        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
            'meta_key'       => 'avis_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ];

        if ($lieu_id && $lieu_id !== 'all') {
            $args['meta_query'] = [
                [
                    'key'   => 'avis_lieu_id',
                    'value' => $lieu_id,
                ],
            ];
        }

        return array_map('sj_normalize_review', get_posts($args));
    }

    /**
     * Build a minimal JSON array for the prompt (saves tokens).
     */
    private static function build_review_payload(array $reviews): string {
        $compact = [];
        foreach ($reviews as $r) {
            $item = [
                'r' => (int) $r['rating'],
            ];
            if (!empty($r['text'])) {
                // Truncate to ~200 chars per review to cap total tokens
                $text = mb_substr(strip_tags($r['text']), 0, 200);
                $item['t'] = $text;
            }
            if (!empty($r['source'])) {
                $item['s'] = $r['source'];
            }
            $compact[] = $item;
        }
        return wp_json_encode($compact, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Call Claude API to generate summary.
     */
    private static function call_claude(string $api_key, string $reviews_json): string|\WP_Error {
        $prompt = <<<PROMPT
Tu es un assistant d'analyse d'avis clients. Voici des avis au format JSON compact :
- r = note (1-5), t = texte, s = source

$reviews_json

Génère un résumé factuel en 2-3 phrases en français. Mentionne les points forts récurrents et les axes d'amélioration s'il y en a. Sois direct, pas de formule de politesse. Pas de markdown. Max 150 mots.
PROMPT;

        $body = wp_json_encode([
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 300,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $response = wp_remote_post(self::API_URL, [
            'timeout' => 30,
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $api_key,
                'anthropic-version'  => '2023-06-01',
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('api_error', 'Erreur réseau : ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200) {
            $msg = $data['error']['message'] ?? "Erreur API (HTTP $code)";
            return new \WP_Error('api_error', $msg, ['status' => $code]);
        }

        $text = $data['content'][0]['text'] ?? '';
        if (empty($text)) {
            return new \WP_Error('empty_response', 'Réponse vide de l\'API.');
        }

        return sanitize_text_field($text);
    }

    /**
     * Clear cached summary for a lieu (or all).
     */
    public static function clear_cache(string $lieu_id = ''): void {
        if ($lieu_id) {
            delete_transient(self::CACHE_PREFIX . sanitize_key($lieu_id));
        } else {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . self::CACHE_PREFIX . '%'
                )
            );
        }
    }
}
