<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WhatsNew {

	/**
	 * Option name for the DB option that
	 * highlights the What's new menu item
	 */
	const READ = 'reycore_whatsnew_read';

	/**
	 * Name of the option that will hide
	 * this page until a new major update.
	 */
	const HIDE_UNTIL_MAJOR = 'reycore_whatsnew_hide_minor';

	/**
	 * ACF option name that adds the ability to permanently hide
	 * the Whats new page from ever showing up.
	 */
	const WHATS_NEW_OPTION = 'rey_show_whatsnew';

	/**
	 * Page is enabled
	 *
	 * @var boolean
	 */
	public $is_enabled = true;

	public function __construct(){

		return;

		add_action( 'init', [$this, 'init'] );
		add_action( 'reycore/updates/major_version', [ $this, 'on_major_update' ] );
		add_action( 'admin_menu', [$this, 'register_admin_menu'], 200 );
		add_action( 'admin_head', [ $this, 'load_google_font' ] );
		add_filter( 'rey/admin/submenu/classes', [ $this, 'submenu_classes' ], 10, 2 );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public function init(){

		$this->add_acf_show_button();

		$this->is_enabled = $this->is_enabled();

	}

	/**
	 * Performs after Rey Core plugin update.
	 * Renews the plugin version DB option
	 * Checks if What's new page needs to be highlighted
	 *
	 * @param array $options
	 * @return void
	 */
	public function on_major_update(){
		// mark "What's new" page back as unread
		delete_option(self::READ);
		delete_option(self::HIDE_UNTIL_MAJOR);
	}

	/**
	 * Retrieve page slug
	 *
	 * @return string
	 */
	public function get_menu_slug(){
		return sprintf('%s-whatsnew', REY_CORE_THEME_NAME);
	}

	/**
	 * Marks What's new menu item as read, meaning
	 * that the red bubble will stop showing.
	 *
	 * @return bool
	 */
	private function mark_read(){

		if( ! $this->is_unread() ){
			return;
		}

		return update_option(self::READ, true);
	}

	/**
	 * Checks if Whats new page has been seen.
	 *
	 * @return boolean
	 */
	public function is_unread(){
		return ! get_option(self::READ);
	}

	/**
	 * Filter the submenus classes to be able to add an unread bubble
	 * inside the What's new menu item
	 *
	 * @param string $class
	 * @param array $item
	 * @return string
	 */
	public function submenu_classes($class, $item){

		if( isset($item[2]) && $this->get_menu_slug() === $item[2] ){
			// can show unread bubble
			if( $this->is_unread() ){
				$class .= ' --unread';
			}
		}

		return $class;
	}

	/**
	 * Load Google Font
	 *
	 * @return void
	 */
	public function load_google_font(){

		if( ! $this->is_enabled ){
			return;
		}

		if( isset($_REQUEST['page']) && $this->get_menu_slug() === reycore__clean($_REQUEST['page']) ){
			echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&amp;display=swap">';
		}
	}

	/**
	 * Adds the admin menu item
	 *
	 * @return void
	 */
	public function register_admin_menu() {

		if( ! $this->is_enabled ){
			return;
		}

		if( ! reycore__get_props('branding') ){
			return;
		}

		if( ! reycore__get_props('whats_new') ){
			return;
		}

		if( $dashboard_id = reycore__get_dashboard_page_id() ){
			$title = esc_html__('What\'s New', 'rey-core');
			add_submenu_page(
				$dashboard_id,
				$title,
				$title,
				'update_plugins',
				$this->get_menu_slug(),
				[ $this, 'render_page' ]
			);
		}
	}

	/**
	 * Features that are shown in the page
	 *
	 * @return array
	 */
	public function get_data(){

		$items[] = [
			'label'    => 'Performance & Modularity',
			'title'    => 'Module Manager Pro',
			'desc'     => '<p>Even more modularized code, each module being displayed better, with images, video description, filters, sorting and even searching.</p><p>All while still keeping the powerful Scan for unused modules feature.</p>',
			'img'      => REY_CORE_URI . 'assets/images/whats-new/modules-manager.jpg',
		];

		$items[] = [
			'reversed' => true,
			'label'    => 'Development',
			'title'    => 'White Label',
			'desc'     => '<p>Customize Rey\'s branding to your needs and have the ability to turn off various Rey sections for your customers, but not for you.</p>
			<p>All this is possible with a simple code which can be added into the site configuration. More about <a href="' . reycore__support_url('kb/white-label/') . '" target="_blank">White Labeling</a>.</p>',
			'img'      => REY_CORE_URI . '/assets/images/whats-new/white-label.jpg',
		];

		$items[] = [
			'label'    => 'Design & Utility',
			'title'    => 'Santiago Demo',
			'desc'     => '<p>Make use of a pastel design with clean and fancy approaches, for various uses.</p>
			<p>You can import the demo in the Import Demo section under Rey Dashboard, or <a href="https://demos.reytheme.com/santiago/" target="_blank">preview the Santiago demo</a>.</p>',
			'img'      => REY_CORE_URI . '/assets/images/whats-new/santiago-demo.jpg',
		];

		$items[] = [
			'reversed' => true,
			'label'    => 'Design & Utility',
			'title'    => 'San Francisco Demo',
			'desc'     => '<p>A super stylish demo for all sorts of purposes with a clean finish and gorgeous details.</p>
			<p>You can import the demo in the Import Demo section under Rey Dashboard, or <a href="https://demos.reytheme.com/san-francisco/" target="_blank">preview the San Francisco demo</a>.</p>',
			'img'      => REY_CORE_URI . '/assets/images/whats-new/sanfran-demo.jpg',
		];

		$items[] = [
			'size'  => 'third',
			'label' => 'Performance',
			'title' => 'Code Refactoring',
			'desc'  => '<p>These last couple of months were a lot about performance improvements. Whether it\'s dropping jQuery, WooCommerce\'s Cart Fragments, a new Ajax requests manager, Assets manager or various bits, this update should feel faster.</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/code-refactoring.jpg',
		];

		// Menu horizontal
		$items[] = [
			'size'  => 'third',
			'label' => 'Features',
			'title' => 'Compact Horizontal Menu',
			'desc'  => '<p>Horizontal menus can now be safely rendered horizontally even on mobiles (or especially on mobiles), by either appending a "Show more" button or scrolling horizontally</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/horizontal-menu.jpg',
		];

		$items[] = [
			'size'  => 'third',
			'label' => 'Features',
			'title' => 'Shipping Calculator',
			'desc'  => '<p>In many cases visitors have to access Cart or Checkout first and add their Shipping address in order to get a price. This will show the calculator form from the Cart page, directly into the Cart Panel.</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/shipping-calculator.jpg',
		];

		$items[] = [
			'size'  => 'third',
			'label' => 'Features',
			'title' => 'Lazy Loading for CSS Background Images',
			'desc'  => '<p>Sections, Columns and Contains now have the ability to render their background images only when the element is in viewport when scrolling. Usually caching plugins only handle image tags, but not CSS background images.</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/lazy-background-image.jpg',
		];

		$items[] = [
			'size'  => 'third',
			'label' => 'Features',
			'title' => 'HoverBox CSS Transitions',
			'desc'  => '<p>The HoverBox element has been upgraded to support multiple hovering transitions, along with various captions.</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/hoverbox.jpg',
		];

		$items[] = [
			'size'  => 'third',
			'label' => 'Features',
			'title' => 'Evergreen Sale Badges',
			'desc'  => '<p>While it\'s often a shady practice and abused by many, this feature can show sale badges (eg: Countdowns) endlessly, which seems to be an effective scarcity practice.</p>',
			'img'   => REY_CORE_URI . '/assets/images/whats-new/evergreen-sale.jpg',
		];

		$data = [];

		foreach ($items as $key => $value) {
			$data[$key] = wp_parse_args($value, [
				'size'     => 'full',
				'reversed' => false,
				'label'    => '',
				'title'    => '',
				'desc'     => '',
				'img'      => '',
			]);
		}

		return $data;
	}

	/**
	 * Render the Whats New page
	 *
	 * @return void
	 */
	public function render_page(){

		if( ! $this->is_enabled ){
			return;
		}

		$this->mark_read(); ?>

		<div class="__rey-bg">
			<?php echo reycore__get_svg_icon(['id'=>'logo', 'class' => '__bg-logo']) ?>
			<?php echo reycore__get_svg_icon(['id'=>'logo', 'class' => '__bg-logo __bg-logo--2']) ?>
		</div>

		<div class="wrap rey-whatsNew-wrapper">

			<header class="rey-whatsNew-header">

				<div class="rey-whatsNew-headings">
					<h1><?php printf(__('What\'s new in <u>%s</u>', 'rey-core'), \ReyCore\Version::get_current_major_version()); ?></h1>
					<p><?php printf(__('Rey gets faster, better and easier to use with each update. See <a href="%s" target="_blank">full changelog</a>.', 'rey-core'), reycore__support_url('changelog/')); ?></p>
				</div>

				<div class="rey-whatsNew-logo">
					<hr>
					<?php echo reycore__get_svg_icon(['id'=>'logo']) ?>
					<hr>
				</div>

			</header>

			<div class="rey-whatsNew">

				<?php
				foreach ($this->get_data() as $key => $item):

					$classes = [
						'--item-' . sanitize_title($item['title']),
						'--' . $item['size'],
						$item['reversed'] ? '--reversed' : '',
					];

					printf( '<div class="rey-whatsNew-item %s">', esc_attr(implode(' ', $classes)) ); ?>

						<div class="__text">
							<?php if( $label = $item['label'] ): ?>
								<h2 class="__label"><?php echo $label ?></h2>
							<?php endif; ?>

							<h3 class="__title"><?php echo $item['title'] ?></h3>

							<?php if( $desc = $item['desc'] ): ?>
								<div class="__desc"><?php echo $desc ?></div>
							<?php endif; ?>
						</div>

						<div class="__media">
							<?php if( $img = $item['img'] ): ?>
							<img src="<?php echo $img ?>" class="__img" alt="<?php echo $item['title'] ?>">
							<?php endif; ?>
						</div>

					</div>
					<!-- .rey-whatsNew-item -->
				<?php endforeach; ?>

				<div class="__buttons">
					<a href="<?php echo reycore__support_url('changelog/') ?>" target="_blank" class="rey-adminBtn --btn-outline">Full Changelog</a> &nbsp;&nbsp;&nbsp;
					<a href="#" target="_blank" class="rey-adminBtn --btn-link __hide-until-next">Got it! Hide until next major update</a>
				</div>

				<div class="__buttons">
					<a href="#" target="_blank" class="rey-adminBtn --btn-link __never-show"><span class="__close">&times;</span><span>Never show this page again!</span></a>
				</div>

			</div>
			<!-- .rey-whatsNew -->

		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Register Ajax Actions
	 *
	 * @param object $ajax_manager
	 * @return void
	 */
	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'whatsnew_page_visibility', [$this, 'ajax__update_page_visibility'], 1 );
		$ajax_manager->register_ajax_action( 'whatsnew_hide_until_next_major', [$this, 'ajax__hide_until_next_major'], 1 );
	}

	/**
	 * Hides the Whats new page on demand, through
	 * the "Never show again button".
	 *
	 * @return bool|array
	 */
	public function ajax__update_page_visibility(){

		if( ! current_user_can( 'administrator' ) ){
			return [
				'errors' => [ esc_html__('Operation not allowed!', 'rey-core') ]
			];
		}

		if( ! update_field(self::WHATS_NEW_OPTION, false, REY_CORE_THEME_NAME) ){
			return [
				'errors' => ['Couldnt update the option']
			];
		}

		return true;
	}

	/**
	 * Hides the Whats new page on demand, but only until
	 * the upcoming major version eg: "2.4.x" => "2.5.x"
	 *
	 * @return bool|array
	 */
	public function ajax__hide_until_next_major(){

		if( ! current_user_can( 'administrator' ) ){
			return [
				'errors' => [ esc_html__('Operation not allowed!', 'rey-core') ]
			];
		}

		if( ! update_option(self::HIDE_UNTIL_MAJOR, true) ){
			return [
				'errors' => ['Couldn\'t update the option.']
			];
		}

		return true;
	}

	/**
	 * Adds option in theme settings
	 */
	public function add_acf_show_button(){

		if( ! is_admin() ){
			return;
		}

		if( ! function_exists('acf_add_local_field') ){
			return;
		}

		acf_add_local_field([
			'key'          => 'field_' . self::WHATS_NEW_OPTION,
			'name'         => self::WHATS_NEW_OPTION,
			'label'        => esc_html__('Show "What\'s New"', 'rey-core'),
			'type'         => 'true_false',
			'instructions' => sprintf(esc_html_x('Disable this option to prevent %s from ever showing the "What\'s New" page.', 'Backend setting description', 'rey-core'), reycore__get_props('theme_title')),
			'default_value' => 1,
			'ui'            => 1,
			'parent'        => 'group_5c990a758cfda',
			'menu_order'    => 350,
		]);
	}

	/**
	 * Checks if Whats new page is enabled.
	 * - Only in admin & if the user can update plugins.
	 * - CHecks for major update.
	 * - If ACF is missing, the option will return true, as enabled.
	 * - If the option is null (never saved) or saved and true, will return as enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled(){

		if( ! is_admin() ){
			return false;
		}

		if( ! current_user_can('update_plugins') ){
			return false;
		}

		// Checks if the page is set to be
		// hidden until the upcoming major update.
		if( get_option(self::HIDE_UNTIL_MAJOR) && ! (defined('REY_WHATSNEW_ALWAYS_SHOW') && REY_WHATSNEW_ALWAYS_SHOW) ){
			// if it's not major version switch,
			// just disable the page
			if( ! version_compare( \ReyCore\Version::get_db_version(true), \ReyCore\Version::get_current_major_version(), '<' ) ){
				return false;
			}
		}

		if( ! class_exists('\ACF') ){
			return true;
		}

		$opt = get_field(self::WHATS_NEW_OPTION, REY_CORE_THEME_NAME);

		return is_null($opt) || (bool) $opt === true;

	}
}
