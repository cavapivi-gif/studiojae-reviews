<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_rating] — badge agrégat Google/source
 *
 * Paramètres :
 *   lieu_id      = string|all   ID lieu ou "all" (default: all actifs)
 *   design       = compact | pill | card | hero | grid  (default: card)
 *   show_source  = 1|0          Afficher le nom/logo de la source (default: 1)
 *   show_link    = 1|0          Lien vers Google Maps si place_id (default: 1)
 *   star_color   = hex          Couleur étoiles (default: settings)
 *   label        = string       Texte après le nombre d'avis (default: "avis")
 *
 * Exemples :
 *   [sj_rating]
 *   [sj_rating lieu_id="lieu_abc123" design="hero"]
 *   [sj_rating lieu_id="all" design="grid"]
 *   [sj_rating lieu_id="lieu_abc123" design="compact" show_source="0"]
 */
class RatingShortcode {

    public function init(): void {
        add_shortcode('sj_rating', [$this, 'render']);
    }

    public function render(array $atts): string {
        \SJ_Reviews\Core\Plugin::enqueue_asset('sj-rating-badge');
        \SJ_Reviews\Core\Plugin::enqueue_asset('sj-badge', true);

        $opts = \SJ_Reviews\Includes\Settings::all();

        $a = shortcode_atts([
            'lieu_id'       => 'all',
            'design'        => 'card',
            'show_source'   => 1,
            'show_link'     => 1,
            'star_color'    => $opts['star_color'] ?? '#f5a623',
            'label'         => 'avis',
            'source_filter' => '',
        ], $atts, 'sj_rating');

        $design        = sanitize_key($a['design']);
        $star_color    = sanitize_hex_color($a['star_color']) ?: '#f5a623';
        $show_source   = (bool)(int) $a['show_source'];
        $show_link     = (bool)(int) $a['show_link'];
        $label         = esc_html($a['label']);
        $lieu_id_req   = sanitize_text_field($a['lieu_id']);
        $source_filter = !empty($a['source_filter'])
            ? array_filter(array_map('trim', explode(',', $a['source_filter'])))
            : [];

        $all_lieux = \SJ_Reviews\Includes\Settings::lieux();

        // Filtre : un lieu précis ou tous les actifs avec données
        if ($lieu_id_req === 'all') {
            $lieux = array_filter($all_lieux, fn($l) =>
                ($l['active'] ?? true) && isset($l['rating'], $l['reviews_count']) && $l['reviews_count'] > 0
            );
        } else {
            $lieux = array_filter($all_lieux, fn($l) => $l['id'] === $lieu_id_req);
        }

        // Filtre par source(s) si spécifié — chaque lieu a une source unique
        if (!empty($source_filter)) {
            $lieux = array_filter($lieux, fn($l) => in_array($l['source'] ?? '', $source_filter, true));
        }

        if (empty($lieux)) return '';

        $lieux = array_values($lieux);

        // En mode "grid" ou plusieurs lieux → wrapper grille
        $multi = count($lieux) > 1;

        $badge_data = esc_attr(wp_json_encode([
            'lieu_id'       => $lieu_id_req,
            'source_filter' => $source_filter,
        ]));

        ob_start();
        echo '<div class="sj-rating' . ($multi ? ' sj-rating--grid' : '') . '" data-sj-badge="' . $badge_data . '">';
        foreach ($lieux as $lieu) {
            $this->render_badge($lieu, $design, $star_color, $show_source, $show_link, $label, $source_filter);
        }
        echo '</div>';

        return ob_get_clean();
    }

