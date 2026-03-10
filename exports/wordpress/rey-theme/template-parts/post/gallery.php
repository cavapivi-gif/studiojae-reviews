<?php
// Post Gallery

$gallery_ids = get_post_gallery( get_the_ID(), false );

if ( $gallery_ids ) :
	if( isset( $gallery_ids['ids'] ) ):
		$gallery_html = '';
		foreach(explode(',', $gallery_ids['ids']) as $i => $gitem):

			if( $i > ( apply_filters('rey/post/gallery_limit', 4) - 1 ) ) continue; // allow 3 only
			$gallery_html .= '<div class="splide__slide" >';
			$gallery_html .= wp_get_attachment_image( $gitem, rey__the_post_thumbnail_size(), false, ['class'=>"__img"] );
			$gallery_html .= '</div>';
		endforeach;

	if( $gallery_html ):

		rey_assets()->add_scripts(['splidejs', 'rey-splide']);
		rey_assets()->add_styles('rey-splide');

		?>
		<div class="rey-postMedia">
			<div class="splide" data-rey-splide='{"type":"slide","perPage":1, "animateHeight":true, "arrows": false, "pagination": false}'>
				<div class="splide__track" >
					<div class="splide__list" >
						<?php echo wp_kses_post($gallery_html); ?>
					</div>
				</div>
			</div>
			<?php rey__categoriesList(); ?>
		</div>
	<?php endif;
	endif;
	?>
<?php
endif; ?>
