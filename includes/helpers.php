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
function sj_get_reviews(array $args = [], bool $private = false): array {
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
        $reviews[] = sj_normalize_review($post, $private);
    }
    return $reviews;
}

/**
 * Normalise un post sj_avis en tableau unifié,
 * qu'ACF soit actif ou non.
 *
 * @param WP_Post $post
 * @param bool    $private Inclure les données PII (email brut). Faux par défaut → front-end safe.
 * @return array
 */
function sj_normalize_review(\WP_Post $post, bool $private = false): array {
    $get = fn(string $key) => function_exists('get_field')
        ? get_field($key, $post->ID)
        : get_post_meta($post->ID, $key, true);

    $avatar_field = $get('avis_avatar');
    $avatar_url   = '';
    $avatar_id    = 0;
    if (is_array($avatar_field)) {
        $avatar_url = $avatar_field['sizes']['thumbnail'] ?? $avatar_field['url'] ?? '';
        $avatar_id  = (int) ($avatar_field['ID'] ?? $avatar_field['id'] ?? 0);
    } elseif (is_numeric($avatar_field) && $avatar_field) {
        $avatar_id  = (int) $avatar_field;
        $avatar_url = wp_get_attachment_image_url($avatar_id, 'thumbnail') ?: '';
    }

    $linked_post_id  = (int) ($get('avis_linked_post') ?: 0);
    $linked_post_obj = null;
    if ($linked_post_id > 0) {
        $lp = get_post($linked_post_id);
        if ($lp && $lp->post_status === 'publish') {
            $linked_post_obj = [
                'id'        => $lp->ID,
                'title'     => get_the_title($lp),
                'post_type' => $lp->post_type,
                'url'       => get_permalink($lp) ?: '',
            ];
        }
    }

    $lieu_id  = (string) ($get('avis_lieu_id') ?: '');
    $place_id = (string) ($get('avis_place_id') ?: '');

    // Dérive le place_id depuis le lieu si non défini directement
    // Static cache to avoid N+1 get_option() calls when normalizing multiple reviews
    if (!$place_id && $lieu_id) {
        static $lieux_cache = null;
        if ($lieux_cache === null) {
            $lieux_cache = \SJ_Reviews\Includes\Settings::lieux();
        }
        foreach ($lieux_cache as $l) {
            if ($l['id'] === $lieu_id && !empty($l['place_id'])) {
                $place_id = (string) $l['place_id'];
                break;
            }
        }
    }

    // Sub-critères : lus directement via get_post_meta car sauvegardés via
    // update_post_meta (REST API) sans passer par ACF — évite get_field()
    // qui retourne false si le champ n'est pas dans un ACF field group.
    $crit_int = fn(string $k): ?int => (function() use ($post, $k) {
        $v = (int) get_post_meta($post->ID, $k, true);
        return ($v >= 1 && $v <= 5) ? $v : null;
    })();

    return [
        'id'           => $post->ID,
        'title'        => get_the_title($post),
        'avis_title'   => (string) ($get('avis_title') ?: ''),
        'author'       => (string) ($get('avis_author') ?: get_the_title($post)),
        'rating'       => (function() use ($get) {
            $v = $get('avis_rating');
            $v = (int) $v;
            return ($v >= 1 && $v <= 5) ? $v : 0;
        })(),
        'text'         => (string) ($get('avis_text') ?: ''),
        'certified'    => (bool) $get('avis_certified'),
        'source'       => (string) ($get('avis_source') ?: 'google'),
        'place_id'     => $place_id,
        'lieu_id'      => $lieu_id,
        'avatar'       => $avatar_url,
        'avatar_id'    => $avatar_id,
        'date'         => $post->post_date,
        'date_rel'     => sj_relative_date($post->post_date),
        'linked_post_id' => $linked_post_id ?: null,
        'linked_post'    => $linked_post_obj,
        // Sous-critères (null = non noté)
        'qualite_prix'  => $crit_int('avis_qualite_prix'),
        'ambiance'      => $crit_int('avis_ambiance'),
        'experience'    => $crit_int('avis_experience'),
        'paysage'       => $crit_int('avis_paysage'),
        // Contexte de visite
        'visit_date'    => (string) ($get('avis_visit_date')   ?: ''),
        'language'      => (string) ($get('avis_language')     ?: 'fr'),
        'travel_type'   => (string) ($get('avis_travel_type')  ?: ''),
        // PII : données privées uniquement en contexte admin/REST authentifié.
        // En front, seul customer_hash est disponible pour dédoublonner sans exposer l'email.
        'customer_email' => $private ? (string) ($get('avis_customer_email') ?: '') : '',
        'customer_phone' => $private ? (string) ($get('avis_customer_phone') ?: '') : '',
        'order_id'       => $private ? (string) ($get('avis_order_id') ?: '')       : '',
        'booking_date'   => $private ? (string) ($get('avis_booking_date') ?: '')   : '',
        'customer_hash'  => (function() use ($get): string {
            $e = strtolower(trim((string) ($get('avis_customer_email') ?: '')));
            return $e ? md5($e) : '';
        })(),
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
    $ratings = array_filter(array_column($reviews, 'rating'), fn($v) => $v >= 1 && $v <= 5);
    if (empty($ratings)) return ['avg' => 0.0, 'count' => count($reviews)];
    $avg = round(array_sum($ratings) / count($ratings), 1);
    return ['avg' => $avg, 'count' => count($reviews)];
}

/**
 * Compute enriched aggregate stats matching the dashboard logic exactly.
 *
 * For each source, takes max(CPT count, platform count) then sums all sources.
 * This is the same formula used by the REST API dashboard endpoint (line 644).
 *
 * @param string|array $lieu_id  '' or 'all' = all lieux, string = single lieu, array = multiple lieu IDs.
 * @param array        $sources  Optional source filter (e.g. ['google', 'regiondo']).
 * @return array{avg: float, count: int, sources: string[]}
 */
function sj_enriched_stats(string|array $lieu_id = '', array $sources = []): array {
    global $wpdb;

    $lieu_ids = is_array($lieu_id) ? array_filter($lieu_id) : [];
    $lieu_str = is_string($lieu_id) ? $lieu_id : '';

    // 1. Count CPT reviews per source (SQL, fast)
    $joins  = '';
    $wheres = "AND p.post_type = 'sj_avis' AND p.post_status = 'publish'";

    if (!empty($lieu_ids)) {
        $joins  .= " INNER JOIN {$wpdb->postmeta} pm_lieu ON pm_lieu.post_id = p.ID AND pm_lieu.meta_key = 'avis_lieu_id'";
        $in = implode(',', array_map(fn($l) => $wpdb->prepare('%s', $l), $lieu_ids));
        $wheres .= " AND pm_lieu.meta_value IN ({$in})";
    } elseif ($lieu_str !== '' && $lieu_str !== 'all') {
        $joins  .= " INNER JOIN {$wpdb->postmeta} pm_lieu ON pm_lieu.post_id = p.ID AND pm_lieu.meta_key = 'avis_lieu_id'";
        $wheres .= $wpdb->prepare(" AND pm_lieu.meta_value = %s", $lieu_str);
    }
    if (!empty($sources)) {
        $joins  .= " INNER JOIN {$wpdb->postmeta} pm_src ON pm_src.post_id = p.ID AND pm_src.meta_key = 'avis_source'";
        $in = implode(',', array_map(fn($s) => $wpdb->prepare('%s', $s), $sources));
        $wheres .= " AND pm_src.meta_value IN ({$in})";
    }

    // Global CPT avg (needed for weighted average)
    $cpt_row = $wpdb->get_row(
        "SELECT COUNT(*) AS total, AVG(CAST(pm_r.meta_value AS DECIMAL(3,1))) AS avg_r
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
         {$joins}
         WHERE 1=1 {$wheres}"
    );
    $cpt_total = (int) ($cpt_row->total ?? 0);
    $avg       = round((float) ($cpt_row->avg_r ?? 0), 1);

    // CPT count per source
    $src_rows = $wpdb->get_results(
        "SELECT pm_s.meta_value AS source, COUNT(*) AS cnt
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_s ON pm_s.post_id = p.ID AND pm_s.meta_key = 'avis_source'
         INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
         {$joins}
         WHERE 1=1 {$wheres}
         GROUP BY pm_s.meta_value"
    );
    $by_source = [];
    foreach ($src_rows as $row) {
        $by_source[$row->source] = (int) $row->cnt;
    }

    // 2. Platform data per source (from lieux settings)
    $all_lieux = \SJ_Reviews\Includes\Settings::lieux();
    if (!empty($lieu_ids)) {
        $matched_lieux = array_filter($all_lieux, fn($l) => in_array($l['id'] ?? '', $lieu_ids, true));
    } elseif ($lieu_str !== '' && $lieu_str !== 'all') {
        $matched_lieux = array_filter($all_lieux, fn($l) => ($l['id'] ?? '') === $lieu_str);
    } else {
        $matched_lieux = $all_lieux;
    }

    $platform_by_source = []; // source => ['total' => int, 'sum' => float]
    foreach ($matched_lieux as $l) {
        $p_count  = (int) ($l['reviews_count'] ?? 0);
        $p_rating = (float) ($l['rating'] ?? 0);
        $src      = $l['source'] ?? '';
        if ($p_count <= 0 || !$src) continue;
        if (!isset($platform_by_source[$src])) {
            $platform_by_source[$src] = ['total' => 0, 'sum' => 0.0];
        }
        $platform_by_source[$src]['total'] += $p_count;
        $platform_by_source[$src]['sum']   += $p_count * $p_rating;
    }

    // 3. Merge: for each source, take max(CPT, platform) — same as dashboard
    $all_sources = array_unique(array_merge(array_keys($by_source), array_keys($platform_by_source)));
    $total = 0;
    $weighted_sum = 0.0;
    $source_names = [];

    foreach ($all_sources as $src) {
        $cpt_cnt      = $by_source[$src] ?? 0;
        $platform_cnt = $platform_by_source[$src]['total'] ?? 0;
        $platform_avg = ($platform_cnt > 0)
            ? ($platform_by_source[$src]['sum'] / $platform_cnt)
            : 0;

        $count_for_source = max($cpt_cnt, $platform_cnt);
        $total += $count_for_source;

        // For weighted average: use platform avg if platform has more reviews, otherwise CPT avg
        if ($platform_cnt > $cpt_cnt && $platform_avg > 0) {
            $weighted_sum += $platform_avg * $count_for_source;
        } elseif ($cpt_cnt > 0) {
            // Get CPT avg for this source
            $src_avg_row = $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(CAST(pm_r.meta_value AS DECIMAL(3,1)))
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
                 INNER JOIN {$wpdb->postmeta} pm_s ON pm_s.post_id = p.ID AND pm_s.meta_key = 'avis_source'
                 {$joins}
                 WHERE 1=1 {$wheres} AND pm_s.meta_value = %s",
                $src
            ));
            $weighted_sum += (float) $src_avg_row * $count_for_source;
        }

        if ($count_for_source > 0) {
            $source_names[] = $src;
        }
    }

    $final_avg = ($total > 0) ? round($weighted_sum / $total, 1) : 0.0;

    return [
        'avg'     => $final_avg,
        'count'   => $total,
        'sources' => $source_names,
    ];
}

