<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;
use SJ_Reviews\Elementor\Traits\SummaryStyleControls;

defined('ABSPATH') || exit;

/**
 * Widget Elementor « SJ — Page Avis »
 *
 * Affiche la page avis complète : statistiques (note, distribution, sous-critères),
 * barre de filtres interactive (note, période, langue) et cards d'avis.
 * Peut être utilisé seul pour les stats uniquement.
 *
 * Migrated to SjWidgetBase + SharedControls. Existing controls preserved,
 * SharedControls available for future enhancements.
 */
class SummaryWidget extends SjWidgetBase {

    use SharedControls;
    use SummaryStyleControls;

    protected static function get_sj_config(): array {
        return [
            'id'       => 'sj_summary',
            'title'    => 'SJ — Page Avis',
            'icon'     => 'eicon-star-o',
            'keywords' => ['summary', 'page', 'avis', 'statistiques', 'filtres', 'sj'],
            'css'      => ['sj-summary'],
            'js'       => ['sj-summary'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->selectors = array_merge($this->selectors, [
            'container'    => '{{WRAPPER}} .sj-summary',
            'score'        => '{{WRAPPER}} .sj-summary__score-num',
            'score_label'  => '{{WRAPPER}} .sj-summary__score-label',
            'count'        => '{{WRAPPER}} .sj-summary__count',
            'dist_fill'    => '{{WRAPPER}} .sj-summary__dist-fill',
            'dist_track'   => '{{WRAPPER}} .sj-summary__dist-track',
            'crit_fill'    => '{{WRAPPER}} .sj-summary__crit-fill',
            'crit_track'   => '{{WRAPPER}} .sj-summary__crit-track',
            'card'         => '{{WRAPPER}} .sj-card',
            'card_hover'   => '{{WRAPPER}} .sj-card:hover',
            'card_title'   => '{{WRAPPER}} .sj-card__title',
            'card_text'    => '{{WRAPPER}} .sj-card__text',
            'card_author'  => '{{WRAPPER}} .sj-card__author-name',
            'card_avatar'  => '{{WRAPPER}} .sj-card__avatar img',
            'filter_pill'  => '{{WRAPPER}} .sj-filters__pill',
            'filter_active'=> '{{WRAPPER}} .sj-filters__pill--active',
            'load_btn'     => '{{WRAPPER}} .sj-summary__load-btn',
            'search_input' => '{{WRAPPER}} .sj-search__input',
            'certified'    => '{{WRAPPER}} .sj-card__certified',
            'ai_summary'   => '{{WRAPPER}} .sj-summary__ai',
        ]);
    }

    protected function register_controls(): void {

        /* ── SECTION : Source & Lieu ──────────────────────────────────── */
        $this->start_controls_section('section_source', [
            'label' => __('Source & Lieu', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->register_lieu_control(['default' => 'auto', 'show_auto' => true, 'all_key' => 'all', 'all_label' => 'Tous les avis']);
        $this->register_source_filter_control();
        $this->register_lieu_ids_control();

        $this->register_show_control('schema_enabled', 'Données structurées (JSON-LD)', '1', ['separator' => 'before']);

        $this->add_control('schema_type', [
            'label'     => __('@type entité', 'sj-reviews'),
            'type'      => Controls_Manager::SELECT,
            'options'   => [
                'LocalBusiness' => 'LocalBusiness',
                'Product'       => 'Product',
                'Service'       => 'Service',
                'TouristTrip'   => 'TouristTrip',
            ],
            'default'   => 'LocalBusiness',
            'condition' => ['schema_enabled' => '1'],
        ]);

        $this->add_control('schema_name', [
            'label'       => __('Nom de l\'entité (vide = titre du post)', 'sj-reviews'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'dynamic'     => ['active' => true],
            'condition'   => ['schema_enabled' => '1'],
        ]);

        $this->end_controls_section();

        /* ── SECTION : Statistiques ───────────────────────────────────── */
        $this->start_controls_section('section_stats', [
            'label' => __('Statistiques', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->register_show_control('show_ai_summary', 'Résumé IA (généré par Claude)', '', ['return_value' => '1', 'description' => __('Affiche un résumé auto-généré des avis. Nécessite une clé API Anthropic dans les réglages.', 'sj-reviews')]);
        $this->register_show_control('show_distribution', 'Barres de répartition (★)', '1');
        $this->register_show_control('show_criteria', 'Sous-critères (2 colonnes)', '1');

        $this->add_control('score_layout', [
            'label'   => __('Layout du score', 'sj-reviews'),
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'default' => __('Score au-dessus (défaut)', 'sj-reviews'),
                'left'    => __('Score à gauche (33/67)', 'sj-reviews'),
                'right'   => __('Score à droite (67/33)', 'sj-reviews'),
            ],
            'default'   => 'default',
            'separator' => 'before',
        ]);

        $this->end_controls_section();

        /* ── SECTION : Avis clients ───────────────────────────────────── */
        $this->start_controls_section('section_reviews', [
            'label' => __('Avis clients', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->register_show_control('show_reviews', 'Afficher les avis clients', '1');

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

        $this->register_show_control('show_card_criteria', 'Afficher sous-critères sur chaque card', '', ['return_value' => '1', 'condition' => ['show_reviews' => '1']]);
        $this->register_show_control('show_certified', 'Afficher badge "Certifié"', '1', ['condition' => ['show_reviews' => '1']]);
        $this->register_toggle_text_control('verified_banner', 'Bandeau "avis vérifiés"', 'Texte du bandeau', 'Tous les avis proviennent de client·es vérifié·es', '', ['return_value' => '1', 'condition' => ['show_reviews' => '1']], 'text');

        $this->add_control('text_words', [
            'label'       => __('Mots avant "Voir plus"', 'sj-reviews'),
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'default'     => 40,
            'min'         => 10,
            'max'         => 500,
            'description' => __('Nombre de mots affichés avant troncature. 0 = désactivé.', 'sj-reviews'),
            'condition'   => ['show_reviews' => '1'],
        ]);

        $this->end_controls_section();

        /* ── SECTION : Filtres ────────────────────────────────────────── */
        $this->start_controls_section('section_filters', [
            'label'     => __('Filtres interactifs', 'sj-reviews'),
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => ['show_reviews' => '1'],
        ]);

        $this->register_show_control('show_filters', 'Afficher la barre de filtres', '1');
        $this->register_show_control('show_sort', 'Tri (Plus récent / Meilleure note)', '1', ['condition' => ['show_filters' => '1']]);
        $this->register_show_control('show_rating_filter', 'Filtre par note (★)', '1', ['condition' => ['show_filters' => '1']]);
        $this->register_show_control('show_period_filter', 'Filtre par période (saison)', '1', ['condition' => ['show_filters' => '1']]);
        $this->register_show_control('show_language_filter', 'Filtre par langue', '1', ['condition' => ['show_filters' => '1']]);
        $this->register_show_control('show_search', 'Barre de recherche (titre, texte, auteur)', '', ['return_value' => '1', 'condition' => ['show_reviews' => '1'], 'separator' => 'before']);

        $this->end_controls_section();

        // ── STYLE (extracted to SummaryStyleControls trait) ──────────────────
        $this->register_summary_style_controls();
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
            'show_ai_summary'      => ($s['show_ai_summary']      ?? '') === '1' ? '1' : '0',
            'show_distribution'    => ($s['show_distribution']    ?? '') === '1' ? '1' : '0',
            'show_criteria'        => ($s['show_criteria']        ?? '') === '1' ? '1' : '0',
            'show_reviews'         => ($s['show_reviews']         ?? '') === '1' ? '1' : '0',
            'show_filters'         => ($s['show_filters']         ?? '') === '1' ? '1' : '0',
            'show_sort'            => ($s['show_sort']            ?? '') === '1' ? '1' : '0',
            'show_rating_filter'   => ($s['show_rating_filter']   ?? '') === '1' ? '1' : '0',
            'show_period_filter'   => ($s['show_period_filter']   ?? '') === '1' ? '1' : '0',
            'show_language_filter' => ($s['show_language_filter'] ?? '') === '1' ? '1' : '0',
            'reviews_initial'      => $s['reviews_initial']      ?? 5,
            'cards_columns'        => $s['cards_columns']        ?? '1',
            'text_words'           => max(0, (int) ($s['text_words'] ?: 40)),
            'show_card_criteria'   => ($s['show_card_criteria'] ?? '') === '1' ? '1' : '0',
            'show_certified'       => ($s['show_certified'] ?? '') === '1' ? '1' : '0',
            'show_verified_banner' => ($s['show_verified_banner'] ?? '') === '1' ? '1' : '0',
            'verified_banner_text' => $s['verified_banner_text'] ?? 'Tous les avis proviennent de client·es vérifié·es',
            'schema_enabled'       => ($s['schema_enabled']       ?? '') === '1' ? '1' : '0',
            'schema_type'          => $s['schema_type']          ?? 'LocalBusiness',
            'schema_name'          => $s['schema_name']          ?? '',
            'source_filter'        => implode(',', (array) ($s['source_filter'] ?? [])),
            'lieu_ids'             => implode(',', (array) ($s['lieu_ids'] ?? [])),
            'score_layout'         => $s['score_layout']  ?? 'default',
            'show_search'          => ($s['show_search']  ?? '') === '1' ? '1' : '0',
        ]);
    }
}
