<?php
namespace SJ_Reviews\PostTypes;

defined('ABSPATH') || exit;

/**
 * Meta box « Lieu(x) lié(s) » ajoutée aux post types configurés dans les réglages.
 *
 * Permet de lier un article / produit / page à un ou plusieurs lieux SJ Reviews.
 * Ce lien est lu par le widget Résumé Avis pour filtrer automatiquement
 * les avis du bon lieu lorsque le widget est en mode « Auto ».
 *
 * Meta enregistrée : sj_lieu_id (array de clés de lieu, ex : ['lieu_xxxx', 'lieu_yyyy'])
 * Rétrocompat : les anciennes valeurs scalaires (string) sont aussi lues correctement.
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

        // Rétrocompat : l'ancienne meta est un scalar, la nouvelle est un array.
        $raw     = get_post_meta($post->ID, 'sj_lieu_id', true);
        $current = is_array($raw) ? array_filter($raw) : ($raw ? [$raw] : []);
        $lieux   = \SJ_Reviews\Includes\Settings::lieux();
        ?>
        <style>
            #sj_lieu_metabox .sj-lm { display:flex; flex-direction:column; gap:8px; }
            #sj_lieu_metabox .sj-lm-checks { display:flex; flex-direction:column; gap:5px; }
            #sj_lieu_metabox .sj-lm-check {
                display:flex; align-items:center; gap:7px;
                font-size:12px; color:#1d2327; cursor:pointer; line-height:1.4;
            }
            #sj_lieu_metabox .sj-lm-check input[type=checkbox] {
                width:15px; height:15px; margin:0; flex-shrink:0; cursor:pointer;
                accent-color:#2271b1;
            }
            #sj_lieu_metabox .sj-lm-check--inactive { opacity:.55; }
            #sj_lieu_metabox .sj-lm-source {
                font-size:10px; color:#999; font-style:italic; margin-left:2px;
            }
            #sj_lieu_metabox p.sj-lm-hint { font-size:11px; color:#888; margin:0; }
            #sj_lieu_metabox .sj-lm-empty { font-size:12px; color:#999; font-style:italic; }
        </style>
        <div class="sj-lm">
            <?php if (empty($lieux)): ?>
                <p class="sj-lm-empty">
                    <?php esc_html_e('Aucun lieu configuré dans SJ Reviews → Réglages.', 'sj-reviews'); ?>
                </p>
            <?php else: ?>
                <div class="sj-lm-checks">
                    <?php foreach ($lieux as $l):
                        $checked  = in_array($l['id'], $current, true);
                        $inactive = !($l['active'] ?? true);
                        $source   = $l['source'] ?? '';
                        ?>
                        <label class="sj-lm-check<?php echo $inactive ? ' sj-lm-check--inactive' : ''; ?>">
                            <input
                                type="checkbox"
                                name="sj_lieu_id[]"
                                value="<?php echo esc_attr($l['id']); ?>"
                                <?php checked($checked); ?>
                            >
                            <?php echo esc_html($l['name']); ?>
                            <?php if ($source): ?>
                                <span class="sj-lm-source">(<?php echo esc_html($source); ?>)</span>
                            <?php endif; ?>
                            <?php if ($inactive): ?>
                                <span class="sj-lm-source">(inactif)</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p class="sj-lm-hint">
                Les widgets <strong>Avis</strong> en mode <em>Auto</em> détectent ces lieux
                pour filtrer automatiquement les statistiques.
            </p>
        </div>
        <?php
    }

    public function save(int $post_id): void {
        if (!isset($_POST['sj_lieu_metabox_nonce'])) return;
        if (!wp_verify_nonce($_POST['sj_lieu_metabox_nonce'], 'sj_lieu_metabox')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        // sj_lieu_id[] est maintenant un multi-select → array ou absent.
        $raw     = isset($_POST['sj_lieu_id']) && is_array($_POST['sj_lieu_id'])
            ? $_POST['sj_lieu_id']
            : [];
        $lieu_ids = array_values(array_filter(array_map('sanitize_key', $raw)));

        if (!empty($lieu_ids)) {
            update_post_meta($post_id, 'sj_lieu_id', $lieu_ids);
        } else {
            delete_post_meta($post_id, 'sj_lieu_id');
        }
    }
}
