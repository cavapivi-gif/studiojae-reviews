<?php

class ReyTheme_SvgIcons {

	protected static $theme_icons = [];
	public static $icons = [];
	public static $social_icons = [];
	public static $icon_styles = false;

	public function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	public function init(){

		$raw_icons = $this->raw_icons();
		self::$theme_icons = array_keys($raw_icons);
		self::$icons = $raw_icons;

		// register other icons
		do_action('rey/svg_icons', $this);

		// retrieve svg markup and exit
		// self::get_svg_markup();

		add_filter( 'rey/main_script_params', [$this, 'main_script_params'], 20);

	}

	public function main_script_params($params){

		$params['svg_icons_path'] = add_query_arg([
			'get_svg_icon' => '%%icon%%'
			], get_site_url()
		);

		// always available icons
		$params['svg_icons'] = [
			'close' => sprintf('<svg %s>%s</svg>', rey__implode_html_attributes([
				'role' => 'img',
				'viewbox' => self::$icons['close']['viewbox'],
				'class' => 'rey-icon rey-icon-close',
			]), self::$icons['close']['icon'] )
		];

		return $params;
	}

	public static function get_svg_markup(){

		if( ! isset($_REQUEST['get_svg_icon']) ){
			return;
		}

		header("Content-Type: image/svg+xml");

		if( ! (($icon = reycore__clean($_REQUEST['get_svg_icon'])) && strlen($icon) < 30) ){
			exit;
		}

		echo self::get_icon(['id' => $icon]);
		exit;

		// getting as sprite
		// self::get_svg_as_sprite():
	}

	public static function get_svg_as_sprite($icon){

		$icon_data = self::get_icon([
			'id'     => $icon,
			'return' => 'raw',
		]);

		echo '<?xml version="1.0" encoding="iso-8859-1"?>';
		echo '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="'. $icon_data['viewbox'] .'"><symbol id="rey-icon-'. $icon .'" height="100%" width="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="'. $icon_data['viewbox'] .'">';
		echo $icon_data['icon'];
		echo '</symbol></svg>';

		exit;
	}

