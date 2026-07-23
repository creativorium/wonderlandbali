import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit.jsx';

/**
 * Server-rendered block: save() returns null so the front-end markup comes
 * entirely from render.php.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
