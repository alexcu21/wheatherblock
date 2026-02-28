import { registerBlockType, createBlock } from '@wordpress/blocks';
import './style.scss';

/**
 * Internal dependencies
 */

import Edit from './edit';
import name from './block.json';

registerBlockType( name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	/**
	 * @see ./save.js
	 */
	save: () => null,

	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'weather',
				transform( { attributes: { cityname } } ) {
					return createBlock( 'create-block/weatherblock', {
						cityName: cityname,
					} );
				},
			},
		],
	},
} );
