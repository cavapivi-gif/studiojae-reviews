<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SectionSkew extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'section-skew',
			'title' => __( 'Section - Skew', 'rey-core' ),
			'icon' => 'rey-font-icon-skew-section',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#section-skew');
	}


	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content_left',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

		$this->add_control(
			'outline_text',
			[
				'label' => __( 'Outlined Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'BRAND FOCUS', 'rey-core' ),
				'placeholder' => __( 'eg: SOME TEXT', 'rey-core' ),
				'label_block' => true
			]
		);

		$this->add_control(
			'main_title',
			[
				'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Some text', 'rey-core' ),
				'placeholder' => __( 'eg: SOME TEXT', 'rey-core' ),
				'label_block' => true
			]
		);

		$this->add_control(
			'main_image',
			[
			   'label' => __( 'Image (above title)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
			]
		);


		$this->add_control(
			'btn_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOW NOW', 'rey-core' ),
			]
		);

		$this->add_control(
			'btn_link',
			[
				'label' => __( 'Button Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_content_right',
			[
				'label' => __( 'Media Content', 'rey-core' ),
			]
		);

		$this->add_control(
			'media_type',
			[
				'label' => __( 'Media Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image'  => __( 'Image', 'rey-core' ),
					'video'  => __( 'Video', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'media_image',
			[
			   'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'media_type' => 'image',
				],
			]
		);

		$this->add_control(
			'video_type',
			[
				'label' => __( 'Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube' => __( 'YouTube', 'rey-core' ),
					'vimeo' => __( 'Vimeo', 'rey-core' ),
					'hosted' => __( 'Self Hosted', 'rey-core' ),
				],
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'youtube_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your URL', 'rey-core' ) . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'youtube',
				],
			]
		);

		$this->add_control(
			'vimeo_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your URL', 'rey-core' ) . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'insert_url',
			[
				'label' => __( 'External URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label' => __( 'Choose File', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
					'insert_url' => '',
				],
			]
		);

		$this->add_control(
			'external_url',
			[
				'label' => __( 'URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'autocomplete' => false,
				'show_external' => false,
				'label_block' => true,
				'show_label' => false,
				'media_type' => 'video',
				'placeholder' => __( 'Enter your URL', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
					'insert_url' => 'yes',
				],
			]
		);

		$this->add_control(
			'start',
			[
				'label' => __( 'Start Time', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Specify a start time (in seconds)', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'loop' => '',
				],
			]
		);

		$this->add_control(
			'end',
			[
				'label' => __( 'End Time', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Specify an end time (in seconds)', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'loop' => '',
					'video_type' => [ 'youtube', 'hosted' ],
				],
			]
		);

		$this->add_control(
			'video_options',
			[
				'label' => __( 'Video Options', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'mute',
			[
				'label' => __( 'Mute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => __( 'Loop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'controls',
			[
				'label' => __( 'Player Controls', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type!' => 'vimeo',
				],
			]
		);

		// YouTube.
		$this->add_control(
			'yt_privacy',
			[
				'label' => __( 'Privacy Mode', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'description' => __( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'youtube',
				],
			]
		);

		// Vimeo.

		$this->add_control(
			'color',
			[
				'label' => __( 'Controls Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'media_type' => 'video',
					'video_type' => [ 'vimeo' ],
				],
			]
		);

		$this->add_control(
			'vimeo_title',
			[
				'label' => __( 'Intro Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_portrait',
			[
				'label' => __( 'Intro Portrait', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_byline',
			[
				'label' => __( 'Intro Byline', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'poster',
			[
				'label' => __( 'Poster', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-left' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'media_top',
			[
				'label' => __( 'Media Top Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 400,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-right' => 'top: {{VALUE}}px',
					'{{WRAPPER}} .rey-sectionSkew' => 'padding-bottom: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'outline_text_title',
			[
			   'label' => __( 'Outlined Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'outline_text_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'outline_text_typo',
				'selector' => '{{WRAPPER}} .sectionSkew-text',
			]
		);

		$this->add_control(
			'main_text_title',
			[
			   'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'main_text_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-mainTitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'main_text_typo',
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .sectionSkew-mainTitle',
			]
		);

		$this->add_control(
			'btn_title',
			[
			   'label' => __( 'Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => __( 'Button Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-mainBtn .btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-line-active',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();
	}


	public function render_start()
	{
		$this->add_render_attribute( 'wrapper', 'class', 'rey-sectionSkew' ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_left()
	{
		?>
		<div class="sectionSkew-left">
			<div class="sectionSkew-leftInner">
				<?php

				if( $title = $this->_settings['outline_text'] ):
					printf('<div class="sectionSkew-textWrapper"><h2 class="sectionSkew-text">%s</h2></div>', $title);
				endif;

				echo reycore__get_attachment_image( [
					'image' => $this->_settings['main_image'],
					'size' => 'full',
					'attributes' => ['class'=>'sectionSkew-mainImg']
				] );

				if( $main_title = $this->_settings['main_title'] ):
					printf('<h3 class="sectionSkew-mainTitle">%s</h3>', $main_title);
				endif;

				if( $button_text = $this->_settings['btn_text'] ): ?>

					<div class="sectionSkew-mainBtn">

						<?php
						reycore_assets()->add_styles('rey-buttons');
						$this->add_render_attribute( 'link' , 'class', 'btn ' . $this->_settings['btn_style'] );

						if( isset($this->_settings['btn_link']['url']) && $url = $this->_settings['btn_link']['url'] ){
							$this->add_render_attribute( 'link' , 'href', $url );

							if( $this->_settings['btn_link']['is_external'] ){
								$this->add_render_attribute( 'link' , 'target', '_blank' );
							}

							if( $this->_settings['btn_link']['nofollow'] ){
								$this->add_render_attribute( 'link' , 'rel', 'nofollow' );
							}
						} ?>

						<a <?php echo  $this->get_render_attribute_string('link'); ?>>
							<?php echo $button_text; ?>
						</a>
					</div>
					<!-- .sectionSkew-mainBtn -->
				<?php endif; ?>

			</div>
		</div><?php
	}

	public function render_right()
	{
		?>
		<div class="sectionSkew-right">
			<div class="sectionSkew-rightInner">
				<?php

				if( 'image' === $this->_settings['media_type'] ):

					echo reycore__get_attachment_image( [
						'image' => $this->_settings['media_image'],
						'size' => 'full',
						'attributes' => ['class'=>'sectionSkew-mediaImg']
					] );

				elseif( 'video' === $this->_settings['media_type'] ):

					$this->render_video();

				endif; ?>
			</div>
		</div><?php
	}

	public function render_video(){

		$video_type = $this->_settings['video_type'];

		$video_url = '';

		if( 'youtube' === $video_type ){
			$video_url = $this->_settings['youtube_url'];
		}
		else if( 'vimeo' === $video_type ){
			$video_url = $this->_settings['vimeo_url'];
		}
		else if( 'hosted' === $video_type ){

			if( '' !== $this->_settings['insert_url'] ){
				if( isset($this->_settings['external_url']['url']) ){
					$video_url = $this->_settings['external_url']['url'];
				}
			}
			else {
				if( isset($this->_settings['hosted_url']['id']) ){
					$video_url = wp_get_attachment_url($this->_settings['hosted_url']['id']);
				}
			}
		}

		if($video_url) {
			echo \ReyCore\Helper::get_embed_video( [
				'url'    => $video_url,
				'id'     => 'sectionSkew-video-' . $this->get_id(),
				'lazy'   => true,
				'params' => [
					'controls'       => $this->_settings['controls'] !== '' ? 1: 0,
					'loop'           => $this->_settings['loop'] !== '' ? 1: 0,
					'autoplay'       => $this->_settings['autoplay'] !== '' ? 1: 0,
					'mute'           => $this->_settings['mute'] !== '' ? 1: 0,
					'start'          => absint( $this->_settings['start'] ),
					'end'            => absint( $this->_settings['end'] ),
					// YT
					'yt_privacy'     => $this->_settings['yt_privacy'] !== '' ? 1: 0,
					// VIMEO
					'vimeo_color'     => $this->_settings['color'],
					'vimeo_title'     => $this->_settings['vimeo_title'] !== '' ? 1: 0,
					'vimeo_portrait'  => $this->_settings['vimeo_portrait'] !== '' ? 1: 0,
					'vimeo_byline'    => $this->_settings['vimeo_byline'] !== '' ? 1: 0,
					// hosted
					'poster'    => isset($this->_settings['poster']['id']) ? wp_get_attachment_url($this->_settings['poster']['id']) : '',
				],
			] );
		}

	}

	protected function render() {
		reycore_assets()->add_styles($this->get_style_name());
		$this->_settings = $this->get_settings_for_display();
		$this->render_start();
		$this->render_left();
		$this->render_right();
		$this->render_end();
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
