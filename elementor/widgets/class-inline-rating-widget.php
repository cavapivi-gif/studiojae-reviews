<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

class InlineRatingWidget extends Widget_Base {

    public function get_name():  string { return 'sj_inline_rating'; }
    public function get_title(): string { return 'SJ — Note inline'; }
    public function get_icon():  string { return 'eicon-star'; }
    public function get_categories(): array { return ['sj-reviews']; }
    public function get_keywords(): array { return ['rating', 'inline', 'note', 'étoiles', 'avis', 'sj']; }

    protected function register_controls(): void {
        $lieux = (array) get_option('sj_lieux', []);
        $opts  = ['' => 'Tous les lieux'];
        foreach ($lieux as $l) {
            $opts[$l['id']] = esc_html(($l['name'] ?? $l['id']) . ' (' . ($l['source'] ?? '') . ')');
        }

        // ── Section Données ───────────────────────────────────────────────────
        $this->start_controls_section('section_data', [
            'label' => 'Données',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('lieu_id', [
            'label'   => 'Lieu',
            'type'    => Controls_Manager::SELECT,
            'options' => $opts,
            'default' => '',
        ]);

        $this->add_control('show_stars', [
            'label'        => 'Afficher les étoiles',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_score', [
            'label'        => 'Afficher le score (4.8/5)',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_count', [
            'label'        => 'Afficher le nombre d\'avis',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_sources', [
            'label'        => 'Afficher les sources (Google, …)',
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '',
        ]);

        $this->add_control('star_color', [
            'label'   => 'Couleur des étoiles',
            'type'    => Controls_Manager::COLOR,
            'default' => '#f5a623',
        ]);

        $this->end_controls_section();

        // ── Section Texte ─────────────────────────────────────────────────────
        $this->start_controls_section('section_text', [
            'label' => 'Texte',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('text_before', [
            'label'   => 'Texte avant',
            'type'    => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('text_after', [
            'label'   => 'Texte après',
            'type'    => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->end_controls_section();

        // ── Section Style ─────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => 'Style',
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('font_size', [
            'label'      => 'Taille de police',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 10, 'max' => 30]],
            'selectors'  => ['{{WRAPPER}} .sj-inline-rating' => 'font-size: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control('gap', [
            'label'      => 'Espacement entre éléments',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 2, 'max' => 12]],
            'selectors'  => ['{{WRAPPER}} .sj-inline-rating' => 'gap: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('color', [
            'label'     => 'Couleur du texte',
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => ['{{WRAPPER}} .sj-inline-rating' => 'color: {{VALUE}};'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        require_once SJ_REVIEWS_DIR . 'front/class-inline-rating-shortcode.php';

        $atts = [
            'lieu_id'      => $s['lieu_id']      ?? '',
            'show_stars'   => $s['show_stars']   ?? '1',
            'show_score'   => $s['show_score']   ?? '1',
            'show_count'   => $s['show_count']   ?? '1',
            'show_sources' => $s['show_sources'] ?? '',
            'star_color'   => $s['star_color']   ?? '#f5a623',
            'text_before'  => $s['text_before']  ?? '',
            'text_after'   => $s['text_after']   ?? '',
        ];

        $sc = new \SJ_Reviews\Front\InlineRatingShortcode();
        echo $sc->render($atts); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
