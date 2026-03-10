<?php
namespace ReyCore\Libs\Importer;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\ManagerBase
{

	public $is_manager = false;

	const MENU_PRIORITY = 110;

	public function __construct()
	{

		// determine if is page
		$this->is_manager = (isset($_REQUEST['page']) && $this->get_menu_slug() === reycore__clean($_REQUEST['page']));

		// make defines
		$this->defines();

		// load scripts
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action( 'rey/importer_link', [$this, 'importer_link']);

		parent::__construct();
	}

	public static function manager_is_enabled(){

		// maybe don't show
		if( ! reycore__get_props('demo_import') ){
			return;
		}

		if( ! reycore__get_purchase_code() ){
			return;
		}

		return ! \ReyCore\ACF\Helper::admin_menu_item_is_hidden('demo_import');
	}

	public function get_id(){
		return 'importer';
	}

	public function get_menu_title(){
		return __( 'Premade Sites', 'rey-core' );
	}

	public function set_page_config(){
		return [
			'cols'              => 3,
			'title'             => esc_html__('Import Rey pre-made Sites', 'rey-core'),
			'description'       => sprintf(__('Import any of Rey\'s beautify pre-made sites in just a couple of minutes. You can configure what to import and you can even reset the imported data if you changed your mind. No existing posts, pages, categories, images, custom post types or any other data will be deleted or modified, but it\'s highly recommended to import on a clean WordPress instance. Please <a href="%s" target="_blank">visit the documentation</a> for more information.', 'rey-core'), reycore__support_url('kb/importing-demos/') ),
			'singular_item'     => esc_html__('Demo', 'rey-core'),
			'plural_item'       => esc_html__('Demos', 'rey-core'),
			// 'media_overlay'     => false,
			'toggles'           => false,
			'vis_filter'        => false,
			'cat_filter'        => false,
			'keywords_filter'   => true,
			'not_in_use_notice' => false,
		];
	}

