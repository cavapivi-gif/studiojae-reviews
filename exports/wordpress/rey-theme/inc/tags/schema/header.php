<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rey_SchemaOrg_Header extends Rey_SchemaOrg {

	public function setup() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'rey/header/tag_attributes', [ $this, 'attributes' ] );
	}

	public function attributes( $attr ) {

		$attr['itemtype'] = 'https://schema.org/WPHeader';
		$attr['itemscope'] = 'itemscope';

		return $attr;
	}

	protected function is_enabled() {
		/**
		 * Disabled by default because of a warning in https://search.google.com/structured-data/testing-tool/ , eg:
		 * "The property logo is not recognized by Google for an object of type WPHeader"
		 */
		return apply_filters( 'rey/schema/header/enabled', false );
	}

}

new Rey_SchemaOrg_Header();
