<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_summary] — résumé statistique style TripAdvisor.
 *
 * Paramètres :
 *   lieu_id        = 'auto' | 'all' | 'lieu_xxxx'
 *   show_distribution = '1' | '0'
 *   show_criteria     = '1' | '0'
 *
 * En mode 'auto' (défaut), le shortcode cherche à identifier le lieu de la page :
 *   1. meta `sj_lieu_id` sur le post courant
 *   2. un avis publié lié à ce post (avis_linked_post) qui a un avis_lieu_id
 *   Sinon → tous les avis.
 */
class SummaryShortcode {

    public function init(): void {
        add_shortcode('sj_summary', [$this, 'render']);
    }

    /** Point d'entrée shortcode ET widget Elementor */
    public function render(array $atts = []): string {
        $defaults = [
            'lieu_id'           => 'auto',
            'show_distribution' => '1',
            'show_criteria'     => '1',
        ];
        $a = shortcode_atts($defaults, $atts, 'sj_summary');

        $lieu_id = $this->resolve_lieu($a['lieu_id']);
        $stats   = $this->compute_stats($lieu_id);

        if (empty($stats) || $stats['total'] === 0) {
            return '<div class="sj-summary sj-summary--empty"><p>' . esc_html__('Aucun avis disponible.', 'sj-reviews') . '</p></div>';
        }

        return $this->render_html($stats, $a);
    }

    // ── Résolution du lieu ────────────────────────────────────────────────────

    private function resolve_lieu(string $req): string {
        if ($req !== 'auto') return sanitize_key($req); // 'all' ou lieu_id spécifique

        $post_id = get_the_ID();
        if (!$post_id) return 'all';

        // 1. Meta directe sur le post
        $direct = get_post_meta($post_id, 'sj_lieu_id', true);
        if ($direct) return sanitize_key($direct);

        // 2. Un avis publié lié à ce post porte un lieu_id
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

    // ── Calcul des statistiques ───────────────────────────────────────────────

    private function compute_stats(string $lieu_id): array {
        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ];
        if ($lieu_id && $lieu_id !== 'all') {
            $args['meta_query'] = [
                ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='],
            ];
        }

        $posts = get_posts($args);
        if (empty($posts)) return [];

        $total        = count($posts);
        $rating_sum   = 0;
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $crit_sums    = ['qualite_prix' => 0, 'ambiance' => 0, 'experience' => 0, 'paysage' => 0];
        $crit_counts  = array_fill_keys(array_keys($crit_sums), 0);

        foreach ($posts as $post) {
            $r            = max(1, min(5, (int) get_post_meta($post->ID, 'avis_rating', true) ?: 5));
            $rating_sum  += $r;
            $distribution[$r]++;

            foreach (array_keys($crit_sums) as $c) {
                $v = (int) get_post_meta($post->ID, 'avis_' . $c, true);
                if ($v >= 1 && $v <= 5) {
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

        return compact('total', 'avg', 'distribution', 'criteria_avgs', 'lieu_id');
    }

    // ── Rendu HTML ────────────────────────────────────────────────────────────

    private function render_html(array $stats, array $a): string {
        $avg          = $stats['avg'];
        $total        = $stats['total'];
        $distribution = $stats['distribution'];
        $criteria     = $stats['criteria_avgs'];
        $label        = $this->rating_label($avg);
        $max_dist     = max(1, max($distribution));

        ob_start(); ?>
<div class="sj-summary">

    <!-- Score global -->
    <div class="sj-summary__header">
        <div class="sj-summary__score-block">
            <div class="sj-summary__score-num"><?php echo esc_html(number_format($avg, 1, ',', '')); ?></div>
            <div class="sj-summary__score-info">
                <div class="sj-summary__score-label"><?php echo esc_html($label); ?></div>
                <?php echo $this->bubbles_html($avg); ?>
                <div class="sj-summary__count">
                    <?php printf(esc_html__('%d avis', 'sj-reviews'), $total); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($a['show_distribution'] !== '0'): ?>
    <!-- Distribution par étoiles -->
    <div class="sj-summary__distribution">
        <?php
        $dist_labels = [
            5 => __('Excellent',  'sj-reviews'),
            4 => __('Bien',       'sj-reviews'),
            3 => __('Moyen',      'sj-reviews'),
            2 => __('Médiocre',   'sj-reviews'),
            1 => __('Horrible',   'sj-reviews'),
        ];
        foreach ($dist_labels as $stars => $dist_label):
            $count = $distribution[$stars] ?? 0;
            $pct   = round(($count / $max_dist) * 100);
        ?>
        <div class="sj-summary__dist-row">
            <span class="sj-summary__dist-label"><?php echo esc_html($dist_label); ?></span>
            <div class="sj-summary__dist-track" role="progressbar" aria-valuenow="<?php echo esc_attr($pct); ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="sj-summary__dist-fill" style="width:<?php echo esc_attr($pct); ?>%"></div>
            </div>
            <span class="sj-summary__dist-count"><?php echo esc_html($count); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($a['show_criteria'] !== '0'):
        $crit_labels = [
            'qualite_prix' => __('Qualité/prix', 'sj-reviews'),
            'ambiance'     => __('Ambiance',     'sj-reviews'),
            'experience'   => __('Expérience',   'sj-reviews'),
            'paysage'      => __('Paysage',      'sj-reviews'),
        ];
        $has_criteria = array_filter($criteria, fn($v) => $v !== null);
        if (!empty($has_criteria)):
    ?>
    <!-- Sous-critères -->
    <div class="sj-summary__criteria">
        <?php foreach ($crit_labels as $crit_key => $crit_lbl):
            $crit_avg = $criteria[$crit_key];
            if ($crit_avg === null) continue;
            $crit_pct = round(($crit_avg / 5) * 100);
        ?>
        <div class="sj-summary__criterion">
            <span class="sj-summary__crit-label"><?php echo esc_html($crit_lbl); ?></span>
            <div class="sj-summary__crit-track">
                <div class="sj-summary__crit-fill" style="width:<?php echo esc_attr($crit_pct); ?>%"></div>
            </div>
            <span class="sj-summary__crit-score"><?php echo esc_html(number_format($crit_avg, 1, ',', '')); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; endif; ?>

</div>
        <?php
        return ob_get_clean();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function rating_label(float $avg): string {
        if ($avg >= 4.5) return __('Excellent',    'sj-reviews');
        if ($avg >= 4.0) return __('Très bien',    'sj-reviews');
        if ($avg >= 3.5) return __('Bien',         'sj-reviews');
        if ($avg >= 3.0) return __('Moyen',        'sj-reviews');
        if ($avg >= 2.0) return __('Médiocre',     'sj-reviews');
        return                   __('Mauvais',     'sj-reviews');
    }

    private function bubbles_html(float $avg): string {
        $label = sprintf(__('%s sur 5 bulles', 'sj-reviews'), number_format($avg, 1, ',', ''));
        $html  = '<div class="sj-summary__bubbles" aria-label="' . esc_attr($label) . '">';
        for ($i = 1; $i <= 5; $i++) {
            $fill = min(1.0, max(0.0, $avg - ($i - 1)));
            if ($fill >= 0.75)     $cls = 'full';
            elseif ($fill >= 0.25) $cls = 'half';
            else                   $cls = 'empty';
            $html .= '<span class="sj-summary__bubble sj-summary__bubble--' . $cls . '" aria-hidden="true"></span>';
        }
        $html .= '</div>';
        return $html;
    }
}
