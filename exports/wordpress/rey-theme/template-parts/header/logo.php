<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = rey__header_logo_params(); ?>

<div class="rey-logoWrapper">

	<?php if ( $args['logo'] ) : ?>

        <div class="rey-siteLogo">
			<?php echo rey__custom_logo( $args ); ?>
		</div>

    <?php elseif( ! empty( $args['blog_name'] ) ) : ?>

        <?php if ( is_front_page() && is_home() ) : ?>
            <h1 class="rey-logoTitle">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html($args['blog_name']); ?></a>
            </h1>
        <?php else : ?>
            <div class="rey-logoTitle"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html($args['blog_name']); ?></a></div>
        <?php endif; ?>

        <?php
        if ( $args['blog_description'] || is_customize_preview() ) :
            ?>
            <div class="rey-logoDescription --dnone-sm --dnone-md">
                <?php echo esc_html($args['blog_description']); ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
<!-- .rey-logoWrapper -->
