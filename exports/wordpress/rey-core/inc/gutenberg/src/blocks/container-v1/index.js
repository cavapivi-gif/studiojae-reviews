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
	title: __( 'Container [rey]', 'reycore' ),
	/* translators: block description */
	description: __( 'Add other blocks inside and align them.', 'reycore' ),
	icon: (
		<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
			<g>
				<polygon fill="#000000" fillRule="nonzero" points="12.8 11.2 16 11.2 16 12.8 12.8 12.8 12.8 16 11.2 16 11.2 12.8 8 12.8 8 11.2 11.2 11.2 11.2 8 12.8 8"></polygon>
				<rect stroke="#CD2323" fill="none" strokeWidth="2" x="1" y="4" width="22" height="16" rx="4"></rect>
			</g>
		</svg>
	),
	keywords: [
		'reycore',
		/* translators: block keyword */
		__( 'layout', 'reycore' ),
		/* translators: block keyword */
		__( 'row', 'reycore' ),
	],
	supports: {
		align: true,
		alignWide: true,
		alignFull: true,
	},
	attributes,
	edit,
	save,
};

export { name, category, metadata, settings, attributes };
