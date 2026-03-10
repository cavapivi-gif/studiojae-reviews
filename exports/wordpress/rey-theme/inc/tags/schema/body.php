<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rey_SchemaOrg_Body extends Rey_SchemaOrg {

	public function setup() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'rey/body/tag_attributes', [ $this, 'attributes' ] );
	}

	public function attributes( $attr ) {

		$attr['itemtype'] = 'https://schema.org/WebPage';

		if( rey__is_blog_list() ){
			$attr['itemtype'] = 'https://schema.org/Blog';
		}

		if( is_search() ){
			$attr['itemtype'] = 'https://schema.org/SearchResultsPage';
		}

		$attr['itemscope'] = 'itemscope';

		return $attr;
	}

	protected function is_enabled() {
		return apply_filters( 'rey/schema/body/enabled', parent::is_enabled() );
	}

}

new Rey_SchemaOrg_Body();
