import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

function ImagePicker( { label, id, url, onSelect, onClear } ) {
	return (
		<div style={ { marginBottom: '16px' } }>
			<p style={ { margin: '0 0 4px', fontWeight: 600 } }>{ label }</p>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ id }
					onSelect={ onSelect }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ url ? __( 'Replace', 'wonderland-blocks' ) : __( 'Select', 'wonderland-blocks' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ url && (
				<Button variant="link" isDestructive onClick={ onClear } style={ { marginLeft: '8px' } }>
					{ __( 'Remove', 'wonderland-blocks' ) }
				</Button>
			) }
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		label,
		eyebrow,
		text,
		buttonText,
		buttonUrl,
		imageMainUrl,
		imageSubUrl,
	} = attributes;

	const blockProps = useBlockProps( { className: 'wl-intro' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Images', 'wonderland-blocks' ) }>
					<ImagePicker
						label={ __( 'Main image', 'wonderland-blocks' ) }
						id={ attributes.imageMainId }
						url={ imageMainUrl }
						onSelect={ ( m ) => setAttributes( { imageMainId: m.id, imageMainUrl: m.url } ) }
						onClear={ () => setAttributes( { imageMainId: undefined, imageMainUrl: '' } ) }
					/>
					<ImagePicker
						label={ __( 'Secondary image', 'wonderland-blocks' ) }
						id={ attributes.imageSubId }
						url={ imageSubUrl }
						onSelect={ ( m ) => setAttributes( { imageSubId: m.id, imageSubUrl: m.url } ) }
						onClear={ () => setAttributes( { imageSubId: undefined, imageSubUrl: '' } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Link', 'wonderland-blocks' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-intro__grid">
					<div className="wl-intro__media">
						{ imageMainUrl && (
							<img className="wl-intro__img wl-intro__img--main" src={ imageMainUrl } alt="" />
						) }
						{ imageSubUrl && (
							<img className="wl-intro__img wl-intro__img--sub" src={ imageSubUrl } alt="" />
						) }
					</div>

					<div className="wl-intro__body">
						<RichText
							tagName="h2"
							className="wl-intro__title"
							value={ label }
							onChange={ ( v ) => setAttributes( { label: v } ) }
							placeholder={ __( 'Title…', 'wonderland-blocks' ) }
							allowedFormats={ [ 'core/italic' ] }
						/>
						<RichText
							tagName="p"
							className="wl-intro__eyebrow"
							value={ eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) }
							allowedFormats={ [] }
						/>
						<RichText
							tagName="div"
							className="wl-intro__text"
							value={ text }
							onChange={ ( v ) => setAttributes( { text: v } ) }
							placeholder={ __( 'Body text…', 'wonderland-blocks' ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						<RichText
							tagName="span"
							className="wl-intro__cta"
							value={ buttonText }
							onChange={ ( v ) => setAttributes( { buttonText: v } ) }
							placeholder={ __( 'Button…', 'wonderland-blocks' ) }
							allowedFormats={ [] }
						/>
					</div>
				</div>
			</section>
		</>
	);
}
