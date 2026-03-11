<?php
namespace SJ_Reviews\Elementor\Traits;

defined('ABSPATH') || exit;

/**
 * Trait SharedControls — Aggregator that imports all sub-traits.
 *
 * This trait combines all specialized control traits into a single `use SharedControls;`
 * for backward compatibility. Widgets can also use individual sub-traits directly
 * for a lighter footprint.
 *
 * Sub-traits:
 *   - TypographyControls:  register_typography_controls(), register_separator_controls()
 *   - BoxControls:         register_box_controls(), register_box_hover_controls(), register_layout_controls()
 *   - InteractiveControls: register_button_controls(), register_pill_controls()
 *   - MediaControls:       register_avatar_controls(), register_stars_controls(), register_bar_controls()
 *   - DataControls:        register_lieu_control(), register_source_filter_control(), register_lieu_ids_control()
 *
 * @see CLAUDE.md for full architecture guide.
 */
trait SharedControls {

    use TypographyControls;
    use BoxControls;
    use InteractiveControls;
    use MediaControls;
    use DataControls;
}
