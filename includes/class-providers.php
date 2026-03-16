<?php
namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

/**
 * Registre dynamique des providers (plateformes d'avis).
 *
 * Providers système : non supprimables, définis en dur comme fallback.
 * Providers custom  : stockés dans l'option WordPress 'sj_providers'.
 *
 * Structure d'un provider :
 * {
 *   "id":                    "google",
 *   "label":                 "Google",
 *   "color":                 "#4285F4",
 *   "icon_type":             "svg_inline",   // svg_inline | img_url | emoji | letter
 *   "icon_value":            "<svg>...</svg>",
 *   "icon_url":              "",
 *   "external_link_pattern": "https://search.google.com/local/reviews?placeid={place_id}",
 *   "active":                true,
 *   "is_system":             true
 * }
 */
class Providers {

    const OPTION_KEY = 'sj_providers';

    /** @var array|null Request-level cache */
    private static ?array $cache = null;

    /** Providers système (non supprimables). */
    private static function system_defaults(): array {
        return [
            'google' => [
                'id'                    => 'google',
                'label'                 => 'Google',
                'color'                 => '#4285F4',
                'icon_type'             => 'svg_inline',
                'icon_value'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>',
                'icon_url'              => '',
                'external_link_pattern' => 'https://search.google.com/local/reviews?placeid={place_id}',
                'active'                => true,
                'is_system'             => true,
            ],
            'tripadvisor' => [
                'id'                    => 'tripadvisor',
                'label'                 => 'TripAdvisor',
                'color'                 => '#00AF87',
                'icon_type'             => 'svg_inline',
                'icon_value'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="7" cy="14" r="4" fill="#00AF87"/><circle cx="17" cy="14" r="4" fill="#00AF87"/><path d="M12 5C8 5 4.5 7 3 10h18c-1.5-3-5-5-9-5z" fill="#00AF87"/><circle cx="7" cy="14" r="2" fill="white"/><circle cx="17" cy="14" r="2" fill="white"/></svg>',
                'icon_url'              => '',
                'external_link_pattern' => 'https://www.tripadvisor.fr/Attraction_Review-{location_id}',
                'active'                => true,
                'is_system'             => true,
            ],
            'facebook' => [
                'id'                    => 'facebook',
                'label'                 => 'Facebook',
                'color'                 => '#1877F2',
                'icon_type'             => 'svg_inline',
                'icon_value'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/></svg>',
                'icon_url'              => '',
                'external_link_pattern' => '',
                'active'                => true,
                'is_system'             => true,
            ],
            'trustpilot' => [
                'id'                    => 'trustpilot',
                'label'                 => 'Trustpilot',
                'color'                 => '#00B67A',
                'icon_type'             => 'svg_inline',
                'icon_value'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#00B67A"/></svg>',
                'icon_url'              => '',
                'external_link_pattern' => 'https://www.trustpilot.com/review/{domain}',
                'active'                => true,
                'is_system'             => true,
            ],
            'regiondo' => [
                'id'                    => 'regiondo',
                'label'                 => 'Regiondo',
                'color'                 => '#e85c2c',
                'icon_type'             => 'letter',
                'icon_value'            => 'R',
                'icon_url'              => '',
                'external_link_pattern' => '',
                'active'                => true,
                'is_system'             => true,
            ],
            'direct' => [
                'id'                    => 'direct',
                'label'                 => 'Direct',
                'color'                 => '#374151',
                'icon_type'             => 'letter',
                'icon_value'            => 'D',
                'icon_url'              => '',
                'external_link_pattern' => '',
                'active'                => true,
                'is_system'             => true,
            ],
            'autre' => [
                'id'                    => 'autre',
                'label'                 => 'Autre',
                'color'                 => '#9CA3AF',
                'icon_type'             => 'letter',
                'icon_value'            => 'A',
                'icon_url'              => '',
                'external_link_pattern' => '',
                'active'                => true,
                'is_system'             => true,
            ],
        ];
    }

    /**
     * Retourne tous les providers (système + custom fusionnés).
     * Les overrides admin (label, couleur, actif) sur les providers système sont respectés.
     *
     * @return array  Tableau indexé par provider id.
     */
    public static function all(): array {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $defaults = self::system_defaults();
        $stored   = get_option(self::OPTION_KEY, []);

        if (!is_array($stored)) {
            $stored = [];
        }

        // Merge: system defaults + stored overrides/custom
        $result = $defaults;
        foreach ($stored as $id => $data) {
            if (isset($result[$id])) {
                // Override système : on garde is_system = true
                $result[$id] = array_merge($result[$id], $data, ['id' => $id, 'is_system' => true]);
            } else {
                // Provider custom
                $result[$id] = array_merge(['is_system' => false], $data, ['id' => $id]);
            }
        }

        self::$cache = $result;
        return $result;
    }

