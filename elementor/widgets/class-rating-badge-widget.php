<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

/**
 * Elementor Widget — SJ Rating Badge.
 *
 * Refactored to use SjWidgetBase + SharedControls.
 * Adds: proper hover tabs via trait, rating typography, count typography.
 */
class RatingBadgeWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'       => 'sj_rating_badge',
            'title'    => 'SJ — Badge de note',
            'icon'     => 'eicon-rating',
            'keywords' => ['rating', 'badge', 'avis', 'google', 'note', 'sj'],
            'css'      => ['sj-rating-badge'],
            'js'       => ['sj-badge'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->selectors = array_merge($this->selectors, [
            'badge'      => '{{WRAPPER}} .sj-badge',
            'badge_hover'=> '{{WRAPPER}} .sj-badge:hover',
            'rating'     => '{{WRAPPER}} .sj-badge__rating, {{WRAPPER}} .sj-badge__big-rating',
            'stars'      => '{{WRAPPER}} .sj-badge__stars',
            'count'      => '{{WRAPPER}} .sj-badge__count',
            'name'       => '{{WRAPPER}} .sj-badge__name',
            'source'     => '{{WRAPPER}} .sj-badge__source, {{WRAPPER}} .sj-badge__source-link',
            'meta'       => '{{WRAPPER}} .sj-badge__meta',
        ]);
    }

    protected function register_controls(): void {
        // ── CONTENT TAB ─────────────────────────────────────────────────────

        $this->start_controls_section('section_content', [
            'label' => 'Contenu',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->register_lieu_control(['default' => 'all', 'all_label' => 'Tous les lieux actifs']);
        $this->register_source_filter_control();

        $this->add_control('design', [
            'label'   => 'Design',
            'type'    => Controls_Manager::SELECT,
            'options' => [
                'card'    => 'Card (vertical)',
                'compact' => 'Compact (inline)',
                'pill'    => 'Pill (horizontal)',
                'hero'    => 'Hero (grand)',
            ],
            'default' => 'card',
        ]);

        $this->add_control('show_source', [
            'label'        => 'Afficher la source',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_link', [
            'label'        => 'Lien Google Maps (si Place ID)',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('label', [
            'label'   => 'Libellé compteur',
            'type'    => Controls_Manager::TEXT,
            'default' => 'avis',
        ]);

        $this->end_controls_section();

        // ── STYLE TAB — shared controls ─────────────────────────────────────

        // Badge container with hover tabs (bg, border, shadow, transform)
        $this->register_box_hover_controls(
            'badge', 'Badge',
            $this->sel('badge'),
            ['radius' => 12, 'transition' => 200, 'hover_transform' => '']
        );

        // Stars
        $this->register_stars_controls(
            'badge_stars', 'Étoiles',
            $this->sel('stars'),
            ['color' => '#f5a623', 'size' => 14]
        );

        // Rating number typography
        $this->register_typography_controls(
            'rating', 'Note (chiffre)',
            $this->sel('rating'),
            ['color' => '#111111']
        );

        // Count typography
        $this->register_typography_controls(
            'count', 'Compteur d\'avis',
            $this->sel('count')
        );

        // Name typography
        $this->register_typography_controls(
            'name', 'Nom du lieu',
            $this->sel('name')
        );

        // Source typography
        $this->register_typography_controls(
            'source', 'Source',
            $this->sel('source'),
            ['hover' => true]
        );
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        $atts = [
            'lieu_id'       => $s['lieu_id']     ?? 'all',
            'design'        => $s['design']      ?? 'card',
            'show_source'   => $s['show_source'] ?? '1',
            'show_link'     => $s['show_link']   ?? '1',
            'label'         => $s['label']       ?? 'avis',
            'source_filter' => implode(',', array_filter((array) ($s['source_filter'] ?? []))),
        ];

        $sc = new \SJ_Reviews\Front\RatingShortcode();
        echo $sc->render($atts); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
