<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AdminBanners {

	const STOP_SHOWING_SURVEY_OPT = 'reycore_survey_stopshowing';

	public function __construct(){
		add_action('init', [$this, 'init']);
	}

	public function init(){
		add_action( 'admin_init', [$this, 'admin_init']);
		add_action( 'acf/init', [$this, 'add_acf_enable_banners'] );
	}

	function admin_init(){
		// add_action( 'admin_notices', [$this, 'show_survey'] );
		// add_action( 'network_admin_notices', [$this, 'show_survey'] );
		add_action( "wp_ajax_reycore_survey_stop_showing", [ $this, 'survey_stop_showing_set_opt' ] );
	}

	function survey_stop_showing_set_opt(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
		}

		if( update_option( self::STOP_SHOWING_SURVEY_OPT, true, false) ){
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	function show_survey(){

		if( ! current_user_can( 'switch_themes' ) ){
			return;
		}

		if( ! $this->can_show_banners() ){
			return;
		}

		if( ! $this->can_show_banners_on_pages() ){
			return;
		}

		if( get_option( self::STOP_SHOWING_SURVEY_OPT, false) ){
			return;
		} ?>

		<div class="reyAdm-notice reyAdm-survey notice notice-info" data-key="survey">
			<button type="button" class="reyAdm-noticeDismiss notice-dismiss" data-dismiss="1day"><span class="screen-reader-text">Dismiss this notice.</span></button>
			<div class="__inner">
				<div class="__logo">
					<?php echo reycore__get_svg_icon(['id'=>'logo']) ?>
				</div>
				<div class="__content">
					<h3>Rey needs your opinion to make the theme better for YOU.</h3>
					<!-- <p>
						We'd really appreciate if you could take 5 minutes to answer a few questions that would give us more insights on things that work and don't work in Rey. Thanks a lot!!
					</p> -->
					<p>
						<a href="https://docs.google.com/forms/d/e/1FAIpQLScWBi0xjrZokvKuM4tbQcFvUe4jaxUbWbeumKHNb38GT8Fbpg/viewform?hl=en" class="js-survey-take button-primary" target="_blank">Take Survey</a>
						&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
						<a href="#" class="js-survey-later">Maybe later</a>
						&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
						<a href="#" class="js-survey-stop" data-dismiss="always" data-action="reycore_survey_stop_showing">Already took it, stop showing!</a>
					</p>
				</div>
			</div>
		</div>

		<?php
	}

	function can_show_banners_on_pages()
	{
		$current_screen = get_current_screen();

		if( isset($current_screen->action) && $current_screen->action === 'add' ){
			return;
		}

		if( isset($current_screen->parent_base) && $current_screen->parent_base === 'edit' &&
			isset($current_screen->base) && in_array($current_screen->base, ['post', 'term'], true) ){
			return;
		}

		return true;

	}

	function can_show_banners()
	{
		if( ! class_exists('\ACF') ){
			return false;
		}

		$field = reycore__acf_get_field('rey_banners_enable', REY_CORE_THEME_NAME);

		if( is_null($field) || (bool) $field === true ){
			return true;
		}

		return false;
	}

	/**
	 * Adds option in theme settings
	 */
	function add_acf_enable_banners(){

		if( ! is_admin() ){
			return;
		}

		acf_add_local_field([
			'key'          => 'field_rey_banners_enable',
			'name'         => 'rey_banners_enable',
			'label'        => esc_html__('Enable Periodic banners', 'rey-core'),
			'type'         => 'true_false',
			'instructions' => sprintf(esc_html_x('Disable this option to prevent %s from ever showing any banner or survey message.', 'Backend setting description', 'rey-core'), reycore__get_props('theme_title')),
			'default_value' => 1,
			'ui' => 1,
			'parent'       => 'group_5c990a758cfda',
			'menu_order'   => 350,
		]);
	}

}
