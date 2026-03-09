<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit;

/**
 * Widget Elementor « SJ — Page Avis »
 *
 * Affiche la page avis complète : statistiques (note, distribution, sous-critères),
 * barre de filtres interactive (note, période, langue) et cards d'avis.
 * Peut être utilisé seul pour les stats uniquement.
 */
class SummaryWidget extends Widget_Base {

    public function get_name(): string  { return 'sj_summary'; }
    public function get_title(): string { return __('SJ — Page Avis', 'sj-reviews'); }
    public function get_icon(): string  { return 'eicon-star-o'; }

    public function get_categories(): array { return ['sj-reviews']; }

    protected function register_controls(): void {

        /* ── SECTION : Source & Lieu ──────────────────────────────────── */
        $this->start_controls_section('section_source', [
            'label' => __('Source & Lieu', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $lieux     = (array) get_option('sj_lieux', []);
        $lieu_opts = [
            'auto' => __('Auto (lieu de la page)', 'sj-reviews'),
            'all'  => __('Tous les avis',          'sj-reviews'),
        ];
        foreach ($lieux as $l) {
            $lieu_opts[$l['id']] = esc_html($l['name'] . ($l['active'] ? '' : ' (inactif)'));
        }

        $this->add_control('lieu_id', [
            'label'   => __('Lieu affiché', 'sj-reviews'),
            'type'    => Controls_Manager::SELECT,
            'options' => $lieu_opts,
            'default' => 'auto',
        ]);

        $this->end_controls_section();

        /* ── SECTION : Statistiques ───────────────────────────────────── */
        $this->start_controls_section('section_stats', [
            'label' => __('Statistiques', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('show_distribution', [
            'label'        => __('Barres de répartition (★)', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_criteria', [
            'label'        => __('Sous-critères (2 colonnes)', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->end_controls_section();

        /* ── SECTION : Avis clients ───────────────────────────────────── */
        $this->start_controls_section('section_reviews', [
            'label' => __('Avis clients', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('show_reviews', [
            'label'        => __('Afficher les avis clients', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('reviews_initial', [
            'label'      => __('Nb d\'avis avant "Voir plus"', 'sj-reviews'),
            'type'       => Controls_Manager::NUMBER,
            'min'        => 1,
            'max'        => 50,
            'step'       => 1,
            'default'    => 5,
            'condition'  => ['show_reviews' => '1'],
        ]);

        $this->add_control('cards_columns', [
            'label'     => __('Colonnes de cards', 'sj-reviews'),
            'type'      => Controls_Manager::SELECT,
            'options'   => [
                '1' => __('1 colonne (pleine largeur)', 'sj-reviews'),
                '2' => __('2 colonnes',                 'sj-reviews'),
                '3' => __('3 colonnes',                 'sj-reviews'),
            ],
            'default'  => '1',
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->end_controls_section();

        /* ── SECTION : Filtres ────────────────────────────────────────── */
        $this->start_controls_section('section_filters', [
            'label'     => __('Filtres interactifs', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->add_control('show_filters', [
            'label'        => __('Afficher la barre de filtres', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_sort', [
            'label'        => __('Tri (Plus récent / Meilleure note)', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
            'condition'    => ['show_filters' => '1'],
        ]);

        $this->add_control('show_rating_filter', [
            'label'        => __('Filtre par note (★)', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
            'condition'    => ['show_filters' => '1'],
        ]);

        $this->add_control('show_period_filter', [
            'label'        => __('Filtre par période (saison)', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
            'condition'    => ['show_filters' => '1'],
        ]);

        $this->add_control('show_language_filter', [
            'label'        => __('Filtre par langue', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
            'condition'    => ['show_filters' => '1'],
        ]);

        $this->end_controls_section();

        /* ═══════════════════════════════════════════════════════════
   TAB STYLE — Sections par composant visible
   ═══════════════════════════════════════════════════════════ */

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
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 99]],
            'selectors'  => ['{{WRAPPER}} .sj-summary__load-btn' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        if (!class_exists('\SJ_Reviews\Front\SummaryShortcode')) {
            require_once SJ_REVIEWS_DIR . 'front/class-summary-shortcode.php';
        }

        $sc = new \SJ_Reviews\Front\SummaryShortcode();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $sc->render([
            'lieu_id'              => $s['lieu_id']              ?? 'auto',
            'show_distribution'    => $s['show_distribution']    ?? '1',
            'show_criteria'        => $s['show_criteria']        ?? '1',
            'show_reviews'         => $s['show_reviews']         ?? '1',
            'show_filters'         => $s['show_filters']         ?? '1',
            'show_sort'            => $s['show_sort']            ?? '1',
            'show_rating_filter'   => $s['show_rating_filter']   ?? '1',
            'show_period_filter'   => $s['show_period_filter']   ?? '1',
            'show_language_filter' => $s['show_language_filter'] ?? '1',
            'reviews_initial'      => $s['reviews_initial']      ?? 5,
            'cards_columns'        => $s['cards_columns']        ?? '1',
        ]);
    }
}
