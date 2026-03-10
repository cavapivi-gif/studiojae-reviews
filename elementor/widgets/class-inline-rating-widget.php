<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

/**
 * Elementor Widget — SJ Inline Rating.
 *
 * Refactored to use SjWidgetBase + SharedControls.
 * Previously: 3 basic style controls. Now: full typography, stars, layout.
 */
class InlineRatingWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'       => 'sj_inline_rating',
            'title'    => 'SJ — Note inline',
            'icon'     => 'eicon-star',
            'keywords' => ['rating', 'inline', 'note', 'étoiles', 'avis', 'sj'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this->selectors = array_merge($this->selectors, [
            'container' => '{{WRAPPER}} .sj-inline-rating',
            'score'     => '{{WRAPPER}} .sj-inline-rating__score',
            'count'     => '{{WRAPPER}} .sj-inline-rating__count',
            'stars'     => '{{WRAPPER}} .sj-inline-rating .sj-badge__stars',
            'source'    => '{{WRAPPER}} .sj-inline-rating__source',
            'text'      => '{{WRAPPER}} .sj-inline-rating__text',
        ]);
    }

    protected function register_controls(): void {
        $lieux = \SJ_Reviews\Includes\Settings::lieux();
        $opts  = ['' => 'Tous les lieux'];
        foreach ($lieux as $l) {
            $opts[$l['id']] = esc_html(($l['name'] ?? $l['id']) . ' (' . ($l['source'] ?? '') . ')');
        }

        // ── CONTENT TAB ─────────────────────────────────────────────────────

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

        // ── STYLE TAB — shared controls ─────────────────────────────────────

        // Container typography + color
        $this->register_typography_controls(
            'inline', 'Texte général',
            $this->sel('container'),
            ['color' => '']
        );

        // Layout (gap, alignment)
        $this->register_layout_controls(
            'inline', 'Disposition',
            $this->sel('container'),
            ['gap' => 6]
        );

        // Stars
        $this->register_stars_controls(
            'inline_stars', 'Étoiles',
            $this->sel('stars'),
            ['color' => '#f5a623', 'size' => 14]
        );
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
