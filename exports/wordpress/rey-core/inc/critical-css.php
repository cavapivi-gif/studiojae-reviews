<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

final class CriticalCSS
{

	/**
	 * Holds CSS styles
	 *
	 * @var array
	 */
	protected $_styles = [];

	/**
	 * Holds CSS display none selectors
	 *
	 * @var array
	 */
	protected $_dn_selectors = [];

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	function init(){
		add_action( 'wp_head', [$this, 'print_head_styles'], 5);
		add_action( 'wp_footer', [$this, 'remove_critical_css']);
	}

	function can_add(){

		if( ! get_theme_mod('perf__critical_css_v2', false) ){
			return;
		}

		if( 'inline' === REY_CORE_ASSETS_CSS_HEAD ){
			return;
		}

		$is_wc = class_exists('\WooCommerce') && (is_cart() || is_checkout());

		if( apply_filters('reycore/critical_css/disable', false) && ! $is_wc ){
			return;
		}

		return true;
	}

	public function get_css(){

		$this->default_css();

		do_action('reycore/critical_css/before_render', $this);

		$this->process_dn_selectors();

		$inlined_css = str_replace(
			[': ', ';  ', '; ', '  ', '{ ', ' {'],
			[':', ';', ';', ' ', '{', '{'],
			preg_replace( "/[\t\n\r]+/", '', implode('', apply_filters('reycore/critical_css/css', $this->_styles) ) )
		);

		return $inlined_css;
	}

	function print_head_styles(){

		if( ! $this->can_add() ){
			return;
		}

		printf('<style type="text/css" id="reycore-critical-css" %2$s>%1$s</style>', $this->get_css(), reycore__css_no_opt_attr() ) . "\n";
	}

	public function add_css($css = []){
		$this->_styles = array_merge($this->_styles, $css);
	}

	public function add_dn_selectors($css = []){
		$this->_dn_selectors = array_merge($this->_dn_selectors, $css);
	}

	public function process_dn_selectors(){

		$selectors = [
			'[data-lazy-hidden]',
			'.rey-mainNavigation.rey-mainNavigation--mobile',
			'.rey-mainNavigation-mobileBtn',
			'.rey-accountPanel-wrapper[data-layout="drop"]',
			'.rey-mega-gs,.depth--0>.sub-menu',
			'.rey-stickyContent',
			'.rey-header-dropPanel .rey-header-dropPanel-content',
			':not(.skip-ccss) .elementor-icon svg',
			'.single-product .rey-breadcrumbs-del:nth-last-of-type(1) + .rey-breadcrumbs-item',
		];

		$this->_styles[] = sprintf(':is(%s){display:none}', implode(',', array_merge($selectors, $this->_dn_selectors)));
	}

