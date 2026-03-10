<?php
namespace ReyCore\Compatibility\Misc;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action( 'init', [$this, 'pixel_your_site'], 10);
		add_action( 'css_do_concat', [ $this, 'css_do_concat' ]);
	}

	/**
	 * Jetpack Boost compatibility
	 *
	 * @param bool $status
	 * @return bool
	 */
	function css_do_concat( $status ){

		if( ! empty($_GET['elementor-preview']) ){
			return false;
		}

		return $status;
	}

	/**
	 * Pixel Your Site compatibility
	 * Causing Rey's Combined JS script to not load
	 *
	 * @return void
	 */
	function pixel_your_site(){

		if( class_exists('\PixelYourSite\GATags') && method_exists('\PixelYourSite\GATags', 'instance') ){
			remove_action('wp_footer', [\PixelYourSite\GATags::instance(),'end_output_buffer'], 100);
		}

	}

}
