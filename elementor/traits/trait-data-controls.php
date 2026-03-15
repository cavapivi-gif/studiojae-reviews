<?php
namespace SJ_Reviews\Elementor\Traits;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Trait DataControls — Lieu, Source, and utility content controls.
 *
 * Provides reusable Elementor controls:
 * - Lieu/Source selectors (register_lieu_control, register_source_filter_control, register_lieu_ids_control)
 * - Utility switchers (register_show_control, register_toggle_text_control)
 *
 * @see trait-shared-controls.php for the aggregator trait.
 */
trait DataControls {

    /**
     * Register lieu selector control.
     *
     * @param array $opts {
     *     @type string   $default          Default value: 'linked_post'|'auto'|'all'|'lieu_xxx' (default: 'all')
     *     @type bool     $show_linked_post Add "Avis liés au post" option first (default: false)
     *     @type bool     $show_auto        Add "Auto (lieu de la page)" option (default: false)
     *     @type bool     $show_all         Add "Tous les lieux" option (default: true)
     *     @type string   $all_key          Key for "all" option (default: 'all')
     *     @type string   $all_label        Label for "all" option (default: 'Tous les lieux')
     *     @type array    $condition        Elementor condition array (default: [])
     * }
     */
    protected function register_lieu_control(array $opts = []): void {
        $default          = $opts['default']          ?? 'all';
        $show_linked_post = $opts['show_linked_post'] ?? false;
        $show_auto        = $opts['show_auto']        ?? false;
        $show_all         = $opts['show_all']         ?? true;
        $all_key          = $opts['all_key']          ?? 'all';
        $all_label        = $opts['all_label']        ?? __('Tous les lieux', 'sj-reviews');
        $condition        = $opts['condition']        ?? [];

        $lieux    = \SJ_Reviews\Includes\Settings::lieux();
        $lieu_map = [];

        // "Avis liés au post" — filtre via avis_linked_post (méta CPT) + enriched stats auto
        if ($show_linked_post) {
            $lieu_map['linked_post'] = __('Avis liés à ce post', 'sj-reviews');
        }
        if ($show_auto) {
            $lieu_map['auto'] = __('Auto — lieux de la page (metabox)', 'sj-reviews');
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
            'label'       => __('Lieu / Source', 'sj-reviews'),
            'type'        => Controls_Manager::SELECT,
            'options'     => $lieu_map,
            'default'     => $default,
        ];
        if ($show_linked_post) {
            $control['description'] = __('« Avis liés à ce post » utilise les lieux de la metabox pour le total enrichi.', 'sj-reviews');
        }
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

    /**
     * Register a SWITCHER control (show/hide toggle).
     *
     * Replaces the 5-line boilerplate for every show_X control.
     *
     * @param string $id         Control ID (e.g. 'show_stars').
     * @param string $label      Display label.
     * @param string $default    Default value: 'yes'|'1'|'' (default: 'yes').
     * @param array  $extras     Extra args merged into the control (condition, separator, description, return_value…).
     */
    protected function register_show_control(string $id, string $label, string $default = 'yes', array $extras = []): void {
        $return_value = $extras['return_value'] ?? $default ?: 'yes';
        unset($extras['return_value']);

        $this->add_control($id, array_merge([
            'label'        => __($label, 'sj-reviews'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => $return_value,
            'default'      => $default,
        ], $extras));
    }

    /**
     * Register a SWITCHER + TEXT pair (toggle + conditional text field).
     *
     * Common pattern: show_X toggle → X_label/X_text field (visible when toggle is on).
     *
     * @param string $prefix         Control prefix (e.g. 'certified' → show_certified + certified_label).
     * @param string $toggle_label   Label for the switcher.
     * @param string $text_label     Label for the text field.
     * @param string $text_default   Default text value.
     * @param string $toggle_default Toggle default: 'yes'|'1'|'' (default: 'yes').
     * @param array  $extras         Extra args for the switcher (condition, separator…).
     * @param string $text_suffix    Suffix for text control ID (default: 'label' → {prefix}_label).
     */
    protected function register_toggle_text_control(
        string $prefix,
        string $toggle_label,
        string $text_label,
        string $text_default,
        string $toggle_default = 'yes',
        array  $extras = [],
        string $text_suffix = 'label'
    ): void {
        $show_id = 'show_' . $prefix;
        $text_id = $prefix . '_' . $text_suffix;
        $return_value = $extras['return_value'] ?? $toggle_default ?: 'yes';

        $toggle_extras = $extras;
        unset($toggle_extras['return_value']);

        $this->register_show_control($show_id, $toggle_label, $toggle_default, array_merge(
            ['return_value' => $return_value],
            $toggle_extras
        ));

        $text_condition = [$show_id => $return_value];
        if (!empty($extras['condition'])) {
            $text_condition = array_merge($extras['condition'], $text_condition);
        }

        $this->add_control($text_id, [
            'label'     => __($text_label, 'sj-reviews'),
            'type'      => Controls_Manager::TEXT,
            'default'   => $text_default,
            'condition' => $text_condition,
        ]);
    }
}
