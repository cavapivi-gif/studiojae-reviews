<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_reviews]
 *
 * Attributs :
 *   layout    = slider-i | slider-ii | badge | grid | list   (default: plugin settings)
 *   preset    = minimal | dark | white                        (default: plugin settings)
 *   max       = int                                            (default: 5)
 *   columns   = int (grille)                                  (default: 3)
 *   place_id  = string                                        (default: plugin settings)
 *   rating_min= int                                           (default: 0 = tous)
 *   show_stars    = 1|0   (default: 1)
 *   show_text     = 1|0   (default: 1)
 *   show_author   = 1|0   (default: 1)
 *   show_date     = 1|0   (default: 1)
 *   show_certified= 1|0   (default: 1)
 *   autoplay  = 1|0       (default: 0)
 *   loop      = 1|0       (default: 1)
 *   title     = string    (default: '')
 *   schema    = 1|0       (default: 1)
 *
 * Exemples :
 *   [sj_reviews]
 *   [sj_reviews layout="badge" preset="dark"]
 *   [sj_reviews layout="slider-ii" preset="white" max="5" autoplay="1" title="Nos avis"]
 *   [sj_reviews layout="grid" columns="2" preset="white"]
 */
class Shortcode {

    private static int $instance_count = 0;

    public function init(): void {
        add_shortcode('sj_reviews', [$this, 'render']);
    }

    public function render(array $atts): string {
        self::$instance_count++;
        $uid = 'sj-sc-' . self::$instance_count . '-' . uniqid();

        $opts = get_option('sj_reviews_settings', []);

        $a = shortcode_atts([
            'layout'         => $opts['default_layout']  ?? 'slider-i',
            'preset'         => $opts['default_preset']  ?? 'minimal',
            'max'            => $opts['max_front']        ?? 5,
            'columns'        => 3,
            'place_id'       => $opts['place_id']         ?? '',
            'rating_min'     => 0,
            'show_stars'     => 1,
            'show_text'      => 1,
            'show_author'    => 1,
            'show_date'      => 1,
            'show_certified' => 1,
            'show_source'    => 1,
            'autoplay'       => 0,
            'autoplay_delay' => 4000,
            'loop'           => 1,
            'speed'          => 500,
            'show_arrows'    => 1,
            'arrow_style'    => 'chevron',
            'show_dots'      => 1,
            'dots_style'     => 'bullet',
            'title'          => '',
            'title_tag'      => 'h3',
            'schema'         => 1,
            'certified_label'=> $opts['certified_label'] ?? 'Certifié',
            'star_color'     => $opts['star_color']       ?? '#f5a623',
            'lieu_id'        => 'all',
        ], $atts, 'sj_reviews');

        // Nettoyage
        $layout         = sanitize_key($a['layout']);
        $preset         = sanitize_key($a['preset']);
        $max            = max(1, min(20, (int) $a['max']));
        $columns        = max(1, min(4, (int) $a['columns']));
        $rating_min     = max(0, min(5, (int) $a['rating_min']));
        $place_id       = sanitize_text_field($a['place_id']);
        $star_color     = sanitize_hex_color($a['star_color']) ?: '#f5a623';

        // Lieu filter
        $lieu_id = sanitize_text_field($a['lieu_id']);

        // Récupère les avis
        $args = ['posts_per_page' => $max];
        $meta_query = [];
        if ($rating_min > 0) {
            $meta_query[] = [
                'key'     => 'avis_rating',
                'value'   => $rating_min,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        }
        if ($lieu_id && $lieu_id !== 'all') {
            $meta_query[] = [
                'key'   => 'avis_lieu_id',
                'value' => $lieu_id,
            ];
        }
        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $args['meta_query'] = $meta_query;
        }
        $reviews = sj_get_reviews($args);
        $agg     = sj_aggregate($reviews);

        if (empty($reviews)) return '';

        // Build wrapper classes
        $cls = implode(' ', array_filter([
            'sj-reviews',
            'sj-reviews--' . $layout,
            'sj-reviews--' . $preset,
            $a['show_arrows'] ? 'sj-reviews--has-arrows' : '',
            'sj-reviews--dots-bottom',
        ]));

        // Slider data
        $slider_data = wp_json_encode([
            'uid'          => $uid,
            'autoplay'     => (bool) $a['autoplay'],
            'delay'        => (int) $a['autoplay_delay'],
            'loop'         => (bool) $a['loop'],
            'speed'        => (int) $a['speed'],
            'perView'      => 1,
            'spaceBetween' => 24,
            'showArrows'   => (bool) $a['show_arrows'],
            'showDots'     => (bool) $a['show_dots'],
            'dotsStyle'    => sanitize_key($a['dots_style']),
            'dotsPosition' => 'bottom',
            'arrowPos'     => 'sides',
        ]);

        ob_start();

        echo "<div class=\"{$cls}\" data-sj-slider='{$slider_data}'>";

        // Titre
        if ($a['title']) {
            $tag = esc_attr($a['title_tag']);
            echo "<{$tag} class=\"sj-reviews__title\">" . esc_html($a['title']) . "</{$tag}>";
        }

        // Dispatch layout
        switch ($layout) {
            case 'slider-i':
                $this->render_slider($reviews, $a, $uid, $star_color, false);
                break;
            case 'slider-ii':
                $this->render_slider_ii($reviews, $a, $uid, $agg, $star_color, $place_id);
                break;
            case 'badge':
                $this->render_badge($agg, $a, $star_color, $place_id);
                break;
            case 'grid':
                echo "<div class=\"sj-reviews__grid\" style=\"grid-template-columns:repeat({$columns},1fr)\">";
                foreach ($reviews as $r) $this->render_card($r, $a, $star_color);
                echo '</div>';
                break;
            case 'list':
                echo '<div class="sj-reviews__list">';
                foreach ($reviews as $r) $this->render_card($r, $a, $star_color);
                echo '</div>';
                break;
        }

        echo '</div>';

        // Schema.org
        if ($a['schema'] && !is_admin() && $agg['avg'] > 0) {
            $this->render_schema($reviews, $agg, get_the_title());
        }

        return ob_get_clean();
    }

