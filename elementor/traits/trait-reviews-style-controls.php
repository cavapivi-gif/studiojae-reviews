<?php
namespace SJ_Reviews\Elementor\Traits;

defined('ABSPATH') || exit;

/**
 * Trait ReviewsStyleControls — Style tab controls for the ReviewsWidget.
 *
 * Refactored to use SharedControls trait methods (register_box_controls,
 * register_box_hover_controls, register_typography_controls, register_stars_controls).
 *
 * Widget-specific controls (presets, arrows, dots) remain inline.
 */
trait ReviewsStyleControls {

    /**
     * Register all style controls for the Reviews widget.
     */
    protected function register_reviews_style_controls(): void {

        // ── 1. Widget container ─────────────────────────────────────────
        $this->register_box_controls(
            'widget',
            __('Style — Widget', 'sj-reviews'),
            $this->sel('container')
        );

        // ── 2. Section title ────────────────────────────────────────────
        $this->register_typography_controls(
            'title',
            __('Style — Titre de section', 'sj-reviews'),
            $this->sel('title')
        );

        // ── 3. Preset selector (widget-specific) ────────────────────────
        $this->start_controls_section('style_preset', [
            'label' => __('Preset de style', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('preset', [
            'label'   => __('Preset', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'minimal' => __('Minimal — blanc, lignes fines', 'sj-reviews'),
                'dark'    => __('Dark — fond noir, texte blanc', 'sj-reviews'),
                'white'   => __('White — cartes blanches, ombre douce', 'sj-reviews'),
            ],
            'default' => 'minimal',
            'description' => __('Les contrôles ci-dessous surchargent le preset.', 'sj-reviews'),
        ]);

        $this->end_controls_section();

        // ── 4. Cards (Normal/Hover) ─────────────────────────────────────
        $this->register_box_hover_controls(
            'card',
            __('Style — Cartes', 'sj-reviews'),
            $this->sel('card'),
            [
                'hover_selector' => $this->sel('card_hover'),
                'transition'     => 250,
            ]
        );

        // ── 5. Cards layout (gap) ───────────────────────────────────────
        $this->register_layout_controls(
            'cards',
            __('Style — Espacement cartes', 'sj-reviews'),
            '{{WRAPPER}} .sj-reviews__grid, {{WRAPPER}} .sj-reviews__list',
            ['gap' => 24]
        );

        // ── 6. Stars ────────────────────────────────────────────────────
        $this->register_stars_controls(
            'review_stars',
            __('Style — Étoiles', 'sj-reviews'),
            $this->sel('stars'),
            ['color' => '#f5a623', 'size' => 18]
        );

        // ── 7. Review title ─────────────────────────────────────────────
        $this->register_typography_controls(
            'review_title',
            __('Style — Titre de l\'avis', 'sj-reviews'),
            $this->sel('card_title')
        );

        // ── 8. Review text ──────────────────────────────────────────────
        $this->register_typography_controls(
            'review_text',
            __('Style — Texte de l\'avis', 'sj-reviews'),
            $this->sel('card_text')
        );

        // ── 9. Avatar ───────────────────────────────────────────────────
        $this->register_avatar_controls(
            'avatar',
            __('Style — Avatar', 'sj-reviews'),
            $this->sel('card_avatar'),
            ['size' => 36, 'radius' => 50, 'fit' => 'cover']
        );

        // ── 10. Author ─────────────────────────────────────────────────
        $this->register_typography_controls(
            'author',
            __('Style — Auteur', 'sj-reviews'),
            $this->sel('card_author')
        );

        // ── 11. Date ────────────────────────────────────────────────────
        $this->register_typography_controls(
            'date',
            __('Style — Date', 'sj-reviews'),
            $this->sel('card_date')
        );

        // ── 12. Verified banner ─────────────────────────────────────────
        $this->register_typography_controls(
            'verified',
            __('Style — Bandeau vérifié', 'sj-reviews'),
            $this->sel('verified'),
            ['color' => '#15803d']
        );

        // ── 13. Arrows (widget-specific: slider only) ───────────────────
        $this->start_controls_section('style_arrows', [
            'label' => __('Style — Flèches', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('arrow_bg', [
            'label'     => __('Fond flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [$this->sel('arrow') => 'background-color: {{VALUE}}'],
        ]);

        $this->add_control('arrow_color', [
            'label'     => __('Couleur icône flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [$this->sel('arrow') => 'color: {{VALUE}}'],
        ]);

        $this->end_controls_section();

        // ── 13. Dots (widget-specific: slider only) ─────────────────────
        $this->start_controls_section('style_dots', [
            'label' => __('Style — Dots', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('dot_color', [
            'label'     => __('Couleur dots inactifs', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [$this->sel('dots') => 'background: {{VALUE}}'],
        ]);

        $this->add_control('dot_active_color', [
            'label'     => __('Couleur dot actif', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .swiper-pagination-bullet-active' => 'background: {{VALUE}}'],
        ]);

        $this->end_controls_section();
    }
}
