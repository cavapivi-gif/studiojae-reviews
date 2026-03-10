/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { InnerBlocks, getColorClassName } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const save = ( { attributes } ) => {

	const { align, offsetAlign, maxWidth , backgroundColor, textColor, customBackgroundColor, customTextColor } = attributes;

	const backgroundClass = getColorClassName( 'background-color', backgroundColor );
	const textClass = getColorClassName( 'color', textColor );

	let styles = {
		backgroundColor: backgroundClass ? undefined : customBackgroundColor,
		color: textClass ? undefined : customTextColor,
	};

	if( maxWidth ){
		styles['--max-width'] = `${maxWidth}px`;
		styles['width'] = '100%';
	}

	return (
		<div
			className={ classnames( 'reyBlock-container-v1', {
				[ `--offsetAlign-${ offsetAlign }` ]: offsetAlign !== '',
				'has-background': backgroundColor || customBackgroundColor,
				'has-text-color': textColor || customTextColor,
				[ textClass ]: textClass,
				[ backgroundClass ]: backgroundClass,
			} ) }
			data-align={ align }
			style={ styles }
		>
			<div className="reyBlock-containerInner">
				<InnerBlocks.Content />
			</div>
		</div>
	);

};

export default save;
