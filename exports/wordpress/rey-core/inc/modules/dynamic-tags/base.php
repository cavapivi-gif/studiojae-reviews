<?php
namespace ReyCore\Modules\DynamicTags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $manager;

	private static $settings = [];

	const GROUPS_POST    = 'rey-posts';
	const GROUPS_ARCHIVE = 'rey-archive';
	const GROUPS_SITE    = 'rey-site';
	const GROUPS_ACF     = 'rey-acf';
	const GROUPS_WOO     = 'rey-woo';

	const ACF_CONTROL = 'field';
	const WOO_PRODUCT_CONTROL = 'product_preview_id';

	public function __construct()
	{
		add_action( 'reycore/elementor', [$this, 'init']);
	}

	public function init() {
		add_action( 'elementor/dynamic_tags/register', [$this, 'register']);
	}

	public function register($manager)
	{
		$this->manager = $manager;

		$defaults = [
			self::GROUPS_POST => [
				'title' => esc_html__( 'Post', 'rey-core' ),
				'tags' => [
					'Post\Title',
					'Post\Content',
					'Post\Excerpt',
					'Post\Author',
					'Post\AuthorLink',
					'Post\AuthorImage',
					'Post\Image',
					'Post\ImageData',
					'Post\Url',
					'Post\Terms',
					'Post\Date',
					'Post\HumanDate',
					'Post\Duration',
					'Post\Meta',
					'Post\ImageMeta',
					'Post\CommentsCount',
				],
			],
			self::GROUPS_ARCHIVE => [
				'title' => esc_html__( 'Archive', 'rey-core' ),
				'tags' => [
					'Archive\Title',
					'Archive\Desc',
					'Archive\URL',
					'Archive\Meta',
					'Archive\ProductCategoryImage',
				],
			],
			self::GROUPS_SITE => [
				'title' => esc_html__( 'Site', 'rey-core' ),
				'tags' => [
					'Site\Title',
					'Site\Url',
					'Site\Email',
					'Site\Logo',
					'Site\User',
					'Site\UserImage',
					'Site\Param',
				],
			],
		];

		if( class_exists('\WooCommerce') ){
			$defaults[self::GROUPS_WOO] = [
				'title' => esc_html__( 'WooCommerce', 'rey-core' ),
				'tags' => [
					'Woo\Title',
					'Woo\Url',
					'Woo\Description',
					'Woo\ShortDescription',
					'Woo\Price',
					'Woo\Sku',
					'Woo\AddToCartUrl',
					'Woo\Image',
					'Woo\Gallery',
					'Woo\Stock',
					'Woo\StockQty',
					'Woo\Categories',
					'Woo\Tags',
					'Woo\Attributes',
					'Woo\AttributeTerm',
					'Woo\AttributeImage',
					'Woo\AttributeGallery',
					'Woo\Rating',
					'Woo\Information',
					'Woo\Weight',
					'Woo\Dimensions',
					'Woo\Meta',
				],
			];
		}

		if( class_exists('\ACF') ){
			$defaults[self::GROUPS_ACF] = [
				'title' => esc_html__( 'Advanced Custom Fields', 'rey-core' ),
				'tags' => [
					'Acf\Text',
					'Acf\Link',
					'Acf\Image',
					'Acf\Color',
					'Acf\Gallery',
					'Acf\RepeaterText',
					'Acf\RepeaterLink',
					'Acf\RepeaterImage',
					'Acf\RepeaterGallery',
				],
			];
		}

		foreach ($defaults as $id => $group) {

			$manager->register_group( $id, [
				'title' => $group['title'] . ' (rey)',
			] );

			foreach ($group['tags'] as $tag) {
				$class_name = \ReyCore\Helper::fix_class_name($tag, 'Modules\DynamicTags\Tags');
				if( class_exists($class_name) ){
					$manager->register( new $class_name() );
				}
			}
		}

		self::$settings = [
			'acf_both_markup'   => '<div class="rey-acf-dataRow">%s: %s</div>',
			'acf_single_markup' => '<div class="rey-acf-dataRow">%s</div>',
			'acf_yes_text'      => esc_html__('Yes', 'rey-core'),
			'acf_no_text'       => esc_html__('No', 'rey-core'),
		];

        do_action( 'reycore/dynamic_tags', $this);
	}

	public function get_manager(){
		return $this->manager;
	}

	public static function set_setting($setting, $value){
		if( isset(self::$settings[$setting]) ){
			self::$settings[$setting] = $value;
		}
	}

	public static function get_settings($setting = ''){
		if( $setting && isset(self::$settings[$setting]) ){
			return self::$settings[$setting];
		}
		return self::$settings;
	}

	public static function acf_field_control( $stack, $types = [] ){

		$stack->add_control(
			self::ACF_CONTROL,
			[
				'label'       => esc_html__( 'Select Field', 'rey-core' ),
				'default'     => '',
				'type'        => 'rey-query',
				'label_block' => true,
				'query_args'  => [
					'type'        => 'acf',
					'field_types' => $types,
				],
			]
		);

		$stack->add_control(
			self::ACF_CONTROL . '_custom_key',
			[
				'label'       => esc_html__( 'Or add custom field key', 'rey-core' ),
				'default'     => '',
				'type'        => 'text',
				'label_block' => true,
				// 'condition'   => [
				// 	self::ACF_CONTROL => '',
				// ],
				'ai' => [
					'active' => false
				],
			]
		);
	}

	public static function woo_product_control( $stack ){

		$stack->add_control(
			self::WOO_PRODUCT_CONTROL,
			[
				'label' => esc_html__('Select Product', 'rey-core'),
				'default' => '',
				'label_block' => true,
				'type' => 'rey-query',
				'query_args' => [
					'type'      => 'posts',
					'post_type' => 'product',
				],
			]
		);

	}

	public static function get_product( $tag ){
		$opt = $tag->get_settings(self::WOO_PRODUCT_CONTROL);
		return wc_get_product( (bool) $opt ? $opt : false );
	}

	public static function display_placeholder_data( $text ){
		if( current_user_can('edit_posts') ){
			echo $text;
		}
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Dynamic tags for Elementor', 'Module name', 'rey-core'),
			'description' => esc_html_x('Ability to add dynamic page/post/term/attributes etc. data into Elementor elements and widgets.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Product Page', 'Product catalog', 'WooCommerce'],
			'video'       => true,
			'help'        => reycore__support_url('kb/dynamic-tags-for-elementor/'),
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
