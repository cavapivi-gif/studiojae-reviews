/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

const Inspector = ( props ) => {

	const {
		attributes,
		setAttributes
	} = props;

	const {
		heightDesktop, heightTablet, heightMobile
	} = attributes;

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Spacer Settings', 'rey-core' ) }>
				<RangeControl
					label={ __( 'Desktop Height', 'rey-core' ) + ' (px)' }
					value={ heightDesktop ? parseInt( heightDesktop ) : '' }
					onChange={ ( next ) => setAttributes( { heightDesktop: parseInt( next ) } ) }
					min={ 0 }
					max={ 1000 }
					step={ 1 }
					initialPosition={ 20 }
				/>
				<RangeControl
					label={ __( 'Tablet Height', 'rey-core' ) + ' (px)' }
					value={ heightTablet ? parseInt( heightTablet ) : '' }
					onChange={ ( next ) => setAttributes( { heightTablet: parseInt( next ) } ) }
					min={ 0 }
					max={ 1000 }
					step={ 1 }
					initialPosition={ 20 }
				/>
				<RangeControl
					label={ __( 'Mobile Height', 'rey-core' ) + ' (px)' }
					value={ heightMobile ? parseInt( heightMobile ) : '' }
					onChange={ ( next ) => setAttributes( { heightMobile: parseInt( next ) } ) }
					min={ 0 }
					max={ 1000 }
					step={ 1 }
					initialPosition={ 20 }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
