<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyHFGlobalSections extends \Kirki_Control_Base {

		public $type = 'rey-hf-global-section';

		public function render_content() {

			$label = $this->label;
			$description = $this->description;
            $input_id = '_customize-input-' . $this->id;
			$active_value = $this->value();
			$global_sections = $this->choices['global_sections'];
			$gs_type = $this->choices['type'];

			echo apply_filters("reycore/customizer/pre_text/" . $this->type, '', $this->id);

			printf('<div class="rey-control-wrap rey-hf-gs" data-id="%s" data-placeholder="%s" data-button-text="%s" data-new-link="%s" data-type="%s" data-edit-link="%s">',
				$this->id,
				esc_html__('New Header Global Section', 'rey-core'),
				esc_html__('Add', 'rey-core'),
				sprintf( esc_attr__('+ New %s Global Section', 'rey-core') , ucfirst($gs_type) ),
				esc_attr($gs_type),
				admin_url('post.php?action=elementor&post=')
			); ?>

				<?php if( !empty( $label ) ) : ?>
					<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
				<?php endif; ?>

				<?php if( !empty( $description ) ) : ?>
					<span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
				<?php endif; ?>

				<div class="customize-control-content">

					<div class="rey-radio-choices">
						<?php

						foreach([
								'none'    => esc_html__('Disabled', 'rey-core'),
								'default' => esc_html__('Basic', 'rey-core'),
								'gs'      => esc_html__('Global Section', 'rey-core')
							] as $k => $v ){

							printf( '<input type="radio" name="%1$s[type]" id="%1$s-%3$s" value="%3$s" %4$s><label for="%1$s-%3$s">%2$s</label>',
								esc_attr__( $input_id ),
								$v,
								$k,
								$this->is_checked($k)
							);
						} ?>
					</div>

					<?php if( !empty($global_sections) ){ ?>

						<div class="rey-gs-list <?php echo $active_value !== 'none' && $active_value !== 'default' ? '--active' : '' ?>">
							<?php
							$options = [];

							foreach ($global_sections as $k => $v){
								$options[] = sprintf('<option value="%1$d" %3$s>%2$s</option>',
									$k,
									$v,
									selected($k, $active_value, false)
								);
							}

							if( $options ){ ?>

								<span class="customize-control-title rey-control-title">
								<div class="rey-csTitleHelp-popWrapper --prel">
									<span class="rey-csTitleHelp-title"><?php echo sprintf( esc_html__('Select a %s Global Section. ', 'rey-core'), ucfirst($gs_type)); ?></span>
									<div class="rey-csTitleHelp-pop --pop-qmark">
										<span class="rey-csTitleHelp-label"></span>
										<span class="rey-csTitleHelp-content" style="min-width: 290px;">
											<?php echo reycore__header_footer_layout_desc($gs_type); ?>
										</span>
									</div>
								</div>
								</span><?php

								printf('<select name="%1$s[gs_list]" id="%1$s-gs_list" class="js-gs-list">%2$s</select>',
									esc_attr__( $input_id ),
									implode('', $options)
								);

							} ?>
						</div>

					<?php } else {

						printf( '<p class="description customize-control-description --none-existing">%s</p>', esc_html__('No global sections existing. Please create a new one and refresh Customizer.', 'rey-core') );

					} ?>

					<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $active_value ); ?>" <?php $this->link(); ?> />

				</div>
			</div>

            <?php
		}

		public function is_checked( $val ){

			$not_gs = $this->value() !== 'none' && $this->value() !== 'default';

			$s = 'checked="checked"';

			if( $val == $this->value() ){
				return $s;
			}

			elseif ($not_gs){
				return $s;
			}
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-hf-global-section'] = 'Kirki_Controls_ReyHFGlobalSections';
		return $controls;
	} );

} );
