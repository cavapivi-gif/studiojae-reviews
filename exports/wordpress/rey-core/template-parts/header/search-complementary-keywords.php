<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<?php
if( !empty($args['keywords']) ): ?>
<nav class="rey-searchPanel__suggestions" aria-label="<?php echo esc_attr__( 'Search Suggestions', 'rey-core' ); ?>" data-lazy-hidden>
	<<?php echo esc_html($args['trending_keywords_tag']); ?> class="rey-searchPanel__suggestionsTitle">
		<?php echo esc_html_x('TRENDING', 'Search keywords title', 'rey-core');?>
	</<?php echo esc_html($args['trending_keywords_tag']); ?>>
	<?php
	$keywords_arr = array_map( 'trim', explode( ',', $args['keywords'] ) );
	foreach ($keywords_arr as $kwd):
		printf('<button aria-label="' . esc_html_x('Search for %1$s', 'Suggestion button', 'rey-core') . '">%1$s</button>', esc_html( $kwd ));
	endforeach; ?>
</nav>
<?php endif; ?>
