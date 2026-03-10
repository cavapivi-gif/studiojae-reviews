<?php
namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

/**
 * Centralized label constants & settings-aware getters.
 *
 * Constants provide the canonical defaults.
 * Dynamic getters merge settings overrides on top.
 */
class Labels {

    // ── Static constants (defaults) ────────────────────────────────────────

    public const SOURCES = [
        'google'      => 'Google',
        'tripadvisor' => 'TripAdvisor',
        'facebook'    => 'Facebook',
        'trustpilot'  => 'Trustpilot',
        'regiondo'    => 'Regiondo',
        'direct'      => 'Direct',
        'autre'       => 'Autre',
    ];

    public const TRAVEL_TYPES = [
        'couple'   => 'Couple',
        'solo'     => 'Solo',
        'famille'  => 'Famille',
        'amis'     => 'Entre amis',
        'affaires' => 'Voyage d\'affaires',
    ];

    public const LANGUAGES = [
        'fr' => 'Français',
        'en' => 'Anglais',
        'it' => 'Italien',
        'de' => 'Allemand',
        'es' => 'Espagnol',
    ];

    public const CRITERIA_DEFAULTS = [
        'qualite_prix' => 'Qualité/prix',
        'ambiance'     => 'Ambiance',
        'experience'   => 'Expérience',
        'paysage'      => 'Paysage',
    ];

    public const RATING_DEFAULTS = [
        '5' => 'Excellent',
        '4' => 'Bien',
        '3' => 'Moyen',
        '2' => 'Médiocre',
        '1' => 'Horrible',
    ];

    // ── Dynamic getters (merge settings on top) ────────────────────────────

    /** Criteria labels from settings, falling back to defaults. */
    public static function criteria(): array {
        $custom = Settings::get('criteria_labels', []);
        $custom = is_array($custom) ? $custom : [];
        return array_merge(self::CRITERIA_DEFAULTS, $custom);
    }

    /** Criteria labels with 'avis_' prefix keys (for ACF / metabox fields). */
    public static function criteria_prefixed(): array {
        $labels = self::criteria();
        $out = [];
        foreach ($labels as $k => $v) {
            $out['avis_' . $k] = $v;
        }
        return $out;
    }

    /** Rating distribution labels from settings, falling back to defaults. */
    public static function ratings(): array {
        $custom = Settings::get('rating_labels', []);
        $custom = is_array($custom) ? $custom : [];
        return array_merge(self::RATING_DEFAULTS, $custom);
    }

    /** Rating labels keyed as int (5=>label, 4=>label…). */
    public static function ratings_int(): array {
        $labels = self::ratings();
        $out = [];
        foreach ($labels as $k => $v) {
            $out[(int) $k] = $v;
        }
        return $out;
    }

    /** Human label for a numeric rating average. */
    public static function rating_label(float $avg): string {
        $rl = self::ratings();
        if ($avg >= 4.5) return $rl['5'] ?? 'Excellent';
        if ($avg >= 4.0) return 'Très bien';
        if ($avg >= 3.5) return $rl['4'] ?? 'Bien';
        if ($avg >= 3.0) return $rl['3'] ?? 'Moyen';
        if ($avg >= 2.0) return $rl['2'] ?? 'Médiocre';
        return                   $rl['1'] ?? 'Mauvais';
    }

    /** Source display name. */
    public static function source_name(string $slug): string {
        return self::SOURCES[$slug] ?? ucfirst($slug);
    }
}
