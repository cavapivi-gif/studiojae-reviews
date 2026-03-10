# SJ Reviews — Architecture Guide for AI Agents

> This file guides any AI (Claude, GPT, Copilot...) to build, maintain, and extend
> Elementor widgets for this plugin — and any plugin following the same pattern.

---

## Project Overview

**Type:** WordPress plugin (Elementor + ACF integration)
**Namespace:** `SJ_Reviews`
**CPT:** `sj_avis` (reviews linked to posts via `avis_linked_post`)
**Entry point:** `studiojae-reviews.php` → `core/class-plugin.php`

---

## Directory Structure

```
studiojae-reviews/
├── core/
│   └── class-plugin.php              # Bootstraps everything
├── elementor/
│   ├── class-widget-base.php         # SjWidgetBase — abstract base for ALL widgets
│   ├── class-elementor-manager.php   # Registers widget category
│   ├── traits/
│   │   └── trait-shared-controls.php # SharedControls — reusable control sections
│   └── widgets/
│       ├── class-coup-de-coeur-widget.php  # EXAMPLE: uses SjWidgetBase + SharedControls
│       ├── class-reviews-widget.php        # Legacy (extends Widget_Base directly)
│       ├── class-rating-badge-widget.php   # Legacy
│       ├── class-summary-widget.php        # Legacy
│       └── class-inline-rating-widget.php  # Legacy
├── front/
│   ├── class-*-shortcode.php         # Shortcode classes (render logic)
│   └── assets/
│       ├── sj-*.css                  # Per-feature CSS (layout defaults only)
│       └── sj-*.js                   # Front-end JS
├── post-types/
│   ├── class-avis-cpt.php           # CPT + ACF field registration
│   └── class-lieu-metabox.php       # Location meta box
├── includes/
│   ├── helpers.php                   # sj_get_reviews(), sj_aggregate(), sj_stars_html()
│   ├── class-widget.php             # Classic WP_Widget
│   └── class-cron.php               # Auto-sync scheduling
└── admin/
    └── backoffice/                   # React admin dashboard + REST API
```

---

## How to Create a New Elementor Widget

### Step 1: Create the shortcode (rendering logic)

File: `front/class-{feature}-shortcode.php`

```php
<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

class FeatureShortcode {
    public function init(): void {
        add_shortcode('sj_feature', [$this, 'render']);
    }

    public function render(array $atts = []): string {
        $a = shortcode_atts([
            'post_id' => 0,
            // ... attributes matching Elementor widget controls
        ], $atts, 'sj_feature');

        ob_start();
        // HTML output using BEM classes: .sj-feature, .sj-feature__title, etc.
        return ob_get_clean();
    }
}
```

### Step 2: Create the Elementor widget

File: `elementor/widgets/class-{feature}-widget.php`

```php
<?php
namespace SJ_Reviews\Elementor\Widgets;

use Elementor\Controls_Manager;
use SJ_Reviews\Elementor\SjWidgetBase;
use SJ_Reviews\Elementor\Traits\SharedControls;

defined('ABSPATH') || exit;

class FeatureWidget extends SjWidgetBase {

    use SharedControls;

    protected static function get_sj_config(): array {
        return [
            'id'       => 'sj_feature',
            'title'    => 'SJ — Feature Name',
            'icon'     => 'eicon-star',
            'keywords' => ['feature', 'avis', 'sj'],
            'css'      => ['sj-feature'],        // style handle from wp_enqueue
            'categories' => ['sj-reviews'],
        ];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        // Define selector dictionary for this widget
        $this->selectors = array_merge($this->selectors, [
            'container' => '{{WRAPPER}} .sj-feature',
            'title'     => '{{WRAPPER}} .sj-feature__title',
            'subtitle'  => '{{WRAPPER}} .sj-feature__subtitle',
            'stars'     => '{{WRAPPER}} .sj-feature__stars',
        ]);
    }

    protected function register_controls(): void {
        // ── CONTENT TAB (custom per widget) ────────────────
        $this->start_controls_section('section_content', [
            'label' => 'Contenu',
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);
        // ... widget-specific content controls
        $this->end_controls_section();

        // ── STYLE TAB (shared controls from trait) ─────────
        $this->register_box_controls('container', 'Conteneur', $this->sel('container'));
        $this->register_typography_controls('title', 'Titre', $this->sel('title'));
        $this->register_typography_controls('subtitle', 'Sous-titre', $this->sel('subtitle'));
        $this->register_stars_controls('stars', 'Étoiles', $this->sel('stars'));
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();
        require_once SJ_REVIEWS_DIR . 'front/class-feature-shortcode.php';
        $sc = new \SJ_Reviews\Front\FeatureShortcode();
        echo $sc->render([/* map settings to shortcode atts */]);
    }
}
```

