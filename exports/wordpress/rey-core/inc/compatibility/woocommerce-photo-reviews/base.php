<?php
namespace ReyCore\Compatibility\WoocommercePhotoReviews;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-woo-photo-reviews-styles';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		reycore__remove_filters_for_anonymous_class( 'admin_init', 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin', 'check_update', 10 );
		add_filter( 'woocommerce_reviews_title', [$this, 'title_improvement'], 20, 3);
		add_filter( 'reycore/woocommerce/single/reviews_template', '__return_false', 20, 3);
		add_action( 'reycore/customizer/section=woo-advanced-settings', [ $this, 'add_customizer_options' ] );
		add_filter( 'style_loader_tag', [$this, 'style_loader_tag'], 10, 2);
	}

	function add_customizer_options( $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wpr__use_defaults',
			'label'       => esc_html__( 'WooCommerce Photo Reviews - Override Settings', 'rey-core' ),
			'default'     => false,
		] );

	}

	function set_defaults(){

		if( ! get_theme_mod('wpr__use_defaults', false) ){
			return;
		}


		$params['grid_item_bg'] = '';
		$params['grid_item_border_color'] = '';
		$params['comment_text_color'] = '';
		$params['star_color'] = get_theme_mod('star_rating_color', '#ff4545');
		$params['enable_box_shadow'] = false;
		$params['verified'] = 'badge';
		$params['verified_text'] = 'Verified owner';
		$params['verified_badge'] = 'woocommerce-photo-reviews-badge-tick-4';
		$params['verified_color'] = '#000';

		foreach( $params as $key => $param ){
			add_filter('_wcpr_nkt_setting_photo__' . $key, function() use ($param){
				return $param;
			});
		}

	}

	function title_improvement($reviews_title, $count, $product){

		// reset
		$reviews_title = '';
		// rating
		$rating_average = $product->get_average_rating();
		$reviews_title .= sprintf('<div class="rey-reviewTop">%s <span><strong>%s</strong>/5</span></div>', wc_get_rating_html( $rating_average, $count ), $rating_average);
		// title
		$reviews_title .= sprintf( '<div class="rey-reviewTitle">' . esc_html( _n( '%s Customer review', '%s Customer reviews', $count, 'rey-core' ) ) . '</div>' , esc_html( $count ) );

		return $reviews_title;
	}

	public function init(){
		$this->settings = apply_filters('reycore/woo_photo_reviews/params', []);

		$this->set_defaults();
	}

	public function enqueue_scripts(){
		if( is_product() ){
			reycore_assets()->add_styles(self::ASSET_HANDLE);
		}
	}

	public function style_loader_tag($tag, $handle){

		$pdp_only = [
			'wcpr-country-flags',
			'woocommerce-photo-reviews-form',
			'wcpr-verified-badge-icon',
			'wcpr-shortcode-all-reviews-style',
			'woocommerce-photo-reviews-vote-icons',
			'wcpr-swipebox-css',
			'wcpr-shortcode-masonry-style',
			'wcpr-rotate-font-style',
			'wcpr-default-display-style',
			'woocommerce-photo-reviews-rating-html-shortcode',
		];

		if( in_array($handle, $pdp_only, true) && is_product() ){
			return '';
		}

		return $tag;
	}


	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}
}
