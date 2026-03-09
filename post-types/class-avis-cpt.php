<?php
namespace SJ_Reviews\PostTypes;

defined('ABSPATH') || exit;

/**
 * CPT sj_avis — Post type "Avis clients"
 *
 * Champs (déclarés via ACF local field group si ACF Pro est actif,
 * sinon accessibles directement via les meta WP classiques) :
 *
 *  - avis_author   : Texte — Prénom + Nom de l'auteur
 *  - avis_rating   : Nombre (1-5) — Note en étoiles
 *  - avis_text     : Textarea — Texte de l'avis
 *  - avis_certified: Vrai/Faux — Badge "Certifié"
 *  - avis_source   : Texte — Source (Google, TripAdvisor, etc.)
 *  - avis_avatar   : Image ACF — Avatar/photo de l'auteur
 *
 * La date = post_date (date de publication du post).
 */
class AvisCpt {

    public function init(): void {
        add_action('init',          [$this, 'register_cpt']);
        add_action('acf/init',      [$this, 'register_acf_fields']);
        add_action('add_meta_boxes', [$this, 'register_fallback_metabox']);
        add_action('save_post_sj_avis', [$this, 'save_fallback_meta'], 10, 2);
    }

    // ── CPT ───────────────────────────────────────────────────────────────────

    public function register_cpt(): void {
        register_post_type('sj_avis', [
            'labels' => [
                'name'               => __('Avis', 'sj-reviews'),
                'singular_name'      => __('Avis', 'sj-reviews'),
                'add_new'            => __('Ajouter un avis', 'sj-reviews'),
                'add_new_item'       => __('Ajouter un avis', 'sj-reviews'),
                'edit_item'          => __('Modifier l\'avis', 'sj-reviews'),
                'new_item'           => __('Nouvel avis', 'sj-reviews'),
                'view_item'          => __('Voir l\'avis', 'sj-reviews'),
                'search_items'       => __('Rechercher des avis', 'sj-reviews'),
                'not_found'          => __('Aucun avis trouvé.', 'sj-reviews'),
                'not_found_in_trash' => __('Aucun avis dans la corbeille.', 'sj-reviews'),
                'menu_name'          => __('Avis', 'sj-reviews'),
            ],
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => 'sj-reviews',   // sous notre menu plugin
            'show_in_rest'        => true,
            'supports'            => ['title', 'custom-fields'],
            'capability_type'     => 'post',
            'has_archive'         => false,
            'rewrite'             => false,
            'menu_icon'           => 'dashicons-star-filled',
        ]);
    }

    // ── ACF Local Field Group ─────────────────────────────────────────────────

