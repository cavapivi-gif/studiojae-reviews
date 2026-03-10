<?php

/**
 * Prints content before footer
 * @hook rey/before_footer
 */
rey_action__before_footer(); ?>

<footer class="rey-siteFooter <?php echo esc_attr(implode(' ', apply_filters('rey/footer/footer_classes', []))) ?>" <?php rey__render_attributes('footer'); ?>>

    <?php
	/**
	 * Prints content inside footer
	 * @hook rey/footer/content
	 */
	rey_action__footer_content(); ?>

</footer>
<!-- .rey-siteFooter -->

<?php
/**
 * Prints content after footer
 * @hook rey/after_footer
 */
rey_action__after_footer(); ?>
