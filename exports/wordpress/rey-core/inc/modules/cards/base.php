<?php
namespace ReyCore\Modules\Cards;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-cards';

	public static $defaults = [];

	public $cards = [];

	public $sources = [];

	const CARD_KEY = 'card';

	public function __construct()
	{

		parent::__construct();

		add_action('init', [$this, 'init']);

	}

	public function register_default_cards(){

		foreach ([
			'basic',
			'brooklyn',
			'manhattan',
			'soho',
			'harlem',
		] as $cn) {

			if( ! $this->is_enabled() && 'basic' !== $cn ){
				continue;
			}

			$file = __DIR__ . '/' . $cn . '/card.php';

			if( ! is_file($file) ){
				continue;
			}

			include_once $file;

			$class_name = ucwords( str_replace( '-', ' ', $cn ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Modules\\Cards');

			if( ! class_exists($class_name) ){
				return;
			}

			$this->register_card( new $class_name );
		}
	}

	public function register_card( $class ){

		if( ! ($card_id = $class->get_id()) ){
			return;
		}

		$this->cards[ $card_id ] = $class;
	}

	public function register_default_sources(){

		$types = [
			'images',
			'custom',
			'posts',
			'category'
		];

		if( class_exists('\WooCommerce') ){
			$types[] = 'product-cat';
			$types[] = 'reviews';
			$types[] = 'attributes';
		}

		foreach ($types as $source) {

			$class_name = ucwords( str_replace( '-', ' ', $source ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Modules\\Cards\\Sources');

			if( ! class_exists($class_name) ){
				return;
			}

			$this->register_source( new $class_name($this) );
		}
	}

	public function register_source( $class ){

		if( ! ($source_id = $class->get_id()) ){
			return;
		}

		$this->sources[ $source_id ] = $class;
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		self::$defaults = apply_filters('reycore/cards/settings', [
			'title_tag' => 'h2',
			'label_tag' => 'h3',
			'desc_tag' => 'div',
		]);

		$this->register_default_cards();
		$this->register_default_sources();

		do_action('reycore/cards/init', $this);

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/critical_css/before_render', [$this, 'add_critical_css']);
		add_action( 'elementor/widgets/register', [$this, 'load_card_base'], 9 );
		add_action('reycore/elementor/card/before_item', [$this, 'teaser_before']);
		add_action('reycore/elementor/card/after_item', [$this, 'teaser_after']);
	}

	public function register_assets( $manager ){

		foreach ($this->cards as $id => $card) {

			foreach ([
				'get_css' => 'styles',
				'get_js'  => 'scripts',
			] as $func => $handle) {

				if( $manager && ($assets = $card->$func()) && is_array($assets) ){
					$manager->register_asset($handle, $assets);
				}
			}
		}

		$manager->register_asset('styles', [
			self::ASSET_HANDLE . '-load-more' => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/assets/load-more.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$manager->register_asset('scripts', [
			self::ASSET_HANDLE . '-load-more' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/assets/load-more.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			]
		]);

	}

	public function get_cards_list($cards = []){

		if( empty($this->cards) ){
			return $cards;
		}

		foreach ($this->cards as $id => $card) {
			$cards[ $id ] = $card->get_name();
		}

		return apply_filters('reycore/cards/list', $cards, $this);
	}

	public function load_card_base(){
		require_once __DIR__ . '/card-element.php';
	}

	public function get_card_supports( $type ){

		$supports = [];

		foreach ($this->cards as $card) {

			if( ! in_array( $type, (array) $card->get_supports(), true ) ){
				continue;
			}

			$supports[] = $card->get_id();
		}

		return $supports;
	}

	public function add_cards_controls( $element ){
		foreach ($this->cards as $card) {
			$card->get_card_controls( $element );
		}
	}

	public function render_card( $element ){

		if( ! isset($element->_settings[self::CARD_KEY]) ){
			return;
		}

		$card_id = $element->_settings[self::CARD_KEY];

		if( ! isset( $this->cards[ $card_id ] ) ){
			do_action('reycore/cards/not_existing', $card_id, $element, $this);
			return;
		}

		$this->cards[ $card_id ]->render( $element );
	}

	public function get_sources(){
		return $this->sources;
	}

	public function teaser_before( $element ){

		if( ! (isset($element->_settings['teaser_gs']) && ($teaser_gs_id = $element->_settings['teaser_gs'])) ){
			return;
		}

		if( ! ($position = $element->_settings['teaser_position']) ){
			return;
		}

		$render = false;
		$pos_no = 0;
		$key = $element->item_key;

		if( 'first' === $position && 0 === $key ){
			$render = true;
		}
		else if( 'last' === $position ){
			$pos_no = false;
		}
		else if(
			'custom' === $position
			&& isset($element->_settings['teaser_pos_custom'])
			&& ($pos_no = $element->_settings['teaser_pos_custom']) // 0 will not show (must choose first)
			&& $pos_no === $key
		){
			$render = true;
		}

		if( false !== $pos_no ){
			if(isset($element->_settings['teaser_pos_repeat'])){
				if(($repeat = $element->_settings['teaser_pos_repeat'])){
					if($repeat > 1){
						if( $key > $pos_no ){
							if( ($key - $pos_no) % $repeat === 0 ){
								$render = true;
							}
						}
					}
				}
			}
		}

		if( $render ){
			$this->teaser_render($teaser_gs_id, $element);
		}

	}

	public function teaser_after( $element ){

		if( ! (isset($element->_settings['teaser_gs']) && ($teaser_gs_id = $element->_settings['teaser_gs'])) ){
			return;
		}

		if( ! ($position = $element->_settings['teaser_position']) ){
			return;
		}

		if( 'last' === $position && (count($element->_items) - 1) === $element->item_key ){
			$this->teaser_render($teaser_gs_id, $element);
		}

	}

	public function teaser_render($teaser_gs_id, $element){
		reycore_assets()->defer_page_styles('elementor-post-' . $teaser_gs_id);
		printf('<div class="%s">%s</div>',
			method_exists($element, 'default_item_classes') ? esc_attr(implode(' ', $element::default_item_classes())) : '',
			\ReyCore\Elementor\GlobalSections::do_section($teaser_gs_id, false, true)
		);
	}

	public function add_critical_css( $ccss ){

		foreach ($this->cards as $id => $card) {
			if( $cc = (array) $card->get_critical_css() ){
				$ccss->add_css($cc);
			}
		}

	}

	public function is_enabled(){
		return ! (defined('REYCORE_CARDS_DISABLE') && REYCORE_CARDS_DISABLE);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Cards Skins', 'Module name', 'rey-core'),
			'description' => esc_html_x('Contains several "card" layouts which are used inside Carousel & Grid element', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => [],
			'video' => true,
		];
	}

	public function module_in_use(){

		if( ! $this->is_enabled() ){
			return;
		}

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'element', [
			\ReyCore\Elementor\Widgets::PREFIX . 'grid',
			\ReyCore\Elementor\Widgets::PREFIX . 'carousel',
		] );

		return ! empty($results);

	}

	/**
	 * @deprecated
	 */
	public function register( $class ){
		return $this->register_card($class);
	}

}
