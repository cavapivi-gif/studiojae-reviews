<?php
namespace SJ_Reviews\PostTypes;

defined('ABSPATH') || exit;

/**
 * Meta box « Lieu lié » ajoutée aux post types configurés dans les réglages.
 *
 * Elle permet de lier un article / produit / page à un lieu SJ Reviews.
 * Ce lien est lu par le widget Résumé Avis pour filtrer automatiquement
 * les avis du bon lieu lorsque le widget est en mode « Auto ».
 *
 * Meta enregistrée : sj_lieu_id (clé du lieu, ex : lieu_xxxx)
 */
class LieuMetabox {

    public function init(): void {
        add_action('add_meta_boxes', [$this, 'register']);
        add_action('save_post',      [$this, 'save'], 10, 1);
    }

    public function register(): void {
        $linked_types = \SJ_Reviews\Includes\Settings::linked_post_types();
        if (empty($linked_types)) return;

        $lieux = \SJ_Reviews\Includes\Settings::lieux();
        if (empty($lieux)) return;

        foreach ($linked_types as $pt) {
            add_meta_box(
                'sj_lieu_metabox',
                __('Lieu SJ Reviews', 'sj-reviews'),
                [$this, 'render'],
                sanitize_key($pt),
                'side',
                'default'
            );
        }
    }

    public function render(\WP_Post $post): void {
        wp_nonce_field('sj_lieu_metabox', 'sj_lieu_metabox_nonce');
        $current = get_post_meta($post->ID, 'sj_lieu_id', true);
        $lieux   = \SJ_Reviews\Includes\Settings::lieux();
        ?>
        <style>
            #sj_lieu_metabox .sj-lm { display:flex; flex-direction:column; gap:6px; }
            #sj_lieu_metabox select { width:100%; padding:5px 8px; font-size:12px; border:1px solid #ddd; }
            #sj_lieu_metabox p.sj-lm-hint { font-size:11px; color:#888; margin:0; }
            #sj_lieu_metabox .sj-lm-badge {
                display:inline-flex; align-items:center; gap:4px;
                background:#f0fdf4; border:1px solid #86efac; color:#166534;
                font-size:11px; font-weight:600; padding:2px 7px; border-radius:3px;
            }
        </style>
        <div class="sj-lm">
            <select id="sj_lieu_id" name="sj_lieu_id">
                <option value=""><?php esc_html_e('— Aucun lieu —', 'sj-reviews'); ?></option>
                <?php foreach ($lieux as $l): ?>
                    <option value="<?php echo esc_attr($l['id']); ?>" <?php selected($current, $l['id']); ?>>
                        <?php echo esc_html($l['name']); ?>
                        <?php echo $l['active'] ? '' : ' (inactif)'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($current): ?>
                <?php
                $name = '';
                foreach ($lieux as $l) {
                    if ($l['id'] === $current) { $name = $l['name']; break; }
                }
                ?>
                <span class="sj-lm-badge">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                        <circle cx="5" cy="5" r="4.5" fill="#22c55e"/>
                        <path d="M3 5l1.5 1.5L7 3.5" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo esc_html($name ?: $current); ?>
                </span>
            <?php endif; ?>
            <p class="sj-lm-hint">
                Le widget <strong>Résumé Avis</strong> détecte automatiquement ce lieu
                et affiche les statistiques correspondantes.
            </p>
        </div>
        <?php
    }

    public function save(int $post_id): void {
        if (!isset($_POST['sj_lieu_metabox_nonce'])) return;
        if (!wp_verify_nonce($_POST['sj_lieu_metabox_nonce'], 'sj_lieu_metabox')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $lieu_id = sanitize_key($_POST['sj_lieu_id'] ?? '');
        if ($lieu_id) {
            update_post_meta($post_id, 'sj_lieu_id', $lieu_id);
        } else {
            delete_post_meta($post_id, 'sj_lieu_id');
        }
    }
}
