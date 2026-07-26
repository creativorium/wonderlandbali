import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, images = [], buttonText, buttonUrl } = attributes;
	const blockProps = useBlockProps( { className: 'wl-portfolio' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery', 'wonderland-blocks' ) }>
					<MediaUploadCheck>
						<MediaUpload
							multiple
							gallery
							addToGallery
							allowedTypes={ [ 'image' ] }
							value={ images.map( ( s ) => s.id ).filter( Boolean ) }
							onSelect={ ( media ) =>
								setAttributes( {
									images: ( Array.isArray( media ) ? media : [ media ] ).map(
										( m ) => ( { id: m.id, url: m.url } )
									),
								} )
							}
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ images.length
										? __( 'Edit images', 'wonderland-blocks' ) + ` (${ images.length })`
										: __( 'Select images', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Button URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
						style={ { marginTop: '12px' } }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<RichText
					tagName="h2"
					className="wl-portfolio__title"
					value={ heading }
					onChange={ ( v ) => setAttributes( { heading: v } ) }
					placeholder={ __( 'Section title…', 'wonderland-blocks' ) }
					allowedFormats={ [] }
				/>
				<div className="wl-portfolio__grid">
					{ images.map( ( img, i ) => (
						<span className="wl-portfolio__item" key={ i }>
							<img src={ img.url } alt="" />
						</span>
					) ) }
				</div>
				<div className="wl-portfolio__more">
					<RichText
						tagName="span"
						className="wl-portfolio__btn"
						value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) }
						placeholder={ __( 'Button…', 'wonderland-blocks' ) }
						allowedFormats={ [] }
					/>
				</div>
			</section>
		</>
	);
}
