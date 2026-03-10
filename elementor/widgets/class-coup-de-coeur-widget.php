<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Elementor Widget — Coup de cœur (style Airbnb).
 *
 * Affiche une bannière "Coup de cœur voyageurs" sur les posts
 * dont le champ ACF `best_seller` est activé, avec la note moyenne
 * et le nombre d'avis liés au post.
 */
class CoupDeCoeurWidget extends Widget_Base {

    public function get_name():  string { return 'sj_coup_de_coeur'; }
    public function get_title(): string { return 'SJ — Coup de cœur'; }
    public function get_icon():  string { return 'eicon-heart'; }
    public function get_categories(): array { return ['sj-reviews']; }
    public function get_keywords(): array { return ['coup de coeur', 'best seller', 'favori', 'airbnb', 'badge', 'avis', 'sj']; }

    protected function register_controls(): void {

        // ── Section Contenu ─────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => 'Contenu',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('post_id', [
            'label'       => 'Post ID',
            'description' => 'Laisser vide pour utiliser le post courant.',
            'type'        => Controls_Manager::NUMBER,
            'default'     => 0,
        ]);

        $this->add_control('label', [
            'label'   => 'Texte du badge',
            'type'    => Controls_Manager::TEXT,
            'default' => 'Coup de cœur voyageurs',
        ]);

        $this->add_control('subtitle', [
            'label'   => 'Description',
            'type'    => Controls_Manager::TEXT,
            'default' => 'Un des logements préférés des voyageurs',
        ]);

        $this->add_control('star_color', [
            'label'   => 'Couleur des étoiles',
            'type'    => Controls_Manager::COLOR,
            'default' => '#222222',
        ]);

        $this->end_controls_section();

        // ── Section Style ───────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => 'Style',
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('bg_color', [
            'label'     => 'Couleur de fond',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .sj-cdc' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('border_color', [
            'label'     => 'Couleur de bordure',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e0e0e0',
            'selectors' => ['{{WRAPPER}} .sj-cdc' => 'border-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('border_radius', [
            'label'      => 'Rayon de bordure',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 24]],
            'default'    => ['size' => 12, 'unit' => 'px'],
            'selectors'  => ['{{WRAPPER}} .sj-cdc' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control('padding', [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => ['{{WRAPPER}} .sj-cdc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('text_color', [
            'label'     => 'Couleur du texte',
            'type'      => Controls_Manager::COLOR,
            'default'   => '#222222',
            'selectors' => ['{{WRAPPER}} .sj-cdc' => 'color: {{VALUE}};'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();

        require_once SJ_REVIEWS_DIR . 'front/class-coup-de-coeur-shortcode.php';

        $atts = [
            'post_id'    => $s['post_id']    ?? 0,
            'star_color' => $s['star_color'] ?? '#222222',
            'label'      => $s['label']      ?? 'Coup de cœur voyageurs',
            'subtitle'   => $s['subtitle']   ?? 'Un des logements préférés des voyageurs',
        ];

        $sc = new \SJ_Reviews\Front\CoupDeCoeurShortcode();
        $output = $sc->render($atts);

        if (empty($output) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="sj-cdc sj-cdc--placeholder" style="padding:20px;border:2px dashed #ccc;border-radius:12px;text-align:center;color:#999;">';
            echo 'Widget Coup de cœur — Activez le champ <strong>best_seller</strong> sur le post et liez des avis pour voir le rendu.';
            echo '</div>';
            return;
        }

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
