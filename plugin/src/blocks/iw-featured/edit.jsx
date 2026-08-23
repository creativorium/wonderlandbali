import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl } from '@wordpress/components';

// Reviews are edited as "Name :: quote" lines — the quotes run long, so a
// double colon keeps the separator out of the punctuation they actually use.
const linesToQuotes = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line ) => {
		const [ name, ...rest ] = line.split( '::' );
		return { name: ( name || '' ).trim(), quote: rest.join( '::' ).trim() };
	} );

const quotesToLines = ( quotes ) =>
	( quotes || [] ).map( ( q ) => `${ q.name } :: ${ q.quote }` ).join( '\n' );

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, meta, text, background, quotes, buttonText, buttonUrl, buttonNewTab } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-featured is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Press feature', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'Taupe', 'wonderland-blocks' ), value: 'taupe' },
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<TextControl label={ __( 'Byline', 'wonderland-blocks' ) } value={ meta }
						onChange={ ( v ) => setAttributes( { meta: v } ) } />
					<TextareaControl
						label={ __( 'Summary — a blank line starts a new paragraph', 'wonderland-blocks' ) }
						value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
					/>
					<TextareaControl
						label={ __( 'Reviews — "Name :: quote" per line', 'wonderland-blocks' ) }
						value={ quotesToLines( quotes ) }
						onChange={ ( v ) => setAttributes( { quotes: linesToQuotes( v ) } ) }
					/>
					<TextControl label={ __( 'Button label', 'wonderland-blocks' ) } value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) } />
					<TextControl label={ __( 'Button URL', 'wonderland-blocks' ) } value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) } />
					<ToggleControl label={ __( 'Open in a new tab', 'wonderland-blocks' ) } checked={ !! buttonNewTab }
						onChange={ ( v ) => setAttributes( { buttonNewTab: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-featured__inner">
					<RichText tagName="p" className="wl-iw-featured__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<blockquote className="wl-iw-featured__quote">
						<RichText tagName="h2" className="wl-iw-featured__title" value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							placeholder={ __( 'Headline…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					</blockquote>
					{ meta && <p className="wl-iw-featured__meta">{ meta }</p> }
					{ text && (
						<div className="wl-iw-featured__text">
							{ text.split( '\n\n' ).map( ( p, i ) => <p key={ i }>{ p }</p> ) }
						</div>
					) }
					{ !! ( quotes || [] ).length && (
						<ul className="wl-iw-featured__reviews">
							{ quotes.map( ( q, i ) => (
								<li key={ i } className="wl-iw-featured__review">
									<figure>
										<blockquote className="wl-iw-featured__review-text"><p>{ q.quote }</p></blockquote>
										{ q.name && <figcaption className="wl-iw-featured__review-name">{ q.name }</figcaption> }
									</figure>
								</li>
							) ) }
						</ul>
					) }
					{ buttonText && (
						<div className="wl-iw-featured__actions">
							<span className="wl-iw-featured__btn">{ buttonText }</span>
						</div>
					) }
				</div>
			</section>
		</>
	);
}
