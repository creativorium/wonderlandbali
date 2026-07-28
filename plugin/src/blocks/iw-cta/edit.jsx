import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

const linesToButtons = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line, i ) => {
		const [ text, url ] = line.split( '|' );
		return { text: ( text || '' ).trim(), url: ( url || '' ).trim(), style: i === 0 ? 'solid' : 'ghost' };
	} );

const buttonsToLines = ( buttons ) => ( buttons || [] ).map( ( b ) => `${ b.text } | ${ b.url }` ).join( '\n' );

export default function Edit( { attributes, setAttributes } ) {
	const { heading, text, background, buttons, note } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-cta is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Call to action', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'Taupe', 'wonderland-blocks' ), value: 'taupe' },
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<TextareaControl
						label={ __( 'Buttons — "Label | URL" per line', 'wonderland-blocks' ) }
						value={ buttonsToLines( buttons ) }
						onChange={ ( v ) => setAttributes( { buttons: linesToButtons( v ) } ) }
					/>
					<TextControl label={ __( 'Award note', 'wonderland-blocks' ) } value={ note }
						onChange={ ( v ) => setAttributes( { note: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-cta__inner">
					<RichText tagName="h2" className="wl-iw-cta__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="p" className="wl-iw-cta__text" value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Supporting line…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					{ !! ( buttons || [] ).length && (
						<div className="wl-iw-cta__actions">
							{ buttons.map( ( b, i ) => (
								<span key={ i } className={ `wl-iw-cta__btn${ b.style === 'ghost' ? ' is-ghost' : '' }` }>{ b.text }</span>
							) ) }
						</div>
					) }
					{ note && <p className="wl-iw-cta__note">{ note }</p> }
				</div>
			</section>
		</>
	);
}
