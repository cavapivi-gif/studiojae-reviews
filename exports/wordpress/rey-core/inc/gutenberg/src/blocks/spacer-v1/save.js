/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const save = ( props ) => {

	const { attributes, className } = props;
	const { heightDesktop, heightTablet, heightMobile } = attributes;

	let styles = {};

	if( heightDesktop ){
		styles['--height-lg'] = `${heightDesktop}px`;
	}

	if( heightTablet ){
		styles['--height-md'] = `${heightTablet}px`;
	}

	if( heightMobile ){
		styles['--height-sm'] = `${heightMobile}px`;
	}

	return (
		<div className={ classnames( className, 'reyBlock-spacer-v1', {} ) } style={ styles }></div>
	);

};

export default save;
