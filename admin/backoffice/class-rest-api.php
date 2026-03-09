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

        // Statut de la sync (polling depuis le front)
        register_rest_route($this->ns, '/lieux/(?P<id>[a-z0-9_-]+)/sync-status', [
            'methods'             => 'GET',
            'callback'            => [$this, 'sync_status'],
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

        // Répartition par source
        $sources_raw = $wpdb->get_results(
            "SELECT pm.meta_value AS source, COUNT(*) AS total
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'avis_source'
             AND p.post_type = 'sj_avis'
             AND p.post_status = 'publish'
             GROUP BY pm.meta_value
             ORDER BY total DESC"
        );
        $by_source = [];
        foreach ($sources_raw as $row) {
            $by_source[] = ['source' => $row->source, 'count' => (int) $row->total];
        }

        // Avis récents
        $recent_posts = get_posts([
            'post_type'      => 'sj_avis',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        $recent = array_map(fn($p) => sj_normalize_review($p), $recent_posts);

        return rest_ensure_response([
            'total'        => $total,
            'avg_rating'   => $avg,
            'distribution' => $distribution,
            'by_source'    => $by_source,
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

        $meta_query = [];

        if ($rating >= 1 && $rating <= 5) {
            $meta_query[] = ['key' => 'avis_rating', 'value' => $rating, 'type' => 'NUMERIC'];
        }

        if ($source) {
            $meta_query[] = ['key' => 'avis_source', 'value' => $source, 'compare' => '='];
        }

        if ($lieu_id) {
            $meta_query[] = ['key' => 'avis_lieu_id', 'value' => $lieu_id, 'compare' => '='];
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        $query   = new \WP_Query($args);
        $reviews = array_map(fn($p) => sj_normalize_review($p), $query->posts);

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
        return rest_ensure_response(sj_normalize_review($post));
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
        return rest_ensure_response(sj_normalize_review(get_post($post_id)));
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

        return rest_ensure_response(sj_normalize_review(get_post($post->ID)));
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
            'headers'   => ['Referer' => get_site_url()],
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

    /**
     * GET /lieux/{id}/sync-status — conservé pour compatibilité (retourne toujours idle)
     */
    public function sync_status(\WP_REST_Request $req): \WP_REST_Response {
        return rest_ensure_response(['status' => 'idle']);
    }

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

        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => true]);

        if (is_wp_error($response)) {
            return rest_ensure_response(['ok' => false, 'message' => $response->get_error_message()]);
        }

        $body   = json_decode(wp_remote_retrieve_body($response), true);
        $status = $body['status'] ?? 'UNKNOWN';

        if (in_array($status, ['OK', 'NOT_FOUND'], true)) {
            return rest_ensure_response(['ok' => true, 'message' => '']);
        }

        $messages = [
            'REQUEST_DENIED'  => 'Clé refusée — vérifiez les restrictions et que Places API est activée.',
            'OVER_QUERY_LIMIT' => 'Quota dépassé.',
        ];
        return rest_ensure_response(['ok' => false, 'message' => $messages[$status] ?? "Statut Google: $status"]);
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
        return [
            'author'         => sanitize_text_field($body['author']),
            'avis_title'     => sanitize_text_field($body['avis_title'] ?? ''),
            'rating'         => $rating,
            'text'           => sanitize_textarea_field($body['text'] ?? ''),
            'certified'      => (bool) ($body['certified'] ?? false),
            'source'         => in_array($body['source'] ?? 'google', $allowed_sources, true) ? $body['source'] : 'google',
            'place_id'       => sanitize_text_field($body['place_id'] ?? ''),
            'lieu_id'        => sanitize_key($body['lieu_id'] ?? ''),
            'linked_post_id' => (int) ($body['linked_post_id'] ?? 0),
        ];
    }

    private function save_meta(int $post_id, array $data): void {
        update_post_meta($post_id, 'avis_author',      $data['author']);
        update_post_meta($post_id, 'avis_title',       $data['avis_title'] ?? '');
        update_post_meta($post_id, 'avis_rating',      $data['rating']);
        update_post_meta($post_id, 'avis_text',        $data['text']);
        update_post_meta($post_id, 'avis_certified',   $data['certified'] ? 1 : 0);
        update_post_meta($post_id, 'avis_source',      $data['source']);
        update_post_meta($post_id, 'avis_place_id',    $data['place_id']);
        update_post_meta($post_id, 'avis_lieu_id',     $data['lieu_id']);
        update_post_meta($post_id, 'avis_linked_post', $data['linked_post_id'] ?: '');
    }
}
