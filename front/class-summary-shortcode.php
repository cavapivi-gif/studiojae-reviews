<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_summary] — Page Avis complète style TripAdvisor.
 *
 * Paramètres :
 *   lieu_id              string  'auto' | 'all' | 'lieu_xxxx'
 *   show_distribution    '1'|'0' Barres de répartition ★
 *   show_criteria        '1'|'0' Sous-critères (2 colonnes)
 *   show_filters         '1'|'0' Barre de filtres interactive
 *   show_reviews         '1'|'0' Cards d'avis
 *   show_rating_filter   '1'|'0'
 *   show_period_filter   '1'|'0'
 *   show_language_filter '1'|'0'
 *   show_sort            '1'|'0'
 *   reviews_initial      int     Nb de cards visibles avant "Voir plus"
 *   cards_columns        '1'|'2'|'3'
 */
class SummaryShortcode {

    /** Libellés des types de voyage */
    private const TRAVEL_LABELS = [
        'couple'   => 'Couple',
        'solo'     => 'Solo',
        'famille'  => 'Famille',
        'amis'     => 'Entre amis',
        'affaires' => 'Affaires',
    ];

    /** Libellés des langues */
    private const LANG_LABELS = [
        'fr' => 'Français',
        'en' => 'Anglais',
        'it' => 'Italien',
        'de' => 'Allemand',
        'es' => 'Espagnol',
    ];

    /** Périodes saisonnières (label => mois) */
    private const PERIODS = [
        'spring' => ['label' => 'Mars–Mai',   'months' => [3,4,5]],
        'summer' => ['label' => 'Juin–Août',  'months' => [6,7,8]],
        'autumn' => ['label' => 'Sept.–Nov.', 'months' => [9,10,11]],
        'winter' => ['label' => 'Déc.–Fév.', 'months' => [12,1,2]],
    ];

    public function init(): void {
        add_shortcode('sj_summary', [$this, 'render']);
    }

    /** Point d'entrée shortcode ET widget Elementor */
    public function render(array $atts = []): string {
        $a = shortcode_atts([
            'lieu_id'              => 'auto',
            'show_distribution'    => '1',
            'show_criteria'        => '1',
            'show_filters'         => '1',
            'show_reviews'         => '1',
            'show_rating_filter'   => '1',
            'show_period_filter'   => '1',
            'show_language_filter' => '1',
            'show_sort'            => '1',
            'reviews_initial'      => 5,
            'cards_columns'        => '1',
        ], $atts, 'sj_summary');

        $lieu_id  = $this->resolve_lieu($a['lieu_id']);
        $reviews  = $this->get_reviews($lieu_id);
        $stats    = $this->compute_stats($reviews);

        if (empty($stats) || $stats['total'] === 0) {
            return '<div class="sj-summary sj-summary--empty"><p>'
                . esc_html__('Aucun avis disponible.', 'sj-reviews')
                . '</p></div>';
        }

        return $this->render_html($reviews, $stats, $a);
    }

    // ── Résolution du lieu ────────────────────────────────────────────────────

    private function resolve_lieu(string $req): string {
        if ($req !== 'auto') return sanitize_key($req);

        $post_id = get_the_ID();
        if (!$post_id) return 'all';

        $direct = get_post_meta($post_id, 'sj_lieu_id', true);
        if ($direct) return sanitize_key($direct);

        global $wpdb;
        $found = $wpdb->get_var($wpdb->prepare(
            "SELECT pm2.meta_value
             FROM {$wpdb->postmeta} pm1
             INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
             INNER JOIN {$wpdb->posts} p ON p.ID = pm1.post_id
             WHERE pm1.meta_key  = 'avis_linked_post' AND pm1.meta_value = %s
               AND pm2.meta_key  = 'avis_lieu_id'     AND pm2.meta_value != ''
               AND p.post_type   = 'sj_avis'
               AND p.post_status = 'publish'
             LIMIT 1",
            (string) $post_id
        ));

        return $found ? sanitize_key($found) : 'all';
    }

    // ── Récupération des avis ─────────────────────────────────────────────────

    private function get_reviews(string $lieu_id): array {
        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
        if ($lieu_id && $lieu_id !== 'all') {
            $args['meta_query'] = [
                ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='],
            ];
        }
        return array_map('sj_normalize_review', get_posts($args));
    }

    // ── Calcul des statistiques ───────────────────────────────────────────────