	/**
	 * Get embed SVG icon and render output.
	 *
	 * @since 2.4.0
	 */
	public static function get_icon($args = []){

		// Make sure $args are an array.
		if ( !is_array($args) && empty( $args ) ) {
			return esc_html__( 'Please define default parameters in the form of an array.', 'rey' );
		}

		// Define an icon.
		if ( ! isset($args['id']) ) {
			return esc_html__( 'Id Missing.', 'rey' );
		}

		$svg = '';

		// Set defaults.
		$defaults = [
			'id'          => '',
			'title'       => '',
			'style'       => '',
			'class'       => '',
			'link'        => '',
			'target'      => '_self',
			'color'       => '',
			'hover_color' => '',
			'version'     => REY_THEME_VERSION,
			'social'      => false,
			'return'      => 'svg',
			'id_attribute'=> true,
			'attributes'  => [],
			'style_css'   => true,
		];

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		if( ! $args['id'] ){
			return $svg;
		}

		if( ! self::$icon_styles ){
			rey_assets()->add_styles('rey-icon');
			self::$icon_styles = true;
		}

		// cleanup ID
		$id = str_replace(['reycore-icon-', 'rey-icon-'], '', $args['id']);

		$attributes = $args['attributes'];

		// Set aria hidden.
		$attributes['aria-hidden'] = 'true';

		if ( $args['style'] ) {
			$attributes['style'] = esc_attr($args['style']);
		}

		$attributes['role'] = 'img';

		if( $args['link'] ) {
			// add custom mouse attributes so we can handle hover
			$attributes['onmouseover'] = sprintf('this.style.color=\'%s\'', esc_attr($args['hover_color']));
			$attributes['onmouseout'] = sprintf('this.style.color=\'%s\'', esc_attr($args['color']));
			$svg .= sprintf('<a class="rey-iconLink" href="%s" target="%s">', esc_url($args['link']), esc_attr($args['target']));
		}

		$unique_id = uniqid();

		if( $args['id_attribute'] ){
			$attributes['id'] = sprintf('rey-icon-%s-%s', esc_attr( $id ), $unique_id);
		}

		$attributes['class'] = [
			'rey-icon',
			'rey-icon-' . esc_attr( $id ),
			$args['class']
		];

		if( $args['social'] ){
			$raw_icons = self::$social_icons;
		}
		else {
			$raw_icons = self::$icons;
		}

		if( ! isset($raw_icons[$id]) ){
			return '';
		}

		$raw_icon = $raw_icons[$id];

		if( 'raw' === $args['return'] ){
			return $raw_icon;
		}

		if( isset($raw_icon['class']) ){
			$attributes['class'][] = $raw_icon['class'];
		}

		if( isset($raw_icon['viewbox']) ){
			$attributes['viewbox'] = $raw_icon['viewbox'];
		}

		$title_text = $title_tag = '';

		if( isset($raw_icon['title']) && ! empty($raw_icon['title']) ){
			$title_text = $raw_icon['title'];
		}

		// override by custom title
		if( $args['title'] ){
			$title_text = $args['title'];
		}

		// Force disable text
		if( $args['title'] === false ){
		}
		// force no titles
		$title_text = '';

		// Set ARIA.
		if ( $title_text ) {
			unset($attributes['aria-hidden']);
			$attributes['aria-labelledby'] = 'title-' . $unique_id;
			$title_tag = '<title id="title-' . $unique_id . '">' . esc_html( $title_text ) . '</title>';
		}

		if( $args['style_css'] && isset($raw_icon['css']) && ! empty($raw_icon['css']) ){
			$title_tag .= sprintf('<style type="text/css">%s</style>', $raw_icon['css']);
		}

		// Begin SVG markup.
		$svg .= sprintf('<svg %s>', rey__implode_html_attributes($attributes) );
		$svg .= $title_tag; // icon code
		$svg .= $raw_icon['icon']; // icon code
		$svg .= '</svg>';

		if( $args['link'] ) {
			$svg .= '</a>';
		}

		// ID reset back for filters.
		// @legacy
		$real_id = $args['id'];
		$args['real-id'] = $real_id;
		$prefix = in_array($real_id, self::$theme_icons, true) ? 'rey-icon-' : 'reycore-icon-';
		$args['id'] = $prefix . $real_id;

		/**
		 * To check for the icon id, please use the 'real-id' key:
		 *
			// this code replaces "close" svg icon
		 	add_filter('rey/svg_icon', function($svg, $args){
				if( 'close' === $args['real-id'] ){
					return '<svg ..></svg>';
				}
				return $svg;
			} ,10, 2);
		 *
		 * @since 1.0.0
		 */

		return apply_filters('rey/svg_icon', $svg, $args, $raw_icon);
	}

	/**
	 * Get Long Arrow SVG, wrapped in tag.
	 *
	 * @since 2.4.0
	 */
	public static function get_arrow($args = []){

		$args = wp_parse_args($args, [
			'right'      => true,
			'class'      => '',
			'attributes' => '',
			'tag'        => 'div',
			'type'       => 'arrow-long',
			'markup'     => '',
			'title'      => '',
			'name'       => '',
		]);

		$svg_markup = '';

		if( $args['type'] === '' ){
			$args['type'] = 'arrow-long';
		}

		if( '' !== $args['markup'] ){
			$svg_markup = $args['markup'];
		}
		else {
			$svg_markup = self::get_icon(['id' => $args['type'], 'title' => $args['title']]);
		}

		return sprintf( '<%4$s class="rey-arrowSvg rey-arrowSvg--%1$s %2$s" %3$s>%5$s</%4$s>',
			($args['right'] ? 'right' : 'left'),
			$args['class'],
			$args['attributes'],
			$args['tag'],
			apply_filters( 'rey/svg_arrow_markup', $svg_markup, $args )
		);
	}

