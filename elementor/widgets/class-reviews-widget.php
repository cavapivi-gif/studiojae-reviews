<?php
namespace SJ_Reviews\Elementor\Widgets;

use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

/**
 * Widget Elementor — SJ Reviews
 *
 * Layouts disponibles :
 *  - slider-i   : Slider pleine largeur, une carte par slide
 *  - slider-ii  : 33% header statique (logo+stars+score) + 66% Swiper
 *  - badge      : Bandeau compact (logo source + note + count)
 *  - grid       : Grille CSS responsive
 *  - list       : Liste verticale
 *
 * Presets de style : minimal | dark | white
 *
 * Migrated to SjWidgetBase + SharedControls. Existing controls preserved,
 * SharedControls available for future enhancements.
 */
class ReviewsWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'         => 'sj-reviews',
            'title'      => 'SJ — Carrousel / Grille',
            'icon'       => 'eicon-rating',
            'keywords'   => ['avis', 'reviews', 'rating', 'étoiles', 'slider', 'badge', 'sj'],
            'css'        => ['sj-reviews-front', 'swiper'],
            'js'         => ['sj-reviews-front', 'swiper'],
            'categories' => ['sj-reviews', 'general'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->selectors = array_merge($this->selectors, [
            'container'  => '{{WRAPPER}} .sj-reviews',
            'title'      => '{{WRAPPER}} .sj-reviews__title',
            'card'       => '{{WRAPPER}} .sj-review-card',
            'card_hover' => '{{WRAPPER}} .sj-review-card:hover',
            'card_text'  => '{{WRAPPER}} .sj-review__text',
            'card_author'=> '{{WRAPPER}} .sj-review__author',
            'card_date'  => '{{WRAPPER}} .sj-review__date',
            'card_avatar'=> '{{WRAPPER}} .sj-review__avatar img',
            'stars'      => '{{WRAPPER}} .sj-stars',
            'arrow'      => '{{WRAPPER}} .sj-arrow',
            'dots'       => '{{WRAPPER}} .swiper-pagination-bullet',
        ]);
    }

    // ── CONTROLS ─────────────────────────────────────────────────────────────

    protected function register_controls(): void {

        // ── SOURCE ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_source', [
            'label' => __('Source des avis', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('source_type', [
            'label'   => __('Source', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'cpt'       => __('CPT "Avis" (recommandé)', 'sj-reviews'),
                'acf_field' => __('Champ ACF repeater (legacy)', 'sj-reviews'),
            ],
            'default' => 'cpt',
        ]);

        $this->add_control('max_reviews', [
            'label'   => __('Nombre max d\'avis', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 20,
            'default' => 5,
        ]);

        $this->add_control('rating_min', [
            'label'   => __('Note minimum à afficher', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [0 => 'Toutes', 3 => '3+', 4 => '4+', 5 => '5 uniquement'],
            'default' => 0,
        ]);

        // Legacy ACF
        $this->add_control('acf_field', [
            'label'     => __('Champ ACF repeater', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => 'exp_reviews_highlight',
            'condition' => ['source_type' => 'acf_field'],
        ]);

        $this->add_control('place_id', [
            'label'       => __('Google Place ID (badge uniquement)', 'sj-reviews'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'ChIJ…',
            'description' => __('Affiche le lien vers votre fiche Google dans le badge.', 'sj-reviews'),
        ]);

        $this->end_controls_section();

        // ── LAYOUT ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_layout', [
            'label' => __('Layout', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('layout', [
            'label'   => __('Disposition', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'slider-i'  => __('Slider I — pleine largeur', 'sj-reviews'),
                'slider-ii' => __('Slider II — 33% header + 66% slider', 'sj-reviews'),
                'badge'     => __('Badge compact', 'sj-reviews'),
                'grid'      => __('Grille', 'sj-reviews'),
                'list'      => __('Liste', 'sj-reviews'),
            ],
            'default' => 'slider-i',
        ]);

        $this->add_responsive_control('columns', [
            'label'          => __('Colonnes (grille)', 'sj-reviews'),
            'type'           => \Elementor\Controls_Manager::NUMBER,
            'min'            => 1,
            'max'            => 4,
            'default'        => 3,
            'tablet_default' => 2,
            'mobile_default' => 1,
            'selectors'      => ['{{WRAPPER}} .sj-reviews__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr)'],
            'condition'      => ['layout' => 'grid'],
        ]);

        $this->add_control('show_section_title', [
            'label'        => __('Afficher un titre de section', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
        ]);

        $this->add_control('section_title', [
            'label'     => __('Titre', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => __('Ce que disent nos clients', 'sj-reviews'),
            'dynamic'   => ['active' => true],
            'condition' => ['show_section_title' => 'yes'],
        ]);

        $this->add_control('title_tag', [
            'label'     => __('Balise HTML', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => ['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'p' => 'p'],
            'default'   => 'h3',
            'condition' => ['show_section_title' => 'yes'],
        ]);

        // ── Header badge/slider-ii ──────────────────────────────────────
        $this->add_control('badge_show_logo', [
            'label'        => __('Afficher logo source (Google…)', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => ['layout' => ['badge', 'slider-ii']],
        ]);

        $this->add_control('badge_show_score', [
            'label'        => __('Afficher note globale + count', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => ['layout' => ['badge', 'slider-ii']],
        ]);

        $this->add_control('badge_score_override', [
            'label'       => __('Note à afficher (ex: 4.9)', 'sj-reviews'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => '5.0',
            'description' => __('Laissez vide pour calculer automatiquement.', 'sj-reviews'),
            'condition'   => ['layout' => ['badge', 'slider-ii'], 'badge_show_score' => 'yes'],
        ]);

        $this->add_control('badge_count_override', [
            'label'       => __('Nombre d\'avis à afficher', 'sj-reviews'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => '127',
            'description' => __('Laissez vide pour utiliser le nombre réel.', 'sj-reviews'),
            'condition'   => ['layout' => ['badge', 'slider-ii'], 'badge_show_score' => 'yes'],
        ]);

        $this->add_control('badge_link', [
            'label'       => __('Lien du badge (optionnel)', 'sj-reviews'),
            'type'        => \Elementor\Controls_Manager::URL,
            'condition'   => ['layout' => ['badge']],
        ]);

        $this->end_controls_section();

        // ── CONTENU CARTE ────────────────────────────────────────────────────
        $this->start_controls_section('section_card_content', [
            'label' => __('Contenu des cartes', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('show_stars', [
            'label'        => __('Étoiles', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('show_text', [
            'label'        => __('Texte de l\'avis', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('text_max_chars', [
            'label'     => __('Tronquer à X caractères (0 = désactivé)', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'min'       => 0,
            'max'       => 500,
            'default'   => 0,
            'condition' => ['show_text' => 'yes'],
        ]);

        $this->add_control('show_author', [
            'label'        => __('Auteur', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('show_avatar', [
            'label'        => __('Avatar', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => ['show_author' => 'yes'],
        ]);

        $this->add_control('show_date', [
            'label'        => __('Date relative', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('show_certified', [
            'label'        => __('Badge "Certifié"', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('certified_label', [
            'label'     => __('Texte badge certifié', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => 'Certifié',
            'condition' => ['show_certified' => 'yes'],
        ]);

        $this->add_control('show_source_icon', [
            'label'        => __('Icône de source (Google, etc.)', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->end_controls_section();

        // ── SLIDER ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_slider', [
            'label'     => __('Options slider', 'sj-reviews'),
            'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
            'condition' => ['layout' => ['slider-i', 'slider-ii']],
        ]);

        $this->add_control('slider_autoplay', [
            'label'        => __('Autoplay', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
        ]);

        $this->add_control('slider_autoplay_delay', [
            'label'     => __('Délai autoplay (ms)', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'min'       => 1000,
            'max'       => 10000,
            'step'      => 500,
            'default'   => 4000,
            'condition' => ['slider_autoplay' => 'yes'],
        ]);

        $this->add_control('slider_loop', [
            'label'        => __('Boucle infinie', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('slider_speed', [
            'label'   => __('Vitesse de transition (ms)', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'min'     => 100,
            'max'     => 2000,
            'step'    => 100,
            'default' => 500,
        ]);

        $this->add_control('slider_slides_per_view', [
            'label'   => __('Slides visibles (desktop)', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'min'     => 1,
            'max'     => 4,
            'default' => 1,
            'condition' => ['layout' => 'slider-i'],
        ]);

        $this->add_control('slider_space_between', [
            'label'   => __('Espace entre slides (px)', 'sj-reviews'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'min'     => 0,
            'max'     => 80,
            'default' => 24,
        ]);

        // Flèches
        $this->add_control('show_arrows', [
            'label'        => __('Flèches de navigation', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ]);

        $this->add_control('arrow_style', [
            'label'     => __('Style flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => [
                'chevron'  => __('Chevron (‹ ›)', 'sj-reviews'),
                'arrow'    => __('Flèche longue (← →)', 'sj-reviews'),
                'circle'   => __('Cercle avec flèche', 'sj-reviews'),
            ],
            'default'   => 'chevron',
            'condition' => ['show_arrows' => 'yes'],
        ]);

        $this->add_responsive_control('arrow_size', [
            'label'      => __('Taille flèches (px)', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 20, 'max' => 80]],
            'default'    => ['size' => 40, 'unit' => 'px'],
            'selectors'  => ['{{WRAPPER}} .sj-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}'],
            'condition'  => ['show_arrows' => 'yes'],
        ]);

        $this->add_control('arrow_position', [
            'label'     => __('Position flèches', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => [
                'sides'   => __('Côtés (par défaut)', 'sj-reviews'),
                'bottom'  => __('En bas à droite', 'sj-reviews'),
            ],
            'default'   => 'sides',
            'condition' => ['show_arrows' => 'yes'],
        ]);

        // Dots
        $this->add_control('show_dots', [
            'label'        => __('Points de pagination (dots)', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ]);

        $this->add_control('dots_style', [
            'label'     => __('Style dots', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => [
                'bullet'  => __('Ronds pleins', 'sj-reviews'),
                'line'    => __('Traits horizontaux', 'sj-reviews'),
                'number'  => __('Numérotation (1/5)', 'sj-reviews'),
            ],
            'default'   => 'bullet',
            'condition' => ['show_dots' => 'yes'],
        ]);

        $this->add_responsive_control('dots_size', [
            'label'      => __('Taille dots (px)', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 4, 'max' => 20]],
            'default'    => ['size' => 8, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                '{{WRAPPER}} .sj-dot-line'              => 'height: {{SIZE}}{{UNIT}}',
            ],
            'condition'  => ['show_dots' => 'yes', 'dots_style' => ['bullet', 'line']],
        ]);

        $this->add_control('dots_position', [
            'label'     => __('Position dots', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => [
                'bottom'  => __('Bas (centré)', 'sj-reviews'),
                'bottom-left'  => __('Bas gauche', 'sj-reviews'),
                'bottom-right' => __('Bas droite', 'sj-reviews'),
            ],
            'default'   => 'bottom',
            'condition' => ['show_dots' => 'yes'],
        ]);

        $this->end_controls_section();

        // ── SCHEMA ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_schema', [
            'label' => __('Schema.org (SEO)', 'sj-reviews'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('schema_enabled', [
            'label'        => __('Injecter AggregateRating JSON-LD', 'sj-reviews'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('schema_type', [
            'label'     => __('@type entité', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => [
                'LocalBusiness' => 'LocalBusiness',
                'Product'       => 'Product',
                'Service'       => 'Service',
                'TouristTrip'   => 'TouristTrip',
            ],
            'default'   => 'LocalBusiness',
            'condition' => ['schema_enabled' => 'yes'],
        ]);

        $this->add_control('schema_name', [
            'label'     => __('Nom de l\'entité (vide = titre du post)', 'sj-reviews'),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => '',
            'dynamic'   => ['active' => true],
            'condition' => ['schema_enabled' => 'yes'],
        ]);

        $this->end_controls_section();

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

        // Transition
        $this->add_control('card_transition', [
            'label'      => __('Durée transition hover (ms)', 'sj-reviews'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['ms'],
            'range'      => ['ms' => ['min' => 0, 'max' => 800, 'step' => 50]],
            'default'    => ['size' => 250, 'unit' => 'ms'],
            'selectors'  => ['{{WRAPPER}} .sj-review-card' => 'transition: background-color {{SIZE}}ms ease, border-color {{SIZE}}ms ease, box-shadow {{SIZE}}ms ease, transform {{SIZE}}ms ease'],
        ]);

        // Tabs Normal / Hover
        $this->start_controls_tabs('card_tabs');

        // ── Tab Normal ──
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

        // ── Tab Hover ──
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

    // ── RENDER ───────────────────────────────────────────────────────────────

    protected function render(): void {
        $s       = $this->get_settings_for_display();
        $layout  = $s['layout']  ?: 'slider-i';
        $preset  = $s['preset']  ?: 'minimal';
        $max     = max(1, (int) ($s['max_reviews'] ?: 5));

        // Récupération des avis
        $reviews = $this->get_reviews($s, $max);

        if (empty($reviews)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="sj-widget-placeholder" style="padding:20px;border:1px dashed #ccc;text-align:center;">Aucun avis à afficher. Ajoutez des avis dans le menu <strong>SJ Reviews</strong>.</div>';
            }
            return;
        }

        // Agrégat
        $agg = sj_aggregate($reviews);

        // ID unique pour le swiper
        $uid = 'sj-swiper-' . $this->get_id();

        // Données slider pour le JS
        $slider_data = wp_json_encode([
            'uid'         => $uid,
            'autoplay'    => $s['slider_autoplay'] === 'yes',
            'delay'       => (int) ($s['slider_autoplay_delay'] ?: 4000),
            'loop'        => $s['slider_loop'] === 'yes',
            'speed'       => (int) ($s['slider_speed'] ?: 500),
            'perView'     => (int) ($s['slider_slides_per_view'] ?: 1),
            'spaceBetween'=> (int) ($s['slider_space_between'] ?: 24),
            'showArrows'  => $s['show_arrows'] === 'yes',
            'showDots'    => $s['show_dots'] === 'yes',
            'dotsStyle'   => $s['dots_style'] ?: 'bullet',
            'dotsPosition'=> $s['dots_position'] ?: 'bottom',
            'arrowPos'    => $s['arrow_position'] ?: 'sides',
        ]);

        $wrapper_class = implode(' ', array_filter([
            'sj-reviews',
            'sj-reviews--' . esc_attr($layout),
            'sj-reviews--' . esc_attr($preset),
            $s['show_arrows'] === 'yes' ? 'sj-reviews--has-arrows' : '',
            ($s['arrow_position'] ?: 'sides') === 'bottom' ? 'sj-reviews--arrows-bottom' : '',
            'sj-reviews--dots-' . ($s['dots_position'] ?: 'bottom'),
        ]));

        echo "<div class=\"{$wrapper_class}\" data-sj-slider='{$slider_data}'>";

        // Titre de section
        if ($s['show_section_title'] === 'yes' && !empty($s['section_title'])) {
            $tag = esc_attr($s['title_tag'] ?: 'h3');
            echo "<{$tag} class=\"sj-reviews__title\">" . esc_html($s['section_title']) . "</{$tag}>";
        }

        // Dispatch vers le layout
        match ($layout) {
            'slider-i'  => $this->render_slider_i($reviews, $s, $uid, $agg),
            'slider-ii' => $this->render_slider_ii($reviews, $s, $uid, $agg),
            'badge'     => $this->render_badge($reviews, $s, $agg),
            'grid'      => $this->render_grid($reviews, $s),
            'list'      => $this->render_list($reviews, $s),
            default     => $this->render_grid($reviews, $s),
        };

        echo '</div>';

        // Schema.org
        if ($s['schema_enabled'] === 'yes' && !is_admin()) {
            $this->render_schema($reviews, $s, $agg);
        }
    }

    // ── LAYOUTS ───────────────────────────────────────────────────────────────

    private function render_slider_i(array $reviews, array $s, string $uid, array $agg): void {
        echo '<div class="sj-swiper-container">';
        echo "<div class=\"swiper\" id=\"{$uid}\">";
        echo '<div class="swiper-wrapper">';
        foreach ($reviews as $r) {
            echo '<div class="swiper-slide">';
            $this->render_card($r, $s);
            echo '</div>';
        }
        echo '</div>'; // wrapper

        if ($s['show_dots'] === 'yes') {
            $this->render_pagination($s, $uid);
        }
        echo '</div>'; // swiper

        if ($s['show_arrows'] === 'yes' && ($s['arrow_position'] ?: 'sides') === 'bottom') {
            $this->render_arrows_bottom($s, $uid);
        }
        echo '</div>'; // container
    }

    private function render_slider_ii(array $reviews, array $s, string $uid, array $agg): void {
        echo '<div class="sj-slider-ii">';

        // 33% — header statique
        echo '<div class="sj-slider-ii__header">';
        $this->render_aggregate_header($s, $agg);
        echo '</div>';

        // 66% — Swiper
        echo '<div class="sj-slider-ii__slider">';
        echo '<div class="sj-swiper-container">';
        echo "<div class=\"swiper\" id=\"{$uid}\">";
        echo '<div class="swiper-wrapper">';
        foreach ($reviews as $r) {
            echo '<div class="swiper-slide">';
            $this->render_card($r, $s);
            echo '</div>';
        }
        echo '</div>';
        if ($s['show_dots'] === 'yes') $this->render_pagination($s, $uid);
        echo '</div>'; // swiper
        echo '</div>'; // swiper-container
        echo '</div>'; // slider

        echo '</div>'; // slider-ii
    }

    private function render_badge(array $reviews, array $s, array $agg): void {
        $link     = $s['badge_link']['url'] ?? '';
        $target   = ($s['badge_link']['is_external'] ?? false) ? ' target="_blank" rel="noopener"' : '';
        $place_id = sanitize_text_field($s['place_id'] ?? '');
        if ($place_id && !$link) {
            $link   = 'https://search.google.com/local/reviews?placeid=' . rawurlencode($place_id);
            $target = ' target="_blank" rel="noopener noreferrer"';
        }

        $tag     = $link ? 'a' : 'div';
        $href    = $link ? " href=\"" . esc_url($link) . "\"" : '';

        echo "<{$tag} class=\"sj-badge\"{$href}{$target}>";
        $this->render_aggregate_header($s, $agg);
        echo "</{$tag}>";
    }

    private function render_grid(array $reviews, array $s): void {
        echo '<div class="sj-reviews__grid">';
        foreach ($reviews as $r) {
            $this->render_card($r, $s);
        }
        echo '</div>';
    }

    private function render_list(array $reviews, array $s): void {
        echo '<div class="sj-reviews__list">';
        foreach ($reviews as $r) {
            $this->render_card($r, $s);
        }
        echo '</div>';
    }

    // ── COMPOSANTS ────────────────────────────────────────────────────────────

    private function render_card(array $r, array $s): void {
        echo '<article class="sj-review-card">';

        // Header carte : stars + source icon
        echo '<header class="sj-review-card__header">';
        if ($s['show_stars'] === 'yes') {
            $color = $s['star_color'] ?: '#f5a623';
            echo sj_stars_html($r['rating'], 5, $color);
        }
        if ($s['show_source_icon'] === 'yes') {
            echo '<span class="sj-review-card__source">' . sj_source_icon($r['source']) . '</span>';
        }
        echo '</header>';

        // Texte
        if ($s['show_text'] === 'yes' && $r['text']) {
            $text     = $r['text'];
            $max_chars = (int) ($s['text_max_chars'] ?? 0);
            if ($max_chars > 0 && mb_strlen($text) > $max_chars) {
                $text = mb_substr($text, 0, $max_chars) . '…';
            }
            echo '<blockquote class="sj-review__text">' . esc_html($text) . '</blockquote>';
        }

        // Footer : avatar + auteur + date + certifié
        echo '<footer class="sj-review-card__footer">';

        if ($s['show_avatar'] === 'yes' && $r['avatar']) {
            echo '<img class="sj-review__avatar" src="' . esc_url($r['avatar']) . '" alt="' . esc_attr($r['author']) . '" width="36" height="36" loading="lazy">';
        } elseif ($s['show_author'] === 'yes') {
            echo '<div class="sj-review__avatar-placeholder">' . esc_html(mb_strtoupper(mb_substr($r['author'], 0, 1))) . '</div>';
        }

        echo '<div class="sj-review__meta">';
        if ($s['show_author'] === 'yes') {
            echo '<span class="sj-review__author">' . esc_html($r['author']) . '</span>';
        }
        if ($s['show_date'] === 'yes') {
            echo '<span class="sj-review__date">' . esc_html($r['date_rel']) . '</span>';
        }
        echo '</div>';

        if ($s['show_certified'] === 'yes' && $r['certified']) {
            $label = esc_html($s['certified_label'] ?: 'Certifié');
            echo "<span class=\"sj-review__certified\">{$label}</span>";
        }

        echo '</footer>';
        echo '</article>';
    }

    private function render_aggregate_header(array $s, array $agg): void {
        $score = $s['badge_score_override'] ?: number_format($agg['avg'], 1, '.', '');
        $count = $s['badge_count_override'] ?: $agg['count'];

        echo '<div class="sj-aggregate">';

        if ($s['badge_show_logo'] === 'yes') {
            echo '<span class="sj-aggregate__logo">' . sj_source_icon('google') . '</span>';
        }

        if ($s['badge_show_score'] === 'yes') {
            echo '<div class="sj-aggregate__score">';
            echo '<span class="sj-aggregate__number">' . esc_html($score) . '</span>';
            echo sj_stars_html((int) round((float) $score), 5, $s['star_color'] ?? '#f5a623');
            echo '<span class="sj-aggregate__count">' . esc_html($count) . ' avis</span>';
            echo '</div>';
        }

        echo '</div>';
    }

    private function render_pagination(array $s, string $uid): void {
        $style = $s['dots_style'] ?: 'bullet';
        if ($style === 'number') {
            echo "<div class=\"sj-pagination sj-pagination--number\" id=\"{$uid}-pagination\"></div>";
        } else {
            echo "<div class=\"swiper-pagination sj-pagination sj-pagination--{$style}\" id=\"{$uid}-pagination\"></div>";
        }
    }

    private function render_arrows_bottom(array $s, string $uid): void {
        echo '<div class="sj-arrows-bottom">';
        echo $this->arrow_html($s, $uid, 'prev');
        echo $this->arrow_html($s, $uid, 'next');
        echo '</div>';
    }

    private function arrow_html(array $s, string $uid, string $dir): string {
        $style = $s['arrow_style'] ?: 'chevron';
        $cls   = "sj-arrow sj-arrow--{$dir} sj-arrow--{$style}";
        $id    = "id=\"{$uid}-{$dir}\"";

        $icon_prev = match ($style) {
            'arrow'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>',
            'circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M14 8l-4 4 4 4"/></svg>',
            default  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>',
        };
        $icon_next = match ($style) {
            'arrow'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>',
            'circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M10 8l4 4-4 4"/></svg>',
            default  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>',
        };

        $icon = $dir === 'prev' ? $icon_prev : $icon_next;
        $aria = $dir === 'prev' ? 'Avis précédent' : 'Avis suivant';

        return "<button class=\"{$cls}\" {$id} aria-label=\"{$aria}\">{$icon}</button>";
    }

    // ── DATA ─────────────────────────────────────────────────────────────────

    private function get_reviews(array $s, int $max): array {
        if ($s['source_type'] === 'acf_field') {
            return $this->get_acf_reviews($s, $max);
        }

        $args = ['posts_per_page' => $max];
        $rating_min = (int) ($s['rating_min'] ?? 0);
        if ($rating_min > 0) {
            $args['meta_query'] = [[
                'key'     => 'avis_rating',
                'value'   => $rating_min,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ]];
        }
        return sj_get_reviews($args);
    }

    private function get_acf_reviews(array $s, int $max): array {
        if (!function_exists('get_field')) return [];
        $field_name = sanitize_text_field($s['acf_field'] ?: 'exp_reviews_highlight');
        $rows       = get_field($field_name, get_the_ID());
        if (empty($rows)) return [];
        $reviews = [];
        foreach (array_slice($rows, 0, $max) as $row) {
            $reviews[] = [
                'id'        => 0,
                'author'    => $row['rev_name']   ?? 'Anonyme',
                'rating'    => (int) ($row['rev_rating'] ?? 5),
                'text'      => $row['rev_text']   ?? '',
                'certified' => false,
                'source'    => 'google',
                'place_id'  => '',
                'avatar'    => '',
                'date'      => current_time('Y-m-d'),
                'date_rel'  => '',
            ];
        }
        return $reviews;
    }

    // ── SCHEMA ────────────────────────────────────────────────────────────────

    private function render_schema(array $reviews, array $s, array $agg): void {
        if (empty($reviews) || $agg['avg'] <= 0) return;

        $name = !empty($s['schema_name']) ? $s['schema_name'] : get_the_title();
        $type = $s['schema_type'] ?: 'LocalBusiness';

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => $type,
            'name'            => $name,
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => $agg['avg'],
                'reviewCount' => $agg['count'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ],
            'review' => [],
        ];

        foreach ($reviews as $r) {
            if (!$r['text']) continue;
            $schema['review'][] = [
                '@type'        => 'Review',
                'author'       => ['@type' => 'Person', 'name' => $r['author']],
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r['rating'], 'bestRating' => 5],
                'reviewBody'   => $r['text'],
            ];
        }

        if (empty($schema['review'])) unset($schema['review']);

        echo '<script type="application/ld+json">'
           . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
           . '</script>';
    }
}
