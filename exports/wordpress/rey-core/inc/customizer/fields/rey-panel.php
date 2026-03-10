<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ReyCore_Panels_Basic extends \WP_Customize_Panel {

	public $panel;

	public $type = 'kirki-rey-panel';

	public $css_class = '';
	public $separator = '';
	public $deprecated = '';
	public $title_before = '';
	public $title_after = '';
	public $icon = '';

	public function json() {

		$json = parent::json();

		$json['css_class'] = esc_attr( $this->css_class );
		$json['separator']  = esc_attr( $this->separator );
		$json['deprecated']  = esc_attr( $this->deprecated );
		$json['title_before']  = $this->title_before;
		$json['title_after']  = $this->title_after;
		$json['icon']  = $this->icon;

		return $json;

	}

	/**
	 * An Underscore (JS) template for rendering this panel's container.
	 *
	 * Class variables for this panel class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Panel::json().
	 *
	 * @see WP_Customize_Panel::print_template()
	 *
	 * @since 4.3.0
	 */
	protected function render_template() {
		?>

		<#
			var separator = data.separator ? '--separator-' + data.separator : '';
			var css_classes = data.css_class;
			if( data.title_before ){
				css_classes += '--title-before';
			}
			if( data.title_after ){
				css_classes += '--title-after';
			}
		#>
		<li id="accordion-panel-{{ data.id }}" class="accordion-section control-section control-panel control-panel-{{ data.type }} {{ css_classes }} {{ separator }}">
			<# if( data.title_before ){ #>
				<h4 class="before-section-title">
					<span>{{ data.title_before }}</span>
				</h4>
			<# } #>
			<h3 class="accordion-section-title" >
				<?php
				if ( version_compare( get_bloginfo( 'version' ), '6.7.0', '<' ) ) { ?>
					{{ data.title }}<span class="screen-reader-text"><?php _e( 'Press return or enter to open this panel' ); ?></span>
					<?php } else { ?>
					<button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="{{ data.id }}-content">{{ data.title }}</button>
				<?php } ?>
			</h3>
			<# if( data.title_after ){ #>
				<h4 class="after-section-title">
					<span>{{ data.title_after }}</span>
				</h4>
			<# } #>
			<ul class="accordion-sub-container control-panel-content"></ul>
		</li>

		<?php
	}
}
