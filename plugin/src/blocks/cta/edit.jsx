import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	TextControl,
	ToggleControl,
	SelectControl,
	RangeControl,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const {
		text,
		variant,
		buttonText,
		buttonUrl,
		buttonNewTab,
		backgroundUrl,
		overlayOpacity,
	} = attributes;

	const blockProps = useBlockProps( {
		className: `wl-cta wl-cta--${ variant }${ backgroundUrl ? ' has-bg' : '' }`,
		style: backgroundUrl ? { backgroundImage: `url(${ backgroundUrl })` } : undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Variant', 'wonderland-blocks' ) }
						value={ variant }
						options={ [
							{ label: 'Plain', value: 'plain' },
							{ label: 'Quote', value: 'quote' },
						] }
						onChange={ ( v ) => setAttributes( { variant: v } ) }
					/>
					<TextControl
						label={ __( 'Button URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
					/>
					<ToggleControl
						label={ __( 'Open in new tab', 'wonderland-blocks' ) }
						checked={ buttonNewTab }
						onChange={ ( v ) => setAttributes( { buttonNewTab: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Background', 'wonderland-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.backgroundId }
							onSelect={ ( m ) => setAttributes( { backgroundId: m.id, backgroundUrl: m.url } ) }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ backgroundUrl ? __( 'Replace', 'wonderland-blocks' ) : __( 'Select image', 'wonderland-blocks' ) }
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
					<span className="wl-cta__overlay" style={ { opacity: overlayOpacity / 100 } } aria-hidden="true" />
				) }
				<div className="wl-cta__inner">
					<RichText
						tagName="div"
						className="wl-cta__text"
						value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Optional text / quote…', 'wonderland-blocks' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
					<RichText
						tagName="span"
						className="wl-cta__btn"
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
