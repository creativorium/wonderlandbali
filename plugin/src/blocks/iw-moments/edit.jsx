import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, RangeControl, SelectControl } from '@wordpress/components';

// Frames are edited as "Image URL | width | height | Alt text" lines. The size
// is what the justified rows are worked out from, so it is part of the line
// rather than something the editor has to look up.
const linesToItems = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line ) => {
		const [ url, w, h, ...alt ] = line.split( '|' );
		return {
			url: ( url || '' ).trim(),
			w: parseInt( w, 10 ) || 0,
			h: parseInt( h, 10 ) || 0,
			alt: alt.join( '|' ).trim(),
		};
	} );

const itemsToLines = ( items ) =>
	( items || [] ).map( ( i ) => `${ i.url } | ${ i.w || '' } | ${ i.h || '' } | ${ i.alt || '' }` ).join( '\n' );

const ratio = ( item ) => ( item.w && item.h ? item.w / item.h : 1.5 );

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading, intro, background, videoUrl, videoPoster, videoCaption,
		items = [], initial, batch, buttonText,
	} = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-moments is-${ background }` } );
	const preview = items.slice( 0, initial );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<RangeControl label={ __( 'Frames shown first', 'wonderland-blocks' ) } value={ initial } min={ 3 } max={ 30 }
						onChange={ ( v ) => setAttributes( { initial: v } ) } />
					<RangeControl label={ __( 'Frames per Load More', 'wonderland-blocks' ) } value={ batch } min={ 1 } max={ 12 }
						onChange={ ( v ) => setAttributes( { batch: v } ) } />
					<TextControl label={ __( 'Button text', 'wonderland-blocks' ) } value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) } />
				</PanelBody>
				<PanelBody title={ __( 'Film', 'wonderland-blocks' ) } initialOpen={ false }>
					<TextControl label={ __( 'Video URL (mp4)', 'wonderland-blocks' ) } value={ videoUrl }
						onChange={ ( v ) => setAttributes( { videoUrl: v } ) } />
					<TextControl label={ __( 'Poster image URL', 'wonderland-blocks' ) } value={ videoPoster }
						onChange={ ( v ) => setAttributes( { videoPoster: v } ) } />
					<TextControl label={ __( 'Film label', 'wonderland-blocks' ) } value={ videoCaption }
						onChange={ ( v ) => setAttributes( { videoCaption: v } ) } />
				</PanelBody>
				<PanelBody title={ __( 'Frames', 'wonderland-blocks' ) } initialOpen={ false }>
					<TextareaControl
						label={ __( 'One per line — "Image URL | width | height | Alt text"', 'wonderland-blocks' ) }
						value={ itemsToLines( items ) }
						rows={ 12 }
						onChange={ ( v ) => setAttributes( { items: linesToItems( v ) } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-moments__inner">
					<RichText tagName="p" className="wl-iw-moments__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-moments__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="p" className="wl-iw-moments__intro" value={ intro }
						onChange={ ( v ) => setAttributes( { intro: v } ) }
						placeholder={ __( 'Intro line (optional)…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/italic' ] } />

					{ videoUrl && (
						<figure className="wl-iw-moments__film">
							{ videoPoster ? <img src={ videoPoster } alt="" /> : null }
							{ videoCaption && <figcaption className="wl-iw-moments__cap">{ videoCaption }</figcaption> }
						</figure>
					) }

					<div className="wl-iw-moments__wrap">
						<div className="wl-iw-moments__grid">
							{ preview.map( ( item, i ) => (
								<figure className="wl-iw-moments__frame" key={ i } style={ { '--wl-ar': ratio( item ) } }>
									<img src={ item.url } alt={ item.alt || '' } />
								</figure>
							) ) }
						</div>
						{ items.length > initial && (
							<div className="wl-iw-moments__more">
								<span className="wl-iw-moments__btn">{ buttonText }</span>
								<p className="wl-iw-moments__count">{ initial } / { items.length }</p>
							</div>
						) }
					</div>
				</div>
			</section>
		</>
	);
}
