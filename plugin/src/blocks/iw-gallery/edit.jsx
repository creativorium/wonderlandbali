import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, RangeControl, SelectControl } from '@wordpress/components';

// Tiles are edited as "Image URL | Caption" lines — quicker than a repeater for
// a fixed six-up grid.
const linesToItems = ( value ) =>
	value.split( '\n' ).filter( ( l ) => l.trim() ).map( ( line ) => {
		const [ url, caption ] = line.split( '|' );
		return { url: ( url || '' ).trim(), caption: ( caption || '' ).trim() };
	} );

const itemsToLines = ( items ) => ( items || [] ).map( ( i ) => `${ i.url } | ${ i.caption || '' }` ).join( '\n' );

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, background, columns, items = [], buttonText, buttonUrl } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-gallery is-${ background }` } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery', 'wonderland-blocks' ) }>
					<RangeControl label={ __( 'Columns', 'wonderland-blocks' ) } value={ columns } min={ 1 } max={ 4 }
						onChange={ ( v ) => setAttributes( { columns: v } ) } />
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
						label={ __( 'Tiles — "Image URL | Caption" per line', 'wonderland-blocks' ) }
						value={ itemsToLines( items ) }
						onChange={ ( v ) => setAttributes( { items: linesToItems( v ) } ) }
					/>
					<TextControl label={ __( 'Button text', 'wonderland-blocks' ) } value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) } />
					<TextControl label={ __( 'Button URL', 'wonderland-blocks' ) } value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-gallery__inner">
					<RichText tagName="p" className="wl-iw-gallery__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-gallery__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<div className="wl-iw-gallery__grid" style={ { '--wl-cols': columns } }>
						{ items.map( ( item, i ) => (
							<figure className="wl-iw-gallery__tile" key={ i }>
								<img src={ item.url } alt={ item.caption || '' } />
								{ item.caption && <figcaption className="wl-iw-gallery__cap">{ item.caption }</figcaption> }
							</figure>
						) ) }
					</div>
					{ buttonText && (
						<div className="wl-iw-gallery__more"><span className="wl-iw-gallery__btn">{ buttonText }</span></div>
					) }
				</div>
			</section>
		</>
	);
}
