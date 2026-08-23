import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, RangeControl } from '@wordpress/components';

// Buttons are edited as "Label | URL" lines — the hero only ever carries two.
// Flags with no place in that shorthand (newTab, hideOnMobile) are carried over
// from the button in the same position, so retyping a label does not quietly
// drop them.
const linesToButtons = ( value, existing ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line, i ) => {
		const [ text, url ] = line.split( '|' );
		const { newTab, hideOnMobile } = ( existing || [] )[ i ] || {};
		return {
			text: ( text || '' ).trim(),
			url: ( url || '' ).trim(),
			style: i === 0 ? 'solid' : 'ghost',
			...( newTab ? { newTab: true } : {} ),
			...( hideOnMobile ? { hideOnMobile: true } : {} ),
		};
	} );

const buttonsToLines = ( buttons ) => ( buttons || [] ).map( ( b ) => `${ b.text } | ${ b.url }` ).join( '\n' );

// Slides are "image URL" per line, optionally "image URL | phone crop URL".
// The second is what the hero shows below 760px; without one the first serves
// every width, which is how every slide behaved before this was added.
const linesToSlides = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line ) => {
		const [ url, mobileUrl ] = line.split( '|' );
		return {
			url: ( url || '' ).trim(),
			...( ( mobileUrl || '' ).trim() ? { mobileUrl: mobileUrl.trim() } : {} ),
		};
	} );

const slidesToLines = ( slides ) =>
	( slides || [] ).map( ( s ) => ( s.mobileUrl ? `${ s.url } | ${ s.mobileUrl }` : s.url ) ).join( '\n' );

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, title, subtitle, backgroundUrl, slides, slideDuration, overlayOpacity, buttons, note, stats } = attributes;
	const firstSlide = ( slides || [] ).map( ( s ) => s.url ).filter( Boolean )[ 0 ] || backgroundUrl;
	const blockProps = useBlockProps( { className: `wl-iw-hero${ firstSlide ? ' has-bg' : '' }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Hero', 'wonderland-blocks' ) }>
					<TextareaControl
						label={ __( 'Slides — one image URL per line', 'wonderland-blocks' ) }
						help={ __( 'Two or more crossfade; one is a still banner. Add "| mobile URL" after a slide to use a portrait crop on phones — leave it off and the same image serves both.', 'wonderland-blocks' ) }
						value={ slidesToLines( slides ) }
						onChange={ ( v ) => setAttributes( { slides: linesToSlides( v ) } ) }
					/>
					<RangeControl label={ __( 'Seconds per slide', 'wonderland-blocks' ) } value={ Math.round( ( slideDuration || 5000 ) / 1000 ) }
						min={ 2 } max={ 12 } onChange={ ( v ) => setAttributes( { slideDuration: v * 1000 } ) } />
					<TextControl label={ __( 'Background image URL (fallback)', 'wonderland-blocks' ) } value={ backgroundUrl }
						onChange={ ( v ) => setAttributes( { backgroundUrl: v } ) } />
					<RangeControl label={ __( 'Overlay opacity', 'wonderland-blocks' ) } value={ overlayOpacity } min={ 0 } max={ 80 }
						onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) } />
					<TextareaControl
						label={ __( 'Buttons — "Label | URL" per line', 'wonderland-blocks' ) }
						value={ buttonsToLines( buttons ) }
						onChange={ ( v ) => setAttributes( { buttons: linesToButtons( v, buttons ) } ) }
					/>
					<TextControl label={ __( 'Award line', 'wonderland-blocks' ) } value={ note }
						onChange={ ( v ) => setAttributes( { note: v } ) } />
					<TextareaControl
						label={ __( 'Credentials strip — one per line', 'wonderland-blocks' ) }
						value={ ( stats || [] ).map( ( s ) => s.text ).join( '\n' ) }
						onChange={ ( v ) => setAttributes( {
							stats: v.split( '\n' ).filter( ( l ) => l.trim() ).map( ( text ) => ( { text: text.trim() } ) ),
						} ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-hero__inner">
					<div className="wl-iw-hero__copy">
					<RichText tagName="p" className="wl-iw-hero__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h1" className="wl-iw-hero__title" value={ title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Page title…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="p" className="wl-iw-hero__lead" value={ subtitle }
						onChange={ ( v ) => setAttributes( { subtitle: v } ) }
						placeholder={ __( 'Lead line…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					{ !! ( buttons || [] ).length && (
						<div className="wl-iw-hero__actions">
							{ buttons.map( ( b, i ) => (
								<span key={ i } className={ `wl-iw-hero__btn${ b.style === 'ghost' ? ' is-ghost' : '' }${ b.hideOnMobile ? ' is-desktop-only' : '' }` }>{ b.text }</span>
							) ) }
						</div>
					) }
					</div>
					{ note && (
						<aside className="wl-iw-hero__aside">
							<p className="wl-iw-hero__note">{ note }</p>
						</aside>
					) }
				</div>
				{ !! ( stats || [] ).length && (
					<ul className="wl-iw-hero__stats">
						{ stats.map( ( s, i ) => <li key={ i }>{ s.text }</li> ) }
					</ul>
				) }
			</section>
		</>
	);
}