### Step 3: Create the CSS (layout defaults only)

File: `front/assets/sj-{feature}.css`

**Rules:**
- CSS provides LAYOUT (display, flex, grid) + FALLBACK styles for shortcode-only use
- Elementor controls override colors, fonts, spacing, borders, shadows via `{{WRAPPER}}`
- Use BEM naming: `.sj-feature`, `.sj-feature__title`, `.sj-feature__title--highlight`
- No `!important` — Elementor selectors have higher specificity via `{{WRAPPER}}`

### Step 4: Register in class-plugin.php

```php
// In shortcodes section:
require_once SJ_REVIEWS_DIR . 'front/class-feature-shortcode.php';
(new \SJ_Reviews\Front\FeatureShortcode())->init();

// In Elementor section (inside the closure):
require_once SJ_REVIEWS_DIR . 'elementor/widgets/class-feature-widget.php';
$manager->register(new \SJ_Reviews\Elementor\Widgets\FeatureWidget());

// In enqueue_front_assets():
wp_enqueue_style('sj-feature', SJ_REVIEWS_URL . 'front/assets/sj-feature.css', [], SJ_REVIEWS_VERSION);
```

---

## SharedControls Trait — Available Methods

Each method registers a complete Elementor SECTION (label + controls + selectors).
Call them in `register_controls()` with one line each.

| Method | What it adds | Controls included |
|--------|-------------|-------------------|
| `register_typography_controls($prefix, $label, $selector)` | Typography section | Font family, size, weight, transform, line-height, letter-spacing, color, hover color (opt), alignment (opt), bottom spacing |
| `register_box_controls($prefix, $label, $selector)` | Box/container section | Background (classic/gradient), border, border-radius, padding, margin, box-shadow |
| `register_box_hover_controls($prefix, $label, $selector)` | Box with **Normal/Hover tabs** | Border-radius, padding, transition timing, then tabs: background, border, shadow per state + hover transform effect |
| `register_button_controls($prefix, $label, $selector)` | Button with **Normal/Hover tabs** | Typography, padding, border-radius, transition, then tabs: text color, background, border per state |
| `register_avatar_controls($prefix, $label, $selector)` | Image/avatar section | Size, border-radius, object-fit, border, box-shadow, spacing |
| `register_stars_controls($prefix, $label, $selector)` | Star rating section | Star color, empty color, size, gap |
| `register_separator_controls($prefix, $label, $selector)` | Divider/separator section | Color, width (thickness), height |
| `register_layout_controls($prefix, $label, $selector)` | Flex layout section | Gap, justify-content, align-items |
| `register_bar_controls($prefix, $label, $fill_sel, $track_sel)` | Progress bar section | Fill color, track color, height, border-radius |
| `register_pill_controls($prefix, $label, $selector, $active_sel)` | Pill/tag with **3-state tabs** | Typography, padding, radius, then Normal/Hover/Active tabs: color, bg, border |

**All methods accept an optional `$defaults` array for initial values.**