	public function raw_icons(){

		$icons['arrow-long'] = [
			'title' => 'Arrow',
			'icon' => '<path d="M0.928904706,3.0387609 L44.0113745,3.0387609 L44.0113745,4.97541883 L0.928904706,4.97541883 C0.415884803,4.97541883 2.13162821e-14,4.54188318 2.13162821e-14,4.00708986 C2.13162821e-14,3.47229655 0.415884803,3.0387609 0.928904706,3.0387609 Z" class="rey-arrowSvg-dash" style="transform:var(--i-dsh-tr,initial);transition:var(--i-trs,initial);transform-origin:100% 50%;"></path><path d="M49.6399545,3.16320794 L45.1502484,0.129110528 C45.0056033,0.0532149593 44.8474869,0.0092610397 44.685796,3.99680289e-14 C44.5479741,0.0112891909 44.4144881,0.0554642381 44.2956561,0.129110528 C44.0242223,0.2506013 43.8503957,0.531340097 43.8559745,0.839218433 L43.8559745,6.90741326 C43.8503957,7.21529159 44.0242223,7.49603039 44.2956561,7.61752116 C44.5594727,7.77895738 44.8864318,7.77895738 45.1502484,7.61752116 L49.6399545,4.58342375 C49.8682741,4.42554586 50.0055358,4.15892769 50.0055358,3.87331584 C50.0055358,3.587704 49.8682741,3.32108583 49.6399545,3.16320794 Z"></path>',
			'viewbox' => '0 0 50 8',
			'class' => '--default',
		];

		if( 'alt' === get_theme_mod('style_arrow_long', 'classic') ){
			$icons['arrow-long'] = [
				'title' => 'Arrow',
				'icon' => '<path d="M17.2308 16.5L16.1538 15.45L19 12.75L2 12.75V11.25L19 11.25L16.1538 8.55L17.3077 7.5L22 12L17.2308 16.5Z" fill="currentColor"></path>',
				'viewbox' => '0 0 24 24',
			];
		}

		$icons['chevron'] = [
			'title' => 'Arrow Chevron',
			'icon' => '<polygon fill="currentColor" points="39.5 32 6.83 64 0.5 57.38 26.76 32 0.5 6.62 6.83 0"></polygon>',
			'viewbox' => '0 0 40 64',
			'class' => '',
		];

		$icons['search'] = [
			'title' => 'Search',
			'icon' => '<circle stroke="currentColor" stroke-width="2.2" fill="none" cx="11" cy="11" r="10"></circle>
			<path d="M20.0152578,17.8888876 L23.5507917,21.4244215 C24.1365782,22.010208 24.1365782,22.9599554 23.5507917,23.5457419 C22.9650053,24.1315283 22.0152578,24.1315283 21.4294714,23.5457419 L17.8939375,20.010208 C17.3081511,19.4244215 17.3081511,18.4746741 17.8939375,17.8888876 C18.4797239,17.3031012 19.4294714,17.3031012 20.0152578,17.8888876 Z" fill="currentColor" stroke="none"></path>',
			'viewbox' => '0 0 24 24',
		];

		$icons['spinner'] = [
			'title' => 'Spinner Loading',
			'icon' => '<g><animateTransform attributeName="transform" type="rotate" values="0 33 33;270 33 33" begin="0s" dur="1.4s" fill="freeze" repeatCount="indefinite"/><circle fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30" stroke-dasharray="187" stroke-dashoffset="610" style="stroke:currentColor"><animateTransform attributeName="transform" type="rotate" values="0 33 33;135 33 33;450 33 33" begin="0s" dur="1.4s" fill="freeze" repeatCount="indefinite"/><animate attributeName="stroke-dashoffset" values="187;46.75;187" begin="0s" dur="1.4s" fill="freeze" repeatCount="indefinite"/></circle></g>',
			'viewbox' => '0 0 66 66',
		];

		$icons['help'] = [
			'title' => 'Help',
			'icon' => '<path d="M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM10.59 8.59a1 1 0 1 1-1.42-1.42 4 4 0 1 1 5.66 5.66l-2.12 2.12a1 1 0 1 1-1.42-1.42l2.12-2.12A2 2 0 0 0 10.6 8.6zM12 18a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>',
			'viewbox' => '0 0 24 24',
		];

		$icons['cart'] = [
			'title' => 'Cart',
			'icon' => '<path d="M21,3h-4.4C15.8,1.2,14,0,12,0S8.2,1.2,7.4,3H3C2.4,3,2,3.4,2,4v19c0,0.6,0.4,1,1,1h18c0.6,0,1-0.4,1-1V4  C22,3.4,21.6,3,21,3z M12,1c1.5,0,2.8,0.8,3.4,2H8.6C9.2,1.8,10.5,1,12,1z M20,22H4v-4h16V22z M20,17H4V5h3v4h1V5h8v4h1V5h3V17z"/>',
			'viewbox' => '0 0 24 24',
		];

		$icons['comments'] = [
			'title' => 'Comments',
			'icon' => '<path d="M8.07,22.99 C7.94652326,22.991009 7.82410184,22.9672048 7.71,22.92 C7.33011475,22.7729193 7.0797889,22.4073641 7.08,22 L7.08,17.88 C2.85534035,17.0625739 -0.144710677,13.2902325 0.01,8.99 C0.01,4.04 3.73,0.01 8.31,0.01 L15.71,0.01 C20.29,0.01 24.01,4.04 24.01,8.99 C24.01,13.94 20.29,17.98 15.71,17.98 L13.3,17.98 L8.79,22.69 C8.60171085,22.8851148 8.34112535,22.9936921 8.07,22.99 Z M8.31,1.99 C4.82,1.99 1.99,5.13 1.99,8.99 C1.99,12.8 4.68,15.87 8.11,15.99 C8.64275325,16.0113046 9.06509402,16.4468435 9.07,16.98 L9.07,19.53 L12.16,16.3 C12.3470714,16.1091444 12.6027541,16.0011094 12.87,16 L15.71,16 C19.19,16 22.03,12.85 22.03,8.99 C22.03,5.13 19.19,1.99 15.71,1.99 L8.31,1.99 Z"></path>',
			'viewbox' => '0 0 24 23',
		];

		$icons['mail'] = [
			'title' => 'Mail',
			'icon' => '<path d="M28,5H4C1.791,5,0,6.792,0,9v13c0,2.209,1.791,4,4,4h24c2.209,0,4-1.791,4-4V9  C32,6.792,30.209,5,28,5z M2,10.25l6.999,5.25L2,20.75V10.25z M30,22c0,1.104-0.898,2-2,2H4c-1.103,0-2-0.896-2-2l7.832-5.875  l4.368,3.277c0.533,0.398,1.166,0.6,1.8,0.6c0.633,0,1.266-0.201,1.799-0.6l4.369-3.277L30,22L30,22z M30,20.75l-7-5.25l7-5.25  V20.75z M17.199,18.602c-0.349,0.262-0.763,0.4-1.199,0.4c-0.436,0-0.851-0.139-1.2-0.4L10.665,15.5l-0.833-0.625L2,9.001V9  c0-1.103,0.897-2,2-2h24c1.102,0,2,0.897,2,2L17.199,18.602z"></path>',
			'viewbox' => '0 0 32 32',
		];

		$icons['link'] = [
			'title' => 'Link',
			'icon' => '<path d="M7.8 24c0-3.42 2.78-6.2 6.2-6.2h8V14h-8C8.48 14 4 18.48 4 24s4.48 10 10 10h8v-3.8h-8c-3.42 0-6.2-2.78-6.2-6.2zm8.2 2h16v-4H16v4zm18-12h-8v3.8h8c3.42 0 6.2 2.78 6.2 6.2s-2.78 6.2-6.2 6.2h-8V34h8c5.52 0 10-4.48 10-10s-4.48-10-10-10z"/>',
			'viewbox' => '0 0 48 48',
		];

		$icons['quote'] = [
			'title' => 'Quote',
			'icon' => '<path d="M0 432V304C0 166.982 63.772 67.676 193.827 32.828 209.052 28.748 224 40.265 224 56.027v33.895c0 10.057-6.228 19.133-15.687 22.55C142.316 136.312 104 181.946 104 256h72c26.51 0 48 21.49 48 48v128c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48zm336 48h128c26.51 0 48-21.49 48-48V304c0-26.51-21.49-48-48-48h-72c0-74.054 38.316-119.688 104.313-143.528C505.772 109.055 512 99.979 512 89.922V56.027c0-15.762-14.948-27.279-30.173-23.199C351.772 67.676 288 166.982 288 304v128c0 26.51 21.49 48 48 48z"/>',
			'viewbox' => '0 0 512 512',
		];

		$icons['close'] = [
			'title' => 'Close',
			'icon' => '<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="square"><path d="M4.79541854,4.29541854 L104.945498,104.445498 L4.79541854,4.29541854 Z" stroke="currentColor" stroke-width="var(--stroke-width, 12px)"></path><path d="M4.79541854,104.704581 L104.945498,4.55450209 L4.79541854,104.704581 Z" stroke="currentColor" stroke-width="var(--stroke-width, 12px)"></path></g>',
			'viewbox' => '0 0 110 110',
		];

		$icons['user'] = [
			'title' => 'User',
			'icon' => '<path d="M8.68220488,13 L5.8,13 C4.7,11.6 4,9.9 4,8 C4,3.6 7.6,0 12,0 C16.4,0 20,3.6 20,8 C20,9.9 19.3,11.6 18.2,13 L15.3177951,13 C16.9344907,11.9250785 18,10.0869708 18,8 C18,4.6862915 15.3137085,2 12,2 C8.6862915,2 6,4.6862915 6,8 C6,10.0869708 7.06550934,11.9250785 8.68220488,13 Z"></path><path d="M18,14 L6,14 C2.7,14 0,16.7 0,20 L0,23 C0,23.6 0.4,24 1,24 L23,24 C23.6,24 24,23.6 24,23 L24,20 C24,16.7 21.3,14 18,14 Z M22,22 L2,22 L2,20 C2,17.8 3.8,16 6,16 L18,16 C20.2,16 22,17.8 22,20 L22,22 Z" ></path>',
			'viewbox' => '0 0 24 24',
		];

		$icons['check'] = [
			'title' => 'Check',
			'icon' => '<path d="M23,0 C10.317,0 0,10.317 0,23 C0,35.683 10.317,46 23,46 C35.683,46 46,35.683 46,23 C46,18.44 44.660281,14.189328 42.363281,10.611328 L40.994141,12.228516 C42.889141,15.382516 44,19.06 44,23 C44,34.579 34.579,44 23,44 C11.421,44 2,34.579 2,23 C2,11.421 11.421,2 23,2 C28.443,2 33.393906,4.0997656 37.128906,7.5097656 L38.4375,5.9648438 C34.3525,2.2598437 28.935,0 23,0 Z M41.236328,5.7539062 L21.914062,28.554688 L13.78125,20.96875 L12.417969,22.431641 L22.083984,31.447266 L42.763672,7.046875 L41.236328,5.7539062 Z"></path>',
			'viewbox' => '0 0 46 46',
		];

		$icons['sliders'] = [
			'title' => 'Sliders',
			'icon' => '<path d="M24.4766968,14.7761548 L24.4766968,9.88088774 L31.8197368,9.88088774 L31.8197368,4.98552774 L24.4766968,4.98552774 L24.4766968,0.0901677419 L19.5814297,0.0901677419 L19.5814297,4.98552774 L1.03225807e-05,4.98552774 L1.03225807e-05,9.88088774 L19.5814297,9.88088774 L19.5814297,14.7761548 L24.4766968,14.7761548 Z M12.2408258,31.9098839 L12.2408258,27.0145239 L31.8197677,27.0145239 L31.8197677,22.1191639 L12.2408258,22.1191639 L12.2408258,17.2238968 L7.34304,17.2238968 L7.34304,22.1191639 L2.84217094e-14,22.1191639 L2.84217094e-14,27.0145239 L7.34304,27.0145239 L7.34304,31.9098839 L12.2408258,31.9098839 Z" id="Shape" fill-rule="nonzero"></path>',
			'viewbox' => '0 0 32 32',
		];

		$icons['telephone'] = [
			'title' => 'Telephone',
			'icon' => '<path d="M33.3533245,30.348235 C33.3585492,31.6354238 32.8208627,32.8651684 31.8723596,33.7353547 C30.9238565,34.605541 29.652442,35.0355221 28.3430173,34.916932 C23.4170636,34.3816869 18.6853383,32.6984428 14.5398948,30.0100601 C10.6692995,27.5505229 7.38771043,24.2689338 4.93538673,20.4095814 C2.22998526,16.2333986 0.546358851,11.4786621 0.0185686805,6.5068017 C-0.0969211245,5.2287956 0.330047556,3.96109295 1.19517618,3.01336411 C2.0603048,2.06563527 3.28392694,1.52515299 4.56857512,1.52394388 L9.12500689,1.52401768 C11.4195533,1.50143446 13.3752945,3.18337192 13.6983494,5.46945628 C13.8762716,6.81847711 14.2062339,8.14304017 14.6806548,9.41444158 C15.3086205,11.0850131 14.906975,12.9682845 13.6460712,14.2433447 L12.5533587,15.3360572 C14.3451392,18.1469505 16.7303384,20.5321487 19.5412327,22.3239279 L20.6399822,21.2251975 C21.9089964,19.9703059 23.7922678,19.5686604 25.4594015,20.1953385 C26.7342407,20.671047 28.0588038,21.0010093 29.4214396,21.1807897 C31.7130092,21.5040741 33.4003492,23.4884211 33.353337,25.8000228 L33.3533245,30.348235 Z M30.305718,25.7829915 L30.3061868,25.7451933 C30.3253792,24.971702 29.7618536,24.3066107 29.0093256,24.2003848 C27.4324296,23.9924083 25.8841228,23.6067091 24.3905014,23.0493569 C23.8336442,22.840035 23.2058871,22.9739169 22.7889283,23.3862018 L20.8536902,25.3214399 C20.3686418,25.8064883 19.6192865,25.9076504 19.0229835,25.5685831 C14.9696236,23.263779 11.6135019,19.9076573 9.30869777,15.8542974 C8.96963052,15.2579944 9.07079259,14.5086391 9.55584098,14.0235907 L11.4850668,12.0943986 C11.903364,11.6713938 12.0372459,11.0436367 11.8266365,10.4833417 C11.2705718,8.99315808 10.8848726,7.44485126 10.67881,5.88196532 C10.571763,5.12460721 9.91984929,4.56396139 9.14000369,4.57156292 L4.57001013,4.57156225 C4.14227241,4.57196506 3.73439836,4.75212582 3.44602215,5.06803543 C3.15764594,5.38394505 3.01532305,5.8065126 3.05148367,6.20874453 C3.52638911,10.6808281 5.04805049,14.9781584 7.50040565,18.763835 C9.71998807,22.2568112 12.6814221,25.2182452 16.1862531,27.4454378 C19.9436189,29.8820759 24.2201518,31.4033918 28.6447781,31.8844147 C29.0721031,31.9230342 29.4959079,31.7797072 29.8120756,31.4896451 C30.1282433,31.199583 30.3074721,30.7896681 30.305718,30.3544201 L30.305718,25.7829915 Z" fill-rule="nonzero"></path>
			<path d="M20.9472512,9.1147916 C23.3828955,9.58999667 25.2872842,11.4943854 25.7624893,13.9300297 C25.923646,14.7560321 26.7238966,15.294996 27.549899,15.1338392 C28.3759014,14.9726825 28.9148653,14.1724319 28.7537086,13.3464295 C28.040901,9.69296301 25.1843179,6.83637994 21.5308514,6.12357234 C20.704849,5.96241559 19.9045984,6.50137948 19.7434417,7.32738187 C19.5822849,8.15338426 20.1212488,8.95363485 20.9472512,9.1147916 Z" fill-rule="nonzero"></path>
			<path d="M21.0708033,3.03843656 C26.7319904,3.66734897 31.2030199,8.13275451 31.8390573,13.7931456 C31.9330308,14.6294591 32.6871775,15.2312447 33.5234911,15.1372712 C34.3598047,15.0432977 34.9615902,14.289151 34.8676167,13.4528374 C34.07257,6.37734864 28.4837832,0.795591708 21.4072993,0.0094511945 C20.5708681,-0.0834695941 19.8174794,0.519264694 19.7245586,1.35569591 C19.6316378,2.19212712 20.2343721,2.94551577 21.0708033,3.03843656 Z" fill-rule="nonzero"></path>',
			'viewbox' => '0 0 35 35',
		];

		$icons['active'] = [
			'title' => 'Active',
			'icon' => '<path d="M3,0 L25,0 C26.6568542,-3.04359188e-16 28,1.34314575 28,3 L28,25 C28,26.6568542 26.6568542,28 25,28 L3,28 C1.34314575,28 2.02906125e-16,26.6568542 0,25 L0,3 C-2.02906125e-16,1.34314575 1.34314575,3.04359188e-16 3,0 Z M3,1 C1.8954305,1 1,1.8954305 1,3 L1,25 C1,26.1045695 1.8954305,27 3,27 L25,27 C26.1045695,27 27,26.1045695 27,25 L27,3 C27,1.8954305 26.1045695,1 25,1 L3,1 Z"  fill-rule="nonzero"></path><polygon points="12.3846154 20 7 14.5 9.15384615 12.3 12.3846154 15.6 18.8461538 9 21 11.2"></polygon>',
			'viewbox' => '0 0 28 28',
		];

		$icons['inactive'] = [
			'title' => 'Inactive',
			'icon' => '<path d="M3,0 L25,0 C26.6568542,-3.04359188e-16 28,1.34314575 28,3 L28,25 C28,26.6568542 26.6568542,28 25,28 L3,28 C1.34314575,28 2.02906125e-16,26.6568542 0,25 L0,3 C-2.02906125e-16,1.34314575 1.34314575,3.04359188e-16 3,0 Z M3,1 C1.8954305,1 1,1.8954305 1,3 L1,25 C1,26.1045695 1.8954305,27 3,27 L25,27 C26.1045695,27 27,26.1045695 27,25 L27,3 C27,1.8954305 26.1045695,1 25,1 L3,1 Z" fill-rule="nonzero"></path><polygon points="16 14 19 17 17 19 14 16 11 19 9 17 12 14 9 11 11 9 14 12 17 9 19 11"></polygon>',
			'viewbox' => '0 0 28 28',
		];

		$icons['logo'] = [
			'title' => 'Rey Logo',
			'icon' => '<path d="M78,0.857908847 L68.673913,0.857908847 L63.5869565,15.1206434 L58.5,0.857908847 L49.173913,0.857908847 L59.4008152,24.9865952 L52.7086216,40 L62.0226252,40 L78,0.857908847 Z M8.47826087,5.63002681 L8.47826087,0.857908847 L0,0.857908847 L0,26.5951743 L8.47826087,26.5951743 L8.47826087,17.1045576 C8.47826087,12.922252 10.7038043,10.1340483 13.1413043,9.43699732 C14.6779891,9.0080429 16.2146739,8.95442359 17.8043478,9.43699732 L17.8043478,0 C13.0353261,0.321715818 10.2269022,1.93029491 8.47826087,5.63002681 Z M35.7146739,19.9463807 C34.7078804,19.9463807 33.701087,19.7855228 33.0652174,19.4101877 L48.1141304,10.2949062 C46.1535326,1.769437 39.6888587,0 36.0326087,0 C27.1834239,0 21.8315217,6.11260054 21.8315217,13.7265416 C21.8315217,21.3404826 27.1834239,27.4530831 36.0326087,27.4530831 C40.1127717,27.4530831 43.6100543,25.9517426 46.4184783,23.2171582 L42.0733696,17.4798928 C40.5366848,18.9276139 38.2581522,19.9463807 35.7146739,19.9463807 Z M36.0326087,7.50670241 C37.4103261,7.50670241 38.3641304,8.20375335 38.7880435,8.90080429 L29.9918478,14.2091153 C29.4619565,10.1876676 32.4293478,7.50670241 36.0326087,7.50670241 Z" fill="currentColor" fill-rule="nonzero"></path>',
			'viewbox' => '0 0 78 40',
		];

		$icons['notice'] = [
			'title' => 'Notice',
			'icon' => '<path fill="currentColor" d="M800 1024h-576c-123.712 0-224-100.288-224-224v0-576c0-123.712 100.288-224 224-224v0h320v64h-320c-88.366 0-160 71.634-160 160v0 576c0 88.366 71.634 160 160 160v0h576c88.366 0 160-71.634 160-160v0-320h64v320c0 123.712-100.288 224-224 224v0z"></path><path fill="currentColor" d="M800 448c-123.712 0-224-100.288-224-224s100.288-224 224-224c123.712 0 224 100.288 224 224v0c0 123.712-100.288 224-224 224v0zM800 64c-88.366 0-160 71.634-160 160s71.634 160 160 160c88.366 0 160-71.634 160-160v0c0-88.366-71.634-160-160-160v0z"></path>',
			'viewbox' => '0 0 1024 1024',
		];

		return apply_filters('rey/svg_icons_raw', $icons);

	}
}

