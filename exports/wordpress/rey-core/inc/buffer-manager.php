<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BufferManager
{

	private $buffer = '';

	public function __construct(){
		add_action( 'template_redirect', [$this, 'start_buffering'], 2 );
	}

	public function start_buffering(){

		if( ! apply_filters('reycore/buffer/should_buffer', self::should_buffer()) ){
			\ReyCore\Plugin::instance()->assets_manager->set_settings([
				'save_css' => false,
				'save_js' => false,
			]);
			return;
		}

		ob_start( [$this, 'end_buffering'] );
	}

	public function set_buffer($buffer){
		$this->buffer = $buffer;
	}

	public function get_buffer(){
		return $this->buffer;
	}

	public function end_buffering( $content ){

		// Bail early without modifying anything if we can't handle the content.
		if ( ! self::is_valid_buffer( $content ) ) {
			return $content;
		}

		// set the buffer
		$this->buffer = $content;

		// modify buffer
		do_action('reycore/buffer/assets', $this);

		return $this->buffer;
	}

	 /**
     * Returns true if all the conditions to start output buffering are satisfied.
     *
     * @param bool $doing_tests Allows overriding the optimization of only
     *                          deciding once per request (for use in tests).
     * @return bool
     */
    public static function should_buffer( $doing_tests = false )
    {
        static $do_buffering = null;

        // Only check once in case we're called multiple times by others but
        // still allows multiple calls when doing tests.
        if ( null === $do_buffering || $doing_tests ) {

            $_noptimize = false;

            // Checking for DONOTMINIFY constant as used by e.g. WooCommerce POS.
            if ( defined( 'DONOTMINIFY' ) && ( constant( 'DONOTMINIFY' ) === true || constant( 'DONOTMINIFY' ) === 'true' ) ) {
                $_noptimize = true;
            }

            // Misc. querystring paramaters that will stop from doing optimizations (pagebuilders +
            // 2 generic parameters that could/ should become standard between optimization plugins?).
            if ( false === $_noptimize ) {
                $_qs_showstoppers = [
					'no_cache', 'tve', 'elementor-preview', 'fl_builder', 'vc_action', 'et_fb', 'bt-beaverbuildertheme', 'ct_builder', 'fb-edit', 'siteorigin_panels_live_editor', 'preview', 'wc-api'
				];

                foreach ( $_qs_showstoppers as $_showstopper ) {
                    if ( array_key_exists( $_showstopper, $_GET ) ) {
                        $_noptimize = true;
                        break;
                    }
                }
            }

            // Also honor PageSpeed=off parameter as used by mod_pagespeed, in use by some pagebuilders,
            // see https://www.modpagespeed.com/doc/experiment#ModPagespeed for info on that.
            if ( false === $_noptimize && array_key_exists( 'PageSpeed', $_GET ) && 'off' === $_GET['PageSpeed'] ) {
                $_noptimize = true;
            }

            $is_customize_preview = false;
            // $is_customize_preview = function_exists( 'is_customize_preview' ) && is_customize_preview();

			if ( isset( $_REQUEST['action'] ) && ( 'heartbeat' == strtolower( $_REQUEST['action'] ) ) ) {
                $_noptimize = true;
			}

			// if ( function_exists( 'is_pos' ) && is_pos() ) {
            //     $_noptimize = true;
			// }

            /**
             * We only buffer the frontend requests (and then only if not a feed
             * and not turned off explicitly and not when being previewed in Customizer)!
             * NOTE: Tests throw a notice here due to is_feed() being called
             * while the main query hasn't been ran yet.
             */
            $do_buffering = ( ! is_admin() && ! is_feed() && ! is_embed() && ! $_noptimize && ! $is_customize_preview );
        }

        return $do_buffering;
    }

	public static function is_valid_buffer( $content )
	{
		// Defaults to true.
		$valid = true;

		$has_no_html_tag    = ( false === stripos( $content, '<html' ) );
		$has_xsl_stylesheet = ( false !== stripos( $content, '<xsl:stylesheet' ) || false !== stripos( $content, '<?xml-stylesheet' ) );
		$has_html5_doctype  = ( preg_match( '/^<!DOCTYPE.+html>/i', ltrim( $content ) ) > 0 );
		$has_noptimize_page = ( false !== stripos( $content, '<!-- noptimize-page -->' ) );

		if ( $has_no_html_tag ) {
			// Can't be valid amp markup without an html tag preceding it.
			$is_amp_markup = false;
		} else {
			$is_amp_markup = self::is_amp_markup( $content );
		}

		// If it's not html, or if it's amp or contains xsl stylesheets we don't touch it.
		if ( $has_no_html_tag && ! $has_html5_doctype || $is_amp_markup || $has_xsl_stylesheet || $has_noptimize_page ) {
			$valid = false;
		}

		return $valid;
	}

	public static function is_amp_markup( $content )
	{
		// Short-circuit if the page is already AMP from the start.
		if (
			preg_match(
				sprintf(
					'#^(?:<!.*?>|\s+)*+<html(?=\s)[^>]*?\s(%1$s|%2$s|%3$s)(\s|=|>)#is',
					'amp',
					"\xE2\x9A\xA1", // From \AmpProject\Attribute::AMP_EMOJI.
					"\xE2\x9A\xA1\xEF\xB8\x8F" // From \AmpProject\Attribute::AMP_EMOJI_ALT, per https://github.com/ampproject/amphtml/issues/25990.
				),
				$content
			)
		) {
			return true;
		}

		// Or else short-circuit if the AMP plugin will be processing the output to be an AMP page.
		if ( function_exists( 'amp_is_request' ) ):
			$f = 'amp_is_request';
			return $f(); // For AMP plugin v2.0+.
		elseif ( function_exists( 'is_amp_endpoint' ) ):
			$f = 'is_amp_endpoint';
			return $f(); // For older/other AMP plugins (still supported in 2.0 as an alias).
		endif;

		return false;
	}

}