/**
 * Génère des étoiles SVG avec remplissage partiel (gradient).
 *
 * @param float  $rating      Note (0-5, supporte les décimales).
 * @param string $color       Couleur des étoiles remplies.
 * @param string $empty_color Couleur des étoiles vides.
 * @param int    $size        Largeur/hauteur en px.
 * @param string $path        Path SVG (doit contenir {{ID}} pour le gradient). Vide = polygon par défaut.
 * @param string $viewbox     viewBox SVG (ex: "0 0 24 24").
 * @param string $class       Classe CSS du wrapper.
 * @return string
 */
function sj_stars_svg(
    float  $rating,
    string $color       = '#f5a623',
    string $empty_color = '#d1d5db',
    int    $size        = 14,
    string $path        = '',
    string $viewbox     = '0 0 24 24',
    string $class       = 'sj-stars-svg'
): string {
    if ($path === '') {
        $path = '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#{{ID}})"/>';
    }

    $html = '<span class="' . esc_attr($class) . '" aria-label="' . esc_attr(number_format($rating, 1)) . ' sur 5">';
    for ($i = 1; $i <= 5; $i++) {
        $fill = min(1.0, max(0.0, $rating - ($i - 1)));
        $pct  = round($fill * 100);
        $id   = 'ssg-' . uniqid();
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="' . esc_attr($viewbox) . '" aria-hidden="true">';
        $html .= '<defs><linearGradient id="' . $id . '">'
               . '<stop offset="' . $pct . '%" stop-color="' . esc_attr($color) . '"/>'
               . '<stop offset="' . $pct . '%" stop-color="' . esc_attr($empty_color) . '"/>'
               . '</linearGradient></defs>';
        $html .= str_replace('{{ID}}', $id, $path);
        $html .= '</svg>';
    }
    return $html . '</span>';
}

