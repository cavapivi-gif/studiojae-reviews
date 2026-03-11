<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

/**
 * Elementor Widget — SJ Inline Rating.
 *
 * Refactored to use SjWidgetBase + SharedControls.
 * Previously: 3 basic style controls. Now: full typography, stars, layout.
 */
class InlineRatingWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'       => 'sj_inline_rating',
            'title'    => 'SJ — Note inline',
            'icon'     => 'eicon-star',
            'keywords' => ['rating', 'inline', 'note', 'étoiles', 'avis', 'sj'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->selectors = array_merge($this->selectors, [
            'container' => '{{WRAPPER}} .sj-inline-rating',
            'score'     => '{{WRAPPER}} .sj-inline-rating__score',
            'count'     => '{{WRAPPER}} .sj-inline-rating__count',
            'stars'     => '{{WRAPPER}} .sj-inline-rating__stars',
            'sources'   => '{{WRAPPER}} .sj-inline-rating__sources',
            'before'    => '{{WRAPPER}} .sj-inline-rating__before',
            'after'     => '{{WRAPPER}} .sj-inline-rating__after',
        ]);
    }

    protected function register_controls(): void {

        // ── CONTENT TAB ─────────────────────────────────────────────────────

        $this->start_controls_section('section_data', [
            'label' => 'Données',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->register_lieu_control(['default' => '', 'show_all' => true, 'all_label' => 'Tous les lieux']);
        $this->register_source_filter_control();

        $this->add_control('show_stars', [
            'label'        => 'Afficher les étoiles',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_score', [
            'label'        => 'Afficher le score (4.8/5)',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_count', [
            'label'        => 'Afficher le nombre d\'avis',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_sources', [
            'label'        => 'Afficher les sources (Google, …)',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '',
        ]);

        $this->add_control('star_color', [
            'label'   => 'Couleur des étoiles',
            'type'    => Controls_Manager::COLOR,
            'default' => '#f5a623',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_text', [
            'label' => 'Texte',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('text_before', [
            'label'   => 'Texte avant',
            'type'    => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('text_after', [
            'label'   => 'Texte après',
            'type'    => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->end_controls_section();

        // ── CONTENT: Display options ─────────────────────────────────────────

        $this->start_controls_section('section_display', [
            'label' => 'Affichage',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('display_mode', [
            'label'   => 'Mode d\'affichage',
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'inline' => 'Inline (sur une ligne)',
                'block'  => 'Block (empilé)',
            ],
            'default' => 'inline',
            'selectors' => [
                '{{WRAPPER}} .sj-inline-rating' => 'display: {{VALUE}}; flex-wrap: wrap;',
            ],
            'selectors_dictionary' => [
                'inline' => 'inline-flex',
                'block'  => 'flex; flex-direction: column',
            ],
        ]);

        $this->add_responsive_control('align', [
            'label'   => 'Alignement',
            'type'    => Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
                'center'     => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
                'flex-end'   => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
            ],
            'selectors' => [
                '{{WRAPPER}} .sj-inline-rating' => 'justify-content: {{VALUE}}; align-items: {{VALUE}};',
            ],
        ]);

        $this->add_control('separator_type', [
            'label'   => 'Séparateur entre éléments',
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'none'  => 'Aucun',
                'dot'   => 'Point (·)',
                'pipe'  => 'Barre (|)',
                'dash'  => 'Tiret (–)',
                'slash' => 'Slash (/)',
            ],
            'default' => 'none',
        ]);

        $this->end_controls_section();

        // ── STYLE TAB — shared controls ─────────────────────────────────────

        // Container box (bg, border, radius, padding, shadow)
        $this->register_box_controls(
            'container', 'Conteneur',
            $this->sel('container'),
            ['radius' => 0]
        );

        // Layout (gap, alignment)
        $this->register_layout_controls(
            'inline', 'Disposition',
            $this->sel('container'),
            ['gap' => 6]
        );

        // Score typography (4.8/5)
        $this->register_typography_controls(
            'score', 'Score (4.8/5)',
            $this->sel('score'),
            ['color' => '#111111']
        );

        // Count typography (sur 988 avis)
        $this->register_typography_controls(
            'count', 'Compteur d\'avis',
            $this->sel('count'),
            ['color' => '#6b7280']
        );

        // Sources typography (Google, Regiondo)
        $this->register_typography_controls(
            'sources', 'Sources',
            $this->sel('sources'),
            ['color' => '#9ca3af']
        );

        // Before/After text typography
        $this->register_typography_controls(
            'before_after', 'Texte avant/après',
            $this->sel('before') . ', ' . $this->sel('after'),
            ['color' => '']
        );

        // Stars
        $this->register_stars_controls(
            'inline_stars', 'Étoiles',
            $this->sel('stars'),
            ['color' => '#f5a623', 'size' => 14]
        );

        // Separator style (widget-specific)
        $this->start_controls_section('section_separator_style', [
            'label' => 'Séparateurs',
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('separator_color', [
            'label'     => 'Couleur séparateur',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#d1d5db',
            'selectors' => ['{{WRAPPER}} .sj-inline-rating__sep' => 'color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('separator_spacing', [
            'label'      => 'Espacement séparateur',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 20]],
            'selectors'  => ['{{WRAPPER}} .sj-inline-rating__sep' => 'margin-inline: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        require_once SJ_REVIEWS_DIR . 'front/class-inline-rating-shortcode.php';

        $atts = [
            'lieu_id'        => $s['lieu_id']        ?? '',
            'source_filter'  => implode(',', array_filter((array) ($s['source_filter'] ?? []))),
            'show_stars'     => $s['show_stars']     ?? '1',
            'show_score'     => $s['show_score']     ?? '1',
            'show_count'     => $s['show_count']     ?? '1',
            'show_sources'   => $s['show_sources']   ?? '',
            'star_color'     => $s['star_color']     ?? '#f5a623',
            'text_before'    => $s['text_before']    ?? '',
            'text_after'     => $s['text_after']     ?? '',
            'separator_type' => $s['separator_type'] ?? 'none',
        ];

        $sc = new \SJ_Reviews\Front\InlineRatingShortcode();
        echo $sc->render($atts); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