    public function register_acf_fields(): void {
        if (!function_exists('acf_add_local_field_group')) return;

        $settings      = get_option('sj_reviews_settings', []);
        $linked_types  = array_filter((array) ($settings['linked_post_types'] ?? []));

        $fields = [
            [
                'key'           => 'field_avis_author',
                'name'          => 'avis_author',
                'label'         => 'Auteur',
                'type'          => 'text',
                'required'      => 1,
                'placeholder'   => 'Prénom Nom',
                'instructions'  => 'Prénom et nom de l\'auteur de l\'avis.',
            ],
            [
                'key'           => 'field_avis_title',
                'name'          => 'avis_title',
                'label'         => 'Titre de l\'avis',
                'type'          => 'text',
                'required'      => 0,
                'placeholder'   => 'Ex : Excellent service, très professionnel…',
                'instructions'  => 'Titre court de l\'avis (optionnel).',
            ],
            [
                'key'           => 'field_avis_rating',
                'name'          => 'avis_rating',
                'label'         => 'Note (étoiles)',
                'type'          => 'number',
                'required'      => 1,
                'min'           => 1,
                'max'           => 5,
                'step'          => 1,
                'default_value' => 5,
                'instructions'  => 'Note de 1 à 5.',
            ],
            [
                'key'           => 'field_avis_text',
                'name'          => 'avis_text',
                'label'         => 'Texte de l\'avis',
                'type'          => 'textarea',
                'required'      => 0,
                'rows'          => 4,
                'instructions'  => 'Contenu complet de l\'avis.',
            ],
            [
                'key'           => 'field_avis_certified',
                'name'          => 'avis_certified',
                'label'         => 'Avis certifié',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Certifié',
                'ui_off_text'   => 'Non certifié',
                'instructions'  => 'Marquer cet avis comme vérifié/certifié.',
            ],
            [
                'key'           => 'field_avis_source',
                'name'          => 'avis_source',
                'label'         => 'Source',
                'type'          => 'select',
                'choices'       => [
                    'google'      => 'Google',
                    'tripadvisor' => 'TripAdvisor',
                    'facebook'    => 'Facebook',
                    'trustpilot'  => 'Trustpilot',
                    'regiondo'    => 'Regiondo',
                    'direct'      => 'Direct',
                    'autre'       => 'Autre',
                ],
                'default_value' => 'google',
                'allow_null'    => 0,
                'ui'            => 1,
            ],
            [
                'key'           => 'field_avis_avatar',
                'name'          => 'avis_avatar',
                'label'         => 'Avatar / Photo',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'instructions'  => 'Photo optionnelle de l\'auteur.',
            ],
            [
                'key'           => 'field_avis_place_id',
                'name'          => 'avis_place_id',
                'label'         => 'Google Place ID',
                'type'          => 'text',
                'instructions'  => 'Si cet avis vient de Google Maps, indiquez le Place ID pour le lien.',
            ],
        ];

        // Champ de liaison uniquement si des post types sont configurés dans Réglages
        if (!empty($linked_types)) {
            $fields[] = [
                'key'           => 'field_avis_linked_post',
                'name'          => 'avis_linked_post',
                'label'         => 'Post lié',
                'type'          => 'post_object',
                'post_type'     => array_values(array_map('sanitize_key', $linked_types)),
                'return_format' => 'id',
                'ui'            => 1,
                'allow_null'    => 1,
                'multiple'      => 0,
                'instructions'  => 'Lier cet avis à un contenu (article, produit, page…). Configurez les types dans Réglages.',
            ];
        }

        acf_add_local_field_group([
            'key'      => 'group_sj_avis',
            'title'    => 'Détails de l\'avis',
            'fields'   => $fields,
            'location' => [[
                ['param' => 'post_type', 'operator' => '==', 'value' => 'sj_avis'],
            ]],
            'menu_order'     => 0,
            'position'       => 'normal',
            'style'          => 'default',
            'label_placement' => 'top',
            'active'         => true,
        ]);
    }

    // ── Fallback Meta Box (sans ACF) ──────────────────────────────────────────

    public function register_fallback_metabox(): void {
        if (function_exists('acf_add_local_field_group')) return; // ACF gère

        add_meta_box(
            'sj_avis_details',
            __('Détails de l\'avis', 'sj-reviews'),
            [$this, 'render_fallback_metabox'],
            'sj_avis',
            'normal',
            'high'
        );
    }

