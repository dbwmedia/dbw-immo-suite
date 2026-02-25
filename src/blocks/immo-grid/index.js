/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';
import './editor.scss';

import { registerBlockType } from '@wordpress/blocks';

/**
 * Register the dbw/immo-grid block.
 */
registerBlockType(metadata.name, {
    edit: Edit,
    save,
});