### Defaults examples:
```php
// Typography with hover + alignment
$this->register_typography_controls('link', 'Lien', $sel, [
    'color' => '#222', 'hover_color' => '#0066cc', 'align' => 'center'
]);

// Box with hover effect
$this->register_box_hover_controls('card', 'Carte', $sel, [
    'radius' => 12, 'transition' => 300, 'hover_transform' => 'translateY(-2px)'
]);

// Button
$this->register_button_controls('load', 'Charger plus', $sel, [
    'color' => '#fff', 'bg' => '#222', 'transition' => 200
]);

// Avatar (circular by default)
$this->register_avatar_controls('author', 'Photo auteur', $sel, [
    'size' => 40, 'radius' => 50, 'fit' => 'cover'
]);
```

---

## SjWidgetBase — What It Provides

| Feature | How |
|---------|-----|
| `get_name()`, `get_title()`, `get_icon()`, etc. | Auto-derived from `get_sj_config()` |
| `$selectors` dictionary | Map logical names → CSS selectors with `{{WRAPPER}}` |
| `$this->sel('key')` | Shorthand to resolve a selector from the dictionary |
| `get_style_depends()` / `get_script_depends()` | From config `css` / `js` arrays |

---

## Key Conventions

### Naming
- **Widget IDs:** `sj_{feature}` (e.g. `sj_coup_de_coeur`, `sj_inline_rating`)
- **CSS classes:** `.sj-{feature}` with BEM: `__element`, `--modifier`
- **Control IDs:** `{prefix}_{property}` (e.g. `title_color`, `container_padding`)
- **Section IDs:** `section_{prefix}_style` or `section_{prefix}_box`
- **PHP classes:** PascalCase `FeatureWidget`, `FeatureShortcode`
- **Files:** `class-{feature}-widget.php`, `class-{feature}-shortcode.php`, `sj-{feature}.css`

### Selectors Strategy
- **Always use `{{WRAPPER}}`** — Elementor replaces it with the unique widget wrapper selector
- **Define once in `$selectors`**, reference everywhere via `$this->sel('key')`
- **CSS file = layout defaults**, Elementor controls = dynamic overrides
- When Elementor controls set a value, it generates inline CSS with `{{WRAPPER}}` specificity — automatically wins over the CSS file defaults

### Data Flow
```
ACF field (best_seller) → Shortcode checks field → Queries sj_avis CPT
    → sj_get_reviews() → sj_aggregate() → render HTML
```

Reviews are linked to posts via `avis_linked_post` meta key on `sj_avis` posts.

### Helper Functions (includes/helpers.php)
- `sj_get_reviews(array $args)` — WP_Query wrapper for sj_avis
- `sj_normalize_review(WP_Post)` — Normalize to array (ACF or post_meta)
- `sj_aggregate(array $reviews)` — Returns `['avg' => float, 'count' => int]`
- `sj_stars_html(int $rating, int $max, string $color)` — SVG stars
- `sj_relative_date(string|int)` — French relative date
- `sj_source_icon(string)` — Source SVG icon

---

## Adding SharedControls to Legacy Widgets

To migrate an existing widget to use the shared system:

1. Change `extends \Elementor\Widget_Base` to `extends SjWidgetBase`
2. Add `use SharedControls;`
3. Add `get_sj_config()` static method
4. Define `$selectors` in constructor
5. Replace manual control sections with trait method calls
6. Remove hardcoded color/font values from CSS → let Elementor handle them

---

## For Other Plugins

This architecture is portable. To use it in another plugin:

1. Copy `elementor/class-widget-base.php` → adjust namespace
2. Copy `elementor/traits/trait-shared-controls.php` → adjust namespace
3. Each widget extends your WidgetBase + `use SharedControls`
4. Define `get_sj_config()` (rename to `get_config()` if you want)
5. CSS = layout only, Elementor = dynamic styles

The pattern works for any Elementor plugin: review widgets, booking widgets,
pricing tables, testimonials, etc.
