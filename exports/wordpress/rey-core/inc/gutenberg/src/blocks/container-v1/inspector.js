/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, PanelColorSettings, withColors } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';

const Inspector = ( props ) => {

	const {
		attributes,
		setAttributes,
		backgroundColor,
		setBackgroundColor,
		setTextColor,
		textColor,
	} = props;

	const {
		offsetAlign,
		align,
		maxWidth,
		preview
	} = attributes;

	const setOffsetTo = ( value ) => {
		props.setAttributes( { offsetAlign: value } );
	};

	const offsetAlignOptions = [
		{
			value: '',
			label: __( 'None', 'rey-core' ),
		},
		{
			value: 'semi',
			label: __( 'Semi Offset', 'rey-core' ),
		},
		{
			value: 'full',
			label: __( 'Full Offset', 'rey-core' ),
		},
	];

	const canAlign = align === 'left' || align === 'right';

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Container Settings', 'rey-core' ) }>
				<ToggleControl
					label={ __( 'No Preview Outline', 'rey-core' ) }
					checked={ !! preview }
					onChange={ () => setAttributes( { preview: ! preview } ) }
				/>
				{ canAlign && <SelectControl
					label={ __( 'Offset align', 'rey-core' ) }
					value={ offsetAlign }
					onChange={ setOffsetTo }
					options={ offsetAlignOptions }
				/> }
				{ canAlign && <RangeControl
					label={ __( 'Block width', 'rey-core' ) + ' (px)' }
					value={ maxWidth ? parseInt( maxWidth ) : '' }
					onChange={ ( nextMaxWidth ) => setAttributes( { maxWidth: parseInt( nextMaxWidth ) } ) }
					min={ 100 }
					max={ 1000 }
					step={ 1 }
					initialPosition={ 220 }
				/> }
			</PanelBody>
			<PanelColorSettings
				title={ __( 'Color settings', 'rey-core' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: backgroundColor.color,
						onChange: setBackgroundColor,
						label: __( 'Background color', 'rey-core' ),
					},
					{
						value: textColor.color,
						onChange: setTextColor,
						label: __( 'Text color', 'rey-core' ),
					},
				] }
			></PanelColorSettings>
		</InspectorControls>
	);
};

export default compose( [
	withColors( 'backgroundColor', { textColor: 'color' } ),
] )( Inspector );