    private function render_badge(array $l, string $design, string $color, bool $src, bool $link, string $label, array $source_filter = []): void {
        // Use sj_enriched_stats() for consistent count — same formula as dashboard + JS hydration
        $enriched = sj_enriched_stats($l['id'] ?? '', $source_filter);
        $rating   = $enriched['avg'] ?: (float) ($l['rating'] ?? 0);
        $count    = $enriched['count'] ?: (int) ($l['reviews_count'] ?? 0);
        $name     = esc_html($l['name'] ?? '');
        $source   = $l['source'] ?? 'google';
        $pid      = $l['place_id'] ?? '';
        $gmb_url  = $pid ? 'https://www.google.com/maps/place/?q=place_id:' . urlencode($pid) : '';

        $stars_html = $this->stars_html($rating, $color);
        $rating_fmt = sj_format_rating($rating);
        $count_fmt  = sj_format_count($count);
        $src_label  = $src ? $this->source_name($source) : '';
        $src_icon   = $src ? $this->source_icon_html($source) : '';

        $tag_open  = ($link && $gmb_url) ? '<a href="' . esc_url($gmb_url) . '" target="_blank" rel="noopener noreferrer"' : '<div';
        $tag_close = ($link && $gmb_url) ? '</a>' : '</div>';

        switch ($design) {
            case 'compact':
                echo '<div class="sj-badge sj-badge--compact">';
                echo $src_icon;
                echo '<span class="sj-badge__rating" data-sj-tpl="{{avg}}">' . esc_html($rating_fmt) . '</span>';
                echo $stars_html;
                echo '<span class="sj-badge__count" data-sj-tpl="({{count}} ' . esc_attr($label) . ')">(' . esc_html($count_fmt) . ' ' . $label . ')</span>';
                echo '</div>';
                break;

            case 'pill':
                echo $tag_open . ' class="sj-badge sj-badge--pill">';
                echo $stars_html;
                echo '<span class="sj-badge__rating" data-sj-tpl="{{avg}}">' . esc_html($rating_fmt) . '</span>';
                echo '<span class="sj-badge__sep">·</span>';
                echo '<span class="sj-badge__count" data-sj-tpl="{{count}} ' . esc_attr($label) . '">' . esc_html($count_fmt) . ' ' . $label . '</span>';
                if ($src_label) echo '<span class="sj-badge__source">' . esc_html($src_label) . '</span>';
                echo $tag_close;
                break;

            case 'hero':
                echo '<div class="sj-badge sj-badge--hero">';
                if ($name) echo '<div class="sj-badge__name">' . $name . '</div>';
                echo '<div class="sj-badge__big-rating" data-sj-tpl="{{avg}}">' . esc_html($rating_fmt) . '</div>';
                echo '<div class="sj-badge__stars">' . $stars_html . '</div>';
                echo '<div class="sj-badge__meta">';
                echo '<span class="sj-badge__count" data-sj-tpl="Basé sur {{count}} ' . esc_attr($label) . '">Basé sur ' . esc_html($count_fmt) . ' ' . $label . '</span>';
                if ($src_label && $link && $gmb_url) {
                    echo ' <a href="' . esc_url($gmb_url) . '" class="sj-badge__source-link" target="_blank" rel="noopener noreferrer">' . $src_icon . esc_html($src_label) . '</a>';
                } elseif ($src_label) {
                    echo ' ' . $src_icon . '<span class="sj-badge__source">' . esc_html($src_label) . '</span>';
                }
                echo '</div>';
                echo '</div>';
                break;

            default: // card
                echo $tag_open . ' class="sj-badge sj-badge--card">';
                echo '<div class="sj-badge__header">';
                echo $src_icon;
                if ($name) echo '<span class="sj-badge__name">' . $name . '</span>';
                echo '</div>';
                echo '<div class="sj-badge__body">';
                echo '<span class="sj-badge__rating" data-sj-tpl="{{avg}}">' . esc_html($rating_fmt) . '</span>';
                echo '<div class="sj-badge__stars-wrap">';
                echo $stars_html;
                echo '<span class="sj-badge__count" data-sj-tpl="{{count}} ' . esc_attr($label) . '">' . esc_html($count_fmt) . ' ' . $label . '</span>';
                echo '</div>';
                echo '</div>';
                echo $tag_close;
                break;
        }
    }

    /** Génère des étoiles SVG partielles (délègue au helper global) */
    private function stars_html(float $rating, string $color): string {
        return sj_stars_svg($rating, $color, '#d1d5db', 14, '', '0 0 24 24', 'sj-badge__stars');
    }

    private function source_name(string $source): string {
        return \SJ_Reviews\Includes\Labels::source_name($source);
    }

    private function source_icon_html(string $source): string {
        if ($source === 'regiondo') {
            $svg_path = SJ_REVIEWS_DIR . 'front/assets/logos/regiondo.svg';
            if (file_exists($svg_path)) {
                $svg = file_get_contents($svg_path); // phpcs:ignore WordPress.WP.AlternativeFunctions
                if ($svg) {
                    return '<span class="sj-badge__source-icon sj-badge__source-icon--regiondo" aria-hidden="true" style="width:auto;height:20px;background:none;padding:0">' . $svg . '</span>';
                }
            }
            return '<span class="sj-badge__source-icon sj-badge__source-icon--regiondo" aria-hidden="true">R</span>';
        }
        // Utilise les couleurs officielles des marques
        $icons = [
            'google'      => '<span class="sj-badge__source-icon sj-badge__source-icon--google" aria-hidden="true">G</span>',
            'tripadvisor' => '<span class="sj-badge__source-icon sj-badge__source-icon--tripadvisor" aria-hidden="true">T</span>',
            'facebook'    => '<span class="sj-badge__source-icon sj-badge__source-icon--facebook" aria-hidden="true">f</span>',
            'trustpilot'  => '<span class="sj-badge__source-icon sj-badge__source-icon--trustpilot" aria-hidden="true">✓</span>',
        ];
        return $icons[$source] ?? '';
    }
}
