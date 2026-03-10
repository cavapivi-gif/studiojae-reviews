<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit;

/**
 * Trait SummaryStyleControls — Style tab controls for the SummaryWidget.
 *
 * Extracted from class-summary-widget.php to reduce file size.
 * Control IDs are preserved for backward compatibility with saved Elementor settings.
 */
trait SummaryStyleControls {

    /**
     * Register all style controls for the Summary widget.
     *
     * Called from SummaryWidget::register_controls() after content controls.
     */
    protected function register_summary_style_controls(): void {

        /* ── Style : Widget (conteneur global) ─────────────────────── */
        $this->start_controls_section('style_widget', [
            'label' => __('Widget', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'widget_bg',
                'label'    => __('Fond', 'sj-reviews'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-summary',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'widget_border',
                'selector' => '{{WRAPPER}} .sj-summary',
            ]
        );

        $this->add_responsive_control('widget_radius', [
            'label'      => __('Rayon des coins', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => ['{{WRAPPER}} .sj-summary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'widget_shadow',
                'selector' => '{{WRAPPER}} .sj-summary',
            ]
        );

        $this->add_responsive_control('widget_padding', [
            'label'      => __('Espacement interne', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control('widget_max_width', [
            'label'      => __('Largeur max.', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'vw'],
            'range'      => [
                'px' => ['min' => 200, 'max' => 1600, 'step' => 10],
                '%'  => ['min' => 10, 'max' => 100],
                'vw' => ['min' => 10, 'max' => 100],
            ],
            'selectors'  => [
                '{{WRAPPER}} .sj-summary' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
            ],
            'separator' => 'before',
        ]);

        $this->end_controls_section();

        /* ── Style : En-tête & Score ────────────────────────────────── */
        $this->start_controls_section('style_header', [
            'label' => __('En-tête & Score', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('score_color', [
            'label'     => __('Couleur du score', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__score-num' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'score_typography',
                'label'    => __('Typographie du score', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__score-num',
            ]
        );

        $this->add_control('score_label_color', [
            'label'     => __('Couleur du libellé', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__score-label' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'score_label_typography',
                'label'    => __('Typographie libellé', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__score-label',
            ]
        );

        $this->add_control('count_color', [
            'label'     => __('Couleur du compteur', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__count' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'count_typography',
                'label'    => __('Typographie compteur', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__count',
            ]
        );

        $this->add_control('bubble_color_full', [
            'label'     => __('Couleur bulles (pleines)', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#34d399',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__bubble--full'  => 'background: {{VALUE}};',
                '{{WRAPPER}} .sj-summary__bubble--half'  => 'background: linear-gradient(90deg, {{VALUE}} 50%, #d1fae5 50%);',
            ],
            'separator' => 'before',
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
            'selectors'  => [
                '{{WRAPPER}} .sj-summary__bubble' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
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

        /* ── Style : Distribution (barres ★) ───────────────────────── */
        $this->start_controls_section('style_distribution', [
            'label'     => __('Barres de répartition', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_distribution' => '1'],
        ]);

        $this->add_control('dist_fill_color', [
            'label'     => __('Couleur de remplissage', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#34d399',
            'selectors' => ['{{WRAPPER}} .sj-summary__dist-fill' => 'background: {{VALUE}};'],
        ]);

        $this->add_control('dist_track_color', [
            'label'     => __('Couleur du fond de barre', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f3f4f6',
            'selectors' => ['{{WRAPPER}} .sj-summary__dist-track' => 'background: {{VALUE}};'],
        ]);

        $this->add_responsive_control('dist_bar_height', [
            'label'      => __('Hauteur des barres', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 2, 'max' => 20, 'step' => 1]],
            'default'    => ['size' => 8, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .sj-summary__dist-track' => 'height: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .sj-summary__dist-fill'  => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('dist_label_color', [
            'label'     => __('Couleur des labels', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__dist-label, {{WRAPPER}} .sj-summary__dist-count' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'dist_label_typography',
                'label'    => __('Typographie labels', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__dist-label, {{WRAPPER}} .sj-summary__dist-count',
            ]
        );

        $this->add_control('divider_color', [
            'label'     => __('Couleur des séparateurs', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => ['{{WRAPPER}} .sj-summary' => '--sj-divider-color: {{VALUE}};'],
        ]);

        $this->end_controls_section();

        /* ── Style : Sous-critères ──────────────────────────────────── */
        $this->start_controls_section('style_criteria', [
            'label'     => __('Sous-critères', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_criteria' => '1'],
        ]);

        $this->add_control('crit_fill_color', [
            'label'     => __('Couleur de remplissage', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .sj-summary__crit-fill' => 'background: {{VALUE}};'],
        ]);

        $this->add_control('crit_track_color', [
            'label'     => __('Couleur du fond de barre', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f3f4f6',
            'selectors' => ['{{WRAPPER}} .sj-summary__crit-track' => 'background: {{VALUE}};'],
        ]);

        $this->add_responsive_control('crit_bar_height', [
            'label'      => __('Hauteur des barres', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 2, 'max' => 16, 'step' => 1]],
            'default'    => ['size' => 6, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .sj-summary__crit-track' => 'height: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .sj-summary__crit-fill'  => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('crit_label_color', [
            'label'     => __('Couleur des libellés', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__crit-label' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'crit_label_typography',
                'label'    => __('Typographie libellés', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__crit-label',
            ]
        );

        $this->add_control('crit_score_color', [
            'label'     => __('Couleur des scores', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__crit-score' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'crit_score_typography',
                'label'    => __('Typographie scores', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-summary__crit-score',
            ]
        );

        $this->end_controls_section();

        /* ── Style : Barre de filtres ───────────────────────────────── */
        $this->start_controls_section('style_filters', [
            'label'     => __('Filtres', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_filters' => '1'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'filters_bg',
                'label'    => __('Fond de la barre', 'sj-reviews'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-summary__filters',
            ]
        );

        $this->add_responsive_control('filters_padding', [
            'label'      => __('Espacement interne', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-summary__filters' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('filters_heading_pills', [
            'label'     => __('— Pills / Tags —', 'sj-reviews'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        // Pills Normal / Hover / Active
        $this->start_controls_tabs('pill_tabs');

        $this->start_controls_tab('pill_normal', ['label' => __('Normal', 'sj-reviews')]);

        $this->add_control('pill_color', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#374151',
            'selectors' => ['{{WRAPPER}} .sj-filters__pill' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'pill_bg',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-filters__pill',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'pill_border',
                'selector' => '{{WRAPPER}} .sj-filters__pill',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('pill_hover', ['label' => __('Survol', 'sj-reviews')]);

        $this->add_control('pill_color_hover', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-filters__pill:hover' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'pill_bg_hover',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-filters__pill:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'pill_border_hover',
                'selector' => '{{WRAPPER}} .sj-filters__pill:hover',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('pill_active', ['label' => __('Actif', 'sj-reviews')]);

        $this->add_control('pill_color_active', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .sj-filters__pill.is-active' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'pill_bg_active',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-filters__pill.is-active',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'pill_border_active',
                'selector' => '{{WRAPPER}} .sj-filters__pill.is-active',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('pill_padding', [
            'label'      => __('Espacement interne des pills', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-filters__pill' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_responsive_control('pill_radius', [
            'label'      => __('Arrondi des pills', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 99]],
            'selectors'  => ['{{WRAPPER}} .sj-filters__pill' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'pill_typography',
                'label'     => __('Typographie pills', 'sj-reviews'),
                'selector'  => '{{WRAPPER}} .sj-filters__pill',
                'separator' => 'before',
            ]
        );

        $this->add_control('modal_dot_heading', [
            'label'     => __('— Modal : dots de note —', 'sj-reviews'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
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
                '{{WRAPPER}} .sj-filter-modal__dot-btn:hover'        => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__dot-btn.is-active'    => 'background: {{VALUE}}; border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__pill:hover'            => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .sj-filter-modal__pill.is-active'        => 'background: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        /* ── Style : Cards ──────────────────────────────────────────── */
        $this->start_controls_section('style_cards', [
            'label'     => __('Cards', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->add_responsive_control('cards_gap', [
            'label'      => __('Espacement entre cards', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 60]],
            'default'    => ['size' => 16, 'unit' => 'px'],
            'selectors'  => ['{{WRAPPER}} .sj-cards-grid' => 'gap: {{SIZE}}{{UNIT}};'],
        ]);

        $this->start_controls_tabs('card_tabs');

        $this->start_controls_tab('card_normal', ['label' => __('Normal', 'sj-reviews')]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'card_bg',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .sj-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .sj-card',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('card_hover', ['label' => __('Survol', 'sj-reviews')]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'card_bg_hover',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-card:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'card_border_hover',
                'selector' => '{{WRAPPER}} .sj-card:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow_hover',
                'selector' => '{{WRAPPER}} .sj-card:hover',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('card_padding', [
            'label'      => __('Espacement interne', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_responsive_control('card_radius', [
            'label'      => __('Rayon des coins', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => ['{{WRAPPER}} .sj-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        /* ── Style : Card — Auteur ───────────────────────────────────── */
        $this->start_controls_section('style_card_author', [
            'label'     => __('Card — Auteur', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->add_responsive_control('avatar_size', [
            'label'      => __('Taille de l\'avatar', 'sj-reviews'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 20, 'max' => 80]],
            'default'    => ['size' => 36, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .sj-card__avatar'           => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .sj-card__avatar--initiale' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('author_name_color', [
            'label'     => __('Couleur du nom', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__author-name' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'author_name_typography',
                'label'    => __('Typographie du nom', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-card__author-name',
            ]
        );

        $this->add_control('author_source_color', [
            'label'     => __('Couleur de la source', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__source-name' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'author_source_typography',
                'label'    => __('Typographie source', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-card__source-name',
            ]
        );

        $this->end_controls_section();

        /* ── Style : Card — Titre & Texte ───────────────────────────── */
        $this->start_controls_section('style_card_content', [
            'label'     => __('Card — Titre & Texte', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->add_control('card_title_color', [
            'label'     => __('Couleur du titre', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__title' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'card_title_typography',
                'label'    => __('Typographie titre', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-card__title',
            ]
        );

        $this->add_control('card_text_color', [
            'label'     => __('Couleur du texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__text' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'card_text_typography',
                'label'    => __('Typographie texte', 'sj-reviews'),
                'selector' => '{{WRAPPER}} .sj-card__text',
            ]
        );

        $this->add_control('card_meta_color', [
            'label'     => __('Couleur méta (date, etc.)', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__meta, {{WRAPPER}} .sj-card__date' => 'color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->end_controls_section();

        /* ── Style : Bouton "Voir plus" ─────────────────────────────── */
        $this->start_controls_section('style_loadmore', [
            'label'     => __('Bouton "Voir plus"', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'loadmore_typography',
                'selector' => '{{WRAPPER}} .sj-summary__load-btn',
            ]
        );

        $this->start_controls_tabs('loadmore_tabs');

        $this->start_controls_tab('loadmore_normal', ['label' => __('Normal', 'sj-reviews')]);

        $this->add_control('loadmore_color', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#111111',
            'selectors' => ['{{WRAPPER}} .sj-summary__load-btn' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'loadmore_bg',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-summary__load-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'loadmore_border',
                'selector' => '{{WRAPPER}} .sj-summary__load-btn',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('loadmore_hover', ['label' => __('Survol', 'sj-reviews')]);

        $this->add_control('loadmore_color_hover', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-summary__load-btn:hover' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'loadmore_bg_hover',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .sj-summary__load-btn:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'loadmore_border_hover',
                'selector' => '{{WRAPPER}} .sj-summary__load-btn:hover',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control('loadmore_padding', [
            'label'      => __('Espacement interne', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-summary__load-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_responsive_control('loadmore_radius', [
            'label'      => __('Rayon des coins', 'sj-reviews'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => ['{{WRAPPER}} .sj-summary__load-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        // ── STYLE — Badge Certifié ───────────────────────────────────────────────
        $this->start_controls_section('style_certified', [
            'label'     => __('Style — Badge Certifié', 'sj-reviews'),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => ['show_certified' => '1', 'show_reviews' => '1'],
        ]);

        $this->add_control('certified_bg', [
            'label'     => __('Fond', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f0fdf4',
            'selectors' => ['{{WRAPPER}} .sj-card__certified' => '--sj-certified-bg: {{VALUE}}'],
        ]);

        $this->add_control('certified_color', [
            'label'     => __('Couleur texte', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#15803d',
            'selectors' => ['{{WRAPPER}} .sj-card__certified' => '--sj-certified-color: {{VALUE}}'],
        ]);

        $this->add_control('certified_border_color', [
            'label'     => __('Couleur bordure', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#bbf7d0',
            'selectors' => ['{{WRAPPER}} .sj-card__certified' => '--sj-certified-border: {{VALUE}}'],
        ]);

        $this->add_responsive_control('certified_radius', [
            'label'      => __('Border radius', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => ['{{WRAPPER}} .sj-card__certified' => '--sj-certified-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ]);

        $this->end_controls_section();

        // ── STYLE — Sous-critères par card ───────────────────────────────────────
        $this->start_controls_section('style_card_criteria', [
            'label'     => __('Style — Sous-critères (card)', 'sj-reviews'),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => ['show_card_criteria' => '1', 'show_reviews' => '1'],
        ]);

        $this->add_control('crit_dot_color', [
            'label'     => __('Couleur dot + barre', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#22c55e',
            'selectors' => ['{{WRAPPER}} .sj-card__criteria' => '--sj-crit-color: {{VALUE}}'],
        ]);

        $this->add_control('crit_label_color', [
            'label'     => __('Couleur libellé', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__crit-label' => 'color: {{VALUE}}'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'crit_typo',
            'label'    => __('Typographie', 'sj-reviews'),
            'selector' => '{{WRAPPER}} .sj-card__crit-label, {{WRAPPER}} .sj-card__crit-score',
        ]);

        $this->add_responsive_control('crit_bar_height', [
            'label'      => __('Hauteur barre', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 2, 'max' => 12]],
            'default'    => ['size' => 4, 'unit' => 'px'],
            'selectors'  => ['{{WRAPPER}} .sj-card__crit-track' => 'height: {{SIZE}}{{UNIT}}'],
        ]);

        $this->add_control('crit_card_track_color', [
            'label'     => __('Fond de barre (non rempli)', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-card__crit-track' => 'background: {{VALUE}};'],
        ]);

        $this->end_controls_section();

        /* ── Style : Barre de recherche ─────────────────────────────── */
        $this->start_controls_section('style_search', [
            'label'     => __('Barre de recherche', 'sj-reviews'),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => ['show_search' => '1'],
        ]);

        $this->add_control('search_bg', [
            'label'     => __('Fond', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-search__input' => '--sj-search-bg: {{VALUE}}; background: {{VALUE}};'],
        ]);

        $this->add_control('search_border_color', [
            'label'     => __('Couleur bordure', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-search__input' => '--sj-search-border-color: {{VALUE}};'],
        ]);

        $this->add_control('search_focus_color', [
            'label'     => __('Couleur bordure au focus', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-search__input' => '--sj-search-focus-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('search_radius', [
            'label'      => __('Rayon des coins', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'range'      => ['px' => ['min' => 0, 'max' => 50]],
            'selectors'  => ['{{WRAPPER}} .sj-search__input' => '--sj-search-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('search_icon_color', [
            'label'     => __('Couleur icône loupe', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .sj-search__icon' => '--sj-search-icon-color: {{VALUE}}; color: {{VALUE}};'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'search_typography',
            'label'    => __('Typographie', 'sj-reviews'),
            'selector' => '{{WRAPPER}} .sj-search__input',
        ]);

        $this->end_controls_section();
    }
}
