import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, text, videoUrl, posterUrl, caption, background } = attributes;
	const blockProps = useBlockProps( { className: `wl-video is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Video', 'wonderland-blocks' ) }>
					<TextControl
						label={ __( 'Video URL (.mp4)', 'wonderland-blocks' ) }
						help={ __( 'A root-relative path such as /wp-content/themes/wonderland/assets/video/showreel.mp4', 'wonderland-blocks' ) }
						value={ videoUrl }
						onChange={ ( v ) => setAttributes( { videoUrl: v } ) }
					/>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'video' ] }
							onSelect={ ( m ) => setAttributes( { videoUrl: m.url } ) }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ __( 'Choose from media library', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: 'Ink', value: 'ink' },
							{ label: 'White', value: 'white' },
							{ label: 'Greige', value: 'greige' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-video__inner">
					<RichText
						tagName="p"
						className="wl-video__eyebrow"
						value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow', 'wonderland-blocks' ) }
					/>
					<RichText
						tagName="h2"
						className="wl-video__heading"
						value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Heading', 'wonderland-blocks' ) }
					/>
					<RichText
						tagName="p"
						className="wl-video__text"
						value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Short intro', 'wonderland-blocks' ) }
					/>

					<figure className="wl-video__figure">
						<div className="wl-video__frame">
							<MediaUploadCheck>
								<MediaUpload
									allowedTypes={ [ 'image' ] }
									value={ attributes.posterId }
									onSelect={ ( m ) => setAttributes( { posterId: m.id, posterUrl: m.url } ) }
									render={ ( { open } ) =>
										posterUrl ? (
											<button
												type="button"
												onClick={ open }
												style={ { border: 0, padding: 0, cursor: 'pointer', width: '100%' } }
											>
												<img src={ posterUrl } alt="" style={ { display: 'block', width: '100%' } } />
											</button>
										) : (
											<Button variant="secondary" onClick={ open }>
												{ __( 'Set poster image', 'wonderland-blocks' ) }
											</Button>
										)
									}
								/>
							</MediaUploadCheck>
						</div>
						<RichText
							tagName="figcaption"
							className="wl-video__caption"
							value={ caption }
							onChange={ ( v ) => setAttributes( { caption: v } ) }
							placeholder={ __( 'Caption', 'wonderland-blocks' ) }
						/>
					</figure>
				</div>
			</section>
		</>
	);
}
