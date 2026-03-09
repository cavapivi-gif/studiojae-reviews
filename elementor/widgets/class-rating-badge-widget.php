<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

class RatingBadgeWidget extends Widget_Base {

    public function get_name():  string { return 'sj_rating_badge'; }
    public function get_title(): string { return 'SJ Rating Badge'; }
    public function get_icon():  string { return 'eicon-rating'; }
    public function get_categories(): array { return ['studiojae']; }
    public function get_keywords(): array { return ['rating', 'badge', 'avis', 'google', 'note', 'sj']; }

    protected function register_controls(): void {
        $lieux  = (array) get_option('sj_lieux', []);
        $opts   = [];
        $opts['all'] = 'Tous les lieux actifs';
        foreach ($lieux as $l) {
            $opts[$l['id']] = esc_html($l['name'] . ' (' . ($l['source'] ?? '') . ')');
        }

        // ── Contenu ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => 'Contenu',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('lieu_id', [
            'label'   => 'Lieu',
            'type'    => Controls_Manager::SELECT,
            'options' => $opts,
            'default' => 'all',
        ]);

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

        // ── Style ────────────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => 'Style',
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('star_color', [
            'label'   => 'Couleur des étoiles',
            'type'    => Controls_Manager::COLOR,
            'default' => '#f5a623',
            'selectors' => [], // géré côté PHP via attribut direct sur le SVG
        ]);

        $this->add_control('badge_bg', [
            'label'     => 'Fond du badge',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .sj-badge' => '--sj-bg: {{VALUE}}'],
        ]);

        $this->add_control('badge_border_color', [
            'label'     => 'Couleur bordure',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e5e7eb',
            'selectors' => ['{{WRAPPER}} .sj-badge' => '--sj-border: {{VALUE}}'],
        ]);

        $this->add_control('rating_color', [
            'label'     => 'Couleur de la note',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#111111',
            'selectors' => [
                '{{WRAPPER}} .sj-badge__rating'     => 'color: {{VALUE}}',
                '{{WRAPPER}} .sj-badge__big-rating' => 'color: {{VALUE}}',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .sj-badge',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'badge_shadow',
                'selector' => '{{WRAPPER}} .sj-badge',
            ]
        );

        $this->add_responsive_control('badge_padding', [
            'label'      => 'Padding interne',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .sj-badge--card, {{WRAPPER}} .sj-badge--hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ]);

        $this->add_responsive_control('badge_radius', [
            'label'      => 'Border radius',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 40]],
            'selectors'  => ['{{WRAPPER}} .sj-badge' => '--sj-radius: {{SIZE}}{{UNIT}}'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        $atts = [
            'lieu_id'     => $s['lieu_id']     ?? 'all',
            'design'      => $s['design']      ?? 'card',
            'show_source' => $s['show_source'] ?? '1',
            'show_link'   => $s['show_link']   ?? '1',
            'star_color'  => $s['star_color']  ?? '#f5a623',
            'label'       => $s['label']       ?? 'avis',
        ];

        $sc = new \SJ_Reviews\Front\RatingShortcode();
        echo $sc->render($atts); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
