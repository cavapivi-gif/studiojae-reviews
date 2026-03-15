<?php
namespace SJ_Reviews\Front;

defined('ABSPATH') || exit;

/**
 * Shortcode [sj_form] — Formulaire de soumission d'avis client.
 *
 * Les avis soumis sont créés en statut "pending" (en attente de modération).
 * Anti-spam : honeypot field + rate limiting via transient.
 */
class FormShortcode {

    public function init(): void {
        add_shortcode('sj_form', [$this, 'render']);
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route('sj-reviews/v1', '/submit-review', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_submit'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function render(array $atts = []): string {
        \SJ_Reviews\Core\Plugin::enqueue_asset('sj-form');

        $a = shortcode_atts([
            'lieu_id'        => '',
            'show_criteria'  => '0',
            'success_message' => 'Merci ! Votre avis a été soumis et sera publié après vérification.',
        ], $atts, 'sj_form');

        $uid = 'sj-form-' . wp_unique_id();

        ob_start();
        ?>
<div class="sj-form-wrap" id="<?php echo esc_attr($uid); ?>">
    <form class="sj-form" data-action="<?php echo esc_url(rest_url('sj-reviews/v1/submit-review')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
        <input type="hidden" name="lieu_id" value="<?php echo esc_attr($a['lieu_id']); ?>">
        <input type="hidden" name="show_criteria" value="<?php echo esc_attr($a['show_criteria']); ?>">

        <!-- Honeypot anti-spam -->
        <div style="position:absolute;left:-9999px;top:-9999px" aria-hidden="true">
            <label for="<?php echo esc_attr($uid); ?>-website">Ne pas remplir</label>
            <input type="text" name="sj_website" id="<?php echo esc_attr($uid); ?>-website" tabindex="-1" autocomplete="off">
        </div>

        <div class="sj-form__field">
            <label class="sj-form__label" for="<?php echo esc_attr($uid); ?>-author">Votre nom *</label>
            <input class="sj-form__input" type="text" name="author" id="<?php echo esc_attr($uid); ?>-author" required maxlength="100">
        </div>

        <div class="sj-form__field">
            <label class="sj-form__label" for="<?php echo esc_attr($uid); ?>-email">Email (optionnel)</label>
            <input class="sj-form__input" type="email" name="email" id="<?php echo esc_attr($uid); ?>-email" maxlength="200">
        </div>

        <div class="sj-form__field">
            <label class="sj-form__label">Note *</label>
            <div class="sj-form__stars" data-field="rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="sj-form__star-btn" data-value="<?php echo $i; ?>" aria-label="<?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?>">
                    <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                              fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </button>
                <?php endfor; ?>
                <input type="hidden" name="rating" value="0">
            </div>
        </div>

        <div class="sj-form__field">
            <label class="sj-form__label" for="<?php echo esc_attr($uid); ?>-title">Titre de l'avis</label>
            <input class="sj-form__input" type="text" name="avis_title" id="<?php echo esc_attr($uid); ?>-title" maxlength="200">
        </div>

        <div class="sj-form__field">
            <label class="sj-form__label" for="<?php echo esc_attr($uid); ?>-text">Votre avis *</label>
            <textarea class="sj-form__textarea" name="text" id="<?php echo esc_attr($uid); ?>-text" rows="5" required maxlength="5000"></textarea>
        </div>

        <?php if ($a['show_criteria'] === '1'): ?>
        <?php
        $crit_labels = \SJ_Reviews\Includes\Labels::criteria();
        foreach ($crit_labels as $key => $label):
        ?>
        <div class="sj-form__field">
            <label class="sj-form__label"><?php echo esc_html($label); ?> (optionnel)</label>
            <div class="sj-form__stars sj-form__stars--small" data-field="<?php echo esc_attr($key); ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="sj-form__star-btn" data-value="<?php echo $i; ?>" aria-label="<?php echo $i; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                              fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </button>
                <?php endfor; ?>
                <input type="hidden" name="<?php echo esc_attr($key); ?>" value="0">
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="sj-form__actions">
            <button type="submit" class="sj-form__submit">Envoyer mon avis</button>
        </div>

        <div class="sj-form__message" hidden></div>
    </form>
</div>

<script>
(function() {
    var wrap = document.getElementById('<?php echo esc_js($uid); ?>');
    if (!wrap) return;
    var form = wrap.querySelector('.sj-form');
    var msg  = wrap.querySelector('.sj-form__message');

    // Star pickers
    wrap.querySelectorAll('.sj-form__stars').forEach(function(group) {
        var hidden = group.querySelector('input[type="hidden"]');
        var btns   = group.querySelectorAll('.sj-form__star-btn');
        btns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var val = parseInt(this.dataset.value, 10);
                hidden.value = val;
                btns.forEach(function(b, idx) {
                    var svg = b.querySelector('path');
                    svg.setAttribute('fill', idx < val ? '#f5a623' : 'none');
                    svg.setAttribute('stroke', idx < val ? '#f5a623' : 'currentColor');
                });
            });
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var fd   = new FormData(form);
        var data = {};
        fd.forEach(function(v, k) { data[k] = v; });

        // Validate
        if (!data.author || !data.author.trim()) { showMsg('Veuillez saisir votre nom.', true); return; }
        if (!data.rating || parseInt(data.rating) < 1) { showMsg('Veuillez sélectionner une note.', true); return; }
        if (!data.text || !data.text.trim()) { showMsg('Veuillez écrire votre avis.', true); return; }

        var btn = form.querySelector('.sj-form__submit');
        btn.disabled = true;
        btn.textContent = 'Envoi en cours…';

        fetch(form.dataset.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': form.dataset.nonce
            },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                showMsg(<?php echo wp_json_encode($a['success_message']); ?>, false);
                form.reset();
                wrap.querySelectorAll('.sj-form__star-btn path').forEach(function(p) {
                    p.setAttribute('fill', 'none');
                    p.setAttribute('stroke', 'currentColor');
                });
                wrap.querySelectorAll('input[type="hidden"][name]').forEach(function(h) {
                    if (h.name !== 'lieu_id' && h.name !== 'show_criteria') h.value = '0';
                });
            } else {
                showMsg(result.message || 'Erreur lors de l\'envoi.', true);
            }
        })
        .catch(function() { showMsg('Erreur réseau, veuillez réessayer.', true); })
        .finally(function() { btn.disabled = false; btn.textContent = 'Envoyer mon avis'; });
    });

    function showMsg(text, isError) {
        msg.hidden = false;
        msg.textContent = text;
        msg.className = 'sj-form__message ' + (isError ? 'sj-form__message--error' : 'sj-form__message--success');
    }
})();
</script>
        <?php
        return ob_get_clean();
    }

    public function handle_submit(\WP_REST_Request $req): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params();

        // Honeypot check
        if (!empty($body['sj_website'])) {
            return rest_ensure_response(['success' => true]); // Silent fail for bots
        }

        // Rate limiting: 1 submission per IP per minute
        $ip       = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $lock_key = 'sj_form_lock_' . md5($ip);
        if (get_transient($lock_key)) {
            return new \WP_Error('rate_limited', 'Veuillez patienter avant de soumettre un nouvel avis.', ['status' => 429]);
        }

        $author     = sanitize_text_field($body['author'] ?? '');
        $email      = sanitize_email($body['email'] ?? '');
        $rating     = (int) ($body['rating'] ?? 0);
        $avis_title = sanitize_text_field($body['avis_title'] ?? '');
        $text       = sanitize_textarea_field($body['text'] ?? '');
        $lieu_id    = sanitize_key($body['lieu_id'] ?? '');

        if (empty($author)) {
            return new \WP_Error('missing_author', 'Le nom est requis.', ['status' => 422]);
        }
        if ($rating < 1 || $rating > 5) {
            return new \WP_Error('invalid_rating', 'La note doit être entre 1 et 5.', ['status' => 422]);
        }
        if (empty($text)) {
            return new \WP_Error('missing_text', 'Le texte de l\'avis est requis.', ['status' => 422]);
        }

        // Create review as pending
        $post_id = wp_insert_post([
            'post_type'   => 'sj_avis',
            'post_title'  => $author,
            'post_status' => 'pending',
        ], true);

        if (is_wp_error($post_id)) {
            return new \WP_Error('create_failed', 'Erreur lors de la création.', ['status' => 500]);
        }

        update_post_meta($post_id, 'avis_author', $author);
        update_post_meta($post_id, 'avis_title', $avis_title);
        update_post_meta($post_id, 'avis_rating', $rating);
        update_post_meta($post_id, 'avis_text', $text);
        update_post_meta($post_id, 'avis_certified', 0);
        update_post_meta($post_id, 'avis_source', 'direct');
        update_post_meta($post_id, 'avis_lieu_id', $lieu_id);
        update_post_meta($post_id, 'avis_language', 'fr');
        update_post_meta($post_id, 'avis_travel_type', '');

        if ($email) {
            update_post_meta($post_id, 'avis_customer_email', $email);
        }

        // Sub-criteria
        foreach (['qualite_prix', 'ambiance', 'experience', 'paysage'] as $crit) {
            $v = (int) ($body[$crit] ?? 0);
            update_post_meta($post_id, 'avis_' . $crit, ($v >= 1 && $v <= 5) ? $v : 0);
        }

        // Set rate limit
        set_transient($lock_key, 1, 60);

        return rest_ensure_response(['success' => true, 'id' => $post_id]);
    }
}
