<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Trait DataControls — Lieu & Source filter controls.
 *
 * Provides reusable Elementor controls for selecting lieu(x) and source(s).
 * Used by InlineRating, Reviews, Summary, RatingBadge widgets.
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait DataControls {

    /**
     * Register lieu selector control.
     *
     * @param array $opts {
     *     @type string   $default     Default value: ''|'all'|'auto' (default: 'all')
     *     @type bool     $show_auto   Add "Auto (lieu de la page)" option (default: false)
     *     @type bool     $show_all    Add "Tous les lieux" option (default: true)
     *     @type string   $all_key     Key for "all" option: '' or 'all' (default: matches $default)
     *     @type string   $all_label   Label for "all" option (default: 'Tous les lieux')
     *     @type array    $condition   Elementor condition array (default: [])
     * }
     */
    protected function register_lieu_control(array $opts = []): void {
        $default   = $opts['default']   ?? 'all';
        $show_auto = $opts['show_auto'] ?? false;
        $show_all  = $opts['show_all']  ?? true;
        $all_key   = $opts['all_key']   ?? $default;
        $all_label = $opts['all_label'] ?? __('Tous les lieux', 'sj-reviews');
        $condition = $opts['condition'] ?? [];

        $lieux    = \SJ_Reviews\Includes\Settings::lieux();
        $lieu_map = [];

        if ($show_auto) {
            $lieu_map['auto'] = __('Auto (lieu de la page)', 'sj-reviews');
        }
        if ($show_all) {
            $lieu_map[$all_key] = $all_label;
        }
        foreach ($lieux as $l) {
            $label = esc_html(($l['name'] ?? $l['id']) . ' (' . ($l['source'] ?? '') . ')');
            if (!($l['active'] ?? true)) {
                $label .= ' (inactif)';
            }
            $lieu_map[$l['id']] = $label;
        }

        $control = [
            'label'   => __('Lieu', 'sj-reviews'),
            'type'    => Controls_Manager::SELECT,
            'options' => $lieu_map,
            'default' => $default,
        ];
        if (!empty($condition)) {
            $control['condition'] = $condition;
        }

        $this->add_control('lieu_id', $control);
    }

    /**
     * Register source filter control (SELECT2, multiple).
     *
     * @param array $opts {
     *     @type array  $condition  Elementor condition array (default: [])
     * }
     */
    protected function register_source_filter_control(array $opts = []): void {
        $condition = $opts['condition'] ?? [];

        $control = [
            'label'       => __('Filtrer par source(s)', 'sj-reviews'),
            'type'        => Controls_Manager::SELECT2,
            'multiple'    => true,
            'options'     => \SJ_Reviews\Includes\Labels::SOURCES,
            'default'     => [],
            'description' => __('Laisser vide = toutes les sources', 'sj-reviews'),
        ];
        if (!empty($condition)) {
            $control['condition'] = $condition;
        }

        $this->add_control('source_filter', $control);
    }

    /**
     * Register multi-lieu filter control (SELECT2, multiple).
     */
    protected function register_lieu_ids_control(): void {
        $lieux    = \SJ_Reviews\Includes\Settings::lieux();
        $lieu_map = [];
        foreach ($lieux as $l) {
            $label = esc_html(($l['name'] ?? $l['id']) . ' (' . ($l['source'] ?? '') . ')');
            $lieu_map[$l['id']] = $label;
        }

        $this->add_control('lieu_ids', [
            'label'       => __('Filtrer par lieu(x)', 'sj-reviews'),
            'type'        => Controls_Manager::SELECT2,
            'multiple'    => true,
            'options'     => $lieu_map,
            'default'     => [],
            'description' => __('Laisser vide = tous les lieux', 'sj-reviews'),
        ]);
    }
}
