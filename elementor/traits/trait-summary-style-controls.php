<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit;

/**
 * Trait SummaryStyleControls — Style tab controls for the SummaryWidget.
 *
 * Refactored to use SharedControls trait methods (register_box_controls,
 * register_typography_controls, register_bar_controls, register_pill_controls,
 * register_button_controls, register_avatar_controls).
 *
 * Widget-specific controls (bubbles, modal dots, search) remain inline.
 */
trait SummaryStyleControls {

    /**
     * Register all style controls for the Summary widget.
     */
    protected function register_summary_style_controls(): void {

        // ── 1. Widget container ─────────────────────────────────────────
        $this->register_box_controls(
            'widget',
            __('Widget', 'sj-reviews'),
            $this->sel('container')
        );

        // ── 2. Score typography ─────────────────────────────────────────
        $this->register_typography_controls(
            'score',
            __('Score', 'sj-reviews'),
            $this->sel('score')
        );

        // ── 3. Score label typography ───────────────────────────────────
        $this->register_typography_controls(
            'score_label',
            __('Libellé du score', 'sj-reviews'),
            $this->sel('score_label')
        );

        // ── 4. Count typography ─────────────────────────────────────────
        $this->register_typography_controls(
            'count',
            __('Compteur d\'avis', 'sj-reviews'),
            $this->sel('count')
        );

        // ── 5. Bubbles & dividers (widget-specific) ─────────────────────
        $this->start_controls_section('style_header_extra', [
            'label' => __('Bulles & Séparateurs', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('bubble_color_full', [
            'label'     => __('Couleur bulles (pleines)', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#34d399',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__bubble--full'  => 'background: {{VALUE}};',
                '{{WRAPPER}} .sj-summary__bubble--half'  => 'background: linear-gradient(90deg, {{VALUE}} 50%, #d1fae5 50%);',
            ],
        ]);

        $this->add_control('bubble_color_empty', [
            'label'     => __('Couleur bulles (vides)', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#d1fae5',
            'selectors' => ['{{WRAPPER}} .sj-summary__bubble--empty' => 'background: {{VALUE}};'],
        ]);

        $this->add_responsive_control('bubble_size', [
            'label'      => __('Taille des bulles', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 8, 'max' => 32, 'step' => 1]],
            'selectors'  => ['{{WRAPPER}} .sj-summary__bubble' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('score_divider_color', [
            'label'     => __('Couleur des séparateurs', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => ['{{WRAPPER}} .sj-summary' => '--sj-divider-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('score_divider_width', [
            'label'      => __('Épaisseur des séparateurs', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 4]],
            'selectors'  => ['{{WRAPPER}} .sj-summary' => '--sj-divider-width: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        // ── 6. Distribution bars ────────────────────────────────────────
        $this->register_bar_controls(
            'dist',
            __('Barres de répartition', 'sj-reviews'),
            $this->sel('dist_fill'),
            $this->sel('dist_track'),
            ['fill' => '#34d399', 'track' => '#f3f4f6', 'height' => 8]
        );

        // ── 7. Sub-criteria bars ────────────────────────────────────────
        $this->register_bar_controls(
            'crit',
            __('Sous-critères', 'sj-reviews'),
            $this->sel('crit_fill'),
            $this->sel('crit_track'),
            ['fill' => '#6366f1', 'track' => '#f3f4f6', 'height' => 6]
        );

        // ── 8. Filter pills (Normal/Hover/Active) ──────────────────────
        $this->register_pill_controls(
            'filter',
            __('Filtres', 'sj-reviews'),
            $this->sel('filter_pill'),
            $this->sel('filter_active')
        );

        // ── 8b. Modal dots (widget-specific) ────────────────────────────
        $this->start_controls_section('style_modal_dots', [
            'label'     => __('Filtres — Dots de note', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('modal_dot_color', [
            'label'     => __('Couleur des dots', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#22c55e',
            'selectors' => [
                '{{WRAPPER}} .sj-filter-modal__dot'         => 'border-color: {{VALUE}}; --sj-dot-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__dot--full'   => 'background: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__dot-btn.is-active .sj-filter-modal__dot' => 'border-color: #fff;',
                '{{WRAPPER}} .sj-filter-modal__dot-btn.is-active .sj-filter-modal__dot--full' => 'background: #fff;',
            ],
        ]);

        $this->add_control('modal_accent_color', [
            'label'     => __('Couleur accent hover/actif', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#111111',
            'selectors' => [
                '{{WRAPPER}} .sj-filter-modal__dot-btn:hover'     => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__dot-btn.is-active' => 'background: {{VALUE}}; border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__pill:hover'         => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__pill.is-active'     => 'background: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        // ── 9. Cards (Normal/Hover) ─────────────────────────────────────
        $this->register_box_hover_controls(
            'scard',
            __('Cards', 'sj-reviews'),
            $this->sel('card'),
            [
                'hover_selector' => $this->sel('card_hover'),
                'transition'     => 200,
            ]
        );

        // ── 10. Card — Author (avatar + name) ──────────────────────────
        $this->register_avatar_controls(
            'avatar',
            __('Card — Avatar', 'sj-reviews'),
            $this->sel('card_avatar'),
            ['size' => 36, 'radius' => 50, 'fit' => 'cover']
        );

        $this->register_typography_controls(
            'author_name',
            __('Card — Nom auteur', 'sj-reviews'),
            $this->sel('card_author')
        );

        // ── 11. Card — Title & Text ────────────────────────────────────
        $this->register_typography_controls(
            'card_title',
            __('Card — Titre', 'sj-reviews'),
            $this->sel('card_title')
        );

        $this->register_typography_controls(
            'card_text',
            __('Card — Texte', 'sj-reviews'),
            $this->sel('card_text')
        );

        // ── 12. "Load more" button ─────────────────────────────────────
        $this->register_button_controls(
            'loadmore',
            __('Bouton "Voir plus"', 'sj-reviews'),
            $this->sel('load_btn'),
            ['color' => '#111111', 'transition' => 200]
        );

        // ── 13. Certified badge (widget-specific) ──────────────────────
        $this->start_controls_section('style_certified', [
            'label'     => __('Badge Certifié', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('certified_bg', [
            'label'     => __('Fond', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f0fdf4',
            'selectors' => [$this->sel('certified') => '--sj-certified-bg: {{VALUE}}'],
        ]);

        $this->add_control('certified_color', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#15803d',
            'selectors' => [$this->sel('certified') => '--sj-certified-color: {{VALUE}}'],
        ]);

        $this->add_control('certified_border_color', [
            'label'     => __('Couleur bordure', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#bbf7d0',
            'selectors' => [$this->sel('certified') => '--sj-certified-border: {{VALUE}}'],
        ]);

        $this->end_controls_section();

        // ── 14. Card sub-criteria bars ──────────────────────────────────
        $this->register_bar_controls(
            'card_crit',
            __('Card — Sous-critères', 'sj-reviews'),
            '{{WRAPPER}} .sj-card__crit-fill',
            '{{WRAPPER}} .sj-card__crit-track',
            ['fill' => '#22c55e', 'track' => '#f3f4f6', 'height' => 4]
        );

        // ── 14b. Sub-criteria dot color (widget-specific) ─────────────
        $this->start_controls_section('style_card_crit_dot', [
            'label' => __('Card — Dot sous-critères', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('crit_dot_color', [
            'label'       => __('Couleur du dot', 'sj-reviews'),
            'type'        => Controls_Manager::COLOR,
            'default'     => '',
            'description' => __('Par défaut, la couleur varie selon la note (vert→rouge).', 'sj-reviews'),
            'selectors'   => ['{{WRAPPER}} .sj-card__crit-dot' => '--sj-crit-color: {{VALUE}}; background: {{VALUE}};'],
        ]);

        $this->add_responsive_control('crit_dot_size', [
            'label'      => __('Taille du dot', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 4, 'max' => 20]],
            'selectors'  => ['{{WRAPPER}} .sj-card__crit-dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        // ── 15. Search input (widget-specific) ─────────────────────────
        $this->start_controls_section('style_search', [
            'label'     => __('Barre de recherche', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('search_bg', [
            'label'     => __('Fond', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$this->sel('search_input') => '--sj-search-bg: {{VALUE}}; background: {{VALUE}};'],
        ]);

        $this->add_control('search_border_color', [
            'label'     => __('Couleur bordure', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$this->sel('search_input') => '--sj-search-border-color: {{VALUE}};'],
        ]);

        $this->add_control('search_focus_color', [
            'label'     => __('Couleur bordure au focus', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$this->sel('search_input') => '--sj-search-focus-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('search_radius', [
            'label'      => __('Rayon des coins', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'range'      => ['px' => ['min' => 0, 'max' => 50]],
            'selectors'  => [$this->sel('search_input') => '--sj-search-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'search_typography',
            'label'    => __('Typographie', 'sj-reviews'),
            'selector' => $this->sel('search_input'),
        ]);

        $this->end_controls_section();
    }
}