	public function default_css(){

		$this->_styles[] = 'html{font-size: var(--body-font-size, 16px);}';
		$this->_styles[] = 'body{overflow-y: scroll}';
		$this->_styles[] = '.--invisible{visibility: hidden;}';

		$this->_styles[] = '[data-container] .elementor-section.elementor-section-boxed > .elementor-container{max-width:var(--container-max-width,1440px)}';

		$this->_styles[] = '.btn, button, button[type=button], button[type=submit], input[type=button], input[type=reset], input[type=submit] { display: inline-flex; align-items: center; justify-content: center; text-align: center; vertical-align: middle; background-color: transparent; border: 1px solid transparent; padding: 0; font-size: 1rem; line-height: 1.5; }';

		if( 'default' === get_theme_mod('header_layout_type', 'default') ):
			$this->_styles[] = '.rey-siteHeader-container {
				padding-right: var(--half-gutter-size);
				padding-left: var(--half-gutter-size);
				width: 100%;
				max-width: var(--container-max-width);
				margin-right: auto;
				margin-left: auto
			}';
			$this->_styles[] = '@media (min-width: 1025px) {
				.rey-siteHeader-container {
					--justify: space-between;
					--v-spacing: 20px;
					max-width: var(--header-default--max-width, var(--container-max-width));
				}
			}';
			$this->_styles[] = '.rey-siteHeader-row {
				display: flex;
				padding-top: var(--v-spacing, 15px);
				padding-bottom: var(--v-spacing, 15px);
				align-items: center;
				justify-content: var(--justify, initial);
			}';
		endif;

		// desktop menu
		$this->_styles[] = '.rey-mainMenu {
			list-style: none;
			margin: 0;
			padding: 0;
		}';

		$this->_styles[] = '.rey-mainMenu > .menu-item > a {
			display: inline-block;
			font-size: 0.875rem;
			font-weight: 500;
		}';

		$this->_styles[] = '.rey-mainNavigation.rey-mainNavigation--desktop {
			display: var(--nav-breakpoint-desktop,flex);
		}';

		$this->_styles[] = '.rey-mainMenu--desktop {
			display: inline-flex;
			gap: calc(var(--header-nav-x-spacing, 1rem) * 2);
		}';

		$this->_styles[] = '@media (min-width: 1025px){
			.rey-mainMenu.--has-indicators .menu-item-has-children > a {
				padding-right: var(--indicator-distance, Max( var(--indicator-padding, 12px), 16px ) ) !important;
			}
		}';

		$this->_styles[] = '.rey-mainNavigation-mobileBtn {	position: relative; }';

		// Search

		$this->_styles[] = '.rey-headerSearch--inline form {
			display: flex;
		}';

		$this->_styles[] = '@media (min-width: 1025px){
			.rey-headerSearch--inline :is(.rey-headerSearch-toggle, .rey-inlineSearch-mobileClose) {
				display: none;
			}
		}';

		$this->_styles[] = '.rey-headerSearch--inline input[type="search"] {
			border: 0;
			height: 100%;
			font-size: 16px;
			position: relative;
			background: none;
		}';

		$this->_styles[] = '.reyajfilter-updater {
			position: absolute;
			opacity: 0;
			visibility: hidden;
			left: -350vw;
		}';

		// Sticky Social Icons
		$this->_styles[] = '.rey-stickySocial.--position-left{left:-150vw}.rey-stickySocial.--position-right{right:150vw;}';

		// Header panels
		$this->_styles[] = '.rey-compareNotice-wrapper,.rey-scrollTop,.rey-wishlist-notice-wrapper{left:-150vw;opacity:0;visibility:hidden;pointer-events:none;}';

		// dashed button
		$this->_styles[] = '.elementor-button-dashed.--large{--btn-dashed-p:50px;--btn-line-w: 35px}';

		// Cookie notice
		$this->_styles[] = '.rey-cookieNotice.--visible{left: var(--cookie-distance);opacity: 1;transform: translateY(0);}';

		// Helper classes
		$this->_styles[] = '.--hidden{display:none!important;}@media(max-width:767px){.--dnone-sm,.--dnone-sm{display:none!important;}}@media(min-width:768px) and (max-width:1025px){.--dnone-md,.--dnone-md{display:none!important;}} @media(min-width:1025px){.--dnone-lg,.--dnone-lg{display:none!important;}}';

		// Nest cover
		$this->_styles[] = '.rey-coverNest .cNest-slide{opacity: 0;}';

		// Section slideshow
		$this->_styles[] = '.elementor-element > .rey-section-slideshow { position: absolute; width: 100%; height: 100%; top: 0; left: 0; background-size: cover; background-position: center center; }';

		$this->_styles[] = '.is-animated-entry {opacity: 0;}';

		$this->_styles[] = '@media (min-width: 768px) {
			.el-reycore-cover-sideslide .rey-siteHeader > :is(.elementor, .rey-siteHeader-container) {
				opacity: 0;
			}
		}';

		$this->_styles[] = '.woocommerce div.product .woocommerce-product-gallery { opacity: 0; }';

		$this->_styles[] = '@media (min-width: 1025px) {
			.woocommerce div.product .woocommerce-product-gallery.--is-loading .woocommerce-product-gallery__wrapper {
				opacity:0;
			}
		}';

		$this->_styles[] = '.rey-coverSplit.--mainSlide .cSplit-slide--mainBg{opacity:0}';

		$this->_styles[] = '.rey-breadcrumbs { font-size: var(--breadcrumbs-fz, 0.875rem); margin: var(--breadcrumbs-m, 1.25rem 0); }';
		$this->_styles[] = '.single-product .rey-breadcrumbs { --breadcrumbs-fz: 0.75rem; --breadcrumbs-m: 0 0 2rem; }';
		$this->_styles[] = '.rey-breadcrumbs-item, .rey-breadcrumbs-del { display: inline-block; margin: 0 5px; }';


	}

	function remove_critical_css(){

		if( ! $this->can_add() ){
			return;
		} ?>
		<script type="text/javascript" id="reycore-critical-css-js" <?php echo reycore__js_no_opt_attr(); ?>>
			document.addEventListener("DOMContentLoaded", function() {
				var CCSS = document.getElementById('reycore-critical-css');
				CCSS && CCSS.remove();
			});
		</script>
		<?php
	}

}
