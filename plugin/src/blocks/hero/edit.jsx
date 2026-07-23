import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, TextControl } from '@wordpress/components';

/**
 * Editor UI for the Hero block. Mirrors render.php so the editor preview
 * matches the front end.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		heading,
		subheading,
		buttonText,
		buttonUrl,
		backgroundUrl,
		overlayOpacity,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'wl-hero',
		style: backgroundUrl ? { backgroundImage: `url(${ backgroundUrl })` } : undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Background', 'wonderland-blocks' ) }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { backgroundId: media.id, backgroundUrl: media.url } )
							}
							allowedTypes={ [ 'image' ] }
							value={ attributes.backgroundId }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ backgroundUrl
										? __( 'Replace image', 'wonderland-blocks' )
										: __( 'Select image', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ backgroundUrl && (
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { backgroundId: undefined, backgroundUrl: '' } ) }
							style={ { marginTop: '8px' } }
						>
							{ __( 'Remove image', 'wonderland-blocks' ) }
						</Button>
					) }
					<RangeControl
						label={ __( 'Overlay darkness (%)', 'wonderland-blocks' ) }
						value={ overlayOpacity }
						onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) }
						min={ 0 }
						max={ 90 }
						style={ { marginTop: '16px' } }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Call to action', 'wonderland-blocks' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button URL', 'wonderland-blocks' ) }
						value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) }
						placeholder="https://"
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<span
					className="wl-hero__overlay"
					style={ { opacity: overlayOpacity / 100 } }
					aria-hidden="true"
				/>
				<div className="wl-hero__inner">
					<RichText
						tagName="h1"
						className="wl-hero__title"
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Add a headline…', 'wonderland-blocks' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
					<RichText
						tagName="p"
						className="wl-hero__subtitle"
						value={ subheading }
						onChange={ ( v ) => setAttributes( { subheading: v } ) }
						placeholder={ __( 'Add a subheadline…', 'wonderland-blocks' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
					<RichText
						tagName="span"
						className="wl-hero__cta"
						value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) }
						placeholder={ __( 'Button text…', 'wonderland-blocks' ) }
						allowedFormats={ [] }
					/>
				</div>
			</section>
		</>
	);
}
