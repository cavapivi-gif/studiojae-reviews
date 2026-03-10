<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

defined('ABSPATH') || exit;

/**
 * Trait SharedControls — Centralized Elementor control sections.
 *
 * Use inside any class that extends \Elementor\Widget_Base (or SjWidgetBase).
 * Each method registers a complete control SECTION with consistent naming.
 *
 * Naming convention:
 *   - Control IDs:   {prefix}_{property}  (e.g. title_color, title_typography)
 *   - Section IDs:   section_{prefix}_style / section_{prefix}_box / etc.
 *   - Selectors:     passed as string, always use {{WRAPPER}} prefix
 *
 * @see CLAUDE.md for full architecture guide.
 */
trait SharedControls {

    /* =====================================================================
     * TYPOGRAPHY — font family, size, weight, transform, line-height, color
     * ================================================================== */

    /**
     * Register a complete typography control section.
     *
     * @param string $prefix    Unique prefix for control IDs.
     * @param string $label     Section label in Elementor panel.
     * @param string $selector  CSS selector (must include {{WRAPPER}}).
     * @param array  $defaults  Override: ['color','align','hover_color'].
     */
    protected function register_typography_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_style", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => "{$prefix}_typography",
                'label'    => 'Typographie',
                'selector' => $selector,
            ]
        );

        $this->add_control("{$prefix}_color", [
            'label'     => 'Couleur',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['color'] ?? '',
            'selectors' => [$selector => 'color: {{VALUE}};'],
        ]);

        if (isset($defaults['hover_color']) || ($defaults['hover'] ?? false)) {
            $this->add_control("{$prefix}_hover_color", [
                'label'     => 'Couleur au survol',
                'type'      => Controls_Manager::COLOR,
                'default'   => $defaults['hover_color'] ?? '',
                'selectors' => [$selector . ':hover' => 'color: {{VALUE}};'],
            ]);
        }

        if ($defaults['align'] ?? false) {
            $this->add_responsive_control("{$prefix}_align", [
                'label'   => 'Alignement',
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
                    'center' => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
                    'right'  => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
                ],
                'default'   => $defaults['align'],
                'selectors' => [$selector => 'text-align: {{VALUE}};'],
            ]);
        }

        $this->add_responsive_control("{$prefix}_spacing", [
            'label'      => 'Marge inférieure',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em'],
            'range'      => ['px' => ['min' => 0, 'max' => 60]],
            'selectors'  => [$selector => 'margin-bottom: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    /* =====================================================================
     * BOX — background, border, radius, padding, shadow
     * ================================================================== */

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

    /* =====================================================================
     * BOX WITH HOVER TABS — Normal + Hover states (like Rey cards)
     * ================================================================== */

    /**
     * Register a box with Normal/Hover tabs for background, border, shadow.
     *
     * @param string $selector       CSS selector for the element.
     * @param string $hover_selector CSS selector for hover (default: $selector . ':hover').
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

        // Shared (non-tabbed) controls
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

        // ── Normal / Hover tabs ──
        $this->start_controls_tabs("{$prefix}_tabs");

        // Normal
        $this->start_controls_tab("{$prefix}_tab_normal", [
            'label' => 'Normal',
        ]);

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
        $this->start_controls_tab("{$prefix}_tab_hover", [
            'label' => 'Survol',
        ]);

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

    /* =====================================================================
     * BUTTON — typography, colors with Normal/Hover, border, padding
     * ================================================================== */

    /**
     * Register button style controls (like Rey's button pattern).
     *
     * @param string $selector  CSS selector for the button/link element.
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

        // ── Normal / Hover tabs ──
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

    /* =====================================================================
     * AVATAR / IMAGE — size, border-radius, object-fit, border
     * ================================================================== */

    /**
     * Register image/avatar style controls.
     *
     * @param string $selector  CSS selector for the img or container.
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

    /* =====================================================================
     * STARS — color, size, gap
     * ================================================================== */

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

    /* =====================================================================
     * SEPARATOR — width, height, color
     * ================================================================== */

    /**
     * Register separator/divider style controls.
     */
    protected function register_separator_controls(
        string $prefix,
        string $label,
        string $selector,
        array $defaults = []
    ): void {
        $this->start_controls_section("section_{$prefix}_sep", [
            'label' => $label,
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control("{$prefix}_sep_color", [
            'label'     => 'Couleur',
            'type'      => Controls_Manager::COLOR,
            'default'   => $defaults['color'] ?? '#e0e0e0',
            'selectors' => [$selector => 'background-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control("{$prefix}_sep_width", [
            'label'      => 'Épaisseur',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 4]],
            'default'    => ['size' => $defaults['width'] ?? 1, 'unit' => 'px'],
            'selectors'  => [$selector => 'width: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control("{$prefix}_sep_height", [
            'label'      => 'Hauteur',
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 10, 'max' => 80]],
            'default'    => ['size' => $defaults['height'] ?? 40, 'unit' => 'px'],
            'selectors'  => [$selector => 'height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    /* =====================================================================
     * LAYOUT — gap, alignment for flex/grid containers
     * ================================================================== */

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

    /* =====================================================================
     * PROGRESS BAR — fill color, track color, height, radius
     * ================================================================== */

    /**
     * Register progress/distribution bar controls.
     *
     * @param string $fill_selector   CSS selector for the filled portion.
     * @param string $track_selector  CSS selector for the track/background.
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

    /* =====================================================================
     * PILL/TAG — bg, color, radius, padding with Normal/Hover/Active tabs
     * ================================================================== */

    /**
     * Register pill/tag/filter controls with three states.
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

        // ── 3-state tabs ──
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
