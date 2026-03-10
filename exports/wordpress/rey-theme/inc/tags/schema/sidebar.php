<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rey_SchemaOrg_Sidebar extends Rey_SchemaOrg {

	public function setup() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'rey/sidebar/tag_attributes', [ $this, 'attributes' ] );
	}

	public function attributes( $attr ) {

		$attr['itemtype'] = 'https://schema.org/WPSideBar';
		$attr['itemscope'] = 'itemscope';

		return $attr;
	}

	protected function is_enabled() {
		return apply_filters( 'rey/schema/sidebar/enabled', parent::is_enabled() );
	}

}

new Rey_SchemaOrg_Sidebar();
