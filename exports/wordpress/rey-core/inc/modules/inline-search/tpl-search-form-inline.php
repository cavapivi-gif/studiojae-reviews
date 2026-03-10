<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args();
$unique_id = rey__unique_id( 'search-form-' ) ;

reycore_assets()->add_styles('rey-header-icon');
?>

<div class="rey-headerIcon rey-searchForm rey-headerSearch--inline rey-searchAjax js-rey-ajaxSearch">

	<button class="btn rey-headerIcon-btn rey-headerSearch-toggle" aria-label="<?php esc_html_e('Search', 'rey-core') ?>">
		<?php echo reycore__get_svg_icon(['id' => 'search', 'class' => 'icon-search rey-headerSearch-toggle-svg']) ?>
	</button>

	<div class="rey-inlineSearch-wrapper ">
		<div class="rey-inlineSearch-holder"></div>

		<button class="btn rey-inlineSearch-mobileClose" aria-label="<?php esc_html_e('Close', 'rey-core') ?>">
			<?php echo reycore__get_svg_icon(['id' => 'close', 'class' => 'icon-close']) ?>
		</button>

		<form role="search" action="<?php echo esc_url(home_url('/')) ?>" method="get" class="rey-inlineSearch-form">
			<label for="<?php echo esc_attr($unique_id); ?>"  class="screen-reader-text">
				<?php echo esc_html_x( 'Search for:', 'label', 'rey-core' ); ?>
			</label>
			<input class="rey-inlineSearch-searchField" type="search" id="<?php echo esc_attr($unique_id); ?>" name="s" placeholder="<?php echo esc_attr( ($placeholder = get_theme_mod('header_search__input_placeholder', '')) ? $placeholder : __( 'type to search..', 'rey-core' ) ); ?>" autocomplete="off" value="<?php echo esc_attr( get_search_query() ); ?>" />
			<button class="search-btn rey-inlineSearch-searchBtn" type="submit" aria-label="<?php esc_html_e('Click to search', 'rey-core') ?>">
				<?php echo reycore__get_svg_icon(['id' => 'search', 'class' => 'icon-search']) ?></button>
			<?php do_action('rey/search_form'); ?>
			<?php do_action('wpml_add_language_form_field'); ?>
		</form>

		<?php do_action('reycore/search_panel/after_search_form', $args); ?>
	</div>

</div>
