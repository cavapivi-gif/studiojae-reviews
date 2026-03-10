<?php
namespace ReyCore\Compatibility\WpStoreLocator;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	const ASSET_HANDLE = 'reycore-wpsl-styles';

	public function __construct()
	{
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp', [ $this, 'determine_dealer_button_location' ] );
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['wpsl-styles'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function determine_dealer_button_location(){
		$priority = 25;

		if(get_theme_mod('single_skin', 'default') === 'compact' ){
			$priority = 26;
		}

		add_action( 'woocommerce_single_product_summary', [ $this, 'add_dealer_button' ], $priority );
	}

	public function add_dealer_button(){

		if( class_exists('\ACF') && get_theme_mod('wpsl_enable_button', get_field('wpsl_enable_button', REY_CORE_THEME_NAME)) ):

			$link_attributes = '';

			$btn_text = get_theme_mod('wpsl_button_text', (
				($inherited_text = get_field('wpsl_button_text', REY_CORE_THEME_NAME)) ? $inherited_text : esc_html__('FIND A DEALER', 'rey-core')
			));

			$btn_url = '';
			if( class_exists('\ACF') && ($acf_btn_url = get_field('wpsl_button_url', REY_CORE_THEME_NAME)) && isset($acf_btn_url['url']) ){
				$btn_url = $acf_btn_url['url'];
			}

			$url = get_theme_mod('wpsl_button_url', $btn_url);

			if( $url ){
				$link_attributes .= "href='{$url}'";
				$link_attributes .= "title='{$btn_text}'";
			}

			reycore_assets()->add_styles('rey-buttons');

			ob_start();
			?>

			<div class="rey-wpStoreLocator">
				<a <?php echo $link_attributes; ?> class="rey-wpsl-btn btn btn-primary">
					<i class="fa fa-map-marker" aria-hidden="true"></i>
					<span><?php echo $btn_text ?></span>
				</a>
			</div>

			<?php
			return apply_filters('reycore/wp_store_locator/button', ob_get_contents());
		endif;
	}

}
