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

    /** @deprecated Use Labels::TRAVEL_TYPES */
    private const TRAVEL_LABELS = [];

    /** @deprecated Use Labels::LANGUAGES */
    private const LANG_LABELS = [];

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
            'text_words'           => 40,
            'show_card_criteria'   => '0',
            'show_certified'       => '1',
            'schema_enabled'       => '1',
            'schema_type'          => 'LocalBusiness',
            'schema_name'          => '',
            'source_filter'        => '',
            'lieu_ids'             => '',
            'score_layout'         => 'default',
            'show_search'          => '0',
            'show_ai_summary'      => '0',
            'show_verified_banner' => '0',
            'verified_banner_text' => 'Tous les avis proviennent de client·es vérifié·es',
            'max_width'            => '',
            'max_width_tablet'     => '',
            'max_width_mobile'     => '',
        ], $atts, 'sj_summary');

        $lieu_id  = $this->resolve_lieu($a['lieu_id']);
        $initial  = max(1, (int) $a['reviews_initial']);

        // Compute stats from ALL reviews via SQL (accurate count/avg)
        $stats = $this->compute_stats_sql($lieu_id, $a);

        if (empty($stats) || $stats['total'] === 0) {
            return '<div class="sj-summary sj-summary--empty"><p>'
                . esc_html__('Aucun avis disponible.', 'sj-reviews')
                . '</p></div>';
        }

        // Only load initial cards for server render (rest loaded via AJAX)
        $reviews = $this->get_reviews($lieu_id, $a, $initial);

        return $this->render_html($reviews, $stats, $a, $lieu_id);
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

    private function get_reviews(string $lieu_id, array $a = [], int $limit = 200): array {
        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $meta_query = ['relation' => 'AND'];

        // lieu_ids multi-select (F3) takes priority; fall back to single lieu_id
        if (!empty($a['lieu_ids'])) {
            $lieux = array_filter(array_map('trim', explode(',', $a['lieu_ids'])));
            if (!empty($lieux)) {
                $meta_query[] = [
                    'key'     => 'avis_lieu_id',
                    'value'   => $lieux,
                    'compare' => 'IN',
                ];
            }
        } elseif ($lieu_id && $lieu_id !== 'all') {
            // backward compat with existing single lieu_id
            $meta_query[] = [
                'key'   => 'avis_lieu_id',
                'value' => $lieu_id,
            ];
        }

        // source_filter multi-select (F3)
        if (!empty($a['source_filter'])) {
            $sources = array_filter(array_map('trim', explode(',', $a['source_filter'])));
            if (!empty($sources)) {
                $meta_query[] = [
                    'key'     => 'avis_source',
                    'value'   => $sources,
                    'compare' => 'IN',
                ];
            }
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        return array_map('sj_normalize_review', get_posts($args));
    }

    /**
     * Compute stats from ALL reviews via SQL — not limited by posts_per_page.
     * This ensures the header score, count, and distribution are always accurate.
     */
    private function compute_stats_sql(string $lieu_id, array $a): array {
        global $wpdb;

        // Build WHERE conditions for lieu/source filters
        $joins  = '';
        $wheres = "AND p.post_type = 'sj_avis' AND p.post_status = 'publish'";

        if (!empty($a['lieu_ids'])) {
            $lieux = array_filter(array_map('trim', explode(',', $a['lieu_ids'])));
            if (!empty($lieux)) {
                $joins  .= " INNER JOIN {$wpdb->postmeta} pm_lieu ON pm_lieu.post_id = p.ID AND pm_lieu.meta_key = 'avis_lieu_id'";
                $in = implode(',', array_map(fn($l) => $wpdb->prepare('%s', $l), $lieux));
                $wheres .= " AND pm_lieu.meta_value IN ({$in})";
            }
        } elseif ($lieu_id && $lieu_id !== 'all') {
            $joins  .= " INNER JOIN {$wpdb->postmeta} pm_lieu ON pm_lieu.post_id = p.ID AND pm_lieu.meta_key = 'avis_lieu_id'";
            $wheres .= $wpdb->prepare(" AND pm_lieu.meta_value = %s", $lieu_id);
        }

        if (!empty($a['source_filter'])) {
            $sources = array_filter(array_map('trim', explode(',', $a['source_filter'])));
            if (!empty($sources)) {
                $joins  .= " INNER JOIN {$wpdb->postmeta} pm_src ON pm_src.post_id = p.ID AND pm_src.meta_key = 'avis_source'";
                $in = implode(',', array_map(fn($s) => $wpdb->prepare('%s', $s), $sources));
                $wheres .= " AND pm_src.meta_value IN ({$in})";
            }
        }

        // Total + average
        $row = $wpdb->get_row(
            "SELECT COUNT(*) AS total, AVG(CAST(pm_r.meta_value AS DECIMAL(3,1))) AS avg_rating
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
             {$joins}
             WHERE 1=1 {$wheres}"
        );

        $total = (int) ($row->total ?? 0);
        $avg   = round((float) ($row->avg_rating ?? 0), 1);

        // Distribution (even if 0 CPT, we still need the structure for lieu enrichment)
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $dist_rows = $wpdb->get_results(
            "SELECT CAST(pm_r.meta_value AS UNSIGNED) AS rating, COUNT(*) AS cnt
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
             {$joins}
             WHERE 1=1 {$wheres}
             AND pm_r.meta_value BETWEEN '1' AND '5'
             GROUP BY rating"
        );
        foreach ($dist_rows as $dr) {
            $distribution[(int) $dr->rating] = (int) $dr->cnt;
        }

        // Sub-criteria averages
        $criteria_avgs = [];
        foreach (['qualite_prix', 'ambiance', 'experience', 'paysage'] as $crit) {
            $crit_row = $wpdb->get_row(
                "SELECT AVG(CAST(pm_c.meta_value AS DECIMAL(3,1))) AS cavg, COUNT(*) AS cnt
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_c ON pm_c.post_id = p.ID AND pm_c.meta_key = 'avis_{$crit}'
                 {$joins}
                 WHERE 1=1 {$wheres}
                 AND pm_c.meta_value BETWEEN '1' AND '5'"
            );
            $criteria_avgs[$crit] = ($crit_row && $crit_row->cnt > 0) ? round((float) $crit_row->cavg, 1) : null;
        }

        // Enriched total & avg — uses shared helper matching dashboard formula exactly
        // (per-source max of CPT vs platform, then sum)
        $source_filter = !empty($a['source_filter'])
            ? array_filter(array_map('trim', explode(',', $a['source_filter'])))
            : [];
        $enriched_lieu = !empty($a['lieu_ids'])
            ? array_filter(array_map('trim', explode(',', $a['lieu_ids'])))
            : $lieu_id;
        $enriched = sj_enriched_stats($enriched_lieu, $source_filter);
        $total    = $enriched['count'];
        $avg      = $enriched['avg'];

        if ($total === 0) return [];

        return compact('total', 'avg', 'distribution', 'criteria_avgs');
    }

    // ── Rendu HTML global ─────────────────────────────────────────────────────

    private function render_html(array $reviews, array $stats, array $a, string $lieu_id = 'all'): string {
        $uid = 'sj-' . wp_unique_id();
        ob_start();
        ?>
<?php
// Container width inline style
$container_style = '';
if (!empty($a['max_width'])) {
    $container_style .= '--sj-max-width:' . esc_attr($a['max_width']) . ';';
}
if (!empty($a['max_width_tablet'])) {
    $container_style .= '--sj-max-width-tablet:' . esc_attr($a['max_width_tablet']) . ';';
}
if (!empty($a['max_width_mobile'])) {
    $container_style .= '--sj-max-width-mobile:' . esc_attr($a['max_width_mobile']) . ';';
}
?>
<div class="sj-summary<?php echo $container_style ? ' sj-summary--has-max-width' : ''; ?>" id="<?php echo esc_attr($uid); ?>"
     <?php if ($container_style): ?>style="<?php echo $container_style; ?>"<?php endif; ?>
     data-initial="<?php echo esc_attr((int) $a['reviews_initial']); ?>"
     data-words="<?php echo esc_attr((int)$a['text_words']); ?>"
     data-total-reviews="<?php echo esc_attr($stats['total']); ?>"
     data-lieu-id="<?php echo esc_attr($lieu_id); ?>"
     data-lieu-ids="<?php echo esc_attr($a['lieu_ids']); ?>"
     data-source-filter="<?php echo esc_attr($a['source_filter']); ?>"
     data-show-certified="<?php echo esc_attr($a['show_certified']); ?>">

    <!-- ══ SECTION 1 : SCORE + DISTRIBUTION (bloc parent) ══════════════════ -->
    <?php $layout_cls = in_array($a['score_layout'], ['left','right'], true) ? ' sj-summary__top--side-' . $a['score_layout'] : ''; ?>
    <div class="sj-summary__top<?php echo esc_attr($layout_cls); ?>">

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

        <?php
        $show_dist = $a['show_distribution'] !== '0';
        if ($show_dist):
        ?>
        <!-- Distribution par étoiles -->
        <div class="sj-summary__distribution">
            <?php
            $dist_labels = \SJ_Reviews\Includes\Labels::ratings_int();
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

    </div><!-- /.sj-summary__top -->

    <!-- ══ SECTION 2 : SOUS-CRITÈRES (indépendant, masquable) ════════════ -->
    <?php
    $crit_labels  = \SJ_Reviews\Includes\Labels::criteria();
    $has_criteria = $a['show_criteria'] !== '0' && array_filter($stats['criteria_avgs'], fn($v) => $v !== null);
    if ($has_criteria):
    ?>
    <div class="sj-summary__criteria-section">
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
    </div>
    <?php endif; ?>

    <?php if ($a['show_ai_summary'] !== '0'): ?>
    <!-- ══ AI SUMMARY ═════════════════════════════════════════════════════════ -->
    <div class="sj-summary__ai" id="<?php echo esc_attr($uid); ?>-ai" data-lieu-id="<?php echo esc_attr($lieu_id); ?>">
        <div class="sj-summary__ai-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2a4 4 0 014 4v1a2 2 0 012 2v1a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2V6a4 4 0 014-4z"/><path d="M8 14v4a4 4 0 008 0v-4"/></svg>
            <span class="sj-summary__ai-label">Résumé IA</span>
            <span class="sj-summary__ai-badge">Généré par IA</span>
        </div>
        <p class="sj-summary__ai-text" id="<?php echo esc_attr($uid); ?>-ai-text">
            <span class="sj-summary__ai-loading">Chargement du résumé…</span>
        </p>
    </div>
    <?php endif; ?>

    <?php
    $show_filters  = $a['show_filters']  !== '0';
    $show_reviews  = $a['show_reviews']  !== '0';

    // Collecte les valeurs disponibles pour les pills de filtres (via SQL — all reviews, not just initial)
    global $wpdb;
    $avail_ratings = [5, 4, 3, 2, 1]; // Always show all ratings

    // Available languages
    $avail_langs = $wpdb->get_col(
        "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'avis_language' AND pm.meta_value != ''
         AND p.post_type = 'sj_avis' AND p.post_status = 'publish'"
    );

    // Available periods from visit_date
    $avail_periods = [];
    $visit_months = $wpdb->get_col(
        "SELECT DISTINCT MONTH(pm.meta_value) FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'avis_visit_date' AND pm.meta_value != ''
         AND p.post_type = 'sj_avis' AND p.post_status = 'publish'"
    );
    foreach ($visit_months as $vm) {
        $m = (int) $vm;
        foreach (self::PERIODS as $slug => $pd) {
            if (in_array($m, $pd['months'], true)) { $avail_periods[$slug] = $pd['label']; break; }
        }
    }
    ?>

    <?php if ($show_filters && $show_reviews): ?>
<div class="sj-summary__filterbar" data-summary="<?php echo esc_attr($uid); ?>">
    <!-- Tri (reste dans la barre) -->
    <?php if ($a['show_sort'] !== '0'): ?>
    <div class="sj-filters__sort">
        <label class="sj-filters__sort-label" for="<?php echo esc_attr($uid); ?>-sort">Trier par</label>
        <select id="<?php echo esc_attr($uid); ?>-sort" class="sj-filters__sort-select" data-filter="sort">
            <option value="recent">Plus récent</option>
            <option value="rating_desc">Meilleure note</option>
            <option value="rating_asc">Moins bonne note</option>
        </select>
    </div>
    <?php endif; ?>

    <!-- Bouton filtrer -->
    <button type="button" class="sj-filter-trigger" data-summary="<?php echo esc_attr($uid); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr($uid); ?>-modal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
        Filtrer
        <span class="sj-filter-trigger__badge" hidden></span>
    </button>

    <!-- Badge filtres actifs -->
    <div class="sj-filters__active" aria-live="polite" hidden>
        <button type="button" class="sj-filters__reset">Réinitialiser <span class="sj-filters__active-count"></span></button>
    </div>

    <?php if ($a['show_search'] !== '0'): ?>
    <!-- Recherche inline dans la barre -->
    <div class="sj-search__wrap sj-search__wrap--inline">
        <svg class="sj-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search"
               class="sj-search__input"
               placeholder="<?php esc_attr_e('Rechercher un avis…', 'sj-reviews'); ?>"
               aria-label="<?php esc_attr_e('Rechercher dans les avis', 'sj-reviews'); ?>"
               data-summary="<?php echo esc_attr($uid); ?>">
    </div>
    <?php endif; ?>
</div>

<!-- Modal filtres -->
<div class="sj-filter-modal" id="<?php echo esc_attr($uid); ?>-modal" role="dialog" aria-modal="true" aria-label="Filtrer les avis" hidden>
    <div class="sj-filter-modal__overlay" data-close="modal"></div>
    <div class="sj-filter-modal__panel">
        <div class="sj-filter-modal__head">
            <h2 class="sj-filter-modal__title">Filtrer les avis</h2>
            <button type="button" class="sj-filter-modal__close" data-close="modal" aria-label="Fermer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="sj-filter-modal__body">

            <!-- Note attribuée -->
            <?php if ($a['show_rating_filter'] !== '0'): ?>
            <div class="sj-filter-modal__group">
                <p class="sj-filter-modal__group-label">Note attribuée</p>
                <div class="sj-filter-modal__dots-row">
                    <?php foreach ([5,4,3,2,1] as $r):
                        $cnt = $stats['distribution'][$r] ?? 0;
                    ?>
                    <button type="button" class="sj-filter-modal__dot-btn" data-filter="rating" data-value="<?php echo esc_attr($r); ?>" aria-pressed="false">
                        <?php for ($b=1;$b<=5;$b++): ?><span class="sj-filter-modal__dot<?php echo $b<=$r?' sj-filter-modal__dot--full':''; ?>"></span><?php endfor; ?>
                        <span class="sj-filter-modal__dot-count"><?php echo esc_html($cnt); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Type de voyageur -->
            <?php
            $avail_travel_types = $wpdb->get_col(
                "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = 'avis_travel_type' AND pm.meta_value != ''
                 AND p.post_type = 'sj_avis' AND p.post_status = 'publish'"
            );
            if (!empty($avail_travel_types)):
            ?>
            <div class="sj-filter-modal__group">
                <p class="sj-filter-modal__group-label">Type de voyageur</p>
                <div class="sj-filter-modal__pills">
                    <?php foreach (\SJ_Reviews\Includes\Labels::TRAVEL_TYPES as $slug => $label):
                        if (!in_array($slug, $avail_travel_types, true)) continue;
                    ?>
                    <button type="button" class="sj-filter-modal__pill" data-filter="travel" data-value="<?php echo esc_attr($slug); ?>" aria-pressed="false">
                        <?php echo esc_html($label); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Période de l'année -->
            <?php if ($a['show_period_filter'] !== '0' && !empty($avail_periods)): ?>
            <div class="sj-filter-modal__group">
                <p class="sj-filter-modal__group-label">Période de l'année</p>
                <div class="sj-filter-modal__pills">
                    <?php foreach (self::PERIODS as $slug => $pd):
                        if (!isset($avail_periods[$slug])) continue;
                    ?>
                    <button type="button" class="sj-filter-modal__pill" data-filter="period" data-value="<?php echo esc_attr($slug); ?>" aria-pressed="false">
                        <?php echo esc_html($pd['label']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Langue -->
            <?php if ($a['show_language_filter'] !== '0' && count($avail_langs) >= 1): ?>
            <div class="sj-filter-modal__group">
                <p class="sj-filter-modal__group-label">Langue</p>
                <div class="sj-filter-modal__pills">
                    <?php foreach ($avail_langs as $lang): ?>
                    <button type="button" class="sj-filter-modal__pill" data-filter="language" data-value="<?php echo esc_attr($lang); ?>" aria-pressed="false">
                        <?php echo esc_html(\SJ_Reviews\Includes\Labels::LANGUAGES[$lang] ?? strtoupper($lang)); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.body -->

        <div class="sj-filter-modal__foot">
            <button type="button" class="sj-filter-modal__btn-reset">Réinitialiser</button>
            <button type="button" class="sj-filter-modal__btn-apply">Appliquer</button>
        </div>
    </div>
</div>
<?php endif; ?>


    <?php if ($show_reviews && $a['show_verified_banner'] !== '0' && !empty($a['verified_banner_text'])): ?>
    <div class="sj-summary__verified-banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/>
        </svg>
        <span><?php echo esc_html($a['verified_banner_text']); ?></span>
    </div>
    <?php endif; ?>

    <?php if ($show_reviews): ?>
    <!-- ══ SECTION 4 : CARDS D'AVIS ══════════════════════════════════════════ -->
    <div class="sj-summary__reviews sj-cards-grid sj-cards-grid--<?php echo esc_attr($a['cards_columns']); ?>col"
         id="<?php echo esc_attr($uid); ?>-reviews"
         data-summary="<?php echo esc_attr($uid); ?>"
         aria-live="polite">
        <?php
        // Contributions par reviewer (via hash anonyme, email non exposé en front)
        $hash_counts = [];
        foreach ($reviews as $rv2) {
            $h = $rv2['customer_hash'] ?? '';
            if ($h) $hash_counts[$h] = ($hash_counts[$h] ?? 0) + 1;
        }
        ?>
        <?php
        foreach ($reviews as $idx => $rv):
            $period = $this->get_period($rv['visit_date'] ?? '');
            $hash   = $rv['customer_hash'] ?? '';
            $contribs = ($hash && isset($hash_counts[$hash])) ? $hash_counts[$hash] : 1;
        ?>
        <article class="sj-card"
                 data-rating="<?php echo esc_attr($rv['rating']); ?>"
                 data-language="<?php echo esc_attr($rv['language'] ?: 'fr'); ?>"
                 data-period="<?php echo esc_attr($period); ?>"
                 data-date="<?php echo esc_attr($rv['date']); ?>"
                 data-travel="<?php echo esc_attr($rv['travel_type']); ?>"
                 data-contributions="<?php echo esc_attr($contribs); ?>"
                 aria-label="Avis de <?php echo esc_attr($rv['author']); ?>">

            <!-- En-tête : avatar + auteur + source -->
            <div class="sj-card__header">
                <div class="sj-card__author-block">
                    <?php if (!empty($rv['avatar'])): ?>
                        <?php
                        // srcset for avatar images
                        $avatar_srcset = '';
                        if (is_numeric($rv['avatar_id'] ?? 0) && $rv['avatar_id'] > 0) {
                            $srcset = wp_get_attachment_image_srcset((int) $rv['avatar_id'], 'thumbnail');
                            if ($srcset) $avatar_srcset = ' srcset="' . esc_attr($srcset) . '"';
                        }
                        ?>
                        <img class="sj-card__avatar sj-card__avatar--img"
                             src="<?php echo esc_url($rv['avatar']); ?>"
                             alt="<?php echo esc_attr($rv['author']); ?>"
                             width="36" height="36" loading="lazy"<?php echo $avatar_srcset; ?>>
                    <?php else: ?>
                        <?php
                        // Colored backgrounds based on author name
                        $avatar_colors = [
                            ['bg' => '#e0e7ff', 'color' => '#4f46e5'],
                            ['bg' => '#fce7f3', 'color' => '#be185d'],
                            ['bg' => '#d1fae5', 'color' => '#059669'],
                            ['bg' => '#fef3c7', 'color' => '#d97706'],
                            ['bg' => '#ede9fe', 'color' => '#7c3aed'],
                            ['bg' => '#fee2e2', 'color' => '#dc2626'],
                            ['bg' => '#e0f2fe', 'color' => '#0284c7'],
                            ['bg' => '#f0fdf4', 'color' => '#16a34a'],
                        ];
                        $color_idx = ord(mb_strtolower(mb_substr($rv['author'] ?: 'A', 0, 1))) % count($avatar_colors);
                        $ac = $avatar_colors[$color_idx];
                        ?>
                        <div class="sj-card__avatar sj-card__avatar--initiale" aria-hidden="true"
                             style="background:<?php echo esc_attr($ac['bg']); ?>;color:<?php echo esc_attr($ac['color']); ?>">
                            <?php echo esc_html(mb_strtoupper(mb_substr($rv['author'], 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                    <div class="sj-card__author-info">
                        <span class="sj-card__author-name"><?php echo esc_html($rv['author']); ?></span>
                        <?php if (!empty($rv['source']) && $rv['source'] !== 'direct'):
                            echo '<span class="sj-card__source-name">' . esc_html(ucfirst($rv['source'])) . '</span>';
                        endif; ?>
                        <?php if ($contribs > 1): ?>
                        <span class="sj-card__contributions">(<?php echo esc_html($contribs); ?> contributions)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($a['show_certified'] !== '0' && !empty($rv['certified'])): ?>
                <span class="sj-card__certified">Certifié</span>
                <?php endif; ?>
            </div>

            <!-- Note + meta visite -->
            <div class="sj-card__rating">
                <?php echo $this->bubbles_html((float) $rv['rating'], 'sm'); ?>
                <?php
                $meta_parts = [];
                if (!empty($rv['visit_date'])) {
                    $meta_parts[] = esc_html(date_i18n('M Y', strtotime($rv['visit_date'])));
                }
                if (!empty($rv['travel_type']) && isset(\SJ_Reviews\Includes\Labels::TRAVEL_TYPES[$rv['travel_type']])) {
                    $meta_parts[] = esc_html(\SJ_Reviews\Includes\Labels::TRAVEL_TYPES[$rv['travel_type']]);
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
                <?php
                $word_count = str_word_count(strip_tags($rv['text']));
                if ($word_count > (int)$a['text_words']):
                ?>
                <button type="button" class="sj-card__more" aria-expanded="false">
                    Voir plus
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                        <path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($a['show_card_criteria'] !== '0'): ?>
            <?php
              $crit_labels_card = $crit_labels; // uses settings-based labels from above
              $has_crit = array_filter(['qualite_prix'=>$rv['qualite_prix'],'ambiance'=>$rv['ambiance'],'experience'=>$rv['experience'],'paysage'=>$rv['paysage']], fn($v)=>$v!==null);
              if (!empty($has_crit)):
            ?>
            <div class="sj-card__criteria">
              <?php foreach ($crit_labels_card as $k => $lbl):
                $v = $rv[$k]; if ($v === null) continue;
                $pct = round(($v/5)*100);
              ?>
              <div class="sj-card__crit-row">
                <span class="sj-card__crit-dot" data-val="<?php echo esc_attr($v); ?>"></span>
                <span class="sj-card__crit-label"><?php echo esc_html($lbl); ?></span>
                <div class="sj-card__crit-track">
                  <div class="sj-card__crit-fill" style="width:<?php echo esc_attr($pct); ?>%"></div>
                </div>
                <span class="sj-card__crit-score"><?php echo esc_html(number_format($v,1,',','')); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
    <?php $remaining = max(0, $stats['total'] - count($reviews)); ?>
    <?php if ($remaining > 0): ?>
    <div class="sj-summary__loadmore">
        <button type="button" class="sj-summary__load-btn"
                data-summary="<?php echo esc_attr($uid); ?>"
                aria-controls="<?php echo esc_attr($uid); ?>-reviews">
            Voir plus d'avis
            <span class="sj-summary__load-count">(<?php echo esc_html($remaining); ?>)</span>
        </button>
    </div>
    <?php endif; ?>

    <?php endif; // show_reviews ?>

</div><!-- /.sj-summary -->
<?php if ($a['schema_enabled'] !== '0' && !is_admin() && $stats['avg'] > 0):
    $schema_type = $a['schema_type'] ?: 'LocalBusiness';
    $schema_name = $a['schema_name'] ?: (get_the_title() ?: get_bloginfo('name'));
    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => $schema_type,
        'name'            => $schema_name,
        'url'             => get_permalink() ?: home_url('/'),
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => $stats['avg'],
            'reviewCount' => $stats['total'],
            'bestRating'  => 5,
            'worstRating' => 1,
        ],
    ];
    // Enrich with lieu data if available
    $schema_lieux = \SJ_Reviews\Includes\Settings::lieux();
    $schema_lieu  = null;
    if ($lieu_id && $lieu_id !== 'all') {
        foreach ($schema_lieux as $_sl) {
            if ($_sl['id'] === $lieu_id) { $schema_lieu = $_sl; break; }
        }
    } elseif (count($schema_lieux) === 1) {
        $schema_lieu = $schema_lieux[0];
    }
    if ($schema_lieu && !empty($schema_lieu['address'])) {
        $schema['address'] = [
            '@type'         => 'PostalAddress',
            'streetAddress' => $schema_lieu['address'],
        ];
    }
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            $schema['image'] = $logo_url;
        }
    }

    // Inject a few individual reviews for Google rich results (max 5)
    $schema_reviews = [];
    $candidates = array_filter($reviews, fn($r) => $r['rating'] >= 1 && $r['rating'] <= 5 && !empty($r['text']));
    usort($candidates, function($a, $b) {
        if ($b['rating'] !== $a['rating']) return $b['rating'] - $a['rating'];
        return strcmp($b['date'], $a['date']);
    });
    foreach (array_slice($candidates, 0, 5) as $r) {
        $entry = [
            '@type'        => 'Review',
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => $r['rating'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ],
            'author' => [
                '@type' => 'Person',
                'name'  => $r['author'] ?: 'Anonyme',
            ],
        ];
        if (!empty($r['text'])) {
            $entry['reviewBody'] = wp_strip_all_tags($r['text']);
        }
        if (!empty($r['date'])) {
            $entry['datePublished'] = gmdate('Y-m-d', strtotime($r['date']));
        }
        $schema_reviews[] = $entry;
    }
    if (!empty($schema_reviews)) {
        $schema['review'] = $schema_reviews;
    }

    sj_output_schema($schema);
endif; ?>
        <?php
        return ob_get_clean();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function rating_label(float $avg): string {
        return \SJ_Reviews\Includes\Labels::rating_label($avg);
    }

    private function bubbles_html(float $rating, string $size = ''): string {
        if ($rating <= 0) return '';
        $label    = number_format($rating, 1, ',', '') . ' sur 5 bulles';
        $size_cls = $size ? ' sj-summary__bubbles--' . $size : '';
        $html     = '<div class="sj-summary__bubbles' . esc_attr($size_cls) . '" aria-label="' . esc_attr($label) . '">';
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