new ReyTheme_SvgIcons();

// Wrapper Functions

if(!function_exists('rey__get_svg_icon')):
	/**
	 * Wrapper for SvgIcons::get_icon().
	 *
	 * @since 2.4.0
	 */
	function rey__get_svg_icon( $args = [] ) {
		return ReyTheme_SvgIcons::get_icon($args);
	}
endif;


if(!function_exists('rey__echo_svg_icon')):
	/**
	 * Echo get icon
	 *
	 * @since 1.0.0
	 */
	function rey__echo_svg_icon( $args = [] )
	{
		echo ReyTheme_SvgIcons::get_icon( $args );
	}
endif;


if ( ! function_exists( 'rey__arrowSvg' ) ) :
	/**
	 * Arrow SVG.
	 * @since 1.0.0
	 */
	function rey__arrowSvg($args = []){
		return ReyTheme_SvgIcons::get_arrow($args);
	}
endif;


if(!function_exists('rey__kses_post_with_svg')):
	/**
	 * Add SVG support to wp_kses
	 * @since 1.0.0
	 */
	function rey__kses_post_with_svg( $html = '' )
	{
		if( $html === '' ){
			return;
		}

		$kses_defaults = wp_kses_allowed_html( 'post' );

		$svg_args = [
			'svg'   => [
				'class' => true,
				'aria-hidden' => true,
				'aria-labelledby' => true,
				'role' => true,
				'xmlns' => true,
				'width' => true,
				'height' => true,
				'viewbox' => true, // <= Must be lower case!
			],
			'g' => [
				'fill'   => true
			],
			'use' => [
				'href'   => true,
				'xlink:href' => true
			],
			'title'   => [
				'title'  => true
			],
			'path'    => [
				'd'         => true,
				'fill'      => true,
				'fill-rule' => true,
				'class'     => true,
				'style'     => true,
			],
			'polygon' => [
				'points' => true,
				'fill'      => true,
				'fill-rule' => true,
				'class'     => true,
				'style'     => true,
			],
		];

		$allowed_tags = array_merge( $kses_defaults, $svg_args );

		return wp_kses( $html, $allowed_tags );
	}
endif;
