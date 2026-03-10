<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Check registration
$is_registerd = ReyTheme_Base::get_purchase_code();

// Create plugins markup
$required_plugins = \Rey\Plugins::get_required_plugins();
rsort($required_plugins);
$plugins_output = '';
$plugins_to_install = []; // used to determine if the box should be shown

foreach( $required_plugins as $key => $plugin ):

	$name = $plugin['name'];
	$classes = [];

	$is_installed = $plugin['installed'];
	$is_active = $plugin['active'];

	$classes['status'] = '--is-active';

	// if can't be installed because the plugin is premium and the site isn't registerd with purchase code
	if( $plugin['type'] === REY_THEME_NAME && !$is_registerd && ! $is_installed ){
		$name .= sprintf(' <em>%s</em>', esc_html__('(Needs theme registration)', 'rey'));
		$classes[] = '--uninstallable';
	}

	// if installed but not activated
	if( $is_installed && ! $is_active ){
		$name .= sprintf(' <em>%s</em>', esc_html__('(Inactive)', 'rey'));
	}

	// if the plugin is not installed or not activated
	if( ! $is_installed || ! $is_active ){
		$classes['status'] = '--inactive';
		$plugins_to_install[] = $key;
	}

	$plugins_output .= '<div class="reyAdmin-reqPlugin '. implode(' ', $classes) .'" data-slug="'. $plugin['slug'] .'" data-type="'. $plugin['type'] .'">';

		// show spinner (in case the plugin's not installed or activated)
		if( ! $is_installed || ! $is_active ){
			$plugins_output .= '<span class="rey-spinnerIcon"></span>';
		}

		$plugins_output .= rey__get_svg_icon(['id' => 'active']);
		$plugins_output .= rey__get_svg_icon(['id' => 'inactive']);

		$plugins_output .= '<h3>'. $name .'</h3>';

		if(isset($plugin['desc'])){
			$plugins_output .= '<p>'.$plugin['desc'].'</p>';
		}

	$plugins_output .= '</div>';

endforeach;

if( !$is_registerd && isset($_GET['page']) && $_GET['page'] === ReyTheme_Base::DASHBOARD_PAGE_ID ){
	$plugins_output .= '<p class="reyAdmin-notice --info">' .
		esc_html__('Please register the theme to enable installing premium plugins, or install them manually.', 'rey') . ' ' .
		esc_html_x('Read more on ', 'installing plugins kb article', 'rey') .
		'<a href="'. rey__support_url('kb/installing-rey-plugins/') .'" target="_blank">' .
		esc_html__('installing plugins', 'rey') .
		'</a>.</p>';
}
