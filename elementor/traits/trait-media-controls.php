<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit;

/**
 * Trait MediaControls — Avatar/image, stars, and progress bar style sections.
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait MediaControls {

    /**
     * Register image/avatar style controls.
     */
    protected function register_avatar_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_avatar", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control("{$prefix}_size", [
            'label'      => 'Taille',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 16, 'max' => 120]],
            'default'    => ['size' => $defaults['size'] ?? 40, 'unit' => 'px'],
            'selectors'  => [
                $selector => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control("{$prefix}_radius", [
            'label'      => 'Rayon de bordure',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'default'    => isset($defaults['radius'])
                ? ['top' => $defaults['radius'], 'right' => $defaults['radius'], 'bottom' => $defaults['radius'], 'left' => $defaults['radius'], 'unit' => '%', 'isLinked' => true]
                : ['top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%', 'isLinked' => true],
            'selectors'  => [$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control("{$prefix}_fit", [
            'label'   => 'Ajustement',
            'type'    => Controls_Manager::SELECT,
            'default' => $defaults['fit'] ?? 'cover',
            'options' => [
                'cover'   => 'Couvrir',
                'contain' => 'Contenir',
                'fill'    => 'Remplir',
            ],
            'selectors' => [$selector => 'object-fit: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_img_border",
                'selector' => $selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => "{$prefix}_img_shadow",
                'selector' => $selector,
            ]
        );

        $this->add_responsive_control("{$prefix}_spacing", [
            'label'      => 'Espacement',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'selectors'  => [$selector => 'margin-right: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    /**
     * Register star rating style controls.
     */
    protected function register_stars_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_stars", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control("{$prefix}_star_color", [
            'label'   => 'Couleur des étoiles',
            'type'    => Controls_Manager::COLOR,
            'default' => $defaults['color'] ?? '#222222',
        ]);

        $this->add_control("{$prefix}_star_empty_color", [
            'label'   => 'Couleur vide',
            'type'    => Controls_Manager::COLOR,
            'default' => $defaults['empty_color'] ?? '#d1d5db',
        ]);

        $this->add_responsive_control("{$prefix}_star_size", [
            'label'      => 'Taille',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 8, 'max' => 32]],
            'default'    => ['size' => $defaults['size'] ?? 10, 'unit' => 'px'],
            'selectors'  => [
                $selector . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control("{$prefix}_star_gap", [
            'label'      => 'Espacement',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 8]],
            'default'    => ['size' => $defaults['gap'] ?? 1, 'unit' => 'px'],
            'selectors'  => [$selector => 'gap: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    /**
     * Register progress/distribution bar controls.
     */
    protected function register_bar_controls(
        string $prefix,
        string $label,
        string $fill_selector,
        string $track_selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_bar", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control("{$prefix}_fill_color", [
            'label'     => 'Couleur de remplissage',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['fill'] ?? '#222222',
            'selectors' => [$fill_selector => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control("{$prefix}_track_color", [
            'label'     => 'Couleur de piste',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['track'] ?? '#e0e0e0',
            'selectors' => [$track_selector => 'background-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control("{$prefix}_bar_height", [
            'label'      => 'Hauteur',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 2, 'max' => 20]],
            'default'    => ['size' => $defaults['height'] ?? 6, 'unit' => 'px'],
            'selectors'  => [
                $fill_selector  => 'height: {{SIZE}}{{UNIT}};',
                $track_selector => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control("{$prefix}_bar_radius", [
            'label'      => 'Rayon',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 10]],
            'default'    => ['size' => $defaults['radius'] ?? 3, 'unit' => 'px'],
            'selectors'  => [
                $fill_selector  => 'border-radius: {{SIZE}}{{UNIT}};',
                $track_selector => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }
}