    public function render_fallback_metabox(\WP_Post $post): void {
        wp_nonce_field('sj_avis_meta', 'sj_avis_nonce');

        $author      = get_post_meta($post->ID, 'avis_author', true);
        $avis_title  = get_post_meta($post->ID, 'avis_title', true);
        $rating      = get_post_meta($post->ID, 'avis_rating', true) ?: 5;
        $text        = get_post_meta($post->ID, 'avis_text', true);
        $certified   = get_post_meta($post->ID, 'avis_certified', true);
        $source      = get_post_meta($post->ID, 'avis_source', true) ?: 'google';
        $place_id    = get_post_meta($post->ID, 'avis_place_id', true);
        $linked_post = (int) get_post_meta($post->ID, 'avis_linked_post', true);

        $settings      = get_option('sj_reviews_settings', []);
        $linked_types  = array_filter((array) ($settings['linked_post_types'] ?? []));

        $sources = [
            'google' => 'Google', 'tripadvisor' => 'TripAdvisor',
            'facebook' => 'Facebook', 'trustpilot' => 'Trustpilot',
            'regiondo' => 'Regiondo',
            'direct' => 'Direct', 'autre' => 'Autre',
        ];
        ?>
        <style>
            .sj-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
            .sj-meta-field { display:flex; flex-direction:column; gap:4px; }
            .sj-meta-field label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#555; }
            .sj-meta-field input, .sj-meta-field select, .sj-meta-field textarea {
                border:1px solid #ddd; padding:6px 10px; font-size:13px; width:100%; font-family:inherit;
            }
            .sj-meta-field textarea { resize:vertical; min-height:80px; }
            .sj-meta-full { grid-column:1/-1; }
            .sj-certified-row { display:flex; align-items:center; gap:8px; font-size:13px; }
        </style>
        <div class="sj-meta-grid">
            <div class="sj-meta-field">
                <label for="avis_author"><?php esc_html_e('Auteur', 'sj-reviews'); ?></label>
                <input type="text" id="avis_author" name="avis_author" value="<?php echo esc_attr($author); ?>" placeholder="Prénom Nom" required>
            </div>
            <div class="sj-meta-field">
                <label for="avis_title"><?php esc_html_e('Titre de l\'avis', 'sj-reviews'); ?></label>
                <input type="text" id="avis_title" name="avis_title" value="<?php echo esc_attr($avis_title); ?>" placeholder="Ex : Excellent service…">
            </div>
            <div class="sj-meta-field">
                <label for="avis_rating"><?php esc_html_e('Note (1-5)', 'sj-reviews'); ?></label>
                <input type="number" id="avis_rating" name="avis_rating" value="<?php echo esc_attr($rating); ?>" min="1" max="5" step="1">
            </div>
            <div class="sj-meta-field">
                <label for="avis_source"><?php esc_html_e('Source', 'sj-reviews'); ?></label>
                <select id="avis_source" name="avis_source">
                    <?php foreach ($sources as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($source, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sj-meta-field">
                <label for="avis_place_id"><?php esc_html_e('Google Place ID', 'sj-reviews'); ?></label>
                <input type="text" id="avis_place_id" name="avis_place_id" value="<?php echo esc_attr($place_id); ?>" placeholder="ChIJ...">
            </div>
            <div class="sj-meta-field sj-meta-full">
                <label for="avis_text"><?php esc_html_e('Texte de l\'avis', 'sj-reviews'); ?></label>
                <textarea id="avis_text" name="avis_text"><?php echo esc_textarea($text); ?></textarea>
            </div>
            <div class="sj-meta-field sj-meta-full">
                <label class="sj-certified-row">
                    <input type="checkbox" name="avis_certified" value="1" <?php checked($certified, 1); ?>>
                    <?php esc_html_e('Avis certifié / vérifié', 'sj-reviews'); ?>
                </label>
            </div>
            <?php if (!empty($linked_types)): ?>
            <div class="sj-meta-field sj-meta-full">
                <label for="avis_linked_post"><?php esc_html_e('Post lié', 'sj-reviews'); ?></label>
                <select id="avis_linked_post" name="avis_linked_post">
                    <option value=""><?php esc_html_e('— Aucun —', 'sj-reviews'); ?></option>
                    <?php
                    $linked_posts = get_posts([
                        'post_type'      => array_values(array_map('sanitize_key', $linked_types)),
                        'post_status'    => 'publish',
                        'posts_per_page' => 200,
                        'orderby'        => 'title',
                        'order'          => 'ASC',
                    ]);
                    foreach ($linked_posts as $lp):
                    ?>
                        <option value="<?php echo esc_attr($lp->ID); ?>" <?php selected($linked_post, $lp->ID); ?>>
                            <?php echo esc_html(get_the_title($lp) . ' (' . $lp->post_type . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size:11px;color:#888;margin:4px 0 0">Configurez les types dans <strong>Réglages → SJ Reviews</strong>.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_fallback_meta(int $post_id, \WP_Post $post): void {
        if (function_exists('acf_add_local_field_group')) return;
        if (!isset($_POST['sj_avis_nonce']) || !wp_verify_nonce($_POST['sj_avis_nonce'], 'sj_avis_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = ['avis_author', 'avis_title', 'avis_rating', 'avis_source', 'avis_place_id'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        update_post_meta($post_id, 'avis_text', sanitize_textarea_field($_POST['avis_text'] ?? ''));
        update_post_meta($post_id, 'avis_certified', isset($_POST['avis_certified']) ? 1 : 0);
        update_post_meta($post_id, 'avis_linked_post', (int) ($_POST['avis_linked_post'] ?? 0) ?: '');
    }
}
