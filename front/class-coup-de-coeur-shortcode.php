<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_coup_de_coeur] — Bannière "Coup de cœur" style Airbnb.
 *
 * Affichée uniquement si le champ ACF `best_seller` du post courant est true.
 * Récupère les avis liés au post via `avis_linked_post` et calcule la note moyenne.
 *
 * Paramètres :
 *   post_id          = int     ID du post (default: post courant)
 *   star_color       = hex     Couleur des étoiles pleines (default: #222222)
 *   star_empty_color = hex     Couleur des étoiles vides (default: #d1d5db)
 *   label            = string  Texte du badge (default: "Coup de cœur voyageurs")
 *   subtitle         = string  Description (default: "Un des logements préférés des voyageurs")
 */
class CoupDeCoeurShortcode {

    public function init(): void {
        add_shortcode('sj_coup_de_coeur', [$this, 'render']);
    }

    public function render(array $atts = []): string {
        $opts = get_option('sj_reviews_settings', []);

        $a = shortcode_atts([
            'post_id'          => 0,
            'star_color'       => $opts['star_color'] ?? '#222222',
            'star_empty_color' => '#d1d5db',
            'label'            => 'Coup de cœur voyageurs',
            'subtitle'         => 'Un des logements préférés des voyageurs',
        ], $atts, 'sj_coup_de_coeur');

        $post_id          = (int) $a['post_id'] ?: get_the_ID();
        $star_color       = sanitize_hex_color($a['star_color']) ?: '#222222';
        $star_empty_color = sanitize_hex_color($a['star_empty_color']) ?: '#d1d5db';
        $label            = esc_html($a['label']);
        $subtitle         = esc_html($a['subtitle']);

        if (!$post_id) return '';

        // Vérifie le champ ACF best_seller
        $best_seller = function_exists('get_field')
            ? get_field('best_seller', $post_id)
            : get_post_meta($post_id, 'best_seller', true);

        if (!$best_seller) return '';

        // Récupère les avis liés à ce post
        $reviews = sj_get_reviews([
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'avis_linked_post',
                    'value'   => $post_id,
                    'compare' => '=',
                ],
            ],
        ]);

        $agg   = sj_aggregate($reviews);
        $avg   = $agg['avg'];
        $count = $agg['count'];

        // Ne rien afficher si aucun avis
        if ($count === 0) return '';

        $avg_fmt   = number_format($avg, 2, ',', '');
        $count_fmt = number_format($count, 0, ',', ' ');

        ob_start();
        ?>
        <div class="sj-cdc" role="banner" aria-label="<?php echo $label; ?>">

            <!-- Colonne 1 : Badge décoratif -->
            <div class="sj-cdc__badge">
                <div class="sj-cdc__badge-inner" role="img" aria-label="<?php echo $label; ?>">
                    <div class="sj-cdc__leaf">
                        <?php echo self::leaf_svg_left(); ?>
                    </div>
                    <div class="sj-cdc__badge-text">
                        <span><?php echo $label; ?></span>
                    </div>
                    <div class="sj-cdc__leaf sj-cdc__leaf--right">
                        <?php echo self::leaf_svg_right(); ?>
                    </div>
                </div>
            </div>

            <!-- Colonne 2 : Description -->
            <div class="sj-cdc__desc">
                <?php echo $subtitle; ?>
            </div>

            <!-- Colonne 3 : Note + Commentaires -->
            <div class="sj-cdc__stats">
                <div class="sj-cdc__rating-block">
                    <div class="sj-cdc__avg" aria-label="<?php echo esc_attr($avg_fmt); ?> étoiles sur 5">
                        <?php echo $avg_fmt; ?>
                    </div>
                    <div class="sj-cdc__stars">
                        <?php echo self::stars_html($avg, $star_color, $star_empty_color); ?>
                    </div>
                </div>
                <div class="sj-cdc__sep" aria-hidden="true"></div>
                <div class="sj-cdc__count-block">
                    <div class="sj-cdc__count"><?php echo $count_fmt; ?></div>
                    <div class="sj-cdc__count-label">Commentaires</div>
                </div>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Génère des étoiles SVG partielles.
     *
     * Star color and empty color are passed as defaults here, but can be
     * overridden via Elementor's `selectors` system (SharedControls trait).
     */
    public static function stars_html(float $rating, string $color = '#222222', string $empty_color = '#d1d5db'): string {
        $html = '<span class="sj-cdc__stars-wrap" aria-label="' . esc_attr(number_format($rating, 1)) . ' sur 5">';
        for ($i = 1; $i <= 5; $i++) {
            $fill = min(1.0, max(0.0, $rating - ($i - 1)));
            $pct  = round($fill * 100);
            $id   = 'cdc-' . uniqid();
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 32 32" aria-hidden="true">';
            $html .= '<defs><linearGradient id="' . $id . '"><stop offset="' . $pct . '%" stop-color="' . esc_attr($color) . '"/><stop offset="' . $pct . '%" stop-color="' . esc_attr($empty_color) . '"/></linearGradient></defs>';
            $html .= '<path fill-rule="evenodd" d="m15.1 1.58-4.13 8.88-9.86 1.27a1 1 0 0 0-.54 1.74l7.3 6.57-1.97 9.85a1 1 0 0 0 1.48 1.06l8.62-5 8.63 5a1 1 0 0 0 1.48-1.06l-1.97-9.85 7.3-6.57a1 1 0 0 0-.55-1.73l-9.86-1.28-4.12-8.88a1 1 0 0 0-1.82 0z" fill="url(#' . $id . ')"/>';
            $html .= '</svg>';
        }
        return $html . '</span>';
    }

    /** SVG feuille gauche (Airbnb-inspired) */
    public static function leaf_svg_left(): string {
        return '<svg viewBox="0 0 20 32" fill="none" xmlns="http://www.w3.org/2000/svg" height="32"><g clip-path="url(#cdc_leaf_l)"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.49 25.42l-.66-.96 1.7-.81.66.96-.85.4.85-.4c1.47 2.14.76 4.78-1.59 5.89a4.5 4.5 0 0 1-2.48.49l-.32-.01-.07-1.75.32.01c.53.02 1.04-.08 1.49-.29 1.41-.67 1.84-2.25.95-3.53z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.32 10.24c1.76-1.27 2.6-2.75 2.53-4.43-.07-1.67-1.04-3.22-2.92-4.62C6.18 2.46 5.34 3.93 5.41 5.61c.07 1.68 1.04 3.22 2.91 4.63z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.19.49c.36-.26.91-.24 1.3.05 2.05 1.54 3.22 3.31 3.3 5.3.09 1.99-.93 3.69-2.85 5.08-.36.26-.91.24-1.3-.05-2.04-1.54-3.21-3.31-3.3-5.3C4.26 3.57 5.28 1.88 7.19.49zm.73 1.88c-1.14 1.02-1.62 2.1-1.57 3.27.05 1.17.63 2.29 1.86 3.4 1.14-1.02 1.63-2.1 1.57-3.27-.05-1.17-.62-2.29-1.86-3.4z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.68 24.05c-1.55-1.7-3.25-2.58-5.1-2.65-1.84-.07-3.47.69-4.88 2.28 1.55 1.7 3.25 2.58 5.1 2.65 1.84.07 3.47-.69 4.88-2.28z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M4.91 24.18c-.3-.33-.32-.77-.05-1.07 1.53-1.73 3.42-2.67 5.66-2.59 2.25.08 4.22 1.17 5.9 3.02.3.33.32.77.05 1.07-1.53 1.73-3.42 2.67-5.66 2.59-2.25-.08-4.22-1.17-5.9-3.02zm2.08-.46c1.24 1.16 2.48 1.68 3.75 1.73 1.26.05 2.47-.38 3.61-1.46-1.24-1.16-2.48-1.69-3.75-1.73-1.26-.05-2.47.38-3.61 1.46z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M10.68 20.7c-.46-2.1-1.51-3.61-3.15-4.5-1.64-.9-3.53-1.01-5.69-.34.46 2.11 1.51 3.61 3.15 4.51 1.64.9 3.53 1.01 5.69.33z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M.79 15.94c-.09-.4.15-.79.58-.92 2.36-.74 4.6-.65 6.58.44 1.97 1.08 3.15 2.86 3.65 5.14.09.4-.15.79-.58.92-2.36.74-4.6.65-6.58-.44C2.47 20 1.29 18.23.79 15.94zm2.16.52c.48 1.53 1.33 2.54 2.46 3.16 1.12.61 2.43.8 4.03.45-.48-1.53-1.33-2.54-2.46-3.16-1.12-.61-2.43-.8-4.03-.45z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.91 15.63c.75-1.95.63-3.67-.36-5.16-.99-1.49-2.64-2.44-4.97-2.86-.75 1.95-.63 3.67.36 5.15.99 1.49 2.64 2.44 4.97 2.87z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1.66 7.28c.15-.39.61-.61 1.09-.52 2.55.47 4.52 1.55 5.7 3.33 1.17 1.77 1.26 3.76.45 5.88-.15.39-.61.61-1.09.52-2.55-.47-4.52-1.55-5.7-3.33C.94 11.39.85 9.4 1.66 7.28zm1.68 1.38c-.4 1.47-.19 2.69.5 3.72.68 1.02 1.77 1.78 3.39 2.21.4-1.47.19-2.69-.5-3.72-.68-1.02-1.77-1.78-3.39-2.21z" fill="#222"/></g><defs><clipPath id="cdc_leaf_l"><rect width="18.82" height="32" fill="white" transform="translate(.45 0)"/></clipPath></defs></svg>';
    }

    /** SVG feuille droite (miroir) */
    public static function leaf_svg_right(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 32" fill="none" height="32"><g clip-path="url(#cdc_leaf_r)"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.07 25.42l.66-.96-1.7-.81-.66.96.85.4-.85-.4C1.89 26.75 1.6 29.39 3.95 30.5c.75.35 1.6.52 2.48.49l.32-.01.07-1.75-.32.01c-.53.02-1.04-.08-1.49-.29-1.41-.67-1.84-2.25-.95-3.53z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.23 10.24c-1.76-1.27-2.6-2.75-2.53-4.43.07-1.67 1.04-3.22 2.92-4.62 1.76 1.27 2.6 2.75 2.53 4.42-.07 1.68-1.04 3.22-2.92 4.63z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M12.36.49c-.36-.26-.91-.24-1.3.05-2.04 1.54-3.21 3.31-3.3 5.3-.08 1.99.93 3.69 2.85 5.08.36.26.91.24 1.3-.05 2.04-1.54 3.21-3.31 3.3-5.3.08-1.99-.93-3.69-2.85-5.08zm-.73 1.88c1.14 1.02 1.63 2.1 1.58 3.27-.05 1.17-.63 2.29-1.86 3.4-1.14-1.02-1.63-2.1-1.58-3.27.05-1.17.63-2.29 1.86-3.4z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.87 24.05c1.55-1.7 3.25-2.58 5.1-2.65 1.85-.07 3.47.69 4.88 2.28-1.55 1.7-3.25 2.58-5.1 2.65-1.84.07-3.47-.69-4.88-2.28z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14.65 24.18c.3-.33.32-.77.05-1.07-1.53-1.73-3.42-2.67-5.66-2.59-2.25.08-4.22 1.17-5.9 3.02-.3.33-.32.77-.05 1.07 1.53 1.73 3.42 2.67 5.66 2.59 2.25-.08 4.22-1.17 5.9-3.02zm-2.08-.46c-1.24 1.16-2.48 1.69-3.75 1.73-1.26.05-2.47-.38-3.61-1.46 1.24-1.16 2.48-1.69 3.75-1.73 1.26-.05 2.47.38 3.61 1.46z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.88 20.7c.46-2.1 1.51-3.61 3.15-4.5 1.64-.9 3.53-1.01 5.69-.34-.46 2.11-1.51 3.61-3.15 4.51-1.64.9-3.53 1.01-5.69.33z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M18.76 15.94c.09-.4-.15-.79-.58-.92-2.36-.74-4.6-.65-6.58.44-1.97 1.08-3.15 2.86-3.65 5.14-.09.4.15.79.58.92 2.36.74 4.6.65 6.58-.44 1.97-1.08 3.15-2.86 3.65-5.14zm-2.16.52c-.48 1.53-1.33 2.54-2.46 3.16-1.12.61-2.43.8-4.03.45.48-1.53 1.33-2.54 2.46-3.16 1.12-.61 2.43-.8 4.03-.45z" fill="#222"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.65 15.63c-.75-1.95-.63-3.67.36-5.16.99-1.49 2.64-2.44 4.97-2.86.75 1.95.63 3.67-.36 5.15-.99 1.49-2.64 2.44-4.97 2.87z" fill="#F7F7F7"/><path fill-rule="evenodd" clip-rule="evenodd" d="M17.89 7.28c-.15-.39-.61-.61-1.09-.52-2.55.47-4.52 1.55-5.7 3.33-1.17 1.77-1.26 3.76-.45 5.88.15.39.61.61 1.09.52 2.55-.47 4.52-1.55 5.7-3.33 1.17-1.77 1.26-3.76.45-5.88zm-1.68 1.38c.4 1.47.19 2.69-.5 3.72-.68 1.02-1.77 1.78-3.39 2.21-.4-1.47-.19-2.69.5-3.72.68-1.02 1.77-1.78 3.39-2.21z" fill="#222"/></g><defs><clipPath id="cdc_leaf_r"><rect width="18.82" height="32" fill="white" transform="matrix(-1 0 0 1 19.1 0)"/></clipPath></defs></svg>';
    }
}
