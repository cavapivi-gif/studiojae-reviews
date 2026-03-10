<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class HeadingDynamic extends \Elementor\Skin_Base {

	public function __construct( \Elementor\Widget_Base $parent ) {
		parent::__construct( $parent );
		add_filter( 'elementor/widget/print_template', array( $this, 'skin_print_template' ), 10, 2 );
	}

	public function get_id() {
		return 'dynamic_title';
	}

	public function get_title() {
		return __( 'Dynamic Title', 'rey-core' );
	}

	public function dynamic_text() {
		return '{{ ... }}';
	}

	protected function render_text() {

		return \ReyCore\Elementor\Helper::render_dynamic_text([
			'settings' => $this->parent->get_settings_for_display(),
			'fallback' => $this->dynamic_text(),
			'source_key' => 'source',
		]);

	}

	public function render() {

		$settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute( 'title', 'class', 'elementor-heading-title' );

		if ( ! empty( $settings['size'] ) ) {
			$this->parent->add_render_attribute( 'title', 'class', 'elementor-size-' . $settings['size'] );
		}

		$title = $this->render_text();

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->parent->add_render_attribute( 'url', 'href', $settings['link']['url'] );

			if ( $settings['link']['is_external'] ) {
				$this->parent->add_render_attribute( 'url', 'target', '_blank' );
			}

			if ( ! empty( $settings['link']['nofollow'] ) ) {
				$this->parent->add_render_attribute( 'url', 'rel', 'nofollow' );
			}

			$title = sprintf( '<a %1$s>%2$s</a>', $this->parent->get_render_attribute_string( 'url' ), $title );
		}

		$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', $settings['header_size'], $this->parent->get_render_attribute_string( 'title' ), $title );

		echo $title_html;
	}

	public function content_template() {
		?>
		<#
		if( 'dynamic_title' === settings._skin ){
			var title = settings.rey_source_placeholder ? settings.rey_source_placeholder : '<?php echo $this->dynamic_text(); ?>';
		}
		else {
			var title = settings.title;
		}

		if( settings.rey_source_before ){
			title = settings.rey_source_before + title;
		}

		if( settings.rey_source_after ){
			title += settings.rey_source_after;
		}

		if ( '' !== settings.link.url ) {
			title = '<a href="' + settings.link.url + '">' + title + '</a>';
		}

		view.addRenderAttribute( 'title', 'class', [ 'elementor-heading-title', 'elementor-size-' + settings.size ] );

		if( 'dynamic_title' !== settings._skin ){
			view.addInlineEditingAttributes( 'title' );
		}

		var title_html = '<' + settings.header_size  + ' ' + view.getRenderAttributeString( 'title' ) + '>' + title + '</' + settings.header_size + '>';

		print( title_html );
		#>
		<?php
	}

	public function skin_print_template( $content, $heading ) {
		if( 'heading' == $heading->get_name() ) {
			ob_start();
			$this->content_template();
			$content = ob_get_clean();
		}
		return $content;
	}
}
