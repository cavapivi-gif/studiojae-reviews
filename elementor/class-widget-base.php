<?php
namespace SJ_Reviews\Elementor;

use Elementor\Widget_Base;

defined('ABSPATH') || exit;

/**
 * Base class for all SJ Reviews Elementor widgets.
 *
 * Inspired by ReyTheme architecture: each widget declares a static config
 * via get_sj_config() and inherits common behaviour from this base.
 *
 * @see CLAUDE.md for full architecture guide.
 */
abstract class SjWidgetBase extends Widget_Base {

    /**
     * Widget configuration. Override in child classes.
     *
     * Keys:
     *  - id         (string)  Unique widget ID (e.g. 'sj_coup_de_coeur')
     *  - title      (string)  Display name in Elementor panel
     *  - icon       (string)  Elementor eicon class
     *  - keywords   (array)   Search keywords
     *  - css        (array)   CSS file handles to depend on (optional)
     *  - js         (array)   JS file handles to depend on (optional)
     *  - categories (array)   Elementor categories (default: ['sj-reviews'])
     */
    abstract protected static function get_sj_config(): array;

    /** Selector dictionary — child widgets extend this in their constructor. */
    protected array $selectors = [
        'wrapper' => '{{WRAPPER}}',
    ];

    public function get_name(): string {
        return static::get_sj_config()['id'];
    }

    public function get_title(): string {
        return static::get_sj_config()['title'];
    }

    public function get_icon(): string {
        return static::get_sj_config()['icon'] ?? 'eicon-star';
    }

    public function get_categories(): array {
        return static::get_sj_config()['categories'] ?? ['sj-reviews'];
    }

    public function get_keywords(): array {
        return static::get_sj_config()['keywords'] ?? [];
    }

    public function get_style_depends(): array {
        return static::get_sj_config()['css'] ?? [];
    }

    public function get_script_depends(): array {
        return static::get_sj_config()['js'] ?? [];
    }

    /**
     * Shorthand to build a full selector from the dictionary.
     *
     * Usage in controls:
     *   'selectors' => [ $this->sel('title') => 'color: {{VALUE}};' ]
     *
     * @param string $key  Key from $this->selectors.
     * @return string       The resolved CSS selector string.
     */
    protected function sel(string $key): string {
        return $this->selectors[$key] ?? $this->selectors['wrapper'];
    }
}
