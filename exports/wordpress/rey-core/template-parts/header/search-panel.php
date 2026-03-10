<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args();

$attributes = [
	'id' => 'rey-searchPanel',
	'class' => [
		'rey-searchPanel',
		'rey-searchForm',
		'rey-searchAjax',
		'js-rey-ajaxSearch',
	],
	'data-style' => esc_attr( $args['search_style'] )
];

if( 'side' === $args['search_style'] ){
	$attributes['class'][] = 'rey-sidePanel';
	$attributes['data-lazy-hidden'][] = '';
	reycore_assets()->add_styles('reycore-close-arrow');
}
elseif( 'wide' === $args['search_style'] ){
	$attributes['class'][] = '--hidden';

} ?>

<div <?php echo reycore__implode_html_attributes($attributes); ?>>

	<div class="rey-searchPanel-inner">

		<form role="search" action="<?php echo esc_url(home_url('/')) ?>" method="get">

			<?php
			$id = uniqid('search-input-');

			/**
			 * To remove the title's link, please use:
			 *
			 *   add_filter('rey/search_form/title', function($text, $id){
			 * 	  	return sprintf( '<label for="%2$s">%1$s</label>', esc_html__('Search', 'rey-core'), esc_attr($id) );
			 *   }, 10, 2);
			 */

			echo apply_filters(
				'rey/search_form/title',
				sprintf(
					'<label for="%3$s">%1$s %2$s</label>',
					esc_html__('Search', 'rey-core'),
					str_replace(['http://', 'https://'], '', get_site_url()),
					esc_attr($id)
				),
				$id
			); ?>

			<div class="rey-searchPanel-innerForm">

				<input type="search" name="s" placeholder="<?php echo esc_attr( ($placeholder = get_theme_mod('header_search__input_placeholder', '')) ? $placeholder : __( 'type to search..', 'rey-core' ) ); ?>" id="<?php echo esc_attr($id) ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />

				<div class="rey-headerSearch-actions">
					<?php do_action('rey/search_form'); ?>
				</div>

			</div>
			<?php do_action('wpml_add_language_form_field'); ?>

		</form>

		<?php do_action('reycore/search_panel/after_search_form', $args); ?>
		<!-- .row -->
	</div>

</div>
<?php if( 'wide' === $args['search_style'] ):
	reycore_assets()->add_styles('rey-overlay'); ?>
	<div class="rey-searchPanel-wideOverlay rey-overlay --no-js-close" style="opacity:0;"></div>
<?php endif; ?>
<!-- .rey-searchPanel -->
