<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rey_SchemaOrg_SiteNavigation extends Rey_SchemaOrg {

	public function setup() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'rey/site-navigation/tag_attributes', [ $this, 'attributes' ] );
	}

	public function attributes( $attr ) {

		$attr['itemtype']  = 'https://schema.org/SiteNavigationElement';
		$attr['itemscope'] = 'itemscope';

		return $attr;
	}

	protected function is_enabled() {
		return apply_filters( 'rey/schema/site-navigation/enabled', parent::is_enabled() );
	}

}

new Rey_SchemaOrg_SiteNavigation();