    private function compute_stats(array $reviews): array {
        if (empty($reviews)) return [];

        $total        = count($reviews);
        $rating_sum   = 0;
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $crit_sums    = ['qualite_prix' => 0, 'ambiance' => 0, 'experience' => 0, 'paysage' => 0];
        $crit_counts  = array_fill_keys(array_keys($crit_sums), 0);

        foreach ($reviews as $r) {
            $rating      = max(1, min(5, (int) $r['rating']));
            $rating_sum += $rating;
            $distribution[$rating]++;
            foreach (array_keys($crit_sums) as $c) {
                $v = $r[$c];
                if ($v !== null && $v >= 1 && $v <= 5) {
                    $crit_sums[$c]  += $v;
                    $crit_counts[$c]++;
                }
            }
        }

        $avg           = round($rating_sum / $total, 1);
        $criteria_avgs = [];
        foreach (array_keys($crit_sums) as $c) {
            $criteria_avgs[$c] = $crit_counts[$c] > 0
                ? round($crit_sums[$c] / $crit_counts[$c], 1)
                : null;
        }

        return compact('total', 'avg', 'distribution', 'criteria_avgs');
    }

    // ── Rendu HTML global ─────────────────────────────────────────────────────

    private function render_html(array $reviews, array $stats, array $a): string {
        $uid = 'sj-' . wp_unique_id();
        ob_start();
        ?>
<div class="sj-summary" id="<?php echo esc_attr($uid); ?>"
     data-initial="<?php echo esc_attr((int) $a['reviews_initial']); ?>">

    <!-- ══ SECTION 1 : EN-TÊTE ══════════════════════════════════════════════ -->
    <div class="sj-summary__header">

        <!-- Score global -->
        <div class="sj-summary__score-block">
            <div class="sj-summary__score-num">
                <?php echo esc_html(number_format($stats['avg'], 1, ',', '')); ?>
            </div>
            <div class="sj-summary__score-info">
                <div class="sj-summary__score-label">
                    <?php echo esc_html($this->rating_label($stats['avg'])); ?>
                </div>
                <?php echo $this->bubbles_html($stats['avg']); ?>
                <div class="sj-summary__count">
                    <?php printf(esc_html(_n('%d avis', '%d avis', $stats['total'], 'sj-reviews')), $stats['total']); ?>
                </div>
            </div>
        </div>

        <!-- ── SECTION 2 : DISTRIBUTION + SOUS-CRITÈRES (côte à côte) ──── -->
        <div class="sj-summary__middle">

            <?php if ($a['show_distribution'] !== '0'): ?>
            <!-- Distribution par étoiles -->
            <div class="sj-summary__distribution">
                <?php
                $dist_labels = [5=>'Excellent',4=>'Bien',3=>'Moyen',2=>'Médiocre',1=>'Horrible'];
                $max_dist    = max(1, max($stats['distribution']));
                foreach ($dist_labels as $stars => $dlabel):
                    $count = $stats['distribution'][$stars] ?? 0;
                    $pct   = round(($count / $max_dist) * 100);
                ?>
                <div class="sj-summary__dist-row">
                    <span class="sj-summary__dist-label"><?php echo esc_html($dlabel); ?></span>
                    <div class="sj-summary__dist-track"
                         role="progressbar"
                         aria-valuenow="<?php echo esc_attr($pct); ?>"
                         aria-valuemin="0" aria-valuemax="100">
                        <div class="sj-summary__dist-fill" style="width:<?php echo esc_attr($pct); ?>%"></div>
                    </div>
                    <span class="sj-summary__dist-count"><?php echo esc_html($count); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php
            $crit_labels  = ['qualite_prix'=>'Qualité/prix','ambiance'=>'Ambiance','experience'=>'Expérience','paysage'=>'Paysage'];
            $has_criteria = $a['show_criteria'] !== '0' && array_filter($stats['criteria_avgs'], fn($v) => $v !== null);
            if ($has_criteria):
            ?>
            <!-- Sous-critères : grille 2 colonnes -->
            <div class="sj-summary__criteria">
                <?php foreach ($crit_labels as $k => $lbl):
                    $crit_avg = $stats['criteria_avgs'][$k];
                    if ($crit_avg === null) continue;
                    $crit_pct = round(($crit_avg / 5) * 100);
                ?>
                <div class="sj-summary__criterion">
                    <span class="sj-summary__crit-label"><?php echo esc_html($lbl); ?></span>
                    <div class="sj-summary__crit-track">
                        <div class="sj-summary__crit-fill"
                             style="width:<?php echo esc_attr($crit_pct); ?>%"></div>
                    </div>
                    <span class="sj-summary__crit-score">
                        <?php echo esc_html(number_format($crit_avg, 1, ',', '')); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.sj-summary__middle -->

    </div><!-- /.sj-summary__header -->

    <?php
    $show_filters  = $a['show_filters']  !== '0';
    $show_reviews  = $a['show_reviews']  !== '0';

    // Collecte les valeurs disponibles pour les pills de filtres
    $avail_ratings  = array_unique(array_filter(array_column($reviews, 'rating')));
    rsort($avail_ratings);
    $avail_langs    = array_unique(array_filter(array_column($reviews, 'language')));
    $avail_periods  = [];
    foreach ($reviews as $rv) {
        if (!empty($rv['visit_date'])) {
            $m = (int) date('n', strtotime($rv['visit_date']));
            foreach (self::PERIODS as $slug => $pd) {
                if (in_array($m, $pd['months'], true)) { $avail_periods[$slug] = $pd['label']; break; }
            }
        }
    }
    ?>

    <?php if ($show_filters && $show_reviews): ?>
    <!-- ══ SECTION 3 : BARRE DE FILTRES ══════════════════════════════════════ -->
    <div class="sj-summary__filters" data-summary="<?php echo esc_attr($uid); ?>">

        <!-- Trier -->
        <?php if ($a['show_sort'] !== '0'): ?>
        <div class="sj-filters__sort">
            <label class="sj-filters__sort-label" for="<?php echo esc_attr($uid); ?>-sort">
                Trier par
            </label>
            <select id="<?php echo esc_attr($uid); ?>-sort"
                    class="sj-filters__sort-select"
                    data-filter="sort">
                <option value="recent">Plus récent</option>
                <option value="rating_desc">Meilleure note</option>
                <option value="rating_asc">Moins bonne note</option>
            </select>
        </div>
        <?php endif; ?>

        <!-- Pills : Note -->
        <?php if ($a['show_rating_filter'] !== '0' && count($avail_ratings) > 1): ?>
        <div class="sj-filters__group">
            <span class="sj-filters__group-label">Note</span>
            <div class="sj-filters__pills">
                <?php foreach ($avail_ratings as $r): ?>
                <button type="button"
                        class="sj-filters__pill"
                        data-filter="rating"
                        data-value="<?php echo esc_attr($r); ?>"
                        aria-pressed="false">
                    <?php echo esc_html($r); ?>★
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pills : Période -->
        <?php if ($a['show_period_filter'] !== '0' && !empty($avail_periods)): ?>
        <div class="sj-filters__group">
            <span class="sj-filters__group-label">Période</span>
            <div class="sj-filters__pills">
                <?php foreach (self::PERIODS as $slug => $pd):
                    if (!isset($avail_periods[$slug])) continue;
                ?>
                <button type="button"
                        class="sj-filters__pill"
                        data-filter="period"
                        data-value="<?php echo esc_attr($slug); ?>"
                        aria-pressed="false">
                    <?php echo esc_html($pd['label']); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pills : Langue -->
        <?php if ($a['show_language_filter'] !== '0' && count($avail_langs) > 1): ?>
        <div class="sj-filters__group">
            <span class="sj-filters__group-label">Langue</span>
            <div class="sj-filters__pills">
                <?php foreach ($avail_langs as $lang): ?>
                <button type="button"
                        class="sj-filters__pill"
                        data-filter="language"
                        data-value="<?php echo esc_attr($lang); ?>"
                        aria-pressed="false">
                    <?php echo esc_html(self::LANG_LABELS[$lang] ?? strtoupper($lang)); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Badge nb filtres actifs -->
        <div class="sj-filters__active" aria-live="polite" hidden>
            <button type="button" class="sj-filters__reset">
                Réinitialiser les filtres
                <span class="sj-filters__active-count"></span>
            </button>
        </div>

    </div><!-- /.sj-summary__filters -->
    <?php endif; ?>

    <?php if ($show_reviews): ?>
    <!-- ══ SECTION 4 : CARDS D'AVIS ══════════════════════════════════════════ -->
    <div class="sj-summary__reviews sj-cards-grid sj-cards-grid--<?php echo esc_attr($a['cards_columns']); ?>col"
         data-summary="<?php echo esc_attr($uid); ?>"
         aria-live="polite">
        <?php foreach ($reviews as $idx => $rv):
            $period = $this->get_period($rv['visit_date'] ?? '');
            $hidden = $idx >= (int) $a['reviews_initial'] ? ' sj-card--overflow' : '';
        ?>
        <article class="sj-card<?php echo esc_attr($hidden); ?>"
                 data-rating="<?php echo esc_attr($rv['rating']); ?>"
                 data-language="<?php echo esc_attr($rv['language'] ?: 'fr'); ?>"
                 data-period="<?php echo esc_attr($period); ?>"
                 data-date="<?php echo esc_attr($rv['date']); ?>"
                 aria-label="Avis de <?php echo esc_attr($rv['author']); ?>">

            <!-- En-tête : avatar + auteur + source -->
            <div class="sj-card__header">
                <div class="sj-card__author-block">
                    <?php if (!empty($rv['avatar'])): ?>
                        <img class="sj-card__avatar sj-card__avatar--img"
                             src="<?php echo esc_url($rv['avatar']); ?>"
                             alt="<?php echo esc_attr($rv['author']); ?>"
                             width="36" height="36" loading="lazy">
                    <?php else: ?>
                        <div class="sj-card__avatar sj-card__avatar--initiale" aria-hidden="true">
                            <?php echo esc_html(mb_strtoupper(mb_substr($rv['author'], 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                    <div class="sj-card__author-info">
                        <span class="sj-card__author-name"><?php echo esc_html($rv['author']); ?></span>
                        <?php if (!empty($rv['source']) && $rv['source'] !== 'direct'):
                            echo '<span class="sj-card__source-name">' . esc_html(ucfirst($rv['source'])) . '</span>';
                        endif; ?>
                    </div>
                </div>
                <?php if (!empty($rv['certified'])): ?>
                <span class="sj-card__certified">Certifié</span>
                <?php endif; ?>
            </div>

            <!-- Note + meta visite -->
            <div class="sj-card__rating">
                <?php echo $this->bubbles_html((float) $rv['rating']); ?>
                <?php
                $meta_parts = [];
                if (!empty($rv['visit_date'])) {
                    $meta_parts[] = esc_html(date_i18n('M Y', strtotime($rv['visit_date'])));
                }
                if (!empty($rv['travel_type']) && isset(self::TRAVEL_LABELS[$rv['travel_type']])) {
                    $meta_parts[] = esc_html(self::TRAVEL_LABELS[$rv['travel_type']]);
                }
                if (!empty($meta_parts)): ?>
                <span class="sj-card__meta"><?php echo implode(' · ', $meta_parts); ?></span>
                <?php endif; ?>
            </div>

            <!-- Titre -->
            <?php if (!empty($rv['avis_title'])): ?>
            <h3 class="sj-card__title"><?php echo esc_html($rv['avis_title']); ?></h3>
            <?php endif; ?>

            <!-- Texte (tronqué avec JS) -->
            <?php if (!empty($rv['text'])): ?>
            <div class="sj-card__body">
                <p class="sj-card__text"
                   data-full="<?php echo esc_attr($rv['text']); ?>">
                    <?php echo esc_html($rv['text']); ?>
                </p>
                <?php if (mb_strlen($rv['text']) > 220): ?>
                <button type="button" class="sj-card__more" aria-expanded="false">
                    Voir plus
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                        <path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Pied : date de rédaction -->
            <footer class="sj-card__footer">
                <time class="sj-card__date"
                      datetime="<?php echo esc_attr($rv['date']); ?>">
                    Rédigé le <?php echo esc_html(date_i18n('j F Y', strtotime($rv['date']))); ?>
                </time>
            </footer>

        </article><!-- /.sj-card -->
        <?php endforeach; ?>
    </div><!-- /.sj-summary__reviews -->

    <!-- ══ SECTION 5 : VOIR PLUS ══════════════════════════════════════════════ -->
    <?php $hidden_count = max(0, count($reviews) - (int) $a['reviews_initial']); ?>
    <?php if ($hidden_count > 0): ?>
    <div class="sj-summary__loadmore">
        <button type="button" class="sj-summary__load-btn"
                data-summary="<?php echo esc_attr($uid); ?>">
            Voir plus d'avis
            <span class="sj-summary__load-count">(<?php echo esc_html($hidden_count); ?>)</span>
        </button>
    </div>
    <?php endif; ?>

    <?php endif; // show_reviews ?>

</div><!-- /.sj-summary -->
        <?php
        return ob_get_clean();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function rating_label(float $avg): string {
        if ($avg >= 4.5) return 'Excellent';
        if ($avg >= 4.0) return 'Très bien';
        if ($avg >= 3.5) return 'Bien';
        if ($avg >= 3.0) return 'Moyen';
        if ($avg >= 2.0) return 'Médiocre';
        return                   'Mauvais';
    }

    private function bubbles_html(float $rating): string {
        $label = number_format($rating, 1, ',', '') . ' sur 5 bulles';
        $html  = '<div class="sj-summary__bubbles" aria-label="' . esc_attr($label) . '">';
        for ($i = 1; $i <= 5; $i++) {
            $fill = min(1.0, max(0.0, $rating - ($i - 1)));
            if ($fill >= 0.75)     $cls = 'full';
            elseif ($fill >= 0.25) $cls = 'half';
            else                   $cls = 'empty';
            $html .= '<span class="sj-summary__bubble sj-summary__bubble--' . $cls . '" aria-hidden="true"></span>';
        }
        $html .= '</div>';
        return $html;
    }

    private function get_period(string $date): string {
        if (!$date) return '';
        $month = (int) date('n', strtotime($date));
        foreach (self::PERIODS as $slug => $pd) {
            if (in_array($month, $pd['months'], true)) return $slug;
        }
        return '';
    }
}
