<?php
/**
 * Footer Copyright
 */

$copyright_text = apply_filters('rey/footer/copyright_text', sprintf(
    '%s %s <a href="%s">%s</a>. %s',
    '&copy;',
    date('Y'),
    esc_url(get_site_url()),
    get_bloginfo('name'),
    esc_html__('All rights reserved.', 'rey')
) ); ?>

<div class="rey-siteFooter__copyright">
    <?php echo wpautop(wp_kses_post($copyright_text)) ?>
</div>
