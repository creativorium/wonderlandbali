import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { name, text, imageUrl, imagePosition } = attributes;
	const blockProps = useBlockProps( { className: `wl-profile is-${ imagePosition }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Profile', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Image position', 'wonderland-blocks' ) }
						value={ imagePosition }
						options={ [ { label: 'Left', value: 'left' }, { label: 'Right', value: 'right' } ] }
						onChange={ ( v ) => setAttributes( { imagePosition: v } ) }
					/>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.imageId }
							onSelect={ ( m ) => setAttributes( { imageId: m.id, imageUrl: m.url } ) }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ imageUrl ? __( 'Replace photo', 'wonderland-blocks' ) : __( 'Set photo', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<RichText tagName="h2" className="wl-profile__name" value={ name }
					onChange={ ( v ) => setAttributes( { name: v } ) } placeholder={ __( 'Name…', 'wonderland-blocks' ) } allowedFormats={ [] } />
				<div className="wl-profile__grid">
					<figure className="wl-profile__media">
						{ imageUrl && <img src={ imageUrl } alt="" /> }
					</figure>
					<RichText tagName="div" className="wl-profile__text" value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) } placeholder={ __( 'Bio…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
				</div>
			</section>
		</>
	);
}