/**
 * Format rating for display (ex: 4.8 → "4.8").
 *
 * @param float  $rating   Note.
 * @param int    $decimals Décimales (défaut: 1).
 * @param string $dec_sep  Séparateur décimal (défaut: '.').
 * @return string
 */
function sj_format_rating(float $rating, int $decimals = 1, string $dec_sep = '.'): string {
    return number_format($rating, $decimals, $dec_sep, '');
}

/**
 * Format review count with non-breaking space thousands separator.
 *
 * @param int $count Nombre d'avis.
 * @return string
 */
function sj_format_count(int $count): string {
    return number_format($count, 0, ',', "\xc2\xa0"); // U+00A0 NBSP
}

/**
 * Output Schema.org JSON-LD with duplicate prevention.
 *
 * @param array $schema Schema.org data array.
 */
function sj_output_schema(array $schema): void {
    if (!empty($GLOBALS['sj_reviews_schema_rendered'])) return;
    $GLOBALS['sj_reviews_schema_rendered'] = true;

    echo '<script type="application/ld+json">'
       . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
       . '</script>';
}

/**
 * Icône SVG source (Google G, TripAdvisor, etc.)
 */
function sj_source_icon(string $source): string {
    if ($source === 'regiondo') {
        $svg_path = SJ_REVIEWS_DIR . 'front/assets/logos/regiondo.svg';
        if (file_exists($svg_path)) {
            $svg = file_get_contents($svg_path); // phpcs:ignore WordPress.WP.AlternativeFunctions
            if ($svg) return $svg;
        }
        return '<span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#e85c2c;color:#fff;font-size:11px;font-weight:700">R</span>';
    }
    return match ($source) {
        'google' => '<svg class="sj-source-icon" viewBox="0 0 48 48" width="20" height="20" aria-label="Google"><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v8.51h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.14z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="#FBBC05" d="M10.53 28.59c-.5-1.45-.78-2.99-.78-4.59s.27-3.14.78-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/></svg>',
        'tripadvisor' => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="#00AF87" aria-label="TripAdvisor"><circle cx="6.5" cy="13.5" r="3.5"/><circle cx="17.5" cy="13.5" r="3.5"/><path d="M12 4C7.58 4 3.72 6.25 1.5 9.5H4c1.5-2 3.9-3.5 6.5-4A11.49 11.49 0 0 1 12 5.5a11.49 11.49 0 0 1 1.5.17c2.6.47 5 1.97 6.5 4h2.5C20.28 6.25 16.42 4 12 4z"/></svg>',
        'facebook' => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="#1877F2" aria-label="Facebook"><path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.49h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/></svg>',
        default => '<svg class="sj-source-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-label="Avis"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    };
}
