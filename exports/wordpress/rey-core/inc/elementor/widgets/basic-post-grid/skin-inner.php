<?php
namespace ReyCore\Elementor\Widgets\BasicPostGrid;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SkinInner extends \Elementor\Skin_Base
{
	private $posts_archive = null;

	public function get_id() {
		return 'inner';
	}

	public function get_title() {
		return __( 'Inner Content', 'rey-core' );
	}

	public function render() {

		$this->parent->_settings = $this->parent->get_settings_for_display();

		$this->posts_archive = new \ReyCore\Elementor\TagPosts( [
			'el_instance' => $this->parent,
			'placeholder_class' => '--no-titles'
		], $this->parent->_settings );

		if( $this->posts_archive && $this->posts_archive->lazy_start() ){
			return;
		}

		reycore_assets()->add_styles($this->parent->get_style_name());

		$this->parent->query_posts();

		if ( ! $this->parent->_query->found_posts ) {
			return;
		}

		$this->parent->render_start();

		$thumb_class = $this->parent->_settings['inner_bg_overlay_gradient'] !== '' ? ' --inner-bg-gradient' : '';

		while ( $this->parent->_query->have_posts() ) : $this->parent->_query->the_post(); ?>
		<div class="reyEl-bPostGrid-item <?php echo $this->parent->get_classes(); ?> <?php echo (!has_post_thumbnail() ? '--missing-thumb' : ''); ?>">
			<div class="reyEl-bPostGrid-itemInner --box-styler">

				<?php $this->parent->render_thumbnail( $thumb_class, true ); ?>

				<div class="reyEl-bPostGrid-inner">

					<?php $this->parent->render_meta(); ?>

					<div class="reyEl-bpost-contentWrap">
						<?php
						$this->parent->render_title();
						$this->parent->render_excerpt(); ?>
					</div>

					<?php $this->parent->render_footer(); ?>

				</div>
			</div>
		</div>
		<?php endwhile;
		wp_reset_postdata();

		$this->parent->render_end();

		if( $this->posts_archive ){
			$this->posts_archive->lazy_end();
		}
	}

}
