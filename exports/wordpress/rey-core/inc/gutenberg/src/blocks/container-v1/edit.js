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
import { InnerBlocks, withColors } from '@wordpress/block-editor';
// import { BlockAlignmentToolbar, BlockControls, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { compose } from '@wordpress/compose';

/**
 * Block edit function
 */
class Edit extends Component {

	render() {

		const { attributes, className, setAttributes, selected, isSelected, backgroundColor, textColor } = this.props;
		const { align, offsetAlign, maxWidth, preview } = attributes;

		let styles = {
			backgroundColor: backgroundColor.color,
			color: textColor.color,
		};

		if( maxWidth ){
			styles['--max-width'] = `${maxWidth}px`;
			styles['width'] = '100%';
		}

		return (
			<Fragment>

				{ isSelected && (
					<Inspector
						{ ...this.props }
					/>
				) }

				<div
					className={ classnames( className, 'reyBlock-container-v1', {
						[ `--offsetAlign-${ offsetAlign }` ]: offsetAlign !== '',
						'has-background': backgroundColor.color,
						'has-text-color': textColor.color,
						[ backgroundColor.class ]: backgroundColor.class,
						[ textColor.class ]: textColor.class,
						'--no-preview': preview
					} ) }
					data-align={ align }
					style={ styles }
				>
					<div className="reyBlock-containerInner">
						<InnerBlocks
							templateLock={ false }
							renderAppender={ () => ! (selected && selected.innerBlocks.length > 0) ? <InnerBlocks.ButtonBlockAppender /> : <InnerBlocks.DefaultBlockAppender /> }
						/>
					</div>
				</div>
			</Fragment>
		);
	}
}

export default compose( [
	withColors( 'backgroundColor', { textColor: 'color' } ),
] )( Edit );
