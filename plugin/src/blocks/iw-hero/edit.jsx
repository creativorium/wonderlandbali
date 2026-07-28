import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, RangeControl } from '@wordpress/components';

// Buttons are edited as "Label | URL" lines — the hero only ever carries two.
const linesToButtons = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line, i ) => {
		const [ text, url ] = line.split( '|' );
		return { text: ( text || '' ).trim(), url: ( url || '' ).trim(), style: i === 0 ? 'solid' : 'ghost' };
	} );

const buttonsToLines = ( buttons ) => ( buttons || [] ).map( ( b ) => `${ b.text } | ${ b.url }` ).join( '\n' );

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, title, subtitle, backgroundUrl, overlayOpacity, buttons, note, stats } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-hero${ backgroundUrl ? ' has-bg' : '' }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Hero', 'wonderland-blocks' ) }>
					<TextControl label={ __( 'Background image URL', 'wonderland-blocks' ) } value={ backgroundUrl }
						onChange={ ( v ) => setAttributes( { backgroundUrl: v } ) } />
					<RangeControl label={ __( 'Overlay opacity', 'wonderland-blocks' ) } value={ overlayOpacity } min={ 0 } max={ 80 }
						onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) } />
					<TextareaControl
						label={ __( 'Buttons — "Label | URL" per line', 'wonderland-blocks' ) }
						value={ buttonsToLines( buttons ) }
						onChange={ ( v ) => setAttributes( { buttons: linesToButtons( v ) } ) }
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
								<span key={ i } className={ `wl-iw-hero__btn${ b.style === 'ghost' ? ' is-ghost' : '' }` }>{ b.text }</span>
							) ) }
						</div>
					) }
					{ note && <p className="wl-iw-hero__note">{ note }</p> }
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
