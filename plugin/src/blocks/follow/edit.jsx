import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl, TextareaControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, handle, buttonText, buttonUrl, shortcode, logos = [] } = attributes;
	const blockProps = useBlockProps( { className: 'wl-follow' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wonderland-blocks' ) }>
					<TextControl
						label={ __( 'Instagram URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
					/>
					<TextareaControl
						label={ __( 'Feed shortcode', 'wonderland-blocks' ) }
						value={ shortcode }
						onChange={ ( v ) => setAttributes( { shortcode: v } ) }
						help={ __( 'Rendered on the front end (e.g. Instagram Feed plugin).', 'wonderland-blocks' ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Partner logos', 'wonderland-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							multiple
							gallery
							addToGallery
							allowedTypes={ [ 'image' ] }
							value={ logos.map( ( s ) => s.id ).filter( Boolean ) }
							onSelect={ ( media ) =>
								setAttributes( {
									logos: ( Array.isArray( media ) ? media : [ media ] ).map(
										( m ) => ( { id: m.id, url: m.url } )
									),
								} )
							}
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ logos.length
										? __( 'Edit logos', 'wonderland-blocks' ) + ` (${ logos.length })`
										: __( 'Select logos', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-follow__head">
					<RichText
						tagName="h2"
						className="wl-follow__title"
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Heading…', 'wonderland-blocks' ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="span"
						className="wl-follow__handle"
						value={ handle }
						onChange={ ( v ) => setAttributes( { handle: v } ) }
						placeholder={ __( '@handle', 'wonderland-blocks' ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="span"
						className="wl-follow__btn"
						value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) }
						placeholder={ __( 'Button…', 'wonderland-blocks' ) }
						allowedFormats={ [] }
					/>
				</div>
				<p style={ { textAlign: 'center', opacity: 0.6, fontStyle: 'italic' } }>
					{ __( 'Instagram feed renders here on the front end:', 'wonderland-blocks' ) } <code>{ shortcode }</code>
				</p>
				{ logos.length > 0 && (
					<div className="wl-follow__logos">
						{ logos.map( ( l, i ) => (
							<img className="wl-follow__logo" src={ l.url } alt="" key={ i } />
						) ) }
					</div>
				) }
			</section>
		</>
	);
}
