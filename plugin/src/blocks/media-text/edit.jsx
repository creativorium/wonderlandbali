import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl, SelectControl } from '@wordpress/components'; //Notes

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, text, buttonText, buttonUrl, imageUrl, imagePosition, background } = attributes;
	const blockProps = useBlockProps( { className: `wl-mt is-${ imagePosition } is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Image position', 'wonderland-blocks' ) }
						value={ imagePosition }
						options={ [ { label: 'Left', value: 'left' }, { label: 'Right', value: 'right' } ] }
						onChange={ ( v ) => setAttributes( { imagePosition: v } ) }
					/>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [ { label: 'White', value: 'white' }, { label: 'Greige', value: 'greige' } ] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<TextControl
						label={ __( 'Button URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-mt__grid">
					<div className="wl-mt__media">
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={ [ 'image' ] }
								value={ attributes.imageId }
								onSelect={ ( m ) => setAttributes( { imageId: m.id, imageUrl: m.url } ) }
								render={ ( { open } ) =>
									imageUrl ? (
										<button type="button" onClick={ open } style={ { border: 0, padding: 0, cursor: 'pointer', width: '100%' } }>
											<img src={ imageUrl } alt="" style={ { display: 'block', width: '100%' } } />
										</button>
									) : (
										<Button variant="secondary" onClick={ open }>{ __( 'Set image', 'wonderland-blocks' ) }</Button>
									)
								}
							/>
						</MediaUploadCheck>
					</div>
					<div className="wl-mt__body">
						<RichText tagName="p" className="wl-mt__eyebrow" value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) } placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
						<RichText tagName="h2" className="wl-mt__heading" value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) } placeholder={ __( 'Heading…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/italic' ] } />
						<RichText tagName="div" className="wl-mt__text" value={ text }
							onChange={ ( v ) => setAttributes( { text: v } ) } placeholder={ __( 'Text…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] } />
						<RichText tagName="span" className="wl-mt__cta" value={ buttonText }
							onChange={ ( v ) => setAttributes( { buttonText: v } ) } placeholder={ __( 'Button (optional)…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					</div>
				</div>
			</section>
		</>
	);
}
