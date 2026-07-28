import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, text, background, chips } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-specialism is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<TextareaControl
						label={ __( 'Ceremony chips — one per line', 'wonderland-blocks' ) }
						value={ ( chips || [] ).map( ( c ) => c.text ).join( '\n' ) }
						onChange={ ( v ) => setAttributes( {
							chips: v.split( '\n' ).filter( ( l ) => l.trim() ).map( ( t ) => ( { text: t.trim() } ) ),
						} ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-specialism__inner">
					<RichText tagName="p" className="wl-iw-specialism__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-specialism__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="div" className="wl-iw-specialism__text" value={ text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Copy…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
					{ !! ( chips || [] ).length && (
						<ul className="wl-iw-specialism__chips">
							{ chips.map( ( c, i ) => <li key={ i }>{ c.text }</li> ) }
						</ul>
					) }
				</div>
			</section>
		</>
	);
}
