/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';

/**
 * Block edit function
 */
class Edit extends Component {

	render() {

		const { attributes, className, setAttributes, selected, isSelected } = this.props;
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
			<Fragment>

				{ isSelected && (
					<Inspector
						{ ...this.props }
					/>
				) }

				<div className={ classnames( className, 'reyBlock-spacer-v1', {} ) } style={ styles }></div>
			</Fragment>
		);
	}
}

export default Edit;