    private function render_slider(array $reviews, array $a, string $uid, string $star_color, bool $compact = false): void {
        echo '<div class="sj-swiper-container">';
        echo "<div class=\"swiper\" id=\"{$uid}\"><div class=\"swiper-wrapper\">";
        foreach ($reviews as $r) {
            echo '<div class="swiper-slide">';
            $this->render_card($r, $a, $star_color);
            echo '</div>';
        }
        echo '</div>';
        if ($a['show_dots']) {
            echo "<div class=\"swiper-pagination sj-pagination\" id=\"{$uid}-pagination\"></div>";
        }
        echo '</div>'; // swiper

        if ($a['show_arrows']) {
            $this->render_arrows_sides($a, $uid);
        }
        echo '</div>'; // container
    }

    private function render_slider_ii(array $reviews, array $a, string $uid, array $agg, string $star_color, string $place_id): void {
        echo '<div class="sj-slider-ii">';
        echo '<div class="sj-slider-ii__header">';
        $this->render_aggregate($agg, $star_color, $place_id);
        echo '</div>';
        echo '<div class="sj-slider-ii__slider">';
        $this->render_slider($reviews, $a, $uid, $star_color, false);
        echo '</div>';
        echo '</div>';
    }

    private function render_badge(array $agg, array $a, string $star_color, string $place_id): void {
        $link   = '';
        $target = '';
        if ($place_id) {
            $link   = 'https://search.google.com/local/reviews?placeid=' . rawurlencode($place_id);
            $target = ' target="_blank" rel="noopener noreferrer"';
        }
        $tag  = $link ? 'a' : 'div';
        $href = $link ? " href=\"" . esc_url($link) . "\"" : '';

        echo "<{$tag} class=\"sj-badge\"{$href}{$target}>";
        $this->render_aggregate($agg, $star_color, $place_id);
        echo "</{$tag}>";
    }

    private function render_aggregate(array $agg, string $star_color, string $place_id): void {
        echo '<div class="sj-aggregate">';
        echo '<span class="sj-aggregate__logo">' . sj_source_icon('google') . '</span>';
        echo '<div class="sj-aggregate__score">';
        echo '<span class="sj-aggregate__number">' . number_format($agg['avg'], 1, '.', '') . '</span>';
        echo sj_stars_html((int) round($agg['avg']), 5, $star_color);
        echo '<span class="sj-aggregate__count">' . (int) $agg['count'] . ' avis</span>';
        echo '</div>';
        echo '</div>';
    }

    private function render_card(array $r, array $a, string $star_color): void {
        echo '<article class="sj-review-card">';
        echo '<header class="sj-review-card__header">';
        if ($a['show_stars']) echo sj_stars_html($r['rating'], 5, $star_color);
        if ($a['show_source']) echo '<span class="sj-review-card__source">' . sj_source_icon($r['source']) . '</span>';
        echo '</header>';

        if ($a['show_text'] && $r['text']) {
            echo '<blockquote class="sj-review__text">' . esc_html($r['text']) . '</blockquote>';
        }

        echo '<footer class="sj-review-card__footer">';
        if ($r['avatar']) {
            echo '<img class="sj-review__avatar" src="' . esc_url($r['avatar']) . '" alt="' . esc_attr($r['author']) . '" width="36" height="36" loading="lazy">';
        } else {
            echo '<div class="sj-review__avatar-placeholder">' . esc_html(mb_strtoupper(mb_substr($r['author'], 0, 1))) . '</div>';
        }
        echo '<div class="sj-review__meta">';
        if ($a['show_author']) echo '<span class="sj-review__author">' . esc_html($r['author']) . '</span>';
        if ($a['show_date'])   echo '<span class="sj-review__date">'   . esc_html($r['date_rel']) . '</span>';
        echo '</div>';
        if ($a['show_certified'] && $r['certified']) {
            echo '<span class="sj-review__certified">' . esc_html($a['certified_label']) . '</span>';
        }
        echo '</footer>';
        echo '</article>';
    }

    private function render_arrows_sides(array $a, string $uid): void {
        $style = sanitize_key($a['arrow_style'] ?? 'chevron');
        $icons = [
            'chevron' => ['<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>',
                          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>'],
            'arrow'   => ['<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>',
                          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>'],
            'circle'  => ['<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M14 8l-4 4 4 4"/></svg>',
                          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M10 8l4 4-4 4"/></svg>'],
        ];
        [$prev_icon, $next_icon] = $icons[$style] ?? $icons['chevron'];
        echo "<button class=\"sj-arrow sj-arrow--prev sj-arrow--{$style}\" id=\"{$uid}-prev\" aria-label=\"Avis précédent\">{$prev_icon}</button>";
        echo "<button class=\"sj-arrow sj-arrow--next sj-arrow--{$style}\" id=\"{$uid}-next\" aria-label=\"Avis suivant\">{$next_icon}</button>";
    }

    private function render_schema(array $reviews, array $agg, string $name): void {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'LocalBusiness',
            'name'            => $name,
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => $agg['avg'],
                'reviewCount' => $agg['count'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ],
        ];
        echo '<script type="application/ld+json">'
           . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
           . '</script>';
    }
}
