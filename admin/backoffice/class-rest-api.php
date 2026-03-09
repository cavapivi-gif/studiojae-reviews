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
 */
class RestApi {

    private string $ns = 'sj-reviews/v1';

    public function init(): void {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route($this->ns, '/dashboard', [
            'methods'             => 'GET',
            'callback'            => [$this, 'dashboard'],
            'permission_callback' => [$this, 'is_manager'],
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

        // sync-status supprimé — endpoint mort

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

        // Test de la clé API Google
        register_rest_route($this->ns, '/settings/test-google-key', [
            'methods'             => 'POST',
            'callback'            => [$this, 'test_google_key'],
            'permission_callback' => [$this, 'is_manager'],
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

    public function dashboard(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;

        $total = (int) wp_count_posts('sj_avis')->publish;

        $avg_raw = $wpdb->get_var(
            "SELECT AVG(CAST(meta_value AS DECIMAL(3,1)))
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'"
        );
        $avg = round((float) $avg_raw, 1);

        // Répartition par note
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE pm.meta_key = 'avis_rating' AND pm.meta_value = %s
                 AND p.post_type = 'sj_avis' AND p.post_status = 'publish'",
                (string) $i
            ));
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
             WHERE pm_src.meta_key = 'avis_source'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
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

        // Avis récents
        $recent_posts = get_posts([
            'post_type'      => 'sj_avis',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        $recent = array_map(fn($p) => sj_normalize_review($p, true), $recent_posts);

        return rest_ensure_response([
            'total'        => $total,
            'avg_rating'   => $avg,
            'distribution' => $distribution,
            'by_source'    => $by_source,
            'google_total' => $google_total,
            'google_avg'   => $google_avg,
            'recent'       => $recent,
        ]);
    }

    // ── List reviews ──────────────────────────────────────────────────────────

    public function list_reviews(\WP_REST_Request $req): \WP_REST_Response {
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

        return rest_ensure_response(sj_normalize_review(get_post($post->ID), true));
    }

    // ── Delete review ─────────────────────────────────────────────────────────

    public function delete_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }
        wp_delete_post($post->ID, true);
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

    // sync_status supprimé — endpoint mort (retournait toujours 'idle')

    // ── Settings ──────────────────────────────────────────────────────────────

    public function get_settings(\WP_REST_Request $req): \WP_REST_Response {
        $defaults = [
            'default_layout'    => 'slider-i',
            'default_preset'    => 'minimal',
            'star_color'        => '#f5a623',
            'certified_label'   => 'Certifié',
            'max_front'         => 5,
            'google_api_key'    => '',
            'linked_post_types' => [],
        ];
        $saved = get_option('sj_reviews_settings', []);
        // Ensure linked_post_types is always an array
        if (isset($saved['linked_post_types']) && !is_array($saved['linked_post_types'])) {
            $saved['linked_post_types'] = [];
        }
        return rest_ensure_response(array_merge($defaults, $saved));
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

    public function save_settings(\WP_REST_Request $req): \WP_REST_Response {
        $body    = $req->get_json_params();
        $allowed = ['default_layout', 'default_preset', 'star_color', 'certified_label', 'max_front', 'google_api_key', 'linked_post_types'];
        $clean   = [];
        foreach ($allowed as $key) {
            if (!isset($body[$key])) continue;
            if ($key === 'linked_post_types') {
                $clean[$key] = array_values(array_map('sanitize_key', array_filter((array) $body[$key])));
            } else {
                $clean[$key] = sanitize_text_field((string) $body[$key]);
            }
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
    private function validate_rows(array $rows): array {
        global $wpdb;

        // Batch : récupère tous les order_id déjà en base en une seule requête
        $order_ids = array_filter(array_map(fn($r) => trim((string) ($r['order_id'] ?? '')), $rows));
        $existing_set = [];
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
                $existing_set[$e->meta_value] = (int) $e->post_id;
            }
        }

        $results = [];
        foreach ($rows as $i => $row) {
            $order_id = sanitize_text_field($row['order_id'] ?? '');
            $raw_rating = $row['rating'] ?? 0;
            // Supporte float ("4.5" → 5) et virgule française ("4,5" → 5)
            $rating   = (int) round((float) str_replace(',', '.', (string) $raw_rating));
            $author   = sanitize_text_field($row['author'] ?? '');

            if (empty($author)) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Auteur manquant'];
                continue;
            }
            if ($rating < 1 || $rating > 5) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'error', 'reason' => 'Note invalide (doit être 1–5) — reçu : ' . esc_html((string) $raw_rating)];
                continue;
            }
            if ($order_id && isset($existing_set[$order_id])) {
                $results[] = ['index' => $i, 'row' => $row, 'status' => 'duplicate', 'reason' => "Doublon — N° commande {$order_id} déjà importé (ID #{$existing_set[$order_id]})"];
                continue;
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
            'name'     => sanitize_text_field($body['name']),
            'place_id' => sanitize_text_field($body['place_id'] ?? ''),
            'source'   => in_array($body['source'] ?? 'google', $allowed_sources, true) ? $body['source'] : 'google',
            'address'  => sanitize_text_field($body['address'] ?? ''),
            'active'   => (bool) ($body['active'] ?? true),
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
