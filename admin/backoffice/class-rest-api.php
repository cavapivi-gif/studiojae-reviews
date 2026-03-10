<?php
namespace SJ_Reviews\Admin\Backoffice;

defined('ABSPATH') || exit;

/**
 * REST API — namespace sj-reviews/v1
 *
 * GET  /dashboard          → stats globales (total, avg, distribution, par source, récents)
 * GET  /reviews            → liste paginée (filtres : search, rating, source, lieu_id, orderby, order)
 * GET  /reviews/{id}       → détail d'un avis
 * POST /reviews            → créer un avis
 * PUT  /reviews/{id}       → modifier un avis
 * DEL  /reviews/{id}       → supprimer un avis
 * GET  /lieux              → liste des lieux
 * POST /lieux              → créer un lieu
 * PUT  /lieux/{id}         → modifier un lieu
 * DEL  /lieux/{id}         → supprimer un lieu
 * GET  /settings           → lire les réglages
 * POST /settings           → enregistrer les réglages
 * POST /lieux/{id}/sync-trustpilot  → sync Trustpilot
 * POST /lieux/{id}/sync-tripadvisor → sync TripAdvisor
 * GET  /export              → export CSV
 */
class RestApi {

    private string $ns = 'sj-reviews/v1';

    public function init(): void {
        add_action('rest_api_init', [$this, 'register_routes']);

        // Flush dashboard cache once when plugin version changes (e.g. after a code fix).
        $cache_ver_key = 'sj_dash_cache_ver';
        if (get_option($cache_ver_key) !== SJ_REVIEWS_VERSION) {
            $this->invalidate_dashboard_cache();
            update_option($cache_ver_key, SJ_REVIEWS_VERSION, true);
        }
    }

    public function register_routes(): void {
        $valid_period = fn($v) => in_array($v, ['all', '7d', '30d', '90d', '12m', 'custom'], true);
        $valid_date   = fn($v) => !$v || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);
        $valid_season = fn($v) => in_array($v, ['spring', 'summer', 'autumn', 'winter'], true);
        $valid_year   = fn($v) => is_numeric($v) && (int) $v >= 2000 && (int) $v <= 2100;
        $sanitize_key = fn($v) => sanitize_key($v);

