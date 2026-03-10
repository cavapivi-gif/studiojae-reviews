<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

defined('ABSPATH') || exit;

/**
 * Trait InteractiveControls — Button and pill/tag style sections with state tabs.
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait InteractiveControls {

    /**
     * Register button style controls (Normal/Hover tabs).
     */
    protected function register_button_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $hover = $selector . ':hover';

        $this->start_controls_section("section_{$prefix}_btn", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => "{$prefix}_typography",
                'selector' => $selector,
            ]
        );

        $this->add_responsive_control("{$prefix}_padding", [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => [$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_border_radius", [
            'label'      => 'Rayon de bordure',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control("{$prefix}_transition", [
            'label'      => 'Transition (ms)',
            'type'       => Controls_Manager::SLIDER,
            'range'      => ['px' => ['min' => 0, 'max' => 1000, 'step' => 50]],
            'default'    => ['size' => $defaults['transition'] ?? 200],
            'selectors'  => [$selector => 'transition: all {{SIZE}}ms ease;'],
        ]);

        $this->add_control("{$prefix}_btn_hr", [
            'type' => Controls_Manager::DIVIDER,
        ]);

        $this->start_controls_tabs("{$prefix}_btn_tabs");

        $this->start_controls_tab("{$prefix}_btn_normal", ['label' => 'Normal']);

        $this->add_control("{$prefix}_btn_color", [
            'label'     => 'Couleur du texte',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['color'] ?? '',
            'selectors' => [$selector => 'color: {{VALUE}};'],
        ]);

        $this->add_control("{$prefix}_btn_bg", [
            'label'     => 'Fond',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['bg'] ?? '',
            'selectors' => [$selector => 'background-color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_btn_border",
                'selector' => $selector,
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab("{$prefix}_btn_hover", ['label' => 'Survol']);

        $this->add_control("{$prefix}_btn_hover_color", [
            'label'     => 'Couleur du texte',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$hover => 'color: {{VALUE}};'],
        ]);

        $this->add_control("{$prefix}_btn_hover_bg", [
            'label'     => 'Fond',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$hover => 'background-color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => "{$prefix}_btn_border_hover",
                'selector' => $hover,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register pill/tag/filter controls with three states (Normal/Hover/Active).
     */
    protected function register_pill_controls(
        string $prefix,
        string $label,
        string $selector,
        string $active_selector,
        array $defaults = []
    ): void {
        $hover = $selector . ':hover';

        $this->start_controls_section("section_{$prefix}_pill", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => "{$prefix}_pill_typo",
                'selector' => $selector,
            ]
        );

        $this->add_responsive_control("{$prefix}_pill_padding", [
            'label'      => 'Padding',
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => [$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_pill_radius", [
            'label'      => 'Rayon',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'default'    => ['size' => $defaults['radius'] ?? 20, 'unit' => 'px'],
            'selectors'  => [$selector => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->start_controls_tabs("{$prefix}_pill_tabs");

        $this->start_controls_tab("{$prefix}_pill_normal", ['label' => 'Normal']);
        $this->add_control("{$prefix}_pill_color", [
            'label'     => 'Couleur',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$selector => 'color: {{VALUE}};'],
        ]);
        $this->add_control("{$prefix}_pill_bg", [
            'label'     => 'Fond',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$selector => 'background-color: {{VALUE}};'],
        ]);
        $this->add_group_control(
            Group_Control_Border::get_type(),
            ['name' => "{$prefix}_pill_border", 'selector' => $selector]
        );
        $this->end_controls_tab();

        $this->start_controls_tab("{$prefix}_pill_hover", ['label' => 'Survol']);
        $this->add_control("{$prefix}_pill_hover_color", [
            'label'     => 'Couleur',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$hover => 'color: {{VALUE}};'],
        ]);
        $this->add_control("{$prefix}_pill_hover_bg", [
            'label'     => 'Fond',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$hover => 'background-color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab("{$prefix}_pill_active", ['label' => 'Actif']);
        $this->add_control("{$prefix}_pill_active_color", [
            'label'     => 'Couleur',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$active_selector => 'color: {{VALUE}};'],
        ]);
        $this->add_control("{$prefix}_pill_active_bg", [
            'label'     => 'Fond',
            'type'      => Controls_Manager::COLOR,
            'selectors' => [$active_selector => 'background-color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }
}
