<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TextDynamic extends \Elementor\Skin_Base {

	public function __construct( \Elementor\Widget_Base $parent ) {
		parent::__construct( $parent );
		add_filter( 'elementor/widget/print_template', [$this, 'skin_print_template'], 10, 2 );
	}

	public function get_id() {
		return 'dynamic_text';
	}

	public function get_title() {
		return __( 'Dynamic Text', 'rey-core' );
	}

	public function dynamic_text() {
		return '{{ ... }}';
	}

	protected function render_text() {

		return \ReyCore\Elementor\Helper:: render_dynamic_text([
			'settings' => $this->parent->get_settings_for_display(),
			'fallback' => $this->dynamic_text(),
			'source_key' => 'rey_dynamic_source',
		]);

	}

	function widget_text($content, $settings){

		if( ! isset($settings['rey_toggle_text']) ){
			return $content;
		}

		if( $settings['rey_toggle_text'] === '' ){
			return $content;
		}

		return \ReyCore\Elementor\Helper::common_toggle_text($content, $settings);
	}

	public function render() {

		$this->parent->add_render_attribute( 'editor', 'class', [ 'elementor-text-editor', 'elementor-clearfix' ] );
		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'editor' ); ?>>
			<?php echo $this->widget_text( $this->render_text(), $this->parent->get_settings_for_display() ); ?>
		</div>
		<?php
	}

	public function content_template() {
		?>
		<#
		if( 'dynamic_text' === settings._skin ){
			var text = '<?php echo $this->dynamic_text(); ?>';
		}
		else {
			var text = settings.editor;

			view.addInlineEditingAttributes( 'editor', 'advanced' );
		}

		view.addRenderAttribute( 'editor', 'class', [ 'elementor-text-editor', 'elementor-clearfix' ] );

		#>
		<div {{{ view.getRenderAttributeString( 'editor' ) }}}>{{{ text }}}</div>
		<?php
	}

	public function skin_print_template( $content, $heading ) {
		if( 'text-editor' == $heading->get_name() ) {
			ob_start();
			$this->content_template();
			$content = ob_get_clean();
		}
		return $content;
	}
}
