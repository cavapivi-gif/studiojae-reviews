<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Widget Elementor « Résumé Avis » — style TripAdvisor.
 *
 * Affiche : note globale, répartition étoiles et sous-critères.
 * Détecte automatiquement le lieu de la page en mode « Auto ».
 */
class SummaryWidget extends Widget_Base {

    public function get_name(): string  { return 'sj_summary'; }
    public function get_title(): string { return __('Résumé Avis', 'sj-reviews'); }
    public function get_icon(): string  { return 'eicon-star-o'; }

    public function get_categories(): array { return ['sj-reviews']; }

    protected function register_controls(): void {
        // ── Contenu ────────────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Contenu', 'sj-reviews'),
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

        $this->add_control('show_distribution', [
            'label'        => __('Afficher les barres de répartition', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->add_control('show_criteria', [
            'label'        => __('Afficher les sous-critères', 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '1',
            'default'      => '1',
        ]);

        $this->end_controls_section();

        // ── Style : couleurs ──────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => __('Couleurs', 'sj-reviews'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('color_bubble', [
            'label'   => __('Couleur bulles', 'sj-reviews'),
            'type'    => Controls_Manager::COLOR,
            'default' => '#34d399',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__bubble--full,
                 {{WRAPPER}} .sj-summary__dist-fill' => 'background: {{VALUE}};',
            ],
        ]);

        $this->add_control('color_criteria', [
            'label'   => __('Couleur barres critères', 'sj-reviews'),
            'type'    => Controls_Manager::COLOR,
            'default' => '#6366f1',
            'selectors' => [
                '{{WRAPPER}} .sj-summary__crit-fill' => 'background: {{VALUE}};',
            ],
        ]);

        $this->add_control('color_bg', [
            'label'   => __('Fond du widget', 'sj-reviews'),
            'type'    => Controls_Manager::COLOR,
            'default' => '#ffffff',
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
            'lieu_id'           => $s['lieu_id']           ?? 'auto',
            'show_distribution' => $s['show_distribution'] ?? '1',
            'show_criteria'     => $s['show_criteria']     ?? '1',
        ]);
    }
}
