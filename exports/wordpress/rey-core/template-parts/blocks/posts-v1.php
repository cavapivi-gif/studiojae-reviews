<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract($args);

if ( empty($posts) ) {
	printf('<p class="__no-posts"><small>%s</small></p>', esc_html__( 'No posts has been found!', 'rey-core' ));
	return;
}

$wrapper_classes = [
	'reyBlock-posts-v1',
	'--cols-' . absint($columns),
	'--img-align-' . $image_alignment,
	$show_image ? '--showimg' : '',
	$vertical_sep ? '--x-separator' : '',
	$image_height ? '--cover' : '',
];

$styles[] = "--cols:{$columns}";
$styles[] = "--title-size:var(--fz-{$title_size})";
$styles[] = $image_height ? "--img-height:{$image_height}px" : '';
$styles[] = $image_width ? "--img-width:{$image_width}px" : '';
$styles[] = $image_roundness ? "--img-round:{$image_roundness}px" : '';
$styles[] = $gap_size ? "--gap-size:{$gap_size}px" : '';


printf('<div class="%s" style="%s">', esc_attr( implode(' ', $wrapper_classes) ), esc_attr(implode(';', $styles)) );

	foreach ($posts as $i => $post_id) {
		$post = get_post( $post_id );
		setup_postdata( $post );

		$index = '';

		if( $title_numerotation ){

			$index = $i + 1;

			if( 'roman' === $title_numerotation ){
				$index = \ReyCore\Helper::numberToRoman($index);
			}

		} ?>

		<div class="__item" data-index="<?php echo $index ?>">

			<?php if( $show_image ){

				$img = wp_get_attachment_image( get_post_thumbnail_id( $post ), $image_size, false, [
					'class' => '__item-thumb'
				] );

				if( $img ){
					printf('<div class="__item-side --side1"><a href="%s">%s</a></div>', get_the_permalink(), $img);
				}
			} ?>

			<div class="__item-side --side2">

				<?php if( $show_categories && $categories_list = get_the_category_list() ):
					printf(
						'<div class="__cat"><span class="screen-reader-text">%s</span>%s</div>',
						esc_html__('Posted in', 'rey-core'),
						$categories_list );
				endif; ?>

				<h4 class="__item-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h4>

				<?php if( $show_date ): ?>
					<div class="__item-date"><?php rey__humanDate(); ?></div>
				<?php endif; ?>

				<?php if($show_excerpt) {
					ob_start();
					the_excerpt();
					if( $excerpt = ob_get_clean() ){
						printf('<div class="__item-excerpt">%s</div>', $excerpt);
					}
				} ?>

			</div>

		</div>

		<?php
		wp_reset_postdata();
	}

echo '</div>';
