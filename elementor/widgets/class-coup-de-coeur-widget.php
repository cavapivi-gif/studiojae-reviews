<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

/**
 * Elementor Widget — Coup de cœur (style Airbnb).
 *
 * Uses SjWidgetBase + SharedControls trait for centralized controls.
 * All style controls use {{WRAPPER}} selectors → driven by Elementor, not hardcoded CSS.
 */
class CoupDeCoeurWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'         => 'sj_coup_de_coeur',
            'title'      => 'SJ — Coup de cœur',
            'icon'       => 'eicon-heart',
            'keywords'   => ['coup de coeur', 'best seller', 'favori', 'airbnb', 'badge', 'avis', 'sj'],
            'css'        => ['sj-coup-de-coeur'],
            'categories' => ['sj-reviews'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        // Selector dictionary — maps logical names to CSS selectors
        $this->selectors = array_merge($this->selectors, [
            'container'   => '{{WRAPPER}} .sj-cdc',
            'badge'       => '{{WRAPPER}} .sj-cdc__badge-inner',
            'badge_text'  => '{{WRAPPER}} .sj-cdc__badge-text',
            'leaf'        => '{{WRAPPER}} .sj-cdc__leaf svg',
            'desc'        => '{{WRAPPER}} .sj-cdc__desc',
            'avg'         => '{{WRAPPER}} .sj-cdc__avg',
            'stars'       => '{{WRAPPER}} .sj-cdc__stars-wrap',
            'sep'         => '{{WRAPPER}} .sj-cdc__sep',
            'sep_desc'    => '{{WRAPPER}} .sj-cdc__desc',
            'count'       => '{{WRAPPER}} .sj-cdc__count',
            'count_label' => '{{WRAPPER}} .sj-cdc__count-label',
            'stats'       => '{{WRAPPER}} .sj-cdc__stats',
        ]);
    }

    protected function register_controls(): void {

        // ── CONTENT TAB ─────────────────────────────────────────────────────

        $this->start_controls_section('section_content', [
            'label' => 'Contenu',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('post_id', [
            'label'       => 'Post ID',
            'description' => 'Laisser vide = post courant.',
            'type'        => Controls_Manager::NUMBER,
            'default'     => 0,
        ]);

        $this->add_control('label', [
            'label'   => 'Texte du badge',
            'type'    => Controls_Manager::TEXT,
            'default' => 'Coup de cœur voyageurs',
        ]);

        $this->add_control('subtitle', [
            'label'   => 'Description',
            'type'    => Controls_Manager::TEXT,
            'default' => 'Un des logements préférés des voyageurs',
        ]);

        $this->add_control('link', [
            'label'       => __('Lien', 'sj-reviews'),
            'type'        => Controls_Manager::URL,
            'dynamic'     => ['active' => true],
            'placeholder' => '#avis',
            'default'     => ['url' => ''],
            'separator'   => 'before',
            'description' => __('URL ou ancre (#avis) pour scroller vers la section avis.', 'sj-reviews'),
        ]);

        $this->end_controls_section();

        // ── FILTRES ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_filters', [
            'label' => __('Filtres', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        // Construire la liste des lieux pour le sélecteur
        $lieux     = \SJ_Reviews\Includes\Settings::lieux();
        $lieu_opts = [
            'linked_post' => __('Avis liés directement au post', 'sj-reviews'),
            'auto'        => __('Auto — lieux de la page (metabox)', 'sj-reviews'),
        ];
        foreach ($lieux as $l) {
            $label = $l['name'] ?? $l['id'];
            if (!($l['active'] ?? true)) $label .= ' (inactif)';
            if (!empty($l['source']))    $label .= ' (' . $l['source'] . ')';
            $lieu_opts[$l['id']] = esc_html($label);
        }

        $this->add_control('lieu_filter', [
            'label'       => __('Source des avis', 'sj-reviews'),
            'type'        => Controls_Manager::SELECT,
            'options'     => $lieu_opts,
            'default'     => 'linked_post',
            'description' => __('« Auto » lit le(s) lieu(x) lié(s) à la page via la metabox SJ Reviews.', 'sj-reviews'),
        ]);

        $this->register_source_filter_control();

        $this->end_controls_section();

        // ── STYLE TAB — shared controls from trait ──────────────────────────

        // Container (bg, border, radius, padding, shadow)
        $this->register_box_controls(
            'container', 'Conteneur',
            $this->sel('container'),
            ['radius' => 12]
        );

        // Layout (gap, alignment)
        $this->register_layout_controls(
            'main', 'Disposition',
            $this->sel('container')
        );

        // Badge text typography
        $this->register_typography_controls(
            'badge', 'Badge (texte)',
            $this->sel('badge_text'),
            ['color' => '#222222']
        );

        // Description typography
        $this->register_typography_controls(
            'desc', 'Description',
            $this->sel('desc'),
            ['color' => '#222222']
        );

        // Rating number typography
        $this->register_typography_controls(
            'avg', 'Note (chiffre)',
            $this->sel('avg'),
            ['color' => '#222222']
        );

        // Stars
        $this->register_stars_controls(
            'rating', 'Étoiles',
            $this->sel('stars'),
            ['color' => '#222222', 'size' => 10]
        );

        // Separator
        $this->register_separator_controls(
            'divider', 'Séparateurs',
            $this->sel('sep')
        );

        // Count number typography
        $this->register_typography_controls(
            'count', 'Nombre de commentaires',
            $this->sel('count'),
            ['color' => '#222222']
        );

        // Count label typography
        $this->register_typography_controls(
            'count_label', 'Label "Commentaires"',
            $this->sel('count_label'),
            ['color' => '#717171']
        );

        // Leaf SVG size
        $this->start_controls_section('section_leaf_style', [
            'label' => 'Feuilles décoratives',
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('leaf_height', [
            'label'      => 'Hauteur',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 16, 'max' => 48]],
            'default'    => ['size' => 32, 'unit' => 'px'],
            'selectors'  => [$this->sel('leaf') => 'height: {{SIZE}}{{UNIT}}; width: auto;'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        require_once SJ_REVIEWS_DIR . 'front/class-coup-de-coeur-shortcode.php';

        // Pass star colors from the shared controls
        $star_color = $s['rating_star_color'] ?? '#222222';
        $star_empty = $s['rating_star_empty_color'] ?? '#d1d5db';

        $link = $s['link'] ?? [];

        $atts = [
            'post_id'          => $s['post_id']  ?? 0,
            'star_color'       => $star_color,
            'star_empty_color' => $star_empty,
            'label'            => $s['label']     ?? 'Coup de cœur voyageurs',
            'subtitle'         => $s['subtitle']  ?? 'Un des logements préférés des voyageurs',
            'url'              => $link['url']             ?? '',
            'url_external'     => $link['is_external']     ?? '',
            'url_nofollow'     => $link['nofollow']        ?? '',
            'source_filter'    => implode(',', array_filter((array) ($s['source_filter'] ?? []))),
            'lieu_filter'      => $s['lieu_filter'] ?? 'linked_post',
        ];

        $sc = new \SJ_Reviews\Front\CoupDeCoeurShortcode();
        $output = $sc->render($atts);

        if (empty($output) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="sj-cdc sj-cdc--placeholder" style="padding:20px;border:2px dashed #ccc;border-radius:12px;text-align:center;color:#999;">';
            echo 'Widget Coup de cœur — Activez le champ <strong>best_seller</strong> sur le post et liez des avis pour voir le rendu.';
            echo '</div>';
            return;
        }

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
