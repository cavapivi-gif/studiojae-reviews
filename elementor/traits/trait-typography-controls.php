<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit;

/**
 * Trait TypographyControls — Typography + separator style sections.
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait TypographyControls {

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
}