    /** Retourne un provider par son id, ou null. */
    public static function get(string $id): ?array {
        return self::all()[$id] ?? null;
    }

    /**
     * Crée ou met à jour un provider custom (les providers système ne peuvent pas être supprimés
     * mais leurs attributs editables peuvent être overridés).
     */
    public static function save(string $id, array $data): void {
        $stored = get_option(self::OPTION_KEY, []);
        if (!is_array($stored)) $stored = [];

        $defaults = self::system_defaults();
        $is_system = isset($defaults[$id]);

        $stored[$id] = array_merge($stored[$id] ?? [], $data, ['id' => $id]);
        if ($is_system) {
            $stored[$id]['is_system'] = true;
        }

        update_option(self::OPTION_KEY, $stored, false);
        self::$cache = null;
    }

    /**
     * Supprime un provider custom. Les providers système ne peuvent pas être supprimés.
     *
     * @return bool  true si supprimé, false si refusé (système).
     */
    public static function delete(string $id): bool {
        $defaults = self::system_defaults();
        if (isset($defaults[$id])) {
            return false; // Provider système : non supprimable
        }

        $stored = get_option(self::OPTION_KEY, []);
        if (!is_array($stored)) return false;

        if (isset($stored[$id])) {
            unset($stored[$id]);
            update_option(self::OPTION_KEY, $stored, false);
            self::$cache = null;
        }
        return true;
    }

    /** Vide le cache request-level (ex. après update_option). */
    public static function flush(): void {
        self::$cache = null;
    }

    // ── Accesseurs rapides ──────────────────────────────────────────────────

    /** Couleur hex du provider. */
    public static function color(string $id): string {
        $p = self::get($id);
        return $p['color'] ?? '#9CA3AF';
    }

    /** Label affiché du provider. */
    public static function label(string $id): string {
        $p = self::get($id);
        return $p['label'] ?? ucfirst($id);
    }

    /**
     * HTML de l'icône du provider.
     *
     * @param string $id    Provider id.
     * @param int    $size  Taille en pixels.
     * @return string  HTML safe à injecter directement.
     */
    public static function icon_html(string $id, int $size = 16): string {
        $p = self::get($id);
        if (!$p) {
            return self::letter_icon(strtoupper(substr($id, 0, 1)), '#9CA3AF', $size);
        }

        $type  = $p['icon_type']  ?? 'letter';
        $value = $p['icon_value'] ?? '';
        $color = $p['color']      ?? '#9CA3AF';

        switch ($type) {
            case 'svg_inline':
                if ($value) {
                    return '<span class="sj-provider-icon sj-provider-icon--svg" style="display:inline-flex;align-items:center;width:' . esc_attr($size) . 'px;height:' . esc_attr($size) . 'px" aria-label="' . esc_attr(self::label($id)) . '">'
                        . $value // SVG inline — already trusted (from our own config)
                        . '</span>';
                }
                // fall through
            case 'img_url':
                $url = $p['icon_url'] ?? $value;
                if ($url) {
                    return '<img class="sj-provider-icon sj-provider-icon--img" src="' . esc_url($url) . '" alt="' . esc_attr(self::label($id)) . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" loading="lazy">';
                }
                // fall through
            case 'emoji':
                if ($value) {
                    return '<span class="sj-provider-icon sj-provider-icon--emoji" style="font-size:' . esc_attr($size) . 'px;line-height:1" aria-label="' . esc_attr(self::label($id)) . '">' . esc_html($value) . '</span>';
                }
                // fall through
            default: // 'letter'
                $letter = $value ?: strtoupper(substr($id, 0, 1));
                return self::letter_icon($letter, $color, $size);
        }
    }

    /** Génère un badge lettre coloré. */
    private static function letter_icon(string $letter, string $color, int $size): string {
        $font_size = max(8, (int) round($size * 0.55));
        return '<span class="sj-provider-icon sj-provider-icon--letter" style="display:inline-flex;align-items:center;justify-content:center;width:' . esc_attr($size) . 'px;height:' . esc_attr($size) . 'px;border-radius:50%;background:' . esc_attr($color) . ';color:#fff;font-size:' . esc_attr($font_size) . 'px;font-weight:600;line-height:1;font-family:sans-serif" aria-hidden="true">'
            . esc_html($letter)
            . '</span>';
    }

    /**
     * Retourne le tableau des providers actifs sous forme de paires id => label.
     * Remplace Labels::SOURCES pour les usages dynamiques.
     */
    public static function active_sources(): array {
        $out = [];
        foreach (self::all() as $id => $p) {
            if (!empty($p['active'])) {
                $out[$id] = $p['label'];
            }
        }
        return $out;
    }
}
