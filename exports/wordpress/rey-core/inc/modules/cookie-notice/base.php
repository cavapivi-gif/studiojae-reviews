<?php
namespace ReyCore\Modules\CookieNotice;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-cookie-notice';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/customizer/panel=general', [$this, 'load_customizer_options']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action('wp_footer', [$this, 'render'], 5);

	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function enqueue_scripts(){
		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles([self::ASSET_HANDLE, 'rey-buttons']);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style'. $assets::rtl() .'.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high',
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	function render()
	{

		if( ! reycore__can_add_public_content() ){
			return;
		} ?>

		<aside class="rey-cookieNotice">
			<div class="rey-cookieNotice-text">
				<?php echo do_shortcode( get_theme_mod('cookie_notice__text', __('In order to provide you a personalized shopping experience, our site uses cookies. By continuing to use this site, you are agreeing to our cookie policy.', 'rey-core')) ); ?>
			</div>
			<a class="btn btn-primary-outline" href="#"><?php echo get_theme_mod('cookie_notice__btn_text', __('ACCEPT', 'rey-core')); ?></a>
		</aside>

		<?php
		$this->enqueue_scripts();

	}

	public function option() {
		return get_theme_mod('cookie_notice__enable', '');
	}

	public function is_enabled() {
		return $this->option() !== '';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Cookie Notice', 'Module name', 'rey-core'),
			'description' => esc_html_x('Displays a tiny popup with a text notice that the store is using cookies.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['frontend'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/cookie-notice/'),
			'video'       => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
