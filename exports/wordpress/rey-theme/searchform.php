<?php
/**
 * Template for displaying search forms in Rey Theme
 */

rey_assets()->add_styles('rey-searchbox');

$unique_id = rey__unique_id( 'search-form-' ) ;

$settings = apply_filters('rey/search_form/settings', [
	'button' => true,
	'class' => 'rey-searchBox',
	'placeholder' => get_theme_mod('header_search__input_placeholder', esc_attr_x( 'Search &hellip;', 'placeholder', 'rey' )),
	'button_icon' => true,
	'button_text' => esc_html_x( 'Search', 'submit button', 'rey' ),
	'post_type' => ''
]); ?>

<form role="search" method="get" class="<?php echo esc_attr( $settings['class'] ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr($unique_id); ?>"  class="screen-reader-text">
		<span><?php echo esc_html_x( 'Search for:', 'label', 'rey' ); ?></span>
	</label>

	<input type="search" id="<?php echo esc_attr($unique_id); ?>" class="form-control" placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>" value="<?php echo get_search_query(); ?>" name="s" />

	<div class="rey-headerSearch-actions">
		<?php if($settings['button']): ?>
			<button type="submit" class="btn" aria-label="<?php esc_html_e('Search', 'rey') ?>">
				<?php if($settings['button_icon']){
					echo rey__get_svg_icon( [ 'id' => 'search' ] );
				}
				else {
					printf('<span>%s</span>', $settings['button_text']);
				} ?>
				<span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'rey' ); ?></span>
			</button>
		<?php endif;

		if( $settings['post_type'] === '' ){
			do_action('rey/search_form');
		}
		else {
			printf('<input type="hidden" name="post_type" value="%s" />', esc_attr($settings['post_type']));
		} ?>
	</div>

</form>
