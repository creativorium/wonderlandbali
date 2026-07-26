import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, title, subtitle, backgroundUrl, overlayOpacity, height } = attributes;
	const blockProps = useBlockProps( {
		className: `wl-page-hero wl-page-hero--${ height }${ backgroundUrl ? ' has-bg' : '' }`,
		style: backgroundUrl ? { backgroundImage: `url(${ backgroundUrl })` } : undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Height', 'wonderland-blocks' ) }
						value={ height }
						options={ [
							{ label: 'Short', value: 'short' },
							{ label: 'Tall', value: 'tall' },
						] }
						onChange={ ( v ) => setAttributes( { height: v } ) }
					/>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.backgroundId }
							onSelect={ ( m ) => setAttributes( { backgroundId: m.id, backgroundUrl: m.url } ) }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open } style={ { marginTop: '8px' } }>
									{ backgroundUrl ? __( 'Replace background', 'wonderland-blocks' ) : __( 'Set background', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ backgroundUrl && (
						<>
							<Button variant="link" isDestructive onClick={ () => setAttributes( { backgroundId: undefined, backgroundUrl: '' } ) } style={ { marginTop: '8px' } }>
								{ __( 'Remove', 'wonderland-blocks' ) }
							</Button>
							<RangeControl
								label={ __( 'Overlay darkness (%)', 'wonderland-blocks' ) }
								value={ overlayOpacity }
								onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) }
								min={ 0 }
								max={ 90 }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				{ backgroundUrl && (
					<span className="wl-page-hero__overlay" style={ { opacity: overlayOpacity / 100 } } aria-hidden="true" />
				) }
				<div className="wl-page-hero__inner">
					<RichText tagName="p" className="wl-page-hero__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) } placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h1" className="wl-page-hero__title" value={ title }
						onChange={ ( v ) => setAttributes( { title: v } ) } placeholder={ __( 'Title…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="p" className="wl-page-hero__subtitle" value={ subtitle }
						onChange={ ( v ) => setAttributes( { subtitle: v } ) } placeholder={ __( 'Subtitle…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/italic' ] } />
				</div>
			</section>
		</>
	);
}
