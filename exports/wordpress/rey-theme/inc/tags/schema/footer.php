<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rey_SchemaOrg_Footer extends Rey_SchemaOrg {

	public function setup() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'rey/footer/tag_attributes', [ $this, 'attributes' ] );
	}

	public function attributes( $attr ) {

		$attr['itemtype'] = 'https://schema.org/WPFooter';
		$attr['itemscope'] = 'itemscope';

		return $attr;
	}

	protected function is_enabled() {
		return apply_filters( 'rey/schema/footer/enabled', false ); // disabled by default
	}

}

new Rey_SchemaOrg_Footer();
