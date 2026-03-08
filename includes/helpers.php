<?php
/**
 * Helpers globaux du plugin SJ Reviews.
 */

defined('ABSPATH') || exit;

/**
 * Retourne une date relative en français.
 *
 * Logique :
 *  - 0 jour        → "Aujourd'hui"
 *  - 1–6 jours     → "Il y a X jour(s)"
 *  - 7–41 jours    → "Il y a X semaine(s)"  (≤ 6 semaines)
 *  - ≥ 42 jours    → "Il y a X mois"        (arrondi au mois complet)
 *  - ≥ 365 jours   → "Il y a X an(s)"
 *
 * @param string|int $date   Date ISO ou timestamp Unix.
 * @return string
 */
function sj_relative_date(string|int $date): string {
    $ts   = is_numeric($date) ? (int) $date : strtotime($date);
    $now  = time();
    $diff = max(0, $now - $ts);
    $days = (int) floor($diff / DAY_IN_SECONDS);

    if ($days === 0)                         return "Aujourd'hui";
    if ($days < 7)                           return sprintf('Il y a %d jour%s', $days, $days > 1 ? 's' : '');
    if ($days < 42)  { $w = (int) floor($days / 7);   return sprintf('Il y a %d semaine%s', $w, $w > 1 ? 's' : ''); }
    if ($days < 365) { $m = (int) round($days / 30.5); return sprintf('Il y a %d mois', $m); }
    $y = (int) floor($days / 365);
    return sprintf('Il y a %d an%s', $y, $y > 1 ? 's' : '');
}

/**
 * Génère le HTML des étoiles (span SVG ou caractères Unicode).
 *
 * @param int    $rating   Note actuelle (1-5).
 * @param int    $max      Note maximum.
 * @param string $color    Couleur CSS (pour le style inline).
 * @return string
 */
function sj_stars_html(int $rating, int $max = 5, string $color = '#f5a623'): string {
    $rating = max(0, min($max, $rating));
    $html   = '<span class="sj-stars" style="color:' . esc_attr($color) . '" aria-label="' . esc_attr("$rating/$max") . '">';
    for ($i = 1; $i <= $max; $i++) {
        $filled = $i <= $rating;
        $html .= '<svg class="sj-star' . ($filled ? ' sj-star--on' : ' sj-star--off') . '" '
               . 'width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">'
               . '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" '
               . 'fill="' . ($filled ? 'currentColor' : 'none') . '" stroke="currentColor" stroke-width="1.5"/>'
               . '</svg>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Récupère les avis CPT sj_avis.
 *
 * @param array $args  WP_Query args supplémentaires.
 * @return array  Tableau de tableaux normalisés.
 */
function sj_get_reviews(array $args = []): array {
    $defaults = [
        'post_type'      => 'sj_avis',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    $query = new WP_Query(array_merge($defaults, $args));

    $reviews = [];
    foreach ($query->posts as $post) {
        $reviews[] = sj_normalize_review($post);
    }
    return $reviews;
}

/**
 * Normalise un post sj_avis en tableau unifié,
 * qu'ACF soit actif ou non.
 *
 * @param WP_Post $post
 * @return array
 */
function sj_normalize_review(\WP_Post $post): array {
    $get = fn(string $key) => function_exists('get_field')
        ? get_field($key, $post->ID)
        : get_post_meta($post->ID, $key, true);

    $avatar_field = $get('avis_avatar');
    $avatar_url   = '';
    if (is_array($avatar_field)) {
        $avatar_url = $avatar_field['sizes']['thumbnail'] ?? $avatar_field['url'] ?? '';
    } elseif (is_numeric($avatar_field) && $avatar_field) {
        $avatar_url = wp_get_attachment_image_url((int) $avatar_field, 'thumbnail') ?: '';
    }

    return [
        'id'        => $post->ID,
        'title'     => get_the_title($post),
        'author'    => (string) ($get('avis_author') ?: get_the_title($post)),
        'rating'    => (int) ($get('avis_rating') ?: 5),
        'text'      => (string) ($get('avis_text') ?: ''),
        'certified' => (bool) $get('avis_certified'),
        'source'    => (string) ($get('avis_source') ?: 'google'),
        'place_id'  => (string) ($get('avis_place_id') ?: ''),
        'lieu_id'   => (string) ($get('avis_lieu_id') ?: ''),
        'avatar'    => $avatar_url,
        'date'      => $post->post_date,
        'date_rel'  => sj_relative_date($post->post_date),
    ];
}

/**
 * Calcule la note moyenne et le nombre d'avis.
 *
 * @param array $reviews Retour de sj_get_reviews().
 * @return array{avg: float, count: int}
 */
function sj_aggregate(array $reviews): array {
    if (empty($reviews)) return ['avg' => 0.0, 'count' => 0];
    $ratings = array_column($reviews, 'rating');
    $avg     = round(array_sum($ratings) / count($ratings), 1);
    return ['avg' => $avg, 'count' => count($reviews)];
}

/**
 * Icône SVG source (Google G, TripAdvisor, etc.)
 */
function sj_source_icon(string $source): string {
    return match ($source) {
        'google' => '<svg class="sj-source-icon" viewBox="0 0 48 48" width="20" height="20" aria-label="Google"><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v8.51h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.14z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="#FBBC05" d="M10.53 28.59c-.5-1.45-.78-2.99-.78-4.59s.27-3.14.78-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/></svg>',
        'tripadvisor' => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="#00AF87" aria-label="TripAdvisor"><circle cx="6.5" cy="13.5" r="3.5"/><circle cx="17.5" cy="13.5" r="3.5"/><path d="M12 4C7.58 4 3.72 6.25 1.5 9.5H4c1.5-2 3.9-3.5 6.5-4A11.49 11.49 0 0 1 12 5.5a11.49 11.49 0 0 1 1.5.17c2.6.47 5 1.97 6.5 4h2.5C20.28 6.25 16.42 4 12 4z"/></svg>',
        'facebook' => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="#1877F2" aria-label="Facebook"><path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.49h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/></svg>',
        default => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-label="Avis"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    };
}