        register_rest_route($this->ns, '/flush-cache', [
            'methods'             => 'POST',
            'callback'            => [$this, 'flush_cache'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        register_rest_route($this->ns, '/dashboard', [
            'methods'             => 'GET',
            'callback'            => [$this, 'dashboard'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'period'    => ['default' => 'all', 'type' => 'string', 'validate_callback' => $valid_period, 'sanitize_callback' => $sanitize_key],
                'source'    => ['default' => '',    'type' => 'string', 'sanitize_callback' => $sanitize_key],
                'lieu_id'   => ['default' => '',    'type' => 'string', 'sanitize_callback' => $sanitize_key],
                'from_date' => ['default' => '',    'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
                'to_date'   => ['default' => '',    'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route($this->ns, '/dashboard/trends', [
            'methods'             => 'GET',
            'callback'            => [$this, 'dashboard_trends'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'period'    => ['default' => 'all', 'type' => 'string', 'validate_callback' => $valid_period, 'sanitize_callback' => $sanitize_key],
                'source'    => ['default' => '',    'type' => 'string', 'sanitize_callback' => $sanitize_key],
                'lieu_id'   => ['default' => '',    'type' => 'string', 'sanitize_callback' => $sanitize_key],
                'from_date' => ['default' => '',    'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
                'to_date'   => ['default' => '',    'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route($this->ns, '/dashboard/compare', [
            'methods'             => 'GET',
            'callback'            => [$this, 'dashboard_compare'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'season1' => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_season, 'sanitize_callback' => $sanitize_key],
                'year1'   => ['required' => true, 'type' => 'integer', 'validate_callback' => $valid_year],
                'season2' => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_season, 'sanitize_callback' => $sanitize_key],
                'year2'   => ['required' => true, 'type' => 'integer', 'validate_callback' => $valid_year],
            ],
        ]);

        register_rest_route($this->ns, '/dashboard/compare-range', [
            'methods'             => 'GET',
            'callback'            => [$this, 'dashboard_compare_range'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'from1' => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
                'to1'   => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
                'from2' => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
                'to2'   => ['required' => true, 'type' => 'string', 'validate_callback' => $valid_date, 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route($this->ns, '/reviews', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'list_reviews'],
                'permission_callback' => [$this, 'is_manager'],
                'args'                => [
                    'page'     => ['default' => 1,    'type' => 'integer'],
                    'per_page' => ['default' => 20,   'type' => 'integer', 'maximum' => 100],
                    'search'   => ['default' => '',   'type' => 'string'],
                    'rating'   => ['default' => 0,    'type' => 'integer'],
                    'source'   => ['default' => '',   'type' => 'string'],
                    'lieu_id'  => ['default' => '',   'type' => 'string'],
                    'orderby'  => ['default' => 'date', 'type' => 'string'],
                    'order'    => ['default' => 'DESC',  'type' => 'string'],
                    'email'    => ['default' => '',   'type' => 'string'],
                ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_review'],
                'permission_callback' => [$this, 'is_manager'],
            ],
        ]);

        register_rest_route($this->ns, '/reviews/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_review'],
                'permission_callback' => [$this, 'is_manager'],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_review'],
                'permission_callback' => [$this, 'is_manager'],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_review'],
                'permission_callback' => [$this, 'is_manager'],
            ],
        ]);

        // Lieux
        register_rest_route($this->ns, '/lieux', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'list_lieux'],
                'permission_callback' => [$this, 'is_manager'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'create_lieu'],
                'permission_callback' => [$this, 'is_manager'],
            ],
        ]);

        // Sync Google Places pour un lieu (démarre un job en arrière-plan)
        register_rest_route($this->ns, '/lieux/(?P<id>[a-z0-9_-]+)/sync-google', [
            'methods'             => 'POST',
            'callback'            => [$this, 'sync_google'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        // Sync Trustpilot pour un lieu
        register_rest_route($this->ns, '/lieux/(?P<id>[a-z0-9_-]+)/sync-trustpilot', [
            'methods'             => 'POST',
            'callback'            => [$this, 'sync_trustpilot'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        // Sync TripAdvisor pour un lieu
        register_rest_route($this->ns, '/lieux/(?P<id>[a-z0-9_-]+)/sync-tripadvisor', [
            'methods'             => 'POST',
            'callback'            => [$this, 'sync_tripadvisor'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        register_rest_route($this->ns, '/lieux/(?P<id>[a-z0-9_-]+)', [
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_lieu'],
                'permission_callback' => [$this, 'is_manager'],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [$this, 'delete_lieu'],
                'permission_callback' => [$this, 'is_manager'],
            ],
        ]);

        // Post types publics disponibles pour la liaison
        register_rest_route($this->ns, '/post-types', [
            'methods'             => 'GET',
            'callback'            => [$this, 'list_post_types'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        // Posts des types liés (pour le sélecteur dans le formulaire avis)
        register_rest_route($this->ns, '/linked-posts', [
            'methods'             => 'GET',
            'callback'            => [$this, 'list_linked_posts'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'post_type' => ['type' => 'string', 'default' => ''],
            ],
        ]);

        // Test des clés API
        register_rest_route($this->ns, '/settings/test-google-key', [
            'methods'             => 'POST',
            'callback'            => [$this, 'test_google_key'],
            'permission_callback' => [$this, 'is_manager'],
        ]);
        register_rest_route($this->ns, '/settings/test-trustpilot-key', [
            'methods'             => 'POST',
            'callback'            => [$this, 'test_trustpilot_key'],
            'permission_callback' => [$this, 'is_manager'],
        ]);
        register_rest_route($this->ns, '/settings/test-tripadvisor-key', [
            'methods'             => 'POST',
            'callback'            => [$this, 'test_tripadvisor_key'],
            'permission_callback' => [$this, 'is_manager'],
        ]);

        // Export CSV
        register_rest_route($this->ns, '/export', [
            'methods'             => 'GET',
            'callback'            => [$this, 'export_csv'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'source'  => ['default' => '', 'type' => 'string'],
                'lieu_id' => ['default' => '', 'type' => 'string'],
                'rating'  => ['default' => 0,  'type' => 'integer'],
            ],
        ]);

        register_rest_route($this->ns, '/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'is_manager'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'save_settings'],
                'permission_callback' => [$this, 'is_manager'],
            ],
        ]);

        // Public: front-end review loading (AJAX pagination + search)
        register_rest_route($this->ns, '/front/reviews', [
            'methods'             => 'GET',
            'callback'            => [$this, 'front_reviews'],
            'permission_callback' => '__return_true',
            'args'                => [
                'lieu_id'  => ['default' => 'all', 'type' => 'string'],
                'lieu_ids' => ['default' => '',    'type' => 'string'],
                'source_filter' => ['default' => '', 'type' => 'string'],
                'page'     => ['default' => 1,     'type' => 'integer'],
                'per_page' => ['default' => 10,    'type' => 'integer'],
                'sort'     => ['default' => 'recent', 'type' => 'string'],
                'rating'   => ['default' => 0,     'type' => 'integer'],
                'period'   => ['default' => '',    'type' => 'string'],
                'language' => ['default' => '',    'type' => 'string'],
                'travel'   => ['default' => '',    'type' => 'string'],
                'search'   => ['default' => '',    'type' => 'string'],
            ],
        ]);

        // Import Regiondo CSV
        register_rest_route($this->ns, '/import/post-matches', [
            'methods'             => 'GET',
            'callback'            => [$this, 'import_post_matches'],
            'permission_callback' => [$this, 'is_manager'],
            'args'                => [
                'search'    => ['type' => 'string',  'default' => ''],
                'post_type' => ['type' => 'string',  'default' => ''],
            ],
        ]);
        register_rest_route($this->ns, '/import/preview', [
            'methods'             => 'POST',
            'callback'            => [$this, 'import_preview'],
            'permission_callback' => [$this, 'is_manager'],
        ]);
        register_rest_route($this->ns, '/import/execute', [
            'methods'             => 'POST',
            'callback'            => [$this, 'import_execute'],
            'permission_callback' => [$this, 'is_manager'],
        ]);
    }

    // ── Permissions ───────────────────────────────────────────────────────────

    public function is_manager(): bool {
        return current_user_can('manage_options');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    private const DASHBOARD_CACHE_PREFIX = 'sj_dash_';
    private const DASHBOARD_CACHE_TTL = [
        '7d'     => 5 * MINUTE_IN_SECONDS,
        '30d'    => 15 * MINUTE_IN_SECONDS,
        '90d'    => HOUR_IN_SECONDS,
        '12m'    => HOUR_IN_SECONDS,
        'all'    => HOUR_IN_SECONDS,
        'custom' => HOUR_IN_SECONDS,
    ];

    /**
     * Build reusable WHERE fragments for dashboard queries.
     */
    private function build_dashboard_filters(\WP_REST_Request $req): array {
        global $wpdb;

        $period    = sanitize_key($req->get_param('period') ?? 'all');
        $source    = sanitize_key($req->get_param('source') ?? '');
        $lieu_id   = sanitize_key($req->get_param('lieu_id') ?? '');
        $from_date = sanitize_text_field($req->get_param('from_date') ?? '');
        $to_date   = sanitize_text_field($req->get_param('to_date') ?? '');

        $date_where = '';
        if ($period === 'custom' && $from_date) {
            $date_where = $wpdb->prepare(" AND p.post_date >= %s", $from_date . ' 00:00:00');
            if ($to_date) {
                $date_where .= $wpdb->prepare(" AND p.post_date <= %s", $to_date . ' 23:59:59');
            }
        } else {
            switch ($period) {
                case '7d':
                    $date_where = $wpdb->prepare(" AND p.post_date >= %s", gmdate('Y-m-d H:i:s', strtotime('-7 days')));
                    break;
                case '30d':
                    $date_where = $wpdb->prepare(" AND p.post_date >= %s", gmdate('Y-m-d H:i:s', strtotime('-30 days')));
                    break;
                case '90d':
                    $date_where = $wpdb->prepare(" AND p.post_date >= %s", gmdate('Y-m-d H:i:s', strtotime('-90 days')));
                    break;
                case '12m':
                    $date_where = $wpdb->prepare(" AND p.post_date >= %s", gmdate('Y-m-d H:i:s', strtotime('-12 months')));
                    break;
            }
        }

        // Base join: only count reviews that have a source set (harmonize with by_source / donut)
        $base_join = " INNER JOIN {$wpdb->postmeta} pm_has_src ON pm_has_src.post_id = p.ID AND pm_has_src.meta_key = 'avis_source' AND pm_has_src.meta_value != ''";

        // Source filter: JOIN + WHERE on postmeta
        $source_join  = '';
        $source_where = '';
        if ($source) {
            $source_join  = " INNER JOIN {$wpdb->postmeta} pm_fsrc ON pm_fsrc.post_id = p.ID AND pm_fsrc.meta_key = 'avis_source'";
            $source_where = $wpdb->prepare(" AND pm_fsrc.meta_value = %s", $source);
        }

        // Lieu filter: JOIN + WHERE on postmeta
        $lieu_join  = '';
        $lieu_where = '';
        if ($lieu_id) {
            $lieu_join  = " INNER JOIN {$wpdb->postmeta} pm_flieu ON pm_flieu.post_id = p.ID AND pm_flieu.meta_key = 'avis_lieu_id'";
            $lieu_where = $wpdb->prepare(" AND pm_flieu.meta_value = %s", $lieu_id);
        }

        return compact('period', 'date_where', 'base_join', 'source_join', 'source_where', 'lieu_join', 'lieu_where');
    }

    public function dashboard(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;

        $f = $this->build_dashboard_filters($req);
        $period      = $f['period'];
        $date_where  = $f['date_where'];
        $extra_joins = $f['base_join'] . $f['source_join'] . $f['lieu_join'];
        $extra_where = $f['source_where'] . $f['lieu_where'];
        $has_filters = $f['source_where'] || $f['lieu_where'];

        // Per-period cache (only for unfiltered requests)
        $cache_key = self::DASHBOARD_CACHE_PREFIX . $period;
        $flush     = $req->get_param('flush');
        if ($flush) {
            $this->invalidate_dashboard_cache();
        }
        if (!$has_filters && !$flush) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                $cached['recent'] = array_map(
                    fn($p) => sj_normalize_review($p, true),
                    get_posts(['post_type' => 'sj_avis', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC'])
                );
                return rest_ensure_response($cached);
            }
        }

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fragments are prepared above
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(*)
             FROM {$wpdb->posts} p
             {$extra_joins}
             WHERE p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             {$date_where}{$extra_where}"
        );

        $avg_raw = $wpdb->get_var(
            "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,1)))
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             {$extra_joins}
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             {$date_where}{$extra_where}"
        );
        $avg = round((float) $avg_raw, 1);

        // Répartition par note
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $dist_rows = $wpdb->get_results(
            "SELECT CAST(pm.meta_value AS UNSIGNED) AS rating, COUNT(*) AS cnt
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             {$extra_joins}
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             AND pm.meta_value BETWEEN '1' AND '5'
             {$date_where}{$extra_where}
             GROUP BY rating"
        );
        foreach ($dist_rows as $row) {
            $distribution[(int) $row->rating] = (int) $row->cnt;
        }

        // Répartition par source (avec note moyenne par source)
        $sources_raw = $wpdb->get_results(
            "SELECT
                pm_src.meta_value AS source,
                COUNT(*) AS total,
                ROUND(AVG(CAST(pm_rat.meta_value AS DECIMAL(3,1))), 1) AS avg_rating
             FROM {$wpdb->postmeta} pm_src
             INNER JOIN {$wpdb->posts} p ON p.ID = pm_src.post_id
             LEFT JOIN {$wpdb->postmeta} pm_rat
                ON pm_rat.post_id = pm_src.post_id AND pm_rat.meta_key = 'avis_rating'
             {$extra_joins}
             WHERE pm_src.meta_key = 'avis_source'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             {$date_where}{$extra_where}
             GROUP BY pm_src.meta_value
             ORDER BY total DESC"
        );
        $by_source    = [];
        $google_total = 0;
        $google_avg   = 0;
        foreach ($sources_raw as $row) {
            $entry = [
                'source'     => $row->source,
                'count'      => (int) $row->total,
                'avg_rating' => round((float) $row->avg_rating, 1),
            ];
            $by_source[] = $entry;
            if ($row->source === 'google') {
                $google_total = (int) $row->total;
                $google_avg   = round((float) $row->avg_rating, 1);
            }
        }

        // Monthly trend — respects period + source/lieu filters
        // For short periods (7d, 30d), show last 6 months for meaningful monthly context;
        // otherwise, match the selected period.
        $monthly_date_where = $date_where;
        if (!$monthly_date_where || in_array($period, ['7d', '30d'], true)) {
            $monthly_date_where = $wpdb->prepare(
                " AND p.post_date >= %s",
                gmdate('Y-m-d H:i:s', strtotime('-6 months'))
            );
        }
        $monthly_trend = $wpdb->get_results(
            "SELECT YEAR(p.post_date) AS year, MONTH(p.post_date) AS month, COUNT(*) AS cnt
             FROM {$wpdb->posts} p
             {$extra_joins}
             WHERE p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             {$monthly_date_where}
             {$extra_where}
             GROUP BY YEAR(p.post_date), MONTH(p.post_date)
             ORDER BY year ASC, month ASC"
        );
        $trend = [];
        foreach ($monthly_trend as $row) {
            $trend[] = [
                'year'  => (int) $row->year,
                'month' => (int) $row->month,
                'count' => (int) $row->cnt,
            ];
        }

        // Avis récents (within period + filters)
        $date_query = [];
        if ($date_where) {
            switch ($period) {
                case '7d':  $date_query = ['after' => '7 days ago']; break;
                case '30d': $date_query = ['after' => '30 days ago']; break;
                case '90d': $date_query = ['after' => '90 days ago']; break;
                case '12m': $date_query = ['after' => '12 months ago']; break;
            }
        }
        $recent_args = ['post_type' => 'sj_avis', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC'];
        if ($date_query) {
            $recent_args['date_query'] = [$date_query];
        }
        $meta_query = [];
        if ($f['source_where']) {
            $meta_query[] = ['key' => 'avis_source', 'value' => sanitize_key($req->get_param('source'))];
        }
        if ($f['lieu_where']) {
            $meta_query[] = ['key' => 'avis_lieu_id', 'value' => sanitize_key($req->get_param('lieu_id'))];
        }
        if ($meta_query) {
            $recent_args['meta_query'] = $meta_query;
        }
        $recent = array_map(
            fn($p) => sj_normalize_review($p, true),
            get_posts($recent_args)
        );
        // phpcs:enable

        // ── Platform enrichment ──────────────────────────────────────────────
        // Include platform review counts from lieux (Google, Trustpilot, etc.)
        // so dashboard totals match the front-end widgets.
        $all_lieux   = (array) get_option('sj_lieux', []);
        $lieu_filter = sanitize_key($req->get_param('lieu_id'));

        // Determine which lieux to include
        if ($lieu_filter) {
            $matched_lieux = array_filter($all_lieux, fn($l) => ($l['id'] ?? '') === $lieu_filter);
        } else {
            $matched_lieux = $all_lieux;
        }

        // Track platform extras per source for by_source enrichment
        $platform_extras = []; // source => ['extra' => int, 'rating' => float, 'total' => int]

        foreach ($matched_lieux as $l) {
            $platform_count  = (int) ($l['reviews_count'] ?? 0);
            $platform_rating = (float) ($l['rating'] ?? 0);
            if ($platform_count <= 0) continue;

            // How many CPT reviews do we already have for this lieu?
            $lieu_cpt_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'avis_lieu_id'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND pm.meta_value = %s",
                $l['id']
            ));

            $extra = max(0, $platform_count - $lieu_cpt_count);
            if ($extra > 0) {
                $combined_total = $total + $extra;
                if ($platform_rating > 0) {
                    $avg = ($total > 0)
                        ? round(($avg * $total + $platform_rating * $extra) / $combined_total, 1)
                        : round($platform_rating, 1);
                }
                $total = $combined_total;
            }

            // Accumulate platform totals per source
            $source = $l['source'] ?? '';
            if ($source) {
                if (!isset($platform_extras[$source])) {
                    $platform_extras[$source] = ['total' => 0, 'sum' => 0];
                }
                $platform_extras[$source]['total'] += $platform_count;
                $platform_extras[$source]['sum']   += $platform_count * $platform_rating;
            }
        }

        // Enrich by_source with platform data (use platform totals to avoid double-counting)
        foreach ($platform_extras as $src => $pdata) {
            if ($pdata['total'] <= 0) continue;
            $platform_avg = round($pdata['sum'] / $pdata['total'], 1);
            $found = false;
            foreach ($by_source as &$entry) {
                if ($entry['source'] === $src) {
                    // Use the higher of CPT count or platform count
                    $entry['count']      = max($entry['count'], $pdata['total']);
                    $entry['avg_rating'] = ($pdata['total'] > $entry['count'])
                        ? $platform_avg
                        : $entry['avg_rating'];
                    $found = true;
                    break;
                }
            }
            unset($entry);
            if (!$found) {
                $by_source[] = [
                    'source'     => $src,
                    'count'      => $pdata['total'],
                    'avg_rating' => $platform_avg,
                ];
            }
        }

        // Harmonize total with by_source (donut) after enrichment
        $total = array_sum(array_column($by_source, 'count'));

        // Google stat card: use platform totals when available
        $google_total = 0;
        $google_avg   = 0;
        if (isset($platform_extras['google']) && $platform_extras['google']['total'] > 0) {
            $google_total = $platform_extras['google']['total'];
            $google_avg   = round($platform_extras['google']['sum'] / $google_total, 1);
        } else {
            // Fall back to CPT data
            foreach ($by_source as $entry) {
                if ($entry['source'] === 'google') {
                    $google_total = $entry['count'];
                    $google_avg   = $entry['avg_rating'];
                    break;
                }
            }
        }

        $data = [
            'total'         => $total,
            'avg_rating'    => $avg,
            'distribution'  => $distribution,
            'by_source'     => $by_source,
            'google_total'  => $google_total,
            'google_avg'    => $google_avg,
            'monthly_trend' => $trend,
            'recent'        => $recent,
        ];

        if (!$has_filters) {
            $ttl = self::DASHBOARD_CACHE_TTL[$period] ?? HOUR_IN_SECONDS;
            set_transient($cache_key, $data, $ttl);
        }

        return rest_ensure_response($data);
    }

    /** REST endpoint: flush all dashboard caches. */
    public function flush_cache(\WP_REST_Request $req): \WP_REST_Response {
        $this->invalidate_dashboard_cache();
        return rest_ensure_response(['ok' => true, 'message' => 'Cache vidé.']);
    }

    /** Invalidate all dashboard caches (call after any review CRUD). */
    private function invalidate_dashboard_cache(): void {
        foreach (array_keys(self::DASHBOARD_CACHE_TTL) as $period) {
            delete_transient(self::DASHBOARD_CACHE_PREFIX . $period);
        }
    }

    // ── Dashboard trends (time-series) ──────────────────────────────────────

    public function dashboard_trends(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;

        $f = $this->build_dashboard_filters($req);
        $date_where  = $f['date_where'];
        $extra_joins = $f['base_join'] . $f['source_join'] . $f['lieu_join'];
        $extra_where = $f['source_where'] . $f['lieu_where'];

        // Auto granularity: day for ≤30d, week for ≤90d, month otherwise
        $period    = $f['period'];
        $from_date = sanitize_text_field($req->get_param('from_date') ?? '');
        $to_date   = sanitize_text_field($req->get_param('to_date') ?? '');

        // For custom period, compute span in days for auto-granularity
        if ($period === 'custom' && $from_date) {
            $span_days = $to_date
                ? max(1, (int) ((strtotime($to_date) - strtotime($from_date)) / DAY_IN_SECONDS))
                : 365;
            if ($span_days <= 31) {
                $auto_period = '30d';
            } elseif ($span_days <= 90) {
                $auto_period = '90d';
            } else {
                $auto_period = '12m';
            }
        } else {
            $auto_period = $period;
        }

        if (in_array($auto_period, ['7d', '30d'], true)) {
            $granularity = 'day';
            $select_date = "DATE(p.post_date) AS date_key";
            $group_by    = "DATE(p.post_date)";
        } elseif ($auto_period === '90d') {
            $granularity = 'week';
            $select_date = "CONCAT(YEAR(p.post_date), '-W', LPAD(WEEK(p.post_date, 3), 2, '0')) AS date_key";
            $group_by    = "YEAR(p.post_date), WEEK(p.post_date, 3)";
        } else {
            $granularity = 'month';
            $select_date = "DATE_FORMAT(p.post_date, '%Y-%m') AS date_key";
            $group_by    = "DATE_FORMAT(p.post_date, '%Y-%m')";
        }

        // If no period set ('all'), use actual data range from DB
        if ($date_where) {
            $trend_date_where = $date_where;
        } else {
            $oldest_date = $wpdb->get_var(
                "SELECT MIN(p.post_date) FROM {$wpdb->posts} p
                 {$extra_joins}
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 {$extra_where}"
            );
            $trend_date_where = $oldest_date
                ? $wpdb->prepare(" AND p.post_date >= %s", $oldest_date)
                : '';
        }

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // Review count over time
        $count_rows = $wpdb->get_results(
            "SELECT {$select_date}, COUNT(*) AS cnt
             FROM {$wpdb->posts} p
             {$extra_joins}
             WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
             {$trend_date_where}{$extra_where}
             GROUP BY {$group_by}
             ORDER BY date_key ASC"
        );

        // Average rating over time
        $avg_rows = $wpdb->get_results(
            "SELECT {$select_date},
                    ROUND(AVG(CAST(pm_r.meta_value AS DECIMAL(3,1))), 2) AS avg_rating
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
             {$extra_joins}
             WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
             {$trend_date_where}{$extra_where}
             GROUP BY {$group_by}
             ORDER BY date_key ASC"
        );

        // Count by source over time
        $source_rows = $wpdb->get_results(
            "SELECT {$select_date},
                    pm_src.meta_value AS source,
                    COUNT(*) AS cnt
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_src ON pm_src.post_id = p.ID AND pm_src.meta_key = 'avis_source'
             {$extra_joins}
             WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
             {$trend_date_where}{$extra_where}
             GROUP BY {$group_by}, pm_src.meta_value
             ORDER BY date_key ASC"
        );
        // phpcs:enable

        // Merge DB rows into a keyed map
        $data_map = [];
        foreach ($count_rows as $row) {
            $data_map[$row->date_key] = [
                'date'    => $row->date_key,
                'count'   => (int) $row->cnt,
                'avg'     => 0,
                'sources' => [],
            ];
        }
        foreach ($avg_rows as $row) {
            if (isset($data_map[$row->date_key])) {
                $data_map[$row->date_key]['avg'] = round((float) $row->avg_rating, 2);
            }
        }
        foreach ($source_rows as $row) {
            if (isset($data_map[$row->date_key])) {
                $data_map[$row->date_key]['sources'][$row->source] = (int) $row->cnt;
            }
        }

        // Gap-fill: generate all expected buckets so charts don't have misleading jumps
        $points = $this->fill_trend_gaps($data_map, $period, $granularity);

        return rest_ensure_response([
            'granularity' => $granularity,
            'points'      => $points,
        ]);
    }

    /**
     * Fill time gaps with zero-count entries so charts render continuous axes.
     */
    private function fill_trend_gaps(array $data_map, string $period, string $granularity): array {
        if (empty($data_map)) {
            return [];
        }

        $empty_point = fn(string $key) => ['date' => $key, 'count' => 0, 'avg' => 0, 'sources' => []];

        // Determine date range
        switch ($period) {
            case '7d':  $start = strtotime('-7 days'); break;
            case '30d': $start = strtotime('-30 days'); break;
            case '90d': $start = strtotime('-90 days'); break;
            case '12m': $start = strtotime('-12 months'); break;
            default:
                // 'all' or custom: derive start from actual data
                $first_key = array_key_first($data_map);
                if ($granularity === 'month') {
                    $start = strtotime($first_key . '-01');
                } elseif ($granularity === 'week') {
                    // ISO week key like '2024-W03'
                    $parts = explode('-W', $first_key);
                    $dt = new \DateTime();
                    $dt->setISODate((int) $parts[0], (int) ($parts[1] ?? 1));
                    $start = $dt->getTimestamp();
                } else {
                    $start = strtotime($first_key);
                }
                break;
        }

        $all_keys = [];
        $current  = new \DateTime(gmdate('Y-m-d', $start));
        $end      = new \DateTime();

        if ($granularity === 'day') {
            while ($current <= $end) {
                $all_keys[] = $current->format('Y-m-d');
                $current->modify('+1 day');
            }
        } elseif ($granularity === 'week') {
            // Align to ISO week start (Monday)
            $current->modify('monday this week');
            while ($current <= $end) {
                $all_keys[] = $current->format('Y') . '-W' . $current->format('W');
                $current->modify('+7 days');
            }
        } else {
            // month
            $current->modify('first day of this month');
            while ($current <= $end) {
                $all_keys[] = $current->format('Y-m');
                $current->modify('+1 month');
            }
        }

        $points = [];
        foreach ($all_keys as $key) {
            $points[] = $data_map[$key] ?? $empty_point($key);
        }
        return $points;
    }

    // ── Dashboard season comparison ─────────────────────────────────────────

    private const SEASON_MONTHS = [
        'spring' => [3, 4, 5],
        'summer' => [6, 7, 8],
        'autumn' => [9, 10, 11],
        'winter' => [12, 1, 2],
    ];

    public function dashboard_compare(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;

        $season1 = sanitize_key($req->get_param('season1'));
        $year1   = (int) $req->get_param('year1');
        $season2 = sanitize_key($req->get_param('season2'));
        $year2   = (int) $req->get_param('year2');

        if (!isset(self::SEASON_MONTHS[$season1], self::SEASON_MONTHS[$season2])) {
            return new \WP_REST_Response(['message' => 'Invalid season name'], 400);
        }
        if ($year1 < 2000 || $year1 > 2100 || $year2 < 2000 || $year2 > 2100) {
            return new \WP_REST_Response(['message' => 'Invalid year'], 400);
        }

        $results = [];
        foreach ([['season' => $season1, 'year' => $year1], ['season' => $season2, 'year' => $year2]] as $i => $s) {
            $months = self::SEASON_MONTHS[$s['season']];
            $year   = $s['year'];

            // Winter spans two years: Dec of previous year + Jan-Feb of current year
            if ($s['season'] === 'winter') {
                $date_conds = $wpdb->prepare(
                    "(
                        (YEAR(p.post_date) = %d AND MONTH(p.post_date) = 12)
                        OR (YEAR(p.post_date) = %d AND MONTH(p.post_date) IN (1, 2))
                    )",
                    $year - 1,
                    $year
                );
            } else {
                $in_months = implode(',', array_map('intval', $months));
                $date_conds = $wpdb->prepare(
                    "(YEAR(p.post_date) = %d AND MONTH(p.post_date) IN ({$in_months}))",
                    $year
                );
            }

            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $stats = $wpdb->get_row(
                "SELECT
                    COUNT(*) AS total,
                    ROUND(AVG(CAST(pm_r.meta_value AS DECIMAL(3,1))), 2) AS avg_rating
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND {$date_conds}"
            );

            $dist_rows = $wpdb->get_results(
                "SELECT CAST(pm_r.meta_value AS UNSIGNED) AS rating, COUNT(*) AS cnt
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND pm_r.meta_value BETWEEN '1' AND '5'
                 AND {$date_conds}
                 GROUP BY rating"
            );

            $src_rows = $wpdb->get_results(
                "SELECT pm_src.meta_value AS source, COUNT(*) AS cnt
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_src ON pm_src.post_id = p.ID AND pm_src.meta_key = 'avis_source'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND {$date_conds}
                 GROUP BY pm_src.meta_value
                 ORDER BY cnt DESC"
            );
            // phpcs:enable

            $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($dist_rows as $row) {
                $distribution[(int) $row->rating] = (int) $row->cnt;
            }

            $by_source = [];
            foreach ($src_rows as $row) {
                $by_source[] = ['source' => $row->source, 'count' => (int) $row->cnt];
            }

            $results[] = [
                'season'       => $s['season'],
                'year'         => $s['year'],
                'total'        => (int) ($stats->total ?? 0),
                'avg_rating'   => round((float) ($stats->avg_rating ?? 0), 2),
                'distribution' => $distribution,
                'by_source'    => $by_source,
            ];
        }

        return rest_ensure_response(['periods' => $results]);
    }

    // ── Dashboard custom date range comparison ──────────────────────────────

    public function dashboard_compare_range(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;

        $from1 = sanitize_text_field($req->get_param('from1'));
        $to1   = sanitize_text_field($req->get_param('to1'));
        $from2 = sanitize_text_field($req->get_param('from2'));
        $to2   = sanitize_text_field($req->get_param('to2'));

        $results = [];
        foreach ([['from' => $from1, 'to' => $to1], ['from' => $from2, 'to' => $to2]] as $range) {
            $date_conds = $wpdb->prepare(
                "(p.post_date >= %s AND p.post_date <= %s)",
                $range['from'] . ' 00:00:00',
                $range['to'] . ' 23:59:59'
            );

            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $stats = $wpdb->get_row(
                "SELECT
                    COUNT(*) AS total,
                    ROUND(AVG(CAST(pm_r.meta_value AS DECIMAL(3,1))), 2) AS avg_rating
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND {$date_conds}"
            );

            $dist_rows = $wpdb->get_results(
                "SELECT CAST(pm_r.meta_value AS UNSIGNED) AS rating, COUNT(*) AS cnt
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_r ON pm_r.post_id = p.ID AND pm_r.meta_key = 'avis_rating'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND pm_r.meta_value BETWEEN '1' AND '5'
                 AND {$date_conds}
                 GROUP BY rating"
            );

            $src_rows = $wpdb->get_results(
                "SELECT pm_src.meta_value AS source, COUNT(*) AS cnt
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_src ON pm_src.post_id = p.ID AND pm_src.meta_key = 'avis_source'
                 WHERE p.post_type = 'sj_avis' AND p.post_status = 'publish'
                 AND {$date_conds}
                 GROUP BY pm_src.meta_value
                 ORDER BY cnt DESC"
            );
            // phpcs:enable

            $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($dist_rows as $row) {
                $distribution[(int) $row->rating] = (int) $row->cnt;
            }

            $by_source = [];
            foreach ($src_rows as $row) {
                $by_source[] = ['source' => $row->source, 'count' => (int) $row->cnt];
            }

            $results[] = [
                'label'        => $range['from'] . ' → ' . $range['to'],
                'from'         => $range['from'],
                'to'           => $range['to'],
                'total'        => (int) ($stats->total ?? 0),
                'avg_rating'   => round((float) ($stats->avg_rating ?? 0), 2),
                'distribution' => $distribution,
                'by_source'    => $by_source,
            ];
        }

        return rest_ensure_response(['periods' => $results]);
    }

    // ── Front reviews (public, AJAX pagination) ────────────────────────────

    private const PERIOD_MONTHS = [
        'spring' => [3,4,5],
        'summer' => [6,7,8],
        'autumn' => [9,10,11],
        'winter' => [12,1,2],
    ];

    public function front_reviews(\WP_REST_Request $req): \WP_REST_Response {
        $page     = max(1, (int) $req->get_param('page'));
        $per_page = min(50, max(1, (int) $req->get_param('per_page')));
        $sort     = sanitize_text_field($req->get_param('sort'));
        $rating   = (int) $req->get_param('rating');
        $period   = sanitize_key($req->get_param('period'));
        $language = sanitize_key($req->get_param('language'));
        $travel   = sanitize_key($req->get_param('travel'));
        $search   = sanitize_text_field($req->get_param('search'));
        $lieu_id  = sanitize_key($req->get_param('lieu_id'));
        $lieu_ids = sanitize_text_field($req->get_param('lieu_ids'));
        $source_filter = sanitize_text_field($req->get_param('source_filter'));

        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
        ];

        // Sort
        if ($sort === 'rating_desc') {
            $args['meta_key'] = 'avis_rating';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
        } elseif ($sort === 'rating_asc') {
            $args['meta_key'] = 'avis_rating';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
        } else {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        }

        $meta_query = ['relation' => 'AND'];

        // Lieu filter
        if ($lieu_ids) {
            $lieux = array_filter(array_map('trim', explode(',', $lieu_ids)));
            if (!empty($lieux)) {
                $meta_query[] = ['key' => 'avis_lieu_id', 'value' => $lieux, 'compare' => 'IN'];
            }
        } elseif ($lieu_id && $lieu_id !== 'all') {
            $meta_query[] = ['key' => 'avis_lieu_id', 'value' => $lieu_id];
        }

        // Source filter
        if ($source_filter) {
            $sources = array_filter(array_map('trim', explode(',', $source_filter)));
            if (!empty($sources)) {
                $meta_query[] = ['key' => 'avis_source', 'value' => $sources, 'compare' => 'IN'];
            }
        }

        // Rating
        if ($rating >= 1 && $rating <= 5) {
            $meta_query[] = ['key' => 'avis_rating', 'value' => $rating, 'type' => 'NUMERIC'];
        }

        // Language
        if ($language) {
            $meta_query[] = ['key' => 'avis_language', 'value' => $language];
        }

        // Travel type
        if ($travel) {
            $meta_query[] = ['key' => 'avis_travel_type', 'value' => $travel];
        }

        // Period (season-based: visit_date month)
        if ($period && isset(self::PERIOD_MONTHS[$period])) {
            $months = self::PERIOD_MONTHS[$period];
            $month_clauses = [];
            foreach ($months as $m) {
                $month_clauses[] = ['key' => 'avis_visit_date', 'value' => sprintf('-%02d-', $m), 'compare' => 'LIKE'];
            }
            $meta_query[] = array_merge(['relation' => 'OR'], $month_clauses);
        }

        // Search
        if ($search) {
            $args['s'] = $search;
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        $query   = new \WP_Query($args);
        $reviews = array_map('sj_normalize_review', $query->posts);

        $response = rest_ensure_response($reviews);
        $response->header('X-WP-Total',      $query->found_posts);
        $response->header('X-WP-TotalPages', $query->max_num_pages);
        return $response;
    }

    // ── List reviews ──────────────────────────────────────────────────────────

    public function list_reviews(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;
        $page     = max(1, (int) $req->get_param('page'));
        $per_page = min(100, max(1, (int) $req->get_param('per_page')));
        $search   = sanitize_text_field($req->get_param('search'));
        $rating   = (int) $req->get_param('rating');
        $source   = sanitize_text_field($req->get_param('source'));
        $lieu_id  = sanitize_text_field($req->get_param('lieu_id'));
        $orderby  = in_array($req->get_param('orderby'), ['date', 'rating', 'author'], true) ? $req->get_param('orderby') : 'date';
        $order    = strtoupper($req->get_param('order')) === 'ASC' ? 'ASC' : 'DESC';
        $email    = sanitize_email($req->get_param('email'));

        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => $orderby === 'rating' ? 'meta_value_num' : ($orderby === 'author' ? 'title' : 'date'),
            'order'          => $order,
        ];

        if ($orderby === 'rating') {
            $args['meta_key'] = 'avis_rating';
        }

        if ($search) {
            $args['s'] = $search;
        }

        $meta_query = ['relation' => 'AND'];

        // Étend la recherche au texte et titre de l'avis (pas juste le post_title WP)
        if ($search) {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => 'avis_text',  'value' => '%' . $wpdb->esc_like($search) . '%', 'compare' => 'LIKE'],
                ['key' => 'avis_title', 'value' => '%' . $wpdb->esc_like($search) . '%', 'compare' => 'LIKE'],
            ];
        }

        if ($rating >= 1 && $rating <= 5) {
            $meta_query[] = ['key' => 'avis_rating', 'value' => $rating, 'type' => 'NUMERIC'];
        }

        if ($source) {
            $meta_query[] = ['key' => 'avis_source', 'value' => $source, 'compare' => '='];
        }

        if ($lieu_id) {
            $meta_query[] = ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='];
        }

        if ($email) {
            $meta_query[] = ['key' => 'avis_customer_email', 'value' => '%' . $wpdb->esc_like($email) . '%', 'compare' => 'LIKE'];
        }

        if (count($meta_query) > 1) { // > 1 car 'relation' est toujours présent
            $args['meta_query'] = $meta_query;
        }

        $query   = new \WP_Query($args);
        $reviews = array_map(fn($p) => sj_normalize_review($p, true), $query->posts);

        // Batch: compter les contributions par email
        $emails_in_page = array_filter(array_column($reviews, 'customer_email'));
        $email_counts   = [];
        if (!empty($emails_in_page)) {
            global $wpdb;
            $placeholders = implode(',', array_fill(0, count($emails_in_page), '%s'));
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_value AS email, COUNT(*) AS cnt
                     FROM {$wpdb->postmeta}
                     WHERE meta_key = 'avis_customer_email' AND meta_value IN ({$placeholders})
                     GROUP BY meta_value",
                    ...$emails_in_page
                )
            );
            foreach ($rows as $row) {
                $email_counts[$row->email] = (int) $row->cnt;
            }
        }
        $reviews = array_map(function ($r) use ($email_counts) {
            $r['contribution_count'] = isset($r['customer_email']) && $r['customer_email']
                ? ($email_counts[$r['customer_email']] ?? 1)
                : 1;
            return $r;
        }, $reviews);

        $response = rest_ensure_response($reviews);
        $response->header('X-WP-Total',      $query->found_posts);
        $response->header('X-WP-TotalPages', $query->max_num_pages);
        return $response;
    }

    // ── Get review ────────────────────────────────────────────────────────────

    public function get_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }
        return rest_ensure_response(sj_normalize_review($post, true));
    }

    // ── Create review ─────────────────────────────────────────────────────────

    public function create_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $data = $this->validate_body($req);
        if (is_wp_error($data)) return $data;

        $post_id = wp_insert_post([
            'post_type'   => 'sj_avis',
            'post_title'  => $data['author'],
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) return $post_id;

        $this->save_meta($post_id, $data);
        $this->invalidate_dashboard_cache();
        return rest_ensure_response(sj_normalize_review(get_post($post_id), true));
    }

    // ── Update review ─────────────────────────────────────────────────────────

    public function update_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }

        $data = $this->validate_body($req);
        if (is_wp_error($data)) return $data;

        wp_update_post(['ID' => $post->ID, 'post_title' => $data['author']]);
        $this->save_meta($post->ID, $data);
        $this->invalidate_dashboard_cache();

        return rest_ensure_response(sj_normalize_review(get_post($post->ID), true));
    }

