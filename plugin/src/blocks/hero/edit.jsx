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
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

const PinIcon = () => (
	<svg
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="1.6"
		strokeLinecap="round"
		strokeLinejoin="round"
		aria-hidden="true"
	>
		<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" />
		<circle cx="12" cy="10" r="2.5" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		heading,
		subheading,
		sideText,
		buttonText,
		buttonUrl,
		slides = [],
		slideDuration = 4000,
		kenBurns = true,
		badgeUrl,
		overlayOpacity,
	} = attributes;

	// Editor preview shows the first slide as a static background.
	const previewUrl = slides[ 0 ]?.url || attributes.backgroundUrl || '';

	const blockProps = useBlockProps( {
		className: `wl-hero${ kenBurns ? ' wl-hero--kenburns-preview' : '' }`,
		style: previewUrl ? { backgroundImage: `url(${ previewUrl })` } : undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Background slideshow', 'wonderland-blocks' ) }>
					<MediaUploadCheck>
						<MediaUpload
							multiple
							gallery
							addToGallery
							allowedTypes={ [ 'image' ] }
							value={ slides.map( ( s ) => s.id ).filter( Boolean ) }
							onSelect={ ( media ) =>
								setAttributes( {
									slides: ( Array.isArray( media ) ? media : [ media ] ).map(
										( m ) => ( { id: m.id, url: m.url } )
									),
								} )
							}
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ slides.length
										? __( 'Edit images', 'wonderland-blocks' ) +
										  ` (${ slides.length })`
										: __( 'Select images', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ slides.length > 0 && (
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { slides: [] } ) }
							style={ { marginTop: '8px', display: 'block' } }
						>
							{ __( 'Clear images', 'wonderland-blocks' ) }
						</Button>
					) }
					<RangeControl
						label={ __( 'Seconds per slide', 'wonderland-blocks' ) }
						value={ Math.round( slideDuration / 1000 ) }
						onChange={ ( v ) => setAttributes( { slideDuration: v * 1000 } ) }
						min={ 2 }
						max={ 10 }
						style={ { marginTop: '16px' } }
					/>
					<ToggleControl
						label={ __( 'Ken Burns zoom', 'wonderland-blocks' ) }
						checked={ kenBurns }
						onChange={ ( v ) => setAttributes( { kenBurns: v } ) }
					/>
					<RangeControl
						label={ __( 'Overlay darkness (%)', 'wonderland-blocks' ) }
						value={ overlayOpacity }
						onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) }
						min={ 0 }
						max={ 90 }
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

				<PanelBody title={ __( 'Award badge', 'wonderland-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { badgeId: media.id, badgeUrl: media.url } )
							}
							allowedTypes={ [ 'image' ] }
							value={ attributes.badgeId }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ badgeUrl
										? __( 'Replace badge', 'wonderland-blocks' )
										: __( 'Select badge', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ badgeUrl && (
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { badgeId: undefined, badgeUrl: '' } ) }
							style={ { marginTop: '8px' } }
						>
							{ __( 'Remove badge', 'wonderland-blocks' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<span
					className="wl-hero__overlay"
					style={ { opacity: overlayOpacity / 100 } }
					aria-hidden="true"
				/>

				<RichText
					tagName="p"
					className="wl-hero__side"
					value={ sideText }
					onChange={ ( v ) => setAttributes( { sideText: v } ) }
					placeholder={ __( 'Vertical label…', 'wonderland-blocks' ) }
					allowedFormats={ [] }
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
					<span className="wl-hero__cta">
						<PinIcon />
						<RichText
							tagName="span"
							value={ buttonText }
							onChange={ ( v ) => setAttributes( { buttonText: v } ) }
							placeholder={ __( 'Button text…', 'wonderland-blocks' ) }
							allowedFormats={ [] }
						/>
					</span>
				</div>

				{ badgeUrl && <img className="wl-hero__badge" src={ badgeUrl } alt="" /> }
			</section>
		</>
	);
}
