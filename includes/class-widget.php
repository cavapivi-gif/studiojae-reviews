<?php
namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

/**
 * Classic WP_Widget for SJ Reviews.
 *
 * Displays a rating badge or recent reviews in a sidebar.
 */
class ReviewsWidget extends \WP_Widget {

    public function __construct() {
        parent::__construct(
            'sj_reviews_widget',
            'SJ Reviews — Avis',
            [
                'description' => 'Affiche un badge de note ou les avis récents.',
                'classname'   => 'sj-widget',
            ]
        );
    }

    public function widget($args, $instance) {
        $title    = apply_filters('widget_title', $instance['title'] ?? '');
        $mode     = $instance['mode'] ?? 'badge';
        $lieu_id  = $instance['lieu_id'] ?? 'all';
        $count    = max(1, min(10, (int) ($instance['count'] ?? 3)));

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        if ($mode === 'badge') {
            echo do_shortcode('[sj_rating lieu_id="' . esc_attr($lieu_id) . '"]');
        } elseif ($mode === 'summary') {
            echo do_shortcode('[sj_summary lieu_id="' . esc_attr($lieu_id) . '" reviews_initial="' . esc_attr($count) . '" show_filters="0" show_search="0" cards_columns="1"]');
        } else {
            // Recent reviews list (simple)
            $reviews = sj_get_reviews([
                'posts_per_page' => $count,
                'meta_query'     => $lieu_id && $lieu_id !== 'all' ? [
                    ['key' => 'avis_lieu_id', 'value' => $lieu_id],
                ] : [],
            ]);

            if (empty($reviews)) {
                echo '<p class="sj-widget__empty">Aucun avis.</p>';
            } else {
                echo '<div class="sj-widget__list">';
                foreach ($reviews as $r) {
                    echo '<div class="sj-widget__item">';
                    echo '<div class="sj-widget__item-header">';
                    echo sj_stars_html((int) $r['rating'], 5);
                    echo '<span class="sj-widget__author">' . esc_html($r['author']) . '</span>';
                    echo '</div>';
                    if (!empty($r['text'])) {
                        $short = wp_trim_words($r['text'], 20, '…');
                        echo '<p class="sj-widget__text">' . esc_html($short) . '</p>';
                    }
                    echo '<span class="sj-widget__date">' . esc_html($r['date_rel']) . '</span>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title   = $instance['title'] ?? 'Avis clients';
        $mode    = $instance['mode'] ?? 'badge';
        $lieu_id = $instance['lieu_id'] ?? 'all';
        $count   = $instance['count'] ?? 3;
        $lieux   = \SJ_Reviews\Includes\Settings::lieux();
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Titre :</label>
            <input class="widefat" type="text"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('mode')); ?>">Mode :</label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('mode')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('mode')); ?>">
                <option value="badge" <?php selected($mode, 'badge'); ?>>Badge note</option>
                <option value="list" <?php selected($mode, 'list'); ?>>Liste avis récents</option>
                <option value="summary" <?php selected($mode, 'summary'); ?>>Résumé complet</option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('lieu_id')); ?>">Lieu :</label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('lieu_id')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('lieu_id')); ?>">
                <option value="all" <?php selected($lieu_id, 'all'); ?>>Tous les lieux</option>
                <?php foreach ($lieux as $l): ?>
                <option value="<?php echo esc_attr($l['id']); ?>" <?php selected($lieu_id, $l['id']); ?>><?php echo esc_html($l['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">Nombre d'avis (liste) :</label>
            <input class="tiny-text" type="number" min="1" max="10"
                   id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   value="<?php echo esc_attr($count); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        return [
            'title'   => sanitize_text_field($new_instance['title'] ?? ''),
            'mode'    => in_array($new_instance['mode'] ?? 'badge', ['badge', 'list', 'summary'], true) ? $new_instance['mode'] : 'badge',
            'lieu_id' => sanitize_key($new_instance['lieu_id'] ?? 'all'),
            'count'   => max(1, min(10, (int) ($new_instance['count'] ?? 3))),
        ];
    }
}
