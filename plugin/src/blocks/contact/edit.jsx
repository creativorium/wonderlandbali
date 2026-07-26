import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, text, email1, email2, phone, formButton } = attributes;
	const blockProps = useBlockProps( { className: 'wl-contact' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Contact details', 'wonderland-blocks' ) }>
					<TextControl label="Email 1" value={ email1 } onChange={ ( v ) => setAttributes( { email1: v } ) } />
					<TextControl label="Email 2" value={ email2 } onChange={ ( v ) => setAttributes( { email2: v } ) } />
					<TextControl label="Phone" value={ phone } onChange={ ( v ) => setAttributes( { phone: v } ) } />
					<TextControl label="WhatsApp (digits)" value={ attributes.whatsapp } onChange={ ( v ) => setAttributes( { whatsapp: v } ) } />
					<TextControl label="Instagram URL" value={ attributes.instagram } onChange={ ( v ) => setAttributes( { instagram: v } ) } />
					<TextControl label="Facebook URL" value={ attributes.facebook } onChange={ ( v ) => setAttributes( { facebook: v } ) } />
					<TextControl label="Form subject" value={ attributes.formSubject } onChange={ ( v ) => setAttributes( { formSubject: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-contact__inner">
					<div className="wl-contact__intro">
						<RichText tagName="h2" className="wl-contact__title" value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) } placeholder={ __( 'Heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
						<RichText tagName="div" className="wl-contact__text" value={ text }
							onChange={ ( v ) => setAttributes( { text: v } ) } placeholder={ __( 'Intro text…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
						<ul className="wl-contact__details">
							<li>{ email1 }</li><li>{ email2 }</li><li>{ phone }</li>
						</ul>
					</div>
					<div className="wl-contact__form">
						<div style={ { background: '#fff', padding: '2rem', textAlign: 'center', opacity: 0.7, fontStyle: 'italic' } }>
							{ __( 'Contact form renders here on the front end.', 'wonderland-blocks' ) }
							<br />[ { formButton } ]
						</div>
					</div>
				</div>
			</section>
		</>
	);
}
