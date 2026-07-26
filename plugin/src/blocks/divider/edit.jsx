import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { slides = [], slideDuration = 5000 } = attributes;
	const blockProps = useBlockProps( { className: 'wl-divider' } );
	const first = slides[ 0 ]?.url;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Images', 'wonderland-blocks' ) }>
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
										? __( 'Edit images', 'wonderland-blocks' ) + ` (${ slides.length })`
										: __( 'Select images', 'wonderland-blocks' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<RangeControl
						label={ __( 'Seconds per image', 'wonderland-blocks' ) }
						value={ Math.round( slideDuration / 1000 ) }
						onChange={ ( v ) => setAttributes( { slideDuration: v * 1000 } ) }
						min={ 2 }
						max={ 10 }
						style={ { marginTop: '12px' } }
					/>
				</PanelBody>
			</InspectorControls>

			<div
				{ ...blockProps }
				style={ {
					...blockProps.style,
					backgroundImage: first ? `url(${ first })` : undefined,
					backgroundSize: 'cover',
					backgroundPosition: 'center',
					minHeight: '320px',
				} }
			>
				{ ! first && (
					<p style={ { textAlign: 'center', padding: '4rem', opacity: 0.6 } }>
						{ __( 'Select images for the divider slideshow', 'wonderland-blocks' ) }
					</p>
				) }
			</div>
		</>
	);
}
