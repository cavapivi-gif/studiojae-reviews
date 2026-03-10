//  Import CSS.
import './styles/editor.scss';
import './styles/style.scss';

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import save from './save';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Block constants
 */
const { name, category, attributes } = metadata;

const settings = {
	/* translators: block name */
	title: __( 'Spacer [rey]', 'reycore' ),
	/* translators: block description */
	description: __( 'Add spaces between elements.', 'reycore' ),
	icon: (
		<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
			<g id="spacer" stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
				<path d="M4,4 C2.34314575,4 1,5.34314575 1,7 L1,17 C1,18.6568542 2.34314575,20 4,20 L20,20 C21.6568542,20 23,18.6568542 23,17 L23,7 C23,5.34314575 21.6568542,4 20,4 L4,4 Z" stroke="#CD2323" strokeWidth="2"></path>
				<rect fill="#000000" x="6" y="10" width="12" height="1"></rect>
				<rect fill="#000000" x="6" y="13" width="12" height="1"></rect>
			</g>
		</svg>
	),
	keywords: [
		'reycore', 'space', 'distance'
	],
	supports: {},
	attributes,
	edit,
	save,
};

export { name, category, metadata, settings, attributes };
