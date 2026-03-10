<?php
namespace SJ_Reviews\Elementor\Traits;

defined('ABSPATH') || exit;

/**
 * Trait ReviewsStyleControls — Style tab controls for the ReviewsWidget.
 *
 * Extracted from class-reviews-widget.php to reduce file size.
 * Control IDs are preserved for backward compatibility with saved Elementor settings.
 */
trait ReviewsStyleControls {

    /**
     * Register all style controls for the Reviews widget.
     *
     * Called from ReviewsWidget::register_controls() after content controls.
     */
    protected function register_reviews_style_controls(): void {

        // ── STYLE — Widget (conteneur) ───────────────────────────────────────
        $this->start_controls_section('style_widget', [
            'label' => __('Style — Widget', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            'name'     => 'widget_bg',
            'label'    => __('Fond du widget', 'sj-reviews'),
            'types'    => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .sj-reviews',
        ]);

        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            'name'     => 'widget_border',
            'selector' => '{{WRAPPER}} .sj-reviews',
            'separator' => 'before',
        ]);

        $this->add_responsive_control('widget_radius', [
            'label'      => __('Border radius', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}} .sj-reviews' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), [
            'name'     => 'widget_shadow',
            'selector' => '{{WRAPPER}} .sj-reviews',
        ]);

        $this->add_responsive_control('widget_padding', [
            'label'      => __('Padding', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-reviews' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ]);

        $this->end_controls_section();

        // ── STYLE — Titre de section ─────────────────────────────────────────
        $this->start_controls_section('style_title', [
            'label'     => __('Style — Titre de section', 'sj-reviews'),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => ['show_section_title' => 'yes'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'title_typo',
            'selector' => '{{WRAPPER}} .sj-reviews__title',
        ]);

        $this->add_control('title_color', [
            'label'     => __('Couleur', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-reviews__title' => 'color: {{VALUE}}'],
        ]);

        $this->add_responsive_control('title_margin', [
            'label'      => __('Marge bas', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-reviews__title' => 'margin-bottom: {{SIZE}}{{UNIT}}'],
        ]);

        $this->end_controls_section();

        // ── STYLE — Preset ───────────────────────────────────────────────────
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

        // ── STYLE — Cartes ───────────────────────────────────────────────────
        $this->start_controls_section('style_cards', [
            'label' => __('Style — Cartes', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('card_transition', [
            'label'      => __('Durée transition hover (ms)', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['ms'],
            'range'      => ['ms' => ['min' => 0, 'max' => 800, 'step' => 50]],
            'default'    => ['size' => 250, 'unit' => 'ms'],
            'selectors'  => ['{{WRAPPER}} .sj-review-card' => 'transition: background-color {{SIZE}}ms ease, border-color {{SIZE}}ms ease, box-shadow {{SIZE}}ms ease, transform {{SIZE}}ms ease'],
        ]);

        $this->start_controls_tabs('card_tabs');

        // Tab Normal
        $this->start_controls_tab('card_tab_normal', ['label' => __('Normal', 'sj-reviews')]);

        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            'name'     => 'card_bg',
            'label'    => __('Fond de carte', 'sj-reviews'),
            'types'    => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .sj-review-card',
        ]);

        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            'name'     => 'card_border',
            'selector' => '{{WRAPPER}} .sj-review-card',
        ]);

        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), [
            'name'     => 'card_shadow',
            'selector' => '{{WRAPPER}} .sj-review-card',
        ]);

        $this->end_controls_tab();

        // Tab Hover
        $this->start_controls_tab('card_tab_hover', ['label' => __('Hover', 'sj-reviews')]);

        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            'name'     => 'card_bg_hover',
            'label'    => __('Fond au survol', 'sj-reviews'),
            'types'    => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .sj-review-card:hover',
        ]);

        $this->add_control('card_border_color_hover', [
            'label'     => __('Couleur bordure au survol', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-review-card:hover' => 'border-color: {{VALUE}}'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), [
            'name'     => 'card_shadow_hover',
            'selector' => '{{WRAPPER}} .sj-review-card:hover',
        ]);

        $this->add_control('card_transform_hover', [
            'label'     => __('Élévation (translateY)', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'     => ['px' => ['min' => -20, 'max' => 0]],
            'default'   => ['size' => 0, 'unit' => 'px'],
            'selectors' => ['{{WRAPPER}} .sj-review-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}})'],
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('card_radius', [
            'label'      => __('Border radius', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', 'em'],
            'separator'  => 'before',
            'selectors'  => ['{{WRAPPER}} .sj-review-card' => 'border-radius: {{SIZE}}{{UNIT}}'],
        ]);

        $this->add_responsive_control('card_padding', [
            'label'      => __('Padding carte', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'default'    => ['top' => '28', 'right' => '28', 'bottom' => '28', 'left' => '28', 'unit' => 'px', 'isLinked' => true],
            'selectors'  => ['{{WRAPPER}} .sj-review-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ]);

        $this->add_responsive_control('cards_gap', [
            'label'      => __('Espacement entre cartes', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'default'    => ['size' => 24, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .sj-reviews__grid' => 'gap: {{SIZE}}{{UNIT}}',
                '{{WRAPPER}} .sj-reviews__list' => 'gap: {{SIZE}}{{UNIT}}',
            ],
        ]);

        $this->end_controls_section();

        // ── STYLE — Texte ────────────────────────────────────────────────────
        $this->start_controls_section('style_text', [
            'label' => __('Style — Texte & couleurs', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('star_color', [
            'label'     => __('Couleur étoiles', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f5a623',
            'selectors' => [
                '{{WRAPPER}} .sj-stars path[fill]' => 'fill: {{VALUE}}; stroke: {{VALUE}}',
                '{{WRAPPER}} .sj-stars'             => 'color: {{VALUE}}',
            ],
        ]);

        $this->add_responsive_control('star_size', [
            'label'      => __('Taille étoiles', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 10, 'max' => 40]],
            'default'    => ['size' => 18, 'unit' => 'px'],
            'selectors'  => ['{{WRAPPER}} .sj-stars svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'review_text_typo',
            'label'    => __('Typographie avis', 'sj-reviews'),
            'selector' => '{{WRAPPER}} .sj-review__text',
        ]);

        $this->add_control('review_text_color', [
            'label'     => __('Couleur texte avis', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-review__text' => 'color: {{VALUE}}'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'author_typo',
            'label'    => __('Typographie auteur', 'sj-reviews'),
            'selector' => '{{WRAPPER}} .sj-review__author',
        ]);

        $this->add_control('author_color', [
            'label'     => __('Couleur auteur', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-review__author' => 'color: {{VALUE}}'],
        ]);

        $this->add_control('date_color', [
            'label'     => __('Couleur date', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-review__date' => 'color: {{VALUE}}'],
        ]);

        // Flèches
        $this->add_control('arrow_bg', [
            'label'     => __('Fond flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-arrow' => 'background-color: {{VALUE}}'],
            'separator' => 'before',
        ]);

        $this->add_control('arrow_color', [
            'label'     => __('Couleur icône flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-arrow' => 'color: {{VALUE}}'],
        ]);

        // Dots
        $this->add_control('dot_color', [
            'label'     => __('Couleur dots inactifs', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}}'],
            'separator' => 'before',
        ]);

        $this->add_control('dot_active_color', [
            'label'     => __('Couleur dot actif', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .swiper-pagination-bullet-active' => 'background: {{VALUE}}'],
        ]);

        $this->end_controls_section();
    }
}
