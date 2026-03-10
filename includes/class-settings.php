<?php
namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

/**
 * Centralized, cached settings accessor.
 *
 * Replaces all direct `get_option('sj_reviews_settings')` and
 * `get_option('sj_lieux')` calls across the plugin.
 * Uses static cache so the option is read once per request.
 */
class Settings {

    private static ?array $reviews = null;
    private static ?array $lieux   = null;
    private static bool $hooked    = false;

    /** Register WP hooks to auto-flush cache on option updates. */
    private static function ensure_hooks(): void {
        if (self::$hooked) return;
        self::$hooked = true;
        add_action('update_option_sj_reviews_settings', [self::class, 'flush'], 10, 0);
        add_action('update_option_sj_lieux', [self::class, 'flush'], 10, 0);
    }

    /** Full settings array (cached). */
    public static function all(): array {
        self::ensure_hooks();
        if (self::$reviews === null) {
            self::$reviews = (array) get_option('sj_reviews_settings', []);
        }
        return self::$reviews;
    }

    /** Single key with default. */
    public static function get(string $key, mixed $default = null): mixed {
        return self::all()[$key] ?? $default;
    }

    /** All lieux (cached). */
    public static function lieux(): array {
        self::ensure_hooks();
        if (self::$lieux === null) {
            self::$lieux = (array) get_option('sj_lieux', []);
        }
        return self::$lieux;
    }

    /** Linked post types (filtered). */
    public static function linked_post_types(): array {
        return array_filter((array) (self::get('linked_post_types', [])));
    }

    /**
     * Bust the static cache.
     * Call after save_settings / update_option to force re-read.
     */
    public static function flush(): void {
        self::$reviews = null;
        self::$lieux   = null;
    }
}
