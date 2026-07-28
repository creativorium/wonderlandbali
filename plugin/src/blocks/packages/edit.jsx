import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, background, straddle, groups, buttonText, buttonUrl, note } = attributes;
	const blockProps = useBlockProps( { className: `wl-packages is-${ background }${ straddle ? ' is-straddle' : '' }` } );

	const update = ( i, key, value ) => {
		const next = ( groups || [] ).map( ( g, n ) => ( n === i ? { ...g, [ key ]: value } : g ) );
		setAttributes( { groups: next } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section', 'wonderland-blocks' ) }>
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
					<ToggleControl
						label={ __( 'Straddle the section above', 'wonderland-blocks' ) }
						checked={ !! straddle }
						onChange={ ( v ) => setAttributes( { straddle: v } ) }
						help={ __( 'Lifts the heading so the background change lands mid-title.', 'wonderland-blocks' ) }
					/>
					<TextControl label={ __( 'Button text', 'wonderland-blocks' ) } value={ buttonText }
						onChange={ ( v ) => setAttributes( { buttonText: v } ) } />
					<TextControl label={ __( 'Button URL', 'wonderland-blocks' ) } value={ buttonUrl }
						onChange={ ( v ) => setAttributes( { buttonUrl: v } ) } />
					<TextareaControl label={ __( 'Footnote', 'wonderland-blocks' ) } value={ note }
						onChange={ ( v ) => setAttributes( { note: v } ) } />
				</PanelBody>
				{ ( groups || [] ).map( ( g, i ) => (
					<PanelBody key={ i } title={ g.title || __( 'Group', 'wonderland-blocks' ) } initialOpen={ false }>
						<TextControl label={ __( 'Name', 'wonderland-blocks' ) } value={ g.title || '' }
							onChange={ ( v ) => update( i, 'title', v ) } />
						<TextControl label={ __( 'Price', 'wonderland-blocks' ) } value={ g.price || '' }
							onChange={ ( v ) => update( i, 'price', v ) } />
						<TextControl label={ __( 'Image URL', 'wonderland-blocks' ) } value={ g.imageUrl || '' }
							onChange={ ( v ) => update( i, 'imageUrl', v ) } />
						<TextareaControl
							label={ __( 'Inclusions (one per line)', 'wonderland-blocks' ) }
							value={ ( g.items || [] ).join( '\n' ) }
							onChange={ ( v ) => update( i, 'items', v.split( '\n' ).filter( Boolean ) ) }
						/>
						<TextareaControl
							label={ __( 'Add-on rows — "Label | Price" per line', 'wonderland-blocks' ) }
							value={ ( g.rows || [] ).map( ( r ) => `${ r.label } | ${ r.price }` ).join( '\n' ) }
							onChange={ ( v ) => update( i, 'rows', v.split( '\n' ).filter( Boolean ).map( ( line ) => {
								const [ label, price ] = line.split( '|' );
								return { label: ( label || '' ).trim(), price: ( price || '' ).trim() };
							} ) ) }
						/>
					</PanelBody>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<RichText tagName="h2" className="wl-packages__title" value={ heading }
					onChange={ ( v ) => setAttributes( { heading: v } ) }
					placeholder={ __( 'Packages and Prices', 'wonderland-blocks' ) } allowedFormats={ [] } />
				<div className="wl-packages__inner">
					{ ( groups || [] ).map( ( g, i ) => (
						<div key={ i } className="wl-packages__group">
							<div className="wl-packages__copy">
								<h3 className="wl-packages__name">{ g.title }<span className="wl-packages__price">{ g.price }</span></h3>
								{ !! ( g.items || [] ).length && (
									<ul className="wl-packages__list">{ g.items.map( ( t ) => <li key={ t }>{ t }</li> ) }</ul>
								) }
								{ !! ( g.rows || [] ).length && (
									<dl className="wl-packages__rows">
										{ g.rows.map( ( r ) => (
											<div key={ r.label }><dt>{ r.label }</dt><dd>{ r.price }</dd></div>
										) ) }
									</dl>
								) }
							</div>
							{ g.imageUrl && <figure className="wl-packages__figure"><img src={ g.imageUrl } alt="" /></figure> }
						</div>
					) ) }
					{ buttonText && <div className="wl-packages__cta"><span className="wl-packages__btn">{ buttonText }</span></div> }
					{ note && <div className="wl-packages__note"><p>{ note }</p></div> }
				</div>
			</section>
		</>
	);
}
