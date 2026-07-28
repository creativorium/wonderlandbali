import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, ToggleControl, Button } from '@wordpress/components';

/** A labelled image picker that stores { url } entries. */
function Picker( { label, value, multiple, onChange } ) {
	const urls = multiple ? ( value || [] ).map( ( i ) => i.url ) : [ value ].filter( Boolean );
	return (
		<div style={ { marginBottom: '1rem' } }>
			<p style={ { margin: '0 0 .35rem', fontWeight: 600 } }>{ label }</p>
			{ urls.length > 0 && (
				<div style={ { display: 'flex', gap: '.35rem', flexWrap: 'wrap', marginBottom: '.5rem' } }>
					{ urls.map( ( u ) => (
						<img key={ u } src={ u } alt="" style={ { width: 44, height: 44, objectFit: 'cover' } } />
					) ) }
				</div>
			) }
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					multiple={ !! multiple }
					gallery={ !! multiple }
					onSelect={ ( m ) => onChange( multiple ? m.map( ( x ) => ( { url: x.url } ) ) : m.url ) }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ urls.length ? __( 'Replace', 'wonderland-blocks' ) : __( 'Select', 'wonderland-blocks' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ urls.length > 0 && (
				<Button variant="link" isDestructive onClick={ () => onChange( multiple ? [] : '' ) }>
					{ __( 'Clear', 'wonderland-blocks' ) }
				</Button>
			) }
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { heading, headingSize, straddle, background, text,
		leadImageUrl, sideImages, bottomImages, buttonText, buttonUrl } = attributes;

	const blockProps = useBlockProps( {
		className: `wl-showcase is-${ background } heading-${ headingSize }${ straddle ? ' is-straddle' : '' }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Heading size', 'wonderland-blocks' ) }
						value={ headingSize }
						options={ [
							{ label: __( 'Giant', 'wonderland-blocks' ), value: 'giant' },
							{ label: __( 'Normal', 'wonderland-blocks' ), value: 'normal' },
						] }
						onChange={ ( v ) => setAttributes( { headingSize: v } ) }
					/>
					<ToggleControl
						label={ __( 'Straddle the section above', 'wonderland-blocks' ) }
						checked={ !! straddle }
						onChange={ ( v ) => setAttributes( { straddle: v } ) }
						help={ __( 'Lifts a giant heading up over the preceding section.', 'wonderland-blocks' ) }
					/>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Images', 'wonderland-blocks' ) }>
					<Picker label={ __( 'Lead image (above the copy)', 'wonderland-blocks' ) }
						value={ leadImageUrl } onChange={ ( v ) => setAttributes( { leadImageUrl: v } ) } />
					<Picker label={ __( 'Side images (right column)', 'wonderland-blocks' ) } multiple
						value={ sideImages } onChange={ ( v ) => setAttributes( { sideImages: v } ) } />
					<Picker label={ __( 'Images below the copy', 'wonderland-blocks' ) } multiple
						value={ bottomImages } onChange={ ( v ) => setAttributes( { bottomImages: v } ) } />
				</PanelBody>
				<PanelBody title={ __( 'Call to action', 'wonderland-blocks' ) }>
					<TextControl label={ __( 'Button text', 'wonderland-blocks' ) } value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) } />
					<TextControl label={ __( 'Button URL', 'wonderland-blocks' ) } value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<RichText tagName="h2" className="wl-showcase__title" value={ heading }
					onChange={ ( v ) => setAttributes( { heading: v } ) }
					placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
				<div className="wl-showcase__inner">
					<div className="wl-showcase__main">
						{ leadImageUrl && (
							<figure className="wl-showcase__figure"><img src={ leadImageUrl } alt="" /></figure>
						) }
						<RichText tagName="div" className="wl-showcase__text" value={ text }
							onChange={ ( v ) => setAttributes( { text: v } ) }
							placeholder={ __( 'Section copy…', 'wonderland-blocks' ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] } />
						{ !! ( bottomImages || [] ).length && (
							<div className="wl-showcase__row">
								{ bottomImages.map( ( i ) => (
									<figure key={ i.url } className="wl-showcase__figure"><img src={ i.url } alt="" /></figure>
								) ) }
							</div>
						) }
					</div>
					<div className="wl-showcase__aside">
						{ ( sideImages || [] ).map( ( i ) => (
							<figure key={ i.url } className="wl-showcase__figure"><img src={ i.url } alt="" /></figure>
						) ) }
						{ buttonText && (
							<div className="wl-showcase__cta"><span className="wl-showcase__btn">{ buttonText }</span></div>
						) }
					</div>
				</div>
			</section>
		</>
	);
}
