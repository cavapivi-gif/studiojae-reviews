<?php
get_header();

$type = reycore__acf_get_field('gs_type');

$attributes = [
	'class' => [
		'rey-pbTemplate',
		'rey-pbTemplate--gs'
	],
];

$attributes['class'][] = 'rey-pbTemplate--gs-' . $type ;

if( in_array($type, ['header', 'footer'], true) ){
	$attributes['class'][] = 'rey-pbTemplate--gs-hf';
}
if( in_array($type, ['header', 'footer', 'cover'], true) ){
	$attributes['class'][] = 'rey-pbTemplate--gs-hfc';
} ?>


<div <?php echo reycore__implode_html_attributes( apply_filters('reycore/global_section_template/attributes', $attributes, $type) ); ?>>

	<div class="__gs-resize" data-pos="left"></div>

	<div class="rey-pbTemplate-inner">
        <?php
		do_action('reycore/global_section_template/before_the_content', $type);

        while ( have_posts() ) : the_post();
			the_content();
        endwhile;

		do_action('reycore/global_section_template/after_the_content', $type); ?>
    </div>

	<div class="__gs-resize" data-pos="right"></div>

	<?php
	do_action('reycore/global_section_template/after_content_inner'); ?>

</div>
<!-- .rey-pbTemplate -->

<?php
do_action('reycore/global_section_template/after_content');

get_footer();
