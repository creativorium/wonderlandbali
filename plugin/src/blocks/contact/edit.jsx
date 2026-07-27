import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, text, note, email1, email2, phone, formPreset, formButton, introImageUrl } = attributes;
	const blockProps = useBlockProps( { className: `wl-contact wl-contact--${ formPreset }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Form', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Field set', 'wonderland-blocks' ) }
						value={ formPreset }
						options={ [
							{ label: __( 'Contact (short)', 'wonderland-blocks' ), value: 'contact' },
							{ label: __( 'Make a Request (full)', 'wonderland-blocks' ), value: 'request' },
						] }
						onChange={ ( v ) => setAttributes( { formPreset: v } ) }
						help={ __( 'Contact: name, email, phone, message. Request: adds country, date, guests, budget and venue.', 'wonderland-blocks' ) }
					/>
					<TextControl label="Form subject" value={ attributes.formSubject } onChange={ ( v ) => setAttributes( { formSubject: v } ) } />
					<TextControl label="Button text" value={ formButton } onChange={ ( v ) => setAttributes( { formButton: v } ) } />
				</PanelBody>
				<PanelBody title={ __( 'Intro image', 'wonderland-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.introImageId }
							onSelect={ ( m ) => setAttributes( { introImageId: m.id, introImageUrl: m.url } ) }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ introImageUrl ? __( 'Replace image', 'wonderland-blocks' ) : __( 'Select image', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ introImageUrl && (
						<Button variant="link" isDestructive onClick={ () => setAttributes( { introImageId: undefined, introImageUrl: '' } ) }>
							{ __( 'Remove image', 'wonderland-blocks' ) }
						</Button>
					) }
				</PanelBody>
				<PanelBody title={ __( 'Contact details', 'wonderland-blocks' ) }>
					<TextControl label="Email 1" value={ email1 } onChange={ ( v ) => setAttributes( { email1: v } ) } />
					<TextControl label="Email 2" value={ email2 } onChange={ ( v ) => setAttributes( { email2: v } ) } />
					<TextControl label="Phone" value={ phone } onChange={ ( v ) => setAttributes( { phone: v } ) } />
					<TextControl label="WhatsApp (digits)" value={ attributes.whatsapp } onChange={ ( v ) => setAttributes( { whatsapp: v } ) } />
					<TextControl label="Instagram URL" value={ attributes.instagram } onChange={ ( v ) => setAttributes( { instagram: v } ) } />
					<TextControl label="Facebook URL" value={ attributes.facebook } onChange={ ( v ) => setAttributes( { facebook: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-contact__inner">
					<div className="wl-contact__intro">
						<RichText tagName="h2" className="wl-contact__title" value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) } placeholder={ __( 'Heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
						<RichText tagName="div" className="wl-contact__text" value={ text }
							onChange={ ( v ) => setAttributes( { text: v } ) } placeholder={ __( 'Intro text…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
						{ introImageUrl && (
							<figure className="wl-contact__image"><img src={ introImageUrl } alt="" /></figure>
						) }
						<ul className="wl-contact__details">
							<li>{ email1 }</li><li>{ email2 }</li><li>{ phone }</li>
						</ul>
						<RichText tagName="p" className="wl-contact__note" value={ note }
							onChange={ ( v ) => setAttributes( { note: v } ) } placeholder={ __( 'Small print (response time, office hours…)', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
					</div>
					<div className="wl-contact__form">
						<div style={ { background: '#fff', padding: '2rem', textAlign: 'center', opacity: 0.7, fontStyle: 'italic' } }>
							{ 'request' === formPreset
								? __( 'Full request form renders here on the front end.', 'wonderland-blocks' )
								: __( 'Contact form renders here on the front end.', 'wonderland-blocks' ) }
							<br />[ { formButton } ]
						</div>
					</div>
				</div>
			</section>
		</>
	);
}
