<?php
namespace ReyCore\Modules\Cards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CardBase
{
	public $template_path;
	public $asset_key;

	public $_item;
	public $_key;
	public $_el_settings;
	public $_widget_type;

	public function __construct(){
		$this->template_path = sprintf('%s/%s', \ReyCore\Modules\Cards\Base::get_path( basename(__DIR__) ), $this->get_id());
		$this->asset_key = Base::ASSET_HANDLE . '-' . $this->get_id();
	}

	public function get_id(){}

	public function get_name(){}

	public function get_js(){}

	public function get_css(){}

	public function get_critical_css(){}

	public function get_card_controls( $element ){

		/* Example:

		$element->add_control(
			$this->_control_key('_style'),
			[
				'label' => esc_html__( 'Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical'  => esc_html__( 'Vertical text', 'rey-core' ),
					'normal'  => esc_html__( 'Normal text', 'rey-core' ),
				],
				'condition' => [
					Base::CARD_KEY => $this->get_id(),
				],
			]
		);

		*/
	}

	public function get_supports(){}

	public function get_wrapper_classes(){}

	public function get_card_defaults(){}

	public function _control_key( $key ){
		return $this->get_id() . $key;
	}

	public function _control_val( $key, $default = null ){

		if( ( $_key = $this->_control_key($key) ) && isset( $this->_el_settings[$_key] ) ) {
			return $this->_el_settings[$_key];
		}

		return $default;
	}

	public function get_limited_subtitle(){

		$excerpt = $this->_item['subtitle'];

		if( ! ( $ex_length = $this->_el_settings['subtitle_length']) ){
			return $excerpt;
		}

		$excerpt = explode(' ', $excerpt, $ex_length);

		if ( count( $excerpt ) >= $ex_length ) {
			array_pop($excerpt);
			$excerpt = implode(" ", $excerpt) . '&hellip;';
		}
		else {
			$excerpt = implode(" ",$excerpt);
		}

		return preg_replace('`\[[^\]]*\]`','',$excerpt);
	}

	public function get_item_data( $element ){

		$data_defaults = wp_parse_args( (array) $this->get_card_defaults(), [
			'image'              => [
				'url' => '',
				'id'  => '',
			],
			'captions'           => '',
			'title'              => '',
			'subtitle_show'      => '',
			'subtitle'           => '',
			'button_style'       => $this->_el_settings['button_style'],
			'button_show'        => '',
			'button_text'        => '',
			'button_url'         => [
				'url'               => '',
				'is_external'       => '',
				'nofollow'          => '',
				'custom_attributes' => '',
			],
			'video'              => '',
			'video_autoplay'     => '',
			'overlay_color'      => '',
			'label'              => '',
			'text_color'         => '',
			'_id'                => '',
			'uid'                => '',
		] );

		$item = reycore__wp_parse_args( $element->_items[ $element->item_key ], $data_defaults);

		if( ! $item['_id'] ){
			$item['_id'] = $item['uid'];
		}

		return $item;
	}

	public function render( $element ){

		if( method_exists( $element, 'get_unique_name' ) ){
			$this->_widget_type = $element->get_unique_name();
		}

		$this->_el_settings = $element->_settings;
		$this->_item = $this->get_item_data( $element );

		$this->__item_start();
		$this->__item_content();
		$this->__item_end();

	}

	public function __item_content(){}

	public function __item_start(){

		$item_classes['wrapper'] = 'rey-card';

		// card type
		$item_classes['type'] = '--' . $this->get_id();

		if( $this->_item['video'] ){
			$item_classes['video'] = '--video';
		}

		if( isset($this->_item['button_url']['url']) && $url = $this->_item['button_url']['url'] ) {
			global $wp;
			if( ($current = trailingslashit( home_url( $wp->request ) )) && trailingslashit( $url ) === $current){
				$item_classes['active'] = '--active';
			}
		}

		// extra card classes
		foreach ( (array) $this->get_wrapper_classes() as $key => $value ) {
			$item_classes[$key] = $value;
		}

		// render start
		printf('<div class="%s">', esc_attr( implode(' ', $item_classes) ));

		// load styles
		reycore_assets()->add_styles($this->asset_key);
	}

	public function __item_end(){
		?></div><?php
	}

	public function __overlay(){
		echo '<div class="__overlay"></div>';
	}

	public function __image(){

		$img_data = [
			'image'      => $this->_item['image'],
			'size'       => $this->_el_settings['image_size'],
			'attributes' => ['class' => '__media'],
			'settings'   => $this->_el_settings,
		];

		if( isset($this->_el_settings['lazy_load_img']) && $this->_el_settings['lazy_load_img'] ){
			$img_data['lazy-attribute'] = 'data-splide-lazy';
		}

		$image = reycore__get_attachment_image( $img_data );

		if( ! $image ){
			return;
		}

		$link = $this->__link_tag('__media-link');

		echo $link['start'] . $image . $link['end'];

	}