    // ── Delete review ─────────────────────────────────────────────────────────

    public function delete_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }
        wp_delete_post($post->ID, true);
        $this->invalidate_dashboard_cache();
        return rest_ensure_response(['deleted' => true, 'id' => (int) $req['id']]);
    }

    // ── Lieux ─────────────────────────────────────────────────────────────────

    public function list_lieux(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;
        $lieux = $this->get_lieux();

        // Comptage des avis par lieu
        if (!empty($lieux)) {
            $counts_raw = $wpdb->get_results(
                "SELECT pm.meta_value AS lieu_id, COUNT(*) AS total
                 FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = 'avis_lieu_id'
                 AND pm.meta_value != ''
                 AND p.post_type = 'sj_avis'
                 AND p.post_status = 'publish'
                 GROUP BY pm.meta_value"
            );
            $counts = [];
            foreach ($counts_raw as $row) {
                $counts[$row->lieu_id] = (int) $row->total;
            }
            $lieux = array_map(function ($lieu) use ($counts) {
                $lieu['avis_count'] = $counts[$lieu['id']] ?? 0;
                return $lieu;
            }, $lieux);
        }

        return rest_ensure_response($lieux);
    }

    public function create_lieu(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $data = $this->validate_lieu_body($req);
        if (is_wp_error($data)) return $data;

        $lieux   = $this->get_lieux();
        $data['id'] = 'lieu_' . substr(md5(uniqid('', true)), 0, 8);
        $lieux[] = $data;
        update_option('sj_lieux', $lieux);

        return rest_ensure_response($data);
    }

    public function update_lieu(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $id   = sanitize_key($req['id']);
        $data = $this->validate_lieu_body($req);
        if (is_wp_error($data)) return $data;

        $lieux = $this->get_lieux();
        $found = false;
        foreach ($lieux as &$lieu) {
            if ($lieu['id'] === $id) {
                $lieu  = array_merge($lieu, $data);
                $found = true;
                break;
            }
        }
        unset($lieu);

        if (!$found) {
            return new \WP_Error('not_found', 'Lieu introuvable.', ['status' => 404]);
        }

        update_option('sj_lieux', $lieux);
        return rest_ensure_response(array_values(array_filter($lieux, fn($l) => $l['id'] === $id))[0]);
    }

    public function delete_lieu(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $id    = sanitize_key($req['id']);
        $lieux = $this->get_lieux();
        $new   = array_values(array_filter($lieux, fn($l) => $l['id'] !== $id));

        if (count($new) === count($lieux)) {
            return new \WP_Error('not_found', 'Lieu introuvable.', ['status' => 404]);
        }

        update_option('sj_lieux', $new);
        return rest_ensure_response(['deleted' => true, 'id' => $id]);
    }

    // ── Google Maps Sync ──────────────────────────────────────────────────────

    /**
     * POST /lieux/{id}/sync-google
     *
     * Fetch synchrone (1 GET, 2 champs) : rating + user_ratings_total.
     * Pas de cron (fetch < 1 s), pas d'import de commentaires individuels.
     * Anti-loop : verrou transient de 30 s (libéré immédiatement après réponse).
     */
    public function sync_google(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $lieu_id = sanitize_key($req['id']);
        $lieux   = $this->get_lieux();
        $lieu    = null;
        foreach ($lieux as $l) {
            if ($l['id'] === $lieu_id) { $lieu = $l; break; }
        }

        if (!$lieu) {
            return new \WP_Error('not_found', 'Lieu introuvable.', ['status' => 404]);
        }
        if (empty($lieu['place_id'])) {
            return new \WP_Error('no_place_id', 'Ce lieu n\'a pas de Place ID Google.', ['status' => 400]);
        }

        $settings = get_option('sj_reviews_settings', []);
        $api_key  = $settings['google_api_key'] ?? '';
        if (empty($api_key)) {
            return new \WP_Error('no_api_key', 'Clé API Google Maps non configurée dans Réglages.', ['status' => 400]);
        }

        // Anti-loop : verrou 30 s (doubles-clics, appels simultanés)
        $lock_key = 'sj_sync_lock_' . $lieu_id;
        if (get_transient($lock_key)) {
            return new \WP_Error('rate_limited', 'Sync déjà en cours, réessayez dans 30 secondes.', ['status' => 429]);
        }
        set_transient($lock_key, 1, 30);

        // Appel Google Places API — champs de base uniquement
        $url = add_query_arg([
            'place_id' => $lieu['place_id'],
            'fields'   => 'rating,user_ratings_total',
            'key'      => $api_key,
        ], 'https://maps.googleapis.com/maps/api/place/details/json');

        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            // Requis si la clé a des restrictions HTTP referrer dans Google Cloud
            'headers'   => ['Referer' => trailingslashit(get_site_url())],
        ]);

        delete_transient($lock_key); // Libère immédiatement après réponse

        if (is_wp_error($response)) {
            return new \WP_Error('http_error', $response->get_error_message(), ['status' => 502]);
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body      = json_decode(wp_remote_retrieve_body($response), true);
        $g_status  = $body['status'] ?? 'UNKNOWN';

        if ($g_status !== 'OK') {
            // Erreurs Google détaillées pour diagnostic
            $details = [
                'REQUEST_DENIED'  => 'Clé refusée (restrictions HTTP referrer ou Places API non activée).',
                'OVER_QUERY_LIMIT' => 'Quota dépassé.',
                'INVALID_REQUEST'  => 'Place ID invalide ou malformé.',
                'NOT_FOUND'        => 'Aucun lieu trouvé pour ce Place ID.',
            ];
            $msg = $body['error_message']
                ?? $details[$g_status]
                ?? "Erreur Google API : {$g_status} (HTTP {$http_code})";
            return new \WP_Error('google_error', $msg, ['status' => 502]);
        }

        $g_rating = round((float) ($body['result']['rating']             ?? 0), 1);
        $g_count  = (int)         ($body['result']['user_ratings_total'] ?? 0);

        // Persiste sur le lieu
        $all_lieux = $this->get_lieux();
        foreach ($all_lieux as &$l) {
            if ($l['id'] === $lieu_id) {
                $l['rating']        = $g_rating;
                $l['reviews_count'] = $g_count;
                $l['last_sync']     = current_time('Y-m-d H:i:s');
                break;
            }
        }
        unset($l);
        update_option('sj_lieux', $all_lieux);

        return rest_ensure_response([
            'rating'        => $g_rating,
            'reviews_count' => $g_count,
            'last_sync'     => current_time('Y-m-d H:i:s'),
        ]);
    }

    // ── Trustpilot Sync ─────────────────────────────────────────────────────

    /**
     * POST /lieux/{id}/sync-trustpilot
     *
     * Fetch Trustpilot Business Unit API : trustScore + numberOfReviews.
     */
    public function sync_trustpilot(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $lieu_id = sanitize_key($req['id']);
        $lieux   = $this->get_lieux();
        $lieu    = null;
        foreach ($lieux as $l) {
            if ($l['id'] === $lieu_id) { $lieu = $l; break; }
        }

        if (!$lieu) {
            return new \WP_Error('not_found', 'Lieu introuvable.', ['status' => 404]);
        }

        $domain = $lieu['trustpilot_domain'] ?? '';
        if (empty($domain)) {
            return new \WP_Error('no_domain', 'Ce lieu n\'a pas de domaine Trustpilot configuré.', ['status' => 400]);
        }

        $settings = get_option('sj_reviews_settings', []);
        $api_key  = $settings['trustpilot_api_key'] ?? '';
        if (empty($api_key)) {
            return new \WP_Error('no_api_key', 'Clé API Trustpilot non configurée dans Réglages.', ['status' => 400]);
        }

        // Anti-loop
        $lock_key = 'sj_sync_tp_lock_' . $lieu_id;
        if (get_transient($lock_key)) {
            return new \WP_Error('rate_limited', 'Sync en cours, réessayez dans 30 secondes.', ['status' => 429]);
        }
        set_transient($lock_key, 1, 30);

        $url = 'https://api.trustpilot.com/v1/business-units/find?' . http_build_query([
            'name'   => $domain,
            'apikey' => $api_key,
        ]);

        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => true]);
        delete_transient($lock_key);

        if (is_wp_error($response)) {
            return new \WP_Error('http_error', $response->get_error_message(), ['status' => 502]);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body['id'])) {
            $msg = $body['message'] ?? "Erreur Trustpilot (HTTP {$code})";
            return new \WP_Error('trustpilot_error', $msg, ['status' => 502]);
        }

        $tp_rating = round((float) ($body['score']['trustScore'] ?? 0), 1);
        $tp_count  = (int) ($body['numberOfReviews']['total'] ?? 0);
        $bu_id     = $body['id'];

        // Persist
        $all_lieux = $this->get_lieux();
        foreach ($all_lieux as &$l) {
            if ($l['id'] === $lieu_id) {
                $l['rating']           = $tp_rating;
                $l['reviews_count']    = $tp_count;
                $l['trustpilot_bu_id'] = $bu_id;
                $l['last_sync']        = current_time('Y-m-d H:i:s');
                break;
            }
        }
        unset($l);
        update_option('sj_lieux', $all_lieux);

        return rest_ensure_response([
            'rating'        => $tp_rating,
            'reviews_count' => $tp_count,
            'last_sync'     => current_time('Y-m-d H:i:s'),
        ]);
    }

    // ── TripAdvisor Sync ──────────────────────────────────────────────────────

    /**
     * POST /lieux/{id}/sync-tripadvisor
     *
     * Fetch TripAdvisor Content API : rating + num_reviews.
     */
    public function sync_tripadvisor(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $lieu_id = sanitize_key($req['id']);
        $lieux   = $this->get_lieux();
        $lieu    = null;
        foreach ($lieux as $l) {
            if ($l['id'] === $lieu_id) { $lieu = $l; break; }
        }

        if (!$lieu) {
            return new \WP_Error('not_found', 'Lieu introuvable.', ['status' => 404]);
        }

        $location_id = $lieu['tripadvisor_location_id'] ?? '';
        if (empty($location_id)) {
            return new \WP_Error('no_location_id', 'Ce lieu n\'a pas de Location ID TripAdvisor configuré.', ['status' => 400]);
        }

        $settings = get_option('sj_reviews_settings', []);
        $api_key  = $settings['tripadvisor_api_key'] ?? '';
        if (empty($api_key)) {
            return new \WP_Error('no_api_key', 'Clé API TripAdvisor non configurée dans Réglages.', ['status' => 400]);
        }

        // Anti-loop
        $lock_key = 'sj_sync_ta_lock_' . $lieu_id;
        if (get_transient($lock_key)) {
            return new \WP_Error('rate_limited', 'Sync en cours, réessayez dans 30 secondes.', ['status' => 429]);
        }
        set_transient($lock_key, 1, 30);

        $url = "https://api.content.tripadvisor.com/api/v1/location/{$location_id}/details?" . http_build_query([
            'key'      => $api_key,
            'language' => 'fr',
        ]);

        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['accept' => 'application/json'],
        ]);
        delete_transient($lock_key);

        if (is_wp_error($response)) {
            return new \WP_Error('http_error', $response->get_error_message(), ['status' => 502]);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200) {
            $msg = $body['message'] ?? "Erreur TripAdvisor (HTTP {$code})";
            return new \WP_Error('tripadvisor_error', $msg, ['status' => 502]);
        }

        $ta_rating = round((float) ($body['rating'] ?? 0), 1);
        $ta_count  = (int) ($body['num_reviews'] ?? 0);

        // Persist
        $all_lieux = $this->get_lieux();
        foreach ($all_lieux as &$l) {
            if ($l['id'] === $lieu_id) {
                $l['rating']        = $ta_rating;
                $l['reviews_count'] = $ta_count;
                $l['last_sync']     = current_time('Y-m-d H:i:s');
                break;
            }
        }
        unset($l);
        update_option('sj_lieux', $all_lieux);

        return rest_ensure_response([
            'rating'        => $ta_rating,
            'reviews_count' => $ta_count,
            'last_sync'     => current_time('Y-m-d H:i:s'),
        ]);
    }

    // ── Export CSV ─────────────────────────────────────────────────────────────

    /**
     * GET /export — CSV download of all reviews.
     */
    public function export_csv(\WP_REST_Request $req): \WP_REST_Response {
        $source  = sanitize_text_field($req->get_param('source'));
        $lieu_id = sanitize_text_field($req->get_param('lieu_id'));
        $rating  = (int) $req->get_param('rating');

        $meta_query = ['relation' => 'AND'];
        if ($source) {
            $meta_query[] = ['key' => 'avis_source', 'value' => $source, 'compare' => '='];
        }
        if ($lieu_id) {
            $meta_query[] = ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='];
        }
        if ($rating >= 1 && $rating <= 5) {
            $meta_query[] = ['key' => 'avis_rating', 'value' => $rating, 'type' => 'NUMERIC'];
        }

        $headers = ['id', 'author', 'rating', 'source', 'title', 'text', 'certified', 'lieu_id', 'visit_date', 'language', 'travel_type', 'date', 'customer_email', 'order_id'];

        // Batched export to limit memory usage
        $csv   = fopen('php://temp', 'r+');
        fputcsv($csv, $headers, ';');
        $count = 0;
        $page  = 1;
        $batch = 100;

        do {
            $args = [
                'post_type'      => 'sj_avis',
                'post_status'    => 'publish',
                'posts_per_page' => $batch,
                'paged'          => $page,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];
            if (count($meta_query) > 1) {
                $args['meta_query'] = $meta_query;
            }

            $posts = get_posts($args);
            foreach ($posts as $p) {
                $r   = sj_normalize_review($p, true);
                $row = [];
                foreach ($headers as $h) {
                    $val = $r[$h] ?? '';
                    if (is_bool($val)) $val = $val ? '1' : '0';
                    $row[] = (string) $val;
                }
                fputcsv($csv, $row, ';');
                $count++;
            }
            $page++;
        } while (count($posts) === $batch);

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return rest_ensure_response([
            'csv'      => $content,
            'filename' => 'sj-reviews-export-' . wp_date('Y-m-d') . '.csv',
            'count'    => $count,
        ]);
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function get_settings(\WP_REST_Request $req): \WP_REST_Response {
        $defaults = [
            'default_layout'      => 'slider-i',
            'default_preset'      => 'minimal',
            'star_color'          => '#f5a623',
            'certified_label'     => 'Certifié',
            'max_front'           => 5,
            'google_api_key'      => '',
            'trustpilot_api_key'  => '',
            'tripadvisor_api_key' => '',
            'linked_post_types'   => [],
            'sync_frequency'      => 'off',
            'criteria_labels'     => [
                'qualite_prix' => 'Qualité/prix',
                'ambiance'     => 'Ambiance',
                'experience'   => 'Expérience',
                'paysage'      => 'Paysage',
            ],
            'rating_labels'       => [
                '5' => 'Excellent',
                '4' => 'Bien',
                '3' => 'Moyen',
                '2' => 'Médiocre',
                '1' => 'Horrible',
            ],
            'bubble_color'        => '#34d399',
            'text_words'          => 40,
            'autoplay_delay'      => 4000,
            'last_sync'           => get_option('sj_reviews_last_sync', ''),
        ];
        $saved = get_option('sj_reviews_settings', []);
        if (isset($saved['linked_post_types']) && !is_array($saved['linked_post_types'])) {
            $saved['linked_post_types'] = [];
        }
        if (isset($saved['criteria_labels']) && !is_array($saved['criteria_labels'])) {
            $saved['criteria_labels'] = $defaults['criteria_labels'];
        }
        if (isset($saved['rating_labels']) && !is_array($saved['rating_labels'])) {
            $saved['rating_labels'] = $defaults['rating_labels'];
        }
        $merged = array_merge($defaults, $saved);
        $merged['last_sync'] = get_option('sj_reviews_last_sync', '');
        return rest_ensure_response($merged);
    }

    public function list_post_types(\WP_REST_Request $req): \WP_REST_Response {
        $post_types = get_post_types(['public' => true, 'show_ui' => true], 'objects');
        $excluded   = ['sj_avis', 'attachment'];
        $result     = [];
        foreach ($post_types as $slug => $pt) {
            if (in_array($slug, $excluded, true)) continue;
            $result[] = [
                'slug'  => $slug,
                'label' => $pt->labels->singular_name ?? $pt->label ?? $slug,
            ];
        }
        return rest_ensure_response($result);
    }

    public function list_linked_posts(\WP_REST_Request $req): \WP_REST_Response {
        $settings      = get_option('sj_reviews_settings', []);
        $allowed_types = array_map('sanitize_key', (array) ($settings['linked_post_types'] ?? []));

        if (empty($allowed_types)) {
            return rest_ensure_response([]);
        }

        $requested_type = sanitize_key($req->get_param('post_type'));
        if ($requested_type && !in_array($requested_type, $allowed_types, true)) {
            return rest_ensure_response([]);
        }
        $types_to_query = $requested_type ? [$requested_type] : $allowed_types;

        $posts = get_posts([
            'post_type'      => $types_to_query,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        return rest_ensure_response(array_map(fn($p) => [
            'id'        => $p->ID,
            'title'     => get_the_title($p),
            'post_type' => $p->post_type,
        ], $posts));
    }

    public function test_google_key(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $key = sanitize_text_field($req['key'] ?? '');
        if (empty($key)) {
            return new \WP_Error('missing_key', 'Clé API manquante.', ['status' => 400]);
        }

        $url = add_query_arg([
            'place_id' => 'ChIJLU7jZClu5kcR4PcOOO6p3I0',
            'fields'   => 'name',
            'key'      => $key,
        ], 'https://maps.googleapis.com/maps/api/place/details/json');

        // Envoie le Referer du site pour passer les restrictions HTTP referrer Google Cloud
        $referer  = trailingslashit(get_site_url());
        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['Referer' => $referer],
        ]);

        if (is_wp_error($response)) {
            return rest_ensure_response(['ok' => false, 'message' => $response->get_error_message()]);
        }

        $body   = json_decode(wp_remote_retrieve_body($response), true);
        $status = $body['status'] ?? 'UNKNOWN';

        if (in_array($status, ['OK', 'NOT_FOUND'], true)) {
            return rest_ensure_response(['ok' => true, 'message' => '']);
        }

        $messages = [
            'REQUEST_DENIED'   => 'Clé refusée. Si vous utilisez des restrictions HTTP referrer, ajoutez "' . get_site_url() . '/*" dans Cloud Console. Pour un usage serveur, préférez les restrictions par IP ou aucune restriction.',
            'OVER_QUERY_LIMIT' => 'Quota dépassé.',
        ];
        return rest_ensure_response(['ok' => false, 'message' => $messages[$status] ?? ($body['error_message'] ?? "Statut Google: $status")]);
    }

    public function test_trustpilot_key(\WP_REST_Request $req): \WP_REST_Response {
        $key = sanitize_text_field($req['key'] ?? '');
        if (empty($key)) {
            return rest_ensure_response(['ok' => false, 'message' => 'Clé API manquante.']);
        }

        // Test avec un domaine connu
        $url = 'https://api.trustpilot.com/v1/business-units/find?' . http_build_query([
            'name'   => 'trustpilot.com',
            'apikey' => $key,
        ]);

        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => true]);
        if (is_wp_error($response)) {
            return rest_ensure_response(['ok' => false, 'message' => $response->get_error_message()]);
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            return rest_ensure_response(['ok' => true, 'message' => '']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return rest_ensure_response(['ok' => false, 'message' => $body['message'] ?? "HTTP {$code}"]);
    }

    public function test_tripadvisor_key(\WP_REST_Request $req): \WP_REST_Response {
        $key = sanitize_text_field($req['key'] ?? '');
        if (empty($key)) {
            return rest_ensure_response(['ok' => false, 'message' => 'Clé API manquante.']);
        }

        // Test avec la Tour Eiffel (location_id = 188757)
        $url = "https://api.content.tripadvisor.com/api/v1/location/188757/details?" . http_build_query([
            'key'      => $key,
            'language' => 'fr',
        ]);

        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['accept' => 'application/json'],
        ]);
        if (is_wp_error($response)) {
            return rest_ensure_response(['ok' => false, 'message' => $response->get_error_message()]);
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            return rest_ensure_response(['ok' => true, 'message' => '']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return rest_ensure_response(['ok' => false, 'message' => $body['message'] ?? "HTTP {$code}"]);
    }

    public function save_settings(\WP_REST_Request $req): \WP_REST_Response {
        $body    = $req->get_json_params();
        $allowed = [
            'default_layout', 'default_preset', 'star_color', 'certified_label',
            'max_front', 'google_api_key', 'trustpilot_api_key', 'tripadvisor_api_key',
            'linked_post_types', 'sync_frequency', 'criteria_labels', 'rating_labels',
            'bubble_color', 'text_words', 'autoplay_delay',
        ];
        // Merge with existing to avoid losing keys not sent
        $existing = get_option('sj_reviews_settings', []);
        $clean    = $existing;
        foreach ($allowed as $key) {
            if (!isset($body[$key])) continue;
            if ($key === 'linked_post_types') {
                $clean[$key] = array_values(array_map('sanitize_key', array_filter((array) $body[$key])));
            } elseif ($key === 'criteria_labels' || $key === 'rating_labels') {
                $labels = (array) $body[$key];
                $clean[$key] = array_map('sanitize_text_field', $labels);
            } elseif (in_array($key, ['text_words', 'autoplay_delay', 'max_front'], true)) {
                $clean[$key] = max(1, (int) $body[$key]);
            } else {
                $clean[$key] = sanitize_text_field((string) $body[$key]);
            }
        }

        // Reschedule cron if sync_frequency changed
        $old_freq = $existing['sync_frequency'] ?? 'off';
        $new_freq = $clean['sync_frequency'] ?? 'off';
        if ($old_freq !== $new_freq) {
            require_once SJ_REVIEWS_DIR . 'includes/class-cron.php';
            \SJ_Reviews\Includes\Cron::reschedule($new_freq);
        }

        update_option('sj_reviews_settings', $clean);
        return rest_ensure_response($clean);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    /**
     * GET /import/post-matches — Liste de posts pour le mapping produits→excursions
     */
    public function import_post_matches(\WP_REST_Request $req): \WP_REST_Response {
        $settings      = get_option('sj_reviews_settings', []);
        $allowed_types = array_map('sanitize_key', (array) ($settings['linked_post_types'] ?? []));
        $search        = sanitize_text_field($req->get_param('search'));
        $req_type      = sanitize_key($req->get_param('post_type'));

        $types = $allowed_types ?: ['post', 'page'];
        if ($req_type && in_array($req_type, $types, true)) {
            $types = [$req_type];
        }

        $args = [
            'post_type'      => $types,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        if ($search) {
            $args['s'] = $search;
        }

        $posts = get_posts($args);
        return rest_ensure_response(array_map(fn($p) => [
            'id'        => $p->ID,
            'title'     => get_the_title($p),
            'post_type' => $p->post_type,
        ], $posts));
    }

    /**
     * Valide un tableau de lignes CSV et détecte les doublons en un seul batch.
     *
     * @return array<int, array{index:int, row:array, status:'new'|'duplicate'|'error', reason?:string}>
     */
    /**
     * Validate a date string: must be YYYY-MM-DD with valid month/day.
     */
    private function is_valid_date(string $date): bool {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) return false;
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    private function validate_rows(array $rows): array {
        global $wpdb;

        // Batch : récupère tous les order_id déjà en base en une seule requête
        $order_ids = array_filter(array_map(fn($r) => trim((string) ($r['order_id'] ?? '')), $rows));
        $existing_order_ids = [];
        if (!empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '%s'));
            $existing_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_value, post_id FROM {$wpdb->postmeta}
                     WHERE meta_key = 'avis_order_id' AND meta_value IN ({$placeholders})",
                    ...$order_ids
                )
            );
            foreach ($existing_rows as $e) {
                $existing_order_ids[$e->meta_value] = (int) $e->post_id;
            }
        }

        // Enriched duplicate detection: batch fetch email+date combos for fuzzy matching
        $emails = array_filter(array_map(fn($r) => strtolower(trim((string) ($r['email'] ?? ''))), $rows));
        $existing_email_dates = [];
        if (!empty($emails)) {
            $emails_unique = array_unique($emails);
            $placeholders  = implode(',', array_fill(0, count($emails_unique), '%s'));
            $email_rows    = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT pm_email.post_id, pm_email.meta_value AS email, p.post_date
                     FROM {$wpdb->postmeta} pm_email
                     INNER JOIN {$wpdb->posts} p ON p.ID = pm_email.post_id
                     WHERE pm_email.meta_key = 'avis_customer_email'
                     AND LOWER(pm_email.meta_value) IN ({$placeholders})
                     AND p.post_type = 'sj_avis'",
                    ...array_values($emails_unique)
                )
            );
            foreach ($email_rows as $er) {
                $key = strtolower($er->email) . '|' . substr($er->post_date, 0, 10);
                $existing_email_dates[$key] = (int) $er->post_id;
            }
        }

        $results = [];
        foreach ($rows as $i => $row) {
            $order_id   = sanitize_text_field($row['order_id'] ?? '');
            $raw_rating = $row['rating'] ?? 0;
            $rating     = (int) round((float) str_replace(',', '.', (string) $raw_rating));
            $author     = sanitize_text_field($row['author'] ?? '');
            $email      = strtolower(trim((string) ($row['email'] ?? '')));
            $eval_date  = trim((string) ($row['eval_date'] ?? ''));
            $visit_date = trim((string) ($row['visit_date'] ?? ''));
            $booking_date = trim((string) ($row['booking_date'] ?? ''));

            if (empty($author)) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Auteur manquant'];
                continue;
            }
            if ($rating < 1 || $rating > 5) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Note invalide (doit être 1–5) — reçu : ' . esc_html((string) $raw_rating)];
                continue;
            }

            // Date validation
            if ($eval_date && !$this->is_valid_date($eval_date)) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Date d\'évaluation invalide : ' . esc_html($eval_date) . ' (format attendu : AAAA-MM-JJ)'];
                continue;
            }
            if ($visit_date && !$this->is_valid_date($visit_date)) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Date de visite invalide : ' . esc_html($visit_date) . ' (format attendu : AAAA-MM-JJ)'];
                continue;
            }
            if ($booking_date && !$this->is_valid_date($booking_date)) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Date de réservation invalide : ' . esc_html($booking_date) . ' (format attendu : AAAA-MM-JJ)'];
                continue;
            }

            // Duplicate: order_id match
            if ($order_id && isset($existing_order_ids[$order_id])) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'duplicate', 'reason' => "Doublon — N° commande {$order_id} déjà importé (ID #{$existing_order_ids[$order_id]})"];
                continue;
            }

            // Duplicate: email + eval_date match (same person, same day)
            if ($email && $eval_date) {
                $combo_key = $email . '|' . $eval_date;
                if (isset($existing_email_dates[$combo_key])) {
                    $results[] = ['index' => $i, 'row' => $row, 'status' => 'duplicate', 'reason' => "Doublon probable — même email ({$email}) et même date (ID #{$existing_email_dates[$combo_key]})"];
                    continue;
                }
            }

            $results[] = ['index' => $i, 'row' => $row, 'status' => 'new'];
        }

        return $results;
    }

    /**
     * POST /import/preview
     *
     * Body: {
     *   rows: array<{product, order_id, booking_date, visit_date, eval_date, author, email, phone, rating, title, text}>,
     *   defaults: { lieu_id, source, certified, language, sub_criteria_auto },
     *   product_map: { "Produit CSV": post_id }
     * }
     *
     * Returns: array<{ row, status: 'new'|'duplicate'|'error', reason? }>
     */
    public function import_preview(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params();
        $rows = (array) ($body['rows'] ?? []);

        if (empty($rows)) {
            return new \WP_Error('no_rows', 'Aucune ligne à importer.', ['status' => 422]);
        }

        $preview = $this->validate_rows($rows);
        $counts  = ['new' => 0, 'duplicate' => 0, 'error' => 0];
        foreach ($preview as $p) { $counts[$p['status']]++; }

        return rest_ensure_response(['rows' => $preview, 'counts' => $counts]);
    }

    /**
     * POST /import/execute
     *
     * Same body as preview. Creates 'new' rows, skips duplicates/errors.
     * Returns: { imported, skipped, errors: [] }
     */
    public function import_execute(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $body        = $req->get_json_params();
        $rows        = (array) ($body['rows'] ?? []);
        $defaults    = (array) ($body['defaults'] ?? []);
        $product_map = (array) ($body['product_map'] ?? []);

        if (empty($rows)) {
            return new \WP_Error('no_rows', 'Aucune ligne à importer.', ['status' => 422]);
        }

        $lieu_id          = sanitize_key($defaults['lieu_id'] ?? '');
        $source           = sanitize_text_field($defaults['source'] ?? 'regiondo');
        $certified        = (bool) ($defaults['certified'] ?? true);
        $language         = in_array($defaults['language'] ?? 'fr', ['fr','en','it','de','es'], true) ? $defaults['language'] : 'fr';
        $sub_crit_auto    = (bool) ($defaults['sub_criteria_auto'] ?? true);
        $allowed_sources  = ['google', 'tripadvisor', 'facebook', 'trustpilot', 'regiondo', 'direct', 'autre'];
        if (!in_array($source, $allowed_sources, true)) $source = 'regiondo';

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        $validated = $this->validate_rows($rows);

        foreach ($validated as $item) {
            $i   = $item['index'];
            $row = $item['row'];

            if ($item['status'] === 'error') {
                $errors[] = "Ligne {$i}: " . ($item['reason'] ?? 'invalide');
                continue;
            }
            if ($item['status'] === 'duplicate') {
                $skipped++;
                continue;
            }

            $order_id = sanitize_text_field($row['order_id'] ?? '');
            $rating   = (int) round((float) str_replace(',', '.', (string) ($row['rating'] ?? 0)));
            $author   = sanitize_text_field($row['author'] ?? '');

            // Date de publication = date d'évaluation
            $eval_date = sanitize_text_field($row['eval_date'] ?? '');
            $post_date = preg_match('/^\d{4}-\d{2}-\d{2}/', $eval_date) ? $eval_date . ' 12:00:00' : current_time('mysql');

            $post_id = wp_insert_post([
                'post_type'   => 'sj_avis',
                'post_title'  => $author,
                'post_status' => 'publish',
                'post_date'   => $post_date,
            ], true);

            if (is_wp_error($post_id)) {
                $errors[] = "Ligne {$i}: " . $post_id->get_error_message();
                continue;
            }

            // Sous-critères : hériter de la note globale si option activée
            $sub_val = $sub_crit_auto ? $rating : 0;

            // Produit → post lié
            $product_key    = trim($row['product'] ?? '');
            $linked_post_id = (int) ($product_map[$product_key] ?? 0);

            // Dates
            $visit_date    = sanitize_text_field($row['visit_date'] ?? '');
            $booking_date  = sanitize_text_field($row['booking_date'] ?? '');

            update_post_meta($post_id, 'avis_author',          $author);
            update_post_meta($post_id, 'avis_title',           sanitize_text_field($row['title'] ?? ''));
            update_post_meta($post_id, 'avis_rating',          $rating);
            update_post_meta($post_id, 'avis_text',            sanitize_textarea_field($row['text'] ?? ''));
            update_post_meta($post_id, 'avis_certified',       $certified ? 1 : 0);
            update_post_meta($post_id, 'avis_source',          $source);
            update_post_meta($post_id, 'avis_lieu_id',         $lieu_id);
            update_post_meta($post_id, 'avis_language',        $language);
            update_post_meta($post_id, 'avis_travel_type',     '');
            update_post_meta($post_id, 'avis_qualite_prix',    $sub_val);
            update_post_meta($post_id, 'avis_ambiance',        $sub_val);
            update_post_meta($post_id, 'avis_experience',      $sub_val);
            update_post_meta($post_id, 'avis_paysage',         $sub_val);
            update_post_meta($post_id, 'avis_order_id',        $order_id);
            update_post_meta($post_id, 'avis_customer_email',  sanitize_email($row['email'] ?? ''));
            update_post_meta($post_id, 'avis_customer_phone',  sanitize_text_field($row['phone'] ?? ''));

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date)) {
                update_post_meta($post_id, 'avis_visit_date', $visit_date);
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $booking_date)) {
                update_post_meta($post_id, 'avis_booking_date', $booking_date);
            }
            if ($linked_post_id > 0) {
                update_post_meta($post_id, 'avis_linked_post', $linked_post_id);
            }

            // Auto-sync place_id depuis lieu
            if ($lieu_id) {
                $lieux = (array) get_option('sj_lieux', []);
                foreach ($lieux as $l) {
                    if ($l['id'] === $lieu_id && !empty($l['place_id'])) {
                        update_post_meta($post_id, 'avis_place_id', sanitize_text_field($l['place_id']));
                        break;
                    }
                }
            }

            $imported++;
        }

        if ($imported > 0) {
            $this->invalidate_dashboard_cache();
        }

        return rest_ensure_response([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function get_lieux(): array {
        return (array) get_option('sj_lieux', []);
    }

    private function validate_lieu_body(\WP_REST_Request $req): array|\WP_Error {
        $body = $req->get_json_params();
        if (empty($body['name'])) {
            return new \WP_Error('missing_name', 'Le nom du lieu est requis.', ['status' => 422]);
        }
        $allowed_sources = ['google', 'tripadvisor', 'facebook', 'trustpilot', 'regiondo', 'direct', 'autre'];
        return [
            'name'                     => sanitize_text_field($body['name']),
            'place_id'                 => sanitize_text_field($body['place_id'] ?? ''),
            'source'                   => in_array($body['source'] ?? 'google', $allowed_sources, true) ? $body['source'] : 'google',
            'address'                  => sanitize_text_field($body['address'] ?? ''),
            'active'                   => (bool) ($body['active'] ?? true),
            'trustpilot_domain'        => sanitize_text_field($body['trustpilot_domain'] ?? ''),
            'tripadvisor_location_id'  => sanitize_text_field($body['tripadvisor_location_id'] ?? ''),
        ];
    }

    private function validate_body(\WP_REST_Request $req): array|\WP_Error {
        $body = $req->get_json_params();
        if (empty($body['author'])) {
            return new \WP_Error('missing_author', 'Le champ auteur est requis.', ['status' => 422]);
        }
        $rating = (int) ($body['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            return new \WP_Error('invalid_rating', 'La note doit être entre 1 et 5.', ['status' => 422]);
        }
        $allowed_sources = ['google', 'tripadvisor', 'facebook', 'trustpilot', 'regiondo', 'direct', 'autre'];
        $crit_val = function(string $k) use ($body): int {
            $v = (int) ($body[$k] ?? 0);
            return ($v >= 1 && $v <= 5) ? $v : 0;
        };
        return [
            'author'         => sanitize_text_field($body['author']),
            'avis_title'     => sanitize_text_field($body['avis_title'] ?? ''),
            'rating'         => $rating,
            'text'           => sanitize_textarea_field($body['text'] ?? ''),
            'certified'      => (bool) ($body['certified'] ?? false),
            'source'         => in_array($body['source'] ?? 'google', $allowed_sources, true) ? $body['source'] : 'google',
            'lieu_id'        => sanitize_key($body['lieu_id'] ?? ''),
            'linked_post_id' => (int) ($body['linked_post_id'] ?? 0),
            'qualite_prix'   => $crit_val('qualite_prix'),
            'ambiance'       => $crit_val('ambiance'),
            'experience'     => $crit_val('experience'),
            'paysage'        => $crit_val('paysage'),
            // Contexte de visite
            'visit_date'     => (function() use ($body): string {
                $d = sanitize_text_field($body['visit_date'] ?? '');
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
            })(),
            'language'       => in_array($body['language'] ?? 'fr', ['fr','en','it','de','es'], true) ? ($body['language'] ?? 'fr') : 'fr',
            'travel_type'    => in_array($body['travel_type'] ?? '', ['','couple','solo','famille','amis','affaires'], true) ? ($body['travel_type'] ?? '') : '',
        ];
    }

    private function save_meta(int $post_id, array $data): void {
        update_post_meta($post_id, 'avis_author',       $data['author']);
        update_post_meta($post_id, 'avis_title',        $data['avis_title'] ?? '');
        update_post_meta($post_id, 'avis_rating',       $data['rating']);
        update_post_meta($post_id, 'avis_text',         $data['text']);
        update_post_meta($post_id, 'avis_certified',    $data['certified'] ? 1 : 0);
        update_post_meta($post_id, 'avis_source',       $data['source']);
        update_post_meta($post_id, 'avis_lieu_id',      $data['lieu_id']);
        update_post_meta($post_id, 'avis_linked_post',  $data['linked_post_id'] ?: '');
        update_post_meta($post_id, 'avis_qualite_prix', $data['qualite_prix']);
        update_post_meta($post_id, 'avis_ambiance',     $data['ambiance']);
        update_post_meta($post_id, 'avis_experience',   $data['experience']);
        update_post_meta($post_id, 'avis_paysage',      $data['paysage']);
        // Note : la note globale est toujours celle définie par l'utilisateur.
        // Les sous-critères sont informatifs et ne l'écrasent plus.
        // Contexte de visite
        if ($data['visit_date']) {
            update_post_meta($post_id, 'avis_visit_date',  $data['visit_date']);
        } else {
            delete_post_meta($post_id, 'avis_visit_date');
        }
        update_post_meta($post_id, 'avis_language',     $data['language']);
        update_post_meta($post_id, 'avis_travel_type',  $data['travel_type']);

        // Auto-synchronise avis_place_id depuis le lieu si non défini
        if (!get_post_meta($post_id, 'avis_place_id', true) && !empty($data['lieu_id'])) {
            $lieux = (array) get_option('sj_lieux', []);
            foreach ($lieux as $l) {
                if ($l['id'] === $data['lieu_id'] && !empty($l['place_id'])) {
                    update_post_meta($post_id, 'avis_place_id', sanitize_text_field($l['place_id']));
                    break;
                }
            }
        }
    }
}
