<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

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

        /* ── SECTION : Style / Couleurs ───────────────────────────────── */
        $this->start_controls_section('section_style', [
            'label' => __('Couleurs', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('color_bubble', [
            'label'     => __('Couleur bulles & distribution', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#34d399',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__bubble--full' => 'background: {{VALUE}};',
                '{{WRAPPER}} .sj-summary__dist-fill'    => 'background: {{VALUE}};',
            ],
        ]);

        $this->add_control('color_criteria', [
            'label'     => __('Couleur barres sous-critères', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__crit-fill' => 'background: {{VALUE}};',
            ],
        ]);

        $this->add_control('color_pill_active', [
            'label'     => __('Couleur pill actif', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#111111',
            'selectors' => [
                '{{WRAPPER}} .sj-filters__pill.is-active' => 'background: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('color_bg', [
            'label'     => __('Fond du widget', 'sj-reviews'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .sj-summary' => 'background: {{VALUE}};',
            ],
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
