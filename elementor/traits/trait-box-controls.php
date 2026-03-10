<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit;

/**
 * Trait BoxControls — Box, hover-box, and layout style sections.
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait BoxControls {

    /**
     * Register a box styling section (container, card, etc.).
     */
    protected function register_box_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_box", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => "{$prefix}_background",
                'types'    => ['classic', 'gradient'],
                'selector' => $selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_border",
                'selector' => $selector,
            ]
        );

        $this->add_responsive_control("{$prefix}_border_radius", [
            'label'      => 'Rayon de bordure',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'default'    => isset($defaults['radius'])
                ? ['top' => $defaults['radius'], 'right' => $defaults['radius'], 'bottom' => $defaults['radius'], 'left' => $defaults['radius'], 'unit' => 'px', 'isLinked' => true]
                : [],
            'selectors'  => [$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_padding", [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_margin", [
            'label'      => 'Marge externe',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [$selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => "{$prefix}_shadow",
                'selector' => $selector,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register a box with Normal/Hover tabs for background, border, shadow.
     */
    protected function register_box_hover_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $hover = $defaults['hover_selector'] ?? ($selector . ':hover');

        $this->start_controls_section("section_{$prefix}_box", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control("{$prefix}_border_radius", [
            'label'      => 'Rayon de bordure',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'default'    => isset($defaults['radius'])
                ? ['top' => $defaults['radius'], 'right' => $defaults['radius'], 'bottom' => $defaults['radius'], 'left' => $defaults['radius'], 'unit' => 'px', 'isLinked' => true]
                : [],
            'selectors'  => [$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_padding", [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control("{$prefix}_transition", [
            'label'      => 'Transition (ms)',
            'type'       => Controls_Manager::SLIDER,
            'range'      => ['px' => ['min' => 0, 'max' => 1000, 'step' => 50]],
            'default'    => ['size' => $defaults['transition'] ?? 300],
            'selectors'  => [$selector => 'transition: all {{SIZE}}ms ease;'],
        ]);

        $this->add_control("{$prefix}_tabs_hr", [
            'type' => Controls_Manager::DIVIDER,
        ]);

        $this->start_controls_tabs("{$prefix}_tabs");

        // Normal
        $this->start_controls_tab("{$prefix}_tab_normal", ['label' => 'Normal']);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => "{$prefix}_bg_normal",
                'types'    => ['classic', 'gradient'],
                'selector' => $selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_border_normal",
                'selector' => $selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => "{$prefix}_shadow_normal",
                'selector' => $selector,
            ]
        );

        $this->end_controls_tab();

        // Hover
        $this->start_controls_tab("{$prefix}_tab_hover", ['label' => 'Survol']);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => "{$prefix}_bg_hover",
                'types'    => ['classic', 'gradient'],
                'selector' => $hover,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_border_hover",
                'selector' => $hover,
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => "{$prefix}_shadow_hover",
                'selector' => $hover,
            ]
        );

        $this->add_control("{$prefix}_hover_transform", [
            'label'     => 'Effet au survol',
            'type'      => Controls_Manager::SELECT,
            'default'   => $defaults['hover_transform'] ?? '',
            'options'   => [
                ''                   => 'Aucun',
                'translateY(-2px)'   => 'Lever légèrement',
                'translateY(-4px)'   => 'Lever',
                'scale(1.02)'       => 'Zoom léger',
                'scale(1.05)'       => 'Zoom',
            ],
            'selectors' => [$hover => 'transform: {{VALUE}};'],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register layout spacing controls (gap, direction).
     */
    protected function register_layout_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_layout", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control("{$prefix}_gap", [
            'label'      => 'Espacement (gap)',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em'],
            'range'      => ['px' => ['min' => 0, 'max' => 60]],
            'default'    => ['size' => $defaults['gap'] ?? 16, 'unit' => 'px'],
            'selectors'  => [$selector => 'gap: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_justify", [
            'label'   => 'Alignement horizontal',
            'type'    => Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => 'Début', 'icon' => 'eicon-align-start-h'],
                'center'     => ['title' => 'Centre', 'icon' => 'eicon-align-center-h'],
                'flex-end'   => ['title' => 'Fin', 'icon' => 'eicon-align-end-h'],
                'space-between' => ['title' => 'Espacé', 'icon' => 'eicon-align-stretch-h'],
            ],
            'default'   => $defaults['justify'] ?? '',
            'selectors' => [$selector => 'justify-content: {{VALUE}};'],
        ]);

        $this->add_responsive_control("{$prefix}_valign", [
            'label'   => 'Alignement vertical',
            'type'    => Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => 'Haut', 'icon' => 'eicon-align-start-v'],
                'center'     => ['title' => 'Centre', 'icon' => 'eicon-align-center-v'],
                'flex-end'   => ['title' => 'Bas', 'icon' => 'eicon-align-end-v'],
            ],
            'default'   => $defaults['valign'] ?? 'center',
            'selectors' => [$selector => 'align-items: {{VALUE}};'],
        ]);

        $this->end_controls_section();
    }
}
