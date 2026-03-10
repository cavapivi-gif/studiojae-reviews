<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ReyCore_Sections_Basic extends \WP_Customize_Section {

	public $section;

	public $type = 'kirki-rey-section';

	public $css_class = '';
	public $separator = '';
	public $deprecated = '';
	public $title_before = '';
	public $icon = '';
	public $breadcrumbs = [];

	public function json() {

		$json = parent::json();

		$json['css_class'] = esc_attr( $this->css_class );
		$json['separator']  = esc_attr( $this->separator );
		$json['deprecated']  = esc_attr( $this->deprecated );
		$json['title_before']  = esc_attr( $this->title_before );
		$json['icon']  = $this->icon;

		if( $this->breadcrumbs ){
			$json['customizeAction'] = implode(' &#9656; ', $this->breadcrumbs) . ' &#9656; ';
		}

		return $json;

	}

	/**
	 * An Underscore (JS) template for rendering this section.
	 *
	 * Class variables for this section class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Section::json().
	 *
	 * @since 4.3.0
	 *
	 * @see WP_Customize_Section::print_template()
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
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} {{ css_classes }} {{ separator }}">

			<# if( data.title_before ){ #>
				<h4 class="before-section-title">
					<span>{{ data.title_before }}</span>
				</h4>
			<# } #>

			<h3 class="accordion-section-title">
				<?php
				if ( version_compare( get_bloginfo( 'version' ), '6.7.0', '<' ) ) { ?>
					{{ data.title }}<span class="screen-reader-text"><?php _e( 'Press return or enter to open this section' ); ?></span>
				<?php } else { ?>
					<button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="{{ data.id }}-content">{{ data.title }}</button>
				<?php } ?>
			</h3>
			<ul class="accordion-section-content">
				<li class="customize-section-description-container section-meta <# if ( data.description_hidden ) { #>customize-info<# } #>">
					<div class="customize-section-title">
						<button class="customize-section-back" tabindex="-1">
							<span class="screen-reader-text"><?php _e( 'Back' ); ?></span>
						</button>
						<h3>
							<span class="customize-action">
								{{{ data.customizeAction }}}
							</span>
							{{ data.title }}
						</h3>
						<# if ( data.description && data.description_hidden ) { #>
							<button type="button" class="customize-help-toggle dashicons dashicons-editor-help" aria-expanded="false"><span class="screen-reader-text"><?php _e( 'Help' ); ?></span></button>
							<div class="description customize-section-description">
								{{{ data.description }}}
							</div>
						<# } #>

						<div class="customize-control-notifications-container"></div>
					</div>

					<# if ( data.description && ! data.description_hidden ) { #>
						<div class="description customize-section-description">
							{{{ data.description }}}
						</div>
					<# } #>
				</li>
			</ul>

			<# if( data.title_after ){ #>
				<h4 class="after-section-title">
					<span>{{ data.title_after }}</span>
				</h4>
			<# } #>

		</li>
		<?php
	}
}
