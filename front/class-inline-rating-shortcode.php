<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_inline_rating] — badge inline minimaliste
 *
 * Attributs :
 *   lieu_id      string  '' = tous | identifiant de lieu
 *   show_stars   1|0     Afficher les étoiles Unicode (défaut : 1)
 *   show_score   1|0     Afficher "4.8/5" (défaut : 1)
 *   show_count   1|0     Afficher "sur 1 340 avis" (défaut : 1)
 *   show_sources 0|1     Afficher "(Google, TripAdvisor)" (défaut : 0)
 *   star_color   hex     Couleur des étoiles (défaut : #f5a623)
 *   text_before  string  Texte avant le widget (défaut : '')
 *   text_after   string  Texte après le widget (défaut : '')
 */
class InlineRatingShortcode {

    /** Map source slug → nom d'affichage */
    private const SOURCE_NAMES = [
        'google'      => 'Google',
        'tripadvisor' => 'TripAdvisor',
        'facebook'    => 'Facebook',
        'trustpilot'  => 'Trustpilot',
        'regiondo'    => 'Regiondo',
        'direct'      => 'Direct',
        'autre'       => 'Autre',
    ];

    public function __construct() {
        add_shortcode('sj_inline_rating', [$this, 'render']);
    }

    public function render(array $atts = []): string {
        $opts = get_option('sj_reviews_settings', []);

        $a = shortcode_atts([
            'lieu_id'      => '',
            'show_stars'   => '1',
            'show_score'   => '1',
            'show_count'   => '1',
            'show_sources' => '0',
            'star_color'   => $opts['star_color'] ?? '#f5a623',
            'text_before'  => '',
            'text_after'   => '',
        ], $atts, 'sj_inline_rating');

        $lieu_id      = sanitize_text_field($a['lieu_id']);
        $show_stars   = (bool)(int) $a['show_stars'];
        $show_score   = (bool)(int) $a['show_score'];
        $show_count   = (bool)(int) $a['show_count'];
        $show_sources = (bool)(int) $a['show_sources'];
        $star_color   = sanitize_hex_color($a['star_color']) ?: '#f5a623';
        $text_before  = esc_html($a['text_before']);
        $text_after   = esc_html($a['text_after']);

        // Récupération des avis CPT
        $query_args = [
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ];
        if ($lieu_id !== '') {
            $query_args['meta_query'] = [
                ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='],
            ];
        }
        $reviews = sj_get_reviews($query_args);

        $agg   = sj_aggregate($reviews);
        $avg   = $agg['avg'];
        $count = $agg['count'];

        // Enrich with platform data (Google, TripAdvisor, etc.)
        // Same logic as summary widget: add non-synced platform reviews
        $all_lieux = (array) get_option('sj_lieux', []);
        if ($lieu_id !== '') {
            $matched_lieux = array_filter($all_lieux, fn($l) => ($l['id'] ?? '') === $lieu_id);
        } else {
            $matched_lieux = $all_lieux;
        }

        foreach ($matched_lieux as $l) {
            $platform_count  = (int) ($l['reviews_count'] ?? 0);
            $platform_rating = (float) ($l['rating'] ?? 0);
            if ($platform_count <= 0 || $platform_rating <= 0) continue;

            // Count CPT reviews for this lieu (already in $count)
            $lieu_cpt_count = 0;
            if (!empty($l['id'])) {
                global $wpdb;
                $lieu_cpt_count = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} p
                     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'avis_lieu_id'
                     WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                     AND pm.meta_value = %s",
                    $l['id']
                ));
            }

            $extra = max(0, $platform_count - $lieu_cpt_count);
            if ($extra > 0) {
                $combined = $count + $extra;
                $avg   = ($count > 0)
                    ? round(($avg * $count + $platform_rating * $extra) / $combined, 1)
                    : round($platform_rating, 1);
                $count = $combined;
            }
        }

        if ($avg <= 0 && $count === 0) return '';

        // Format du score : "4.8/5" ou "5/5" si entier
        $score_str = (fmod($avg, 1.0) === 0.0)
            ? intval($avg) . '/5'
            : number_format($avg, 1, '.', '') . '/5';

        // Format du count avec espace insécable comme séparateur des milliers
        $count_str = number_format($count, 0, ',', "\xc2\xa0"); // U+00A0 NBSP

        // Étoiles : Unicode remplies/vides selon round($avg)
        $stars_str = '';
        if ($show_stars) {
            $filled  = max(0, min(5, (int) round($avg)));
            $empty   = 5 - $filled;
            $stars_str = str_repeat('★', $filled) . str_repeat('☆', $empty);
        }

        // aria-label global
        $aria_parts = [];
        $aria_parts[] = 'Note\xc2\xa0: ' . number_format($avg, 1, '.', '') . ' sur 5';
        $aria_parts[] = number_format($count, 0, ',', "\xc2\xa0") . ' avis';
        $aria_label = esc_attr('Note : ' . number_format($avg, 1, '.', '') . ' sur 5 – ' . number_format($count, 0, ',', "\xc2\xa0") . ' avis');

        // Sources
        $sources_html = '';
        if ($show_sources) {
            $raw_sources  = array_unique(array_column($reviews, 'source'));
            $source_names = array_map(
                fn(string $s) => self::SOURCE_NAMES[$s] ?? ucfirst($s),
                $raw_sources
            );
            sort($source_names);
            if (!empty($source_names)) {
                $sources_html = '<span class="sj-inline-rating__sources" aria-hidden="true">('
                    . esc_html(implode(', ', $source_names))
                    . ')</span>';
            }
        }

        // Assemblage HTML
        $inner = '';
        if ($text_before !== '') {
            $inner .= '<span class="sj-inline-rating__before">' . $text_before . '</span>';
        }
        if ($show_stars && $stars_str !== '') {
            $inner .= '<span class="sj-inline-rating__stars" style="color:' . esc_attr($star_color) . '" aria-hidden="true">'
                    . esc_html($stars_str)
                    . '</span>';
        }
        if ($show_score) {
            $inner .= '<span class="sj-inline-rating__score">' . esc_html($score_str) . '</span>';
        }
        if ($show_count) {
            $inner .= '<span class="sj-inline-rating__count">sur ' . esc_html($count_str) . ' avis</span>';
        }
        if ($show_sources && $sources_html !== '') {
            $inner .= $sources_html;
        }
        if ($text_after !== '') {
            $inner .= '<span class="sj-inline-rating__after">' . $text_after . '</span>';
        }

        return '<span class="sj-inline-rating" aria-label="' . $aria_label . '">'
             . $inner
             . '</span>';
    }
}