	public function defines(){
		define( 'REY_CORE_IMPORT_DIR', plugin_dir_path( __FILE__ ) );
		define( 'REY_CORE_IMPORT_URI', plugin_dir_url( __FILE__ ) );
		define( 'REY_CORE_IMPORT_SITE_URL', get_site_url());
		define( 'REY_CORE_IMPORT_MULTISITE_URL', is_multisite() ? network_site_url() : false );
		define( 'REY_CORE_IMPORT_UPLOAD_PATH', wp_get_upload_dir()['baseurl']);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'run_import', [$this, 'ajax__run_import'], 1 );
		$ajax_manager->register_ajax_action( 'run_import_reset', [$this, 'ajax__run_reset'], 1 );
		$ajax_manager->register_ajax_action( 'import_get_demo', [$this, 'ajax__get_demo_data'], 1 );
		$ajax_manager->register_ajax_action( 'run_demos_refresh', [$this, 'ajax__demos_refresh'], 1 );
	}

	public function importer_link(){
		return esc_url( add_query_arg( [
			'page' => $this->get_menu_slug()
		], admin_url( 'admin.php' ) ) );
	}

	public function ajax__run_import($action_data){

		if ( ! current_user_can('administrator') ) {
			return [ 'error' => 'Operation not allowed!' ];
		}

		ini_set('display_errors', '0');
		ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

		$importer = new Importer();
		return $importer->run($action_data);
	}

	public function ajax__run_reset(){

		if ( ! current_user_can('administrator') ) {
			return [ 'error' => 'Operation not allowed!' ];
		}

		$importer = new Importer();
		return $importer->reset_data();
	}

	public function ajax__demos_refresh(){
		return delete_option( Api::DEMOS_LIST_OPTION );
	}

	public function ajax__get_demo_data( $action_data ){

		if( ! (isset($action_data['demo']) && ($demo = $action_data['demo'])) ){
			return ['error' => 'Please provide a demo ID'];
		}

		$demos = Api::get_demos();

		if( is_wp_error($demos) ){
			return ['error' => $demos->get_error_message()];
		}

		if( ! isset( $demos['items'], $demos['items'][$demo] ) ){
			return ['error' => 'Missing demo ID.'];
		}

		$demo_data = $demos['items'][$demo];

		$demo_data['info'] = esc_html_x('Choose which plugins to install and content type to import. You can reset the data after importing.', 'Demo import text', 'rey-core');

		$demo_data['contents'] = [
			'terms' => esc_html_x('Terms', 'Demo import text', 'rey-core'),
			'attachments' => esc_html_x('Attachments', 'Demo import text', 'rey-core'),
			'post' => esc_html_x('Posts', 'Demo import text', 'rey-core'),
			'page' => esc_html_x('Pages', 'Demo import text', 'rey-core'),
			'nav_menu_item' => esc_html_x('Navigation Items', 'Demo import text', 'rey-core'),
			'product' => esc_html_x('Products', 'Demo import text', 'rey-core'),
			'rey-global-sections' => esc_html_x('Global Sections', 'Demo import text', 'rey-core'),
			'comments' => esc_html_x('Comments', 'Demo import text', 'rey-core'),
			'widgets' => esc_html_x('Widgets', 'Demo import text', 'rey-core'),
			'customizer' => esc_html_x('Customizer', 'Demo import text', 'rey-core'),
		];

		if( ($map = self::get_map()) && isset($map['rey_demo_config'], $map['rey_demo_config']['name']) ){
			$demo_data['current_demo'] = $map['rey_demo_config']['name'];
		}

		$demo_data['plugins'] = [];

		if( class_exists('\Rey\Libs\Plugins') ){

			$plugins_ob = new \Rey\Libs\Plugins();
			$rey_plugins = $plugins_ob->get_plugins();

			$excludes = [
				'contact-form-7',
				'mailchimp-for-wp',
				'one-click-demo-import',
				'rey-core',
				'wp-store-locator',
			];

			foreach ($demos['items'][$demo]['dependencies'] as $dep){
				if( in_array($dep, $excludes, true) ){
					continue;
				}
				if( $plugins_ob->is_plugin_active($dep) ){
					continue;
				}
				$demo_data['plugins'][] = [
					'id'      => $dep,
					'name'    => isset($rey_plugins[$dep]['name']) ? $rey_plugins[$dep]['name'] : '',
					'desc'    => isset($rey_plugins[$dep]['desc']) ? $rey_plugins[$dep]['desc'] : '',
					'installed'  => $plugins_ob->is_plugin_installed($dep),
				];
			}
		}

		return $demo_data;
	}

	public function enqueue_scripts(){

		if( ! self::manager_is_enabled() ){
			return;
		}

		if( ! $this->is_manager ){
			return;
		}

		add_filter('reycore/modals/always_load', '__return_true');

		$rtl = reycore_assets()::rtl();

		wp_enqueue_style('rey-import-style', REY_CORE_URI . 'assets/css/general-components/admin/importer' . $rtl . '.css', [], REY_CORE_VERSION);
		wp_enqueue_script('rey-import-script', REY_CORE_URI . 'assets/js/lib/importer.js', ['rey-script', 'rey-tmpl'], REY_CORE_VERSION, true);
		wp_localize_script('rey-import-script', 'reyImportParams', [
			'text' => [
				'reloading' => esc_html_x('Reloading page..', 'Demo import text', 'rey-core'),
				'finished' => esc_html_x('Finished importing.', 'Demo import text', 'rey-core'),
				'finished_title' => esc_html_x('Imported ', 'Demo import text', 'rey-core'),
				'finished_status' => esc_html_x('Done!', 'Demo import text', 'rey-core'),
				'failed_title' => esc_html_x('Import has failed!', 'Demo import text', 'rey-core'),
				'failed_reset' => esc_html_x('Resetting...', 'Demo import text', 'rey-core'),
				'init' => esc_html_x('Initializing...', 'Demo import text', 'rey-core'),
			],
			'timeout' => apply_filters('reycore/importer_params/tasks_timeout', 10),
		]);
	}

	public static function get_map(){
		return get_option( 'rey_demo_map_data', []);
	}

	public static function reset_map(){
		delete_option( 'rey_demo_map_data');
	}

	public static function update_map($data = []){
		return update_option( 'rey_demo_map_data', array_merge( self::get_map(), $data ), false );
	}

	public function render_media(){

		$image_path = sprintf('https://rey-theme.s3.us-west-2.amazonaws.com/demos/%1$s/%1$s.jpg', $this->_item['id']); ?>

		<a href="<?php echo esc_url($this->_item['url']) ?>" target="_blank" class="rey-itemManager-media __admin-lazy-img-container">
			<span class="__admin-lazy-img-loader"></span>
			<img data-src="<?php echo $image_path; ?>" alt="<?php echo esc_attr($this->_item['title']); ?>" class="__admin-lazy-img" loading="lazy">
			<?php printf('<span class="rey-itemManager-action --image">%s</span>', reycore__get_svg_icon(['id' => 'external-link'])); ?>
		</a>

		<?php
	}

	public function render_buttons_bar(){

		if( ! reycore__get_purchase_code() ){
			return;
		} ?>

		<div class="rey-itemManager-buttons">

			<button class="rey-adminBtn --btn-primary __importer-refresh">
				<span><?php echo esc_html__('Refresh list', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'sync']); ?>
				<span class="rey-spinnerIcon"></span>
			</button>

			<?php $this->reset_button(); ?>
		</div>
		<?php
	}

	public function reset_button(){

		$attrs['class'] = ['rey-adminBtn', '--btn-outline', '--red', '__importer-reset'];

		if( ($map = self::get_map()) && isset($map['rey_demo_config']) ){
			$attrs['data-demo-name'] = $map['rey_demo_config']['name'];
		} ?>

		<button <?php echo reycore__implode_html_attributes($attrs); ?>>
			<span><?php echo esc_html__('Reset Data', 'rey-core'); ?></span>
			<span class="rey-spinnerIcon"></span>
		</button>
		<?php
	}

	public function after_item_content(){
		?>

		<div class="rey-itemManager-actions">

			<a class="rey-adminBtn --btn-primary" data-import-item="<?php echo esc_attr($this->_item['id']) ?>">
				<span><?php echo esc_html__('Import', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'arrow-long']) ?>
				<span class="rey-spinnerIcon"></span>
			</a>

			<a href="<?php echo esc_url( $this->_item['url'] ) ?>" target="_blank" class="rey-adminBtn --btn-outline __preview">
				<span><?php echo esc_html__('Preview', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'external-link']) ?>
			</a>

		</div>
		<?php
	}

	public function after_render_page(){
		global $wp; ?>
		<script type="text/template" id="tmpl-rey-demo-import-modal">

			<div class="rey-import-modal --hidden" data-id="{{data.slug}}">
				<div class="__step --active" data-step="configure">
					<form action="<?php home_url( $wp->request ) ?>" class="rey-import-configureForm" method="post">
						<div class="__header __row">
							<h2 class="__header-title">
								<span><?php echo esc_html_x('Import', 'Demo import text', 'rey-core') ?> <strong>{{{data.name}}}</strong></span>
								<a href="{{data.url}}" target="_blank" class="__preview"><?php echo reycore__get_svg_icon(['id'=>'external-link']) ?></a>
							</h2>
							<div class="__header-right">
								<?php echo reycore__get_svg_icon(['id'=>'logo']) ?>
							</div>
							<p class="__info">{{{data.info}}}</p>
						</div>
						<# if(data.current_demo) { #>
							<div class="__reset __row">
								<div class="__box --reset">
									<input type="checkbox" name="reset-content" value="1" id="reset-content">
									<label for="reset-content">
										<div class="__name"><?php echo esc_html_x('Reset previously imported data from ', 'Demo import text', 'rey-core') ?> {{data.current_demo}}</div>
										<span class="__chk"></span>
									</label>
								</div>
							</div>
						<# } #>
						<# if(data.plugins.length) { #>
							<div class="__plugins __row">
								<h4><?php echo esc_html_x('Required plugins:', 'Demo import text', 'rey-core') ?></h4>
								<# for (var i = 0; i < data.plugins.length; i++) { #>
									<# var inactiveAttr = data.plugins[i].installed ? 'data-inactive="<?php echo esc_html_x('(inactive)', 'Demo import text', 'rey-core') ?>"' : ''; #>
									<div class="__box --plugin">
										<input type="checkbox" name="import-plugins" value="{{data.plugins[i].id}}" id="import-plugin-{{data.plugins[i].id}}" checked>
										<label for="import-plugin-{{data.plugins[i].id}}">
											<div class="__name" {{{inactiveAttr}}}>{{{data.plugins[i].name}}}</div>
											<div class="__desc">{{{data.plugins[i].desc}}}</div>
											<span class="__chk"></span>
										</label>
									</div>
								<# } #>
							</div>
						<# }  #>
						<div class="__contents __row">
							<h4><?php echo esc_html_x('Content:', 'Demo import text', 'rey-core') ?></h4>
							<# Object.keys(data.contents).forEach(key => { #>
								<div class="__box --content">
									<input type="checkbox" name="import-content" value="{{key}}" id="import-content-{{key}}" checked>
									<label for="import-content-{{key}}">
										<div class="__name">{{{data.contents[key]}}}</div>
										<span class="__chk"></span>
									</label>
								</div>
							<# }); #>
						</div>
						<button type="submit" class="rey-adminBtn --btn-primary __run"><?php echo esc_html_x('Import now', 'Demo import text', 'rey-core') ?></button>
					</form>
				</div>

				<div class="__step" data-step="runner">
					<div class="__header __row">
						<h2 class="__header-title">
							<span class="__text"><?php echo esc_html_x('Importing Content', 'Demo import text', 'rey-core') ?></span>
							<span class="rey-spinnerIcon"></span>
						</h2>
						<div class="__header-right">
							<svg class="__header-check rey-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
								<circle class="__circle" cx="26" cy="26" r="25" fill="none"/>
								<path class="__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
							</svg>
							<?php echo reycore__get_svg_icon(['id'=>'logo']) ?>
						</div>
					</div>
					<div class="__importer">
						<div class="__status" data-status-type="<?php echo esc_html_x('Status: ', 'Demo import text', 'rey-core') ?>" data-status-text="<?php echo esc_html__('Initializing..', 'rey-core') ?>"></div>
						<div class="__progress">
							<div class="__bar" style="--progress:0;"></div>
							<div class="__perc" data-progress="0">%</div>
						</div>
						<div class="__log-wrapper">
							<a href="#" class="__log-toggle">
								<span><?php echo esc_html_x('Show log', 'Demo import text', 'rey-core') ?></span>
								<?php echo reycore__get_svg_icon(['id'=>'arrow']) ?>
							</a>
							<a href="#" class="__log-copy">
								<span><?php echo esc_html_x('Copy', 'Demo import text', 'rey-core') ?></span>
							</a>
							<textarea class="__log-box"></textarea>
						</div>
					</div>
					<div class="__success">
						<a href="<?php echo esc_url( get_site_url() ) ?>" target="_blank" class="rey-adminBtn --btn-primary "><?php echo esc_html_x('PREVIEW SITE', 'Demo import text', 'rey-core') ?></a>
						<a href="#" class="rey-adminBtn --btn-outline __exit"><?php echo esc_html_x('Exit', 'Demo import text', 'rey-core') ?></a>
					</div>
				</div>

			</div>

		</script>
		<?php
	}

	public function default_categories(){
		return [
			'ecommerce' => 'E-commerce',
			'catalogue' => 'Catalogue',
			'creative'  => 'Creative',
		];
	}

	public function prepare_items(){

		$demos = Api::get_demos();

		if( is_wp_error($demos) ){
			$this->set_notice( '<strong>Cannot retrieve demos.</strong> ' . $demos->get_error_message(), 'error');
			return;
		}

		if( ! empty($demos['items']) ){

			$this->items = $demos['items'];

			$def_categories = $this->default_categories();

			foreach ($this->items as $i => $item) {
				if( isset($item['categories']) && ! empty($item['categories']) ){
					foreach ( $item['categories'] as $category) {
						$this->keywords[$category] = isset($def_categories[$category]) ? $def_categories[$category] : ucfirst($category);
					}
				}

				$classes = [];

				// highlight Starter
				if( 'starter' === $item['slug'] ){
					$classes[] = '--highlight';
				}

				if( ! empty($classes) ){
					$this->items[$i]['css_class'] = implode(' ', $classes);
				}

				if( ! empty($item['visibility']) && 'public' !== $item['visibility'] ){
					if( ! \ReyCore\Plugin::is_dev_mode() ){
						unset($this->items[$i]);
					}
				}

			}
		}

	}

	public function prepare_item( $item ){

		$item['id'] = $item['slug'];
		$item['title'] = $item['name'];

		if( ! empty($item['desc']) ){
			$item['description'] = $item['desc'];
		}

		return $item;
	}

	public function get_all_items(){
		return [];
	}

	public function get_default_disabled_items(){
		return [];
	}

}