	public function __video(){

		if( ! $this->_item['video'] ){
			return;
		}

		// no need to load the script if the widget is not a grid
		if( 'reycore-grid' === $this->_widget_type ){
			reycore_assets()->add_scripts( 'reycore-widget-grid-videos' );
		}


		$args = [
			'url' => $this->_item['video'],
			'class' => '__media',
			'autoplay' => ! empty($this->_item['video_autoplay']) && 'yes' === $this->_item['video_autoplay'],
		];

		if( isset($this->_item['image']['id']) && $image_id = $this->_item['image']['id'] ){
			$args['style'] = sprintf('background-image: url(%s)', wp_get_attachment_image_url($image_id, 'large'));
		}

		echo \ReyCore\Helper::get_embed_video( $args );

	}

	public function __media(){

		$this->__video();
		$this->__image();
		$this->__overlay();

	}

	public function __link_tag( $class = '' ){

		$link = [
			'start' => '',
			'end' => '',
		];

		if( ! $this->_item ){
			return $link;
		}

		$attributes = [];

		if( isset($this->_item['button_url']['url']) && $url = $this->_item['button_url']['url'] )
		{
			$attributes['href'] = $url;
			$attributes['class'] = $class;

			if( ! empty($this->_item['title']) ){
				$attributes['aria-label'] = esc_attr(wp_strip_all_tags($this->_item['title']));
			}

			if( $this->_item['button_url']['is_external'] ){
				$attributes['target'] = '_blank';
			}

			if( $this->_item['button_url']['nofollow'] ){
				$attributes['rel'] = 'nofollow';
			}

			if( ($custom_attributes = $this->_item['button_url']['custom_attributes']) ){

				if( is_string($custom_attributes) ){
					$custom_attributes = \Elementor\Utils::parse_custom_attributes( $custom_attributes , "\n" );
				}

				if( is_array($custom_attributes) ){
					foreach ($custom_attributes as $key => $value) {

						if( ! $key ) {
							continue;
						}

						// merge
						if( isset($attributes[$key]) && ! is_array($value) ){
							$attributes[$key] .= ' ' . $value;
						}
						// add
						else {
							$attributes[$key] = $value;
						}

					}
				}

			}

			$link['start'] = sprintf('<a %s>', reycore__implode_html_attributes($attributes) );
			$link['end'] = '</a>';
		}

		return $link;
	}

	public function __label(){

		if( ! ($label = $this->_item['label']) ){
			return;
		}

		printf('<%2$s class="__captionEl __captionLabel">%1$s</%2$s>', $label, Base::$defaults['label_tag']);

	}

	public function __title( $args = [] ){

		if( ! ($title = $this->_item['title']) ){
			return;
		}

		$args = wp_parse_args($args, [
			'class' => [],
		]);

		if( isset($this->_item['before_title']) && ($before_title = $this->_item['before_title'])){
			echo $before_title;
		}

		printf('<%s class="__captionEl __captionTitle %s">', Base::$defaults['title_tag'], esc_attr(implode(' ', $args['class'])));

		$link = [
			'start' => '',
			'end' => '',
		];

		if( isset($this->_el_settings['title_link']) && $this->_el_settings['title_link'] !== '' ){
			$link = $this->__link_tag();
		}

		echo $link['start'] . $title . $link['end'];

		printf('</%s>', Base::$defaults['title_tag']);

		if( isset($this->_item['after_title']) && ($after_title = $this->_item['after_title'])){
			echo $after_title;
		}

	}

	public function __subtitle(){

		if( 'no' === $this->_item['subtitle_show'] ){
			return;
		}

		if( ! ($subtitle = $this->_item['subtitle']) ){
			return;
		}

		printf('<%2$s class="__captionEl __captionSubtitle">%1$s</%2$s>',
			reycore__parse_text_editor($this->get_limited_subtitle()),
			Base::$defaults['desc_tag']
		);
	}

	public function __button(){

		if( 'no' === $this->_item['button_show'] ){
			return;
		}

		if( ! ($button_text = $this->_item['button_text']) ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons');

		$link = $this->__link_tag( 'btn ' . $this->_item['button_style'] );

		echo '<div class="__captionEl __captionBtn">';

			echo $link['start'] . $button_text . $link['end'];

		echo '</div>';
	}

	public function __captions() {

		if( $this->_item['captions'] === '' ){
			return;
		}

		echo '<div class="__caption">';

			$this->__label();
			$this->__title();
			$this->__subtitle();
			$this->__button();

		echo '</div>';

	}

}
