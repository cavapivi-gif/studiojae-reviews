<?php
namespace ReyCore\Modules\PdpCustomTabs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-pdp-custom-tabs';

	private $include_acc = [];

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		new Customizer();
		new AcfFields();

		// if( ! $this->is_enabled() ){
		// 	return;
		// }

		add_filter( 'woocommerce_product_tabs', [$this, 'manage_tabs'], 20);
		add_filter( 'acf/load_value/key=field_5ecae99f56e6d', [$this, 'generate_acf_repeater_tabs']);
		add_filter( 'acf/load_field/key=field_615b37e5b5408', [$this, 'fill_global_tabs_choices']);
		add_filter( 'acf/load_field/key=field_649453638d135', [$this, 'hide_act_as_accordion']);
		// add_filter( 'theme_mod_single__accordion_items', [$this, 'include_custom_in_acc']);

	}

	public function manage_tabs($tabs){

		$global_tabs = $this->option();

		if( ! (is_array($global_tabs) && class_exists('\ACF')) ){
			return $tabs;
		}

		$_pdp_tabs = (array) get_field('product_custom_tabs');

		if( empty($global_tabs) && empty($_pdp_tabs) ){
			return $tabs;
		}

		$pdp_tabs = [];
		$pdp_tabs_extra = [];
		$disabled_global_tabs = [];

		// Split product tabs between the ones that override global tabs
		// and extra ones.
		foreach ($_pdp_tabs as $key => $value)
		{
			$disabled = ! empty($value['tab_disable']);

			// "tab_type" is set
			if( ! empty($value['tab_type']) ){

				// to override Global (and is migrated to UID)
				if( 'custom' !== $value['tab_type'] ){

					if( $disabled ){
						$disabled_global_tabs[] = $value['tab_type'];
					}

					$pdp_tabs[$value['tab_type']] = $value;
				}

				// Custom Tab
				else if( 'custom' === $value['tab_type'] ){
					if( ! $disabled ){
						$pdp_tabs_extra[] = $value;
					}
				}

			}

			// NO PDP UID, no Custom, nothing. Could be a failed migration (legacy tab) OR just a Custom tab.
			else {

				if( $disabled ){
					continue;
				}

				// has Customizer UIDs, so the migration went fine.
				if( isset($global_tabs[0]['uid']) ){
					$pdp_tabs_extra[] = $value;
				}

				// migration failed, so just include the tabs
				else {
					$pdp_tabs[] = $value;
				}

			}
		}

		$c_key = 0;

		foreach ($global_tabs as $key => $global_tab) {

			if( ! empty($global_tab['uid']) && in_array($global_tab['uid'], $disabled_global_tabs, true) ){
				continue;
			}

			$_data = [
				'priority' => absint($global_tab['priority']),
				'uid'      => ! empty($global_tab['uid']) ? $global_tab['uid'] : '',
				'type'     => 'custom',
			];

			// default content (from global tab)
			$_data['content'] = isset($global_tab['content']) ? reycore__parse_text_editor($global_tab['content']) : '';
			$_data['title'] = isset($global_tab['text']) ? reycore__parse_text_editor($global_tab['text']) : '';
			$__pt = false;

			// check for product tab content, matching the globals UID and product tabs `tab_type`
			// and override the global value
			if( ! empty($global_tab['uid']) )
			{
				if( ! empty($pdp_tabs[$global_tab['uid']]) && ($product_tab = $pdp_tabs[$global_tab['uid']]) )
				{
					// we'll cleanup matched product & global tabs
					// so the rest can be printed later
					unset($pdp_tabs[$global_tab['uid']]);

					$__pt = $product_tab;
				}
			}
			// Legacy int keys (in case migration didn't worked)
			else {
				if( ! empty($pdp_tabs[$key]) && ($product_tab = $pdp_tabs[$key]) )
				{
					$__pt = $product_tab;
				}
			}

			if( false !== $__pt ){

				if( $_ptc = $product_tab['tab_content'] ){
					$_data['content'] =  reycore__parse_text_editor( $_ptc );
				}

				if( $_ptt = $product_tab['tab_title'] ){
					$_data['title'] =  reycore__parse_text_editor( $_ptt );
				}

			}

			if( empty($_data['content']) ){
				continue;
			}

			$_data['callback'] = function() use ($_data) {
				echo $_data['content'];
			};

			$tabs['custom_tab_' . $key] = $_data;

			$c_key = $key;
		}

		if( ! empty($pdp_tabs_extra) )
		{
			foreach ($pdp_tabs_extra as $value)
			{
				$_data['content'] = isset($value['tab_content']) ? reycore__parse_text_editor($value['tab_content']) : '';

				if( empty($_data['content']) ){
					continue;
				}

				$tab_key = 'custom_tab_' . $c_key;

				$_data['type'] = 'custom';
				$_data['priority'] = ! empty($value['custom_tab_priority']) ? absint($value['custom_tab_priority']) : 100;
				$_data['force_acc'] = ! empty($value['custom_add_into_accordion']) && $value['custom_add_into_accordion'] ? $tab_key : false;
				$_data['title'] = isset($value['tab_title']) ? reycore__parse_text_editor($value['tab_title']) : '';
				$_data['callback'] = function() use ($_data) {
					echo $_data['content'];
				};

				$tabs[$tab_key] = $_data;

				$c_key++;
			}
		}

		return $tabs;
	}

	/**
	 * Generate ACF repeater;s tabs
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function generate_acf_repeater_tabs($value) {

		$global_tabs = $this->option();

		if( false === $value || '' === $value ){

			if( ! empty($global_tabs) ){
				$value = [];
				foreach ($global_tabs as $tab) {
					// add default tab values.
					$value[] = [
						'field_615b37e5b5408' => ! empty($tab['uid']) ? $tab['uid'] : '', // Override Global Tab
						'field_5ecae9c356e6e' => '', // Tab Title
						'field_5ecae9ef56e6f' => '', // Tab Content
					];
				}
			}

		}

		return $value;
	}

	public function fill_global_tabs_choices($field) {

		$global_tabs = $this->option();

		foreach ($global_tabs as $global_tab) {
			if( ! empty($global_tab['uid']) ){
				$field['choices'][ $global_tab['uid'] ] = sprintf('Override "%s" (ID: %s)', $global_tab['text'], $global_tab['uid']);
			}
		}

		return $field;
	}

	public function hide_act_as_accordion($field) {

		if( empty(get_theme_mod('single__accordion_items', [])) ){
			$field['wrapper']['class'] .= ' --invisible';
		}

		return $field;
	}

	public function option() {
		return (array) get_theme_mod('single__custom_tabs', []);
	}

	public function is_enabled() {
		return ! empty( $this->option() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Custom Tabs in Product Page', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to create as many custom tabs or blocks, inside product pages.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/create-product-page-custom-tabs-blocks/#add-custom-tabs-blocks'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
