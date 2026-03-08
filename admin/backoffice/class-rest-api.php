<?php
namespace SJ_Reviews\Admin\Backoffice;

defined('ABSPATH') || exit;

/**
 * REST API — namespace sj-reviews/v1
 *
 * Endpoints :
 *  GET  /dashboard          → stats globales
 *  GET  /reviews            → liste paginée des avis
 *  GET  /reviews/{id}       → détail d'un avis
 *  POST /reviews            → créer un avis
 *  PUT  /reviews/{id}       → modifier un avis
 *  DEL  /reviews/{id}       → supprimer un avis
 *  GET  /settings           → lire les réglages
 *  POST /settings           → enregistrer les réglages
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
                    'page'     => ['default' => 1, 'type' => 'integer'],
                    'per_page' => ['default' => 20, 'type' => 'integer', 'maximum' => 100],
                    'search'   => ['default' => '', 'type' => 'string'],
                    'rating'   => ['default' => 0, 'type' => 'integer'],
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
        $total = wp_count_posts('sj_avis')->publish;

        // Note moyenne
        global $wpdb;
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

        // Avis récents
        $recent_posts = get_posts([
            'post_type'      => 'sj_avis',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        $recent = array_map(fn($p) => sj_normalize_review($p), $recent_posts);

        return rest_ensure_response([
            'total'        => (int) $total,
            'avg_rating'   => $avg,
            'distribution' => $distribution,
            'recent'       => $recent,
        ]);
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function list_reviews(\WP_REST_Request $req): \WP_REST_Response {
        $page     = max(1, (int) $req->get_param('page'));
        $per_page = min(100, max(1, (int) $req->get_param('per_page')));
        $search   = sanitize_text_field($req->get_param('search'));
        $rating   = (int) $req->get_param('rating');

        $args = [
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        if ($rating >= 1 && $rating <= 5) {
            $args['meta_query'] = [[
                'key'   => 'avis_rating',
                'value' => $rating,
                'type'  => 'NUMERIC',
            ]];
        }

        $query   = new \WP_Query($args);
        $reviews = array_map(fn($p) => sj_normalize_review($p), $query->posts);

        $response = rest_ensure_response($reviews);
        $response->header('X-WP-Total',      $query->found_posts);
        $response->header('X-WP-TotalPages', $query->max_num_pages);
        return $response;
    }

    // ── Get ───────────────────────────────────────────────────────────────────

    public function get_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }
        return rest_ensure_response(sj_normalize_review($post));
    }

    // ── Create ────────────────────────────────────────────────────────────────

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

    // ── Update ────────────────────────────────────────────────────────────────

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

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete_review(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $post = get_post((int) $req['id']);
        if (!$post || $post->post_type !== 'sj_avis') {
            return new \WP_Error('not_found', 'Avis introuvable.', ['status' => 404]);
        }
        wp_delete_post($post->ID, true);
        return rest_ensure_response(['deleted' => true, 'id' => (int) $req['id']]);
    }

    // ── Settings ─────────────────────────────────────────────────────────────

    public function get_settings(\WP_REST_Request $req): \WP_REST_Response {
        $defaults = [
            'place_id'         => '',
            'default_layout'   => 'slider-i',
            'default_preset'   => 'minimal',
            'star_color'       => '#f5a623',
            'certified_label'  => 'Certifié',
            'max_front'        => 5,
        ];
        return rest_ensure_response(array_merge($defaults, get_option('sj_reviews_settings', [])));
    }

    public function save_settings(\WP_REST_Request $req): \WP_REST_Response {
        $body = $req->get_json_params();
        $allowed = ['place_id', 'default_layout', 'default_preset', 'star_color', 'certified_label', 'max_front'];
        $clean   = [];
        foreach ($allowed as $key) {
            if (isset($body[$key])) {
                $clean[$key] = sanitize_text_field((string) $body[$key]);
            }
        }
        update_option('sj_reviews_settings', $clean);
        return rest_ensure_response($clean);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validate_body(\WP_REST_Request $req): array|\WP_Error {
        $body = $req->get_json_params();
        if (empty($body['author'])) {
            return new \WP_Error('missing_author', 'Le champ auteur est requis.', ['status' => 422]);
        }
        $rating = (int) ($body['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            return new \WP_Error('invalid_rating', 'La note doit être entre 1 et 5.', ['status' => 422]);
        }
        return [
            'author'    => sanitize_text_field($body['author']),
            'rating'    => $rating,
            'text'      => sanitize_textarea_field($body['text'] ?? ''),
            'certified' => (bool) ($body['certified'] ?? false),
            'source'    => sanitize_text_field($body['source'] ?? 'google'),
            'place_id'  => sanitize_text_field($body['place_id'] ?? ''),
        ];
    }

    private function save_meta(int $post_id, array $data): void {
        update_post_meta($post_id, 'avis_author',    $data['author']);
        update_post_meta($post_id, 'avis_rating',    $data['rating']);
        update_post_meta($post_id, 'avis_text',      $data['text']);
        update_post_meta($post_id, 'avis_certified', $data['certified'] ? 1 : 0);
        update_post_meta($post_id, 'avis_source',    $data['source']);
        update_post_meta($post_id, 'avis_place_id',  $data['place_id']);
    }
}
