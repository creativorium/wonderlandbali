import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl, TextareaControl, RangeControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, columns, items = [] } = attributes;
	const blockProps = useBlockProps( { className: 'wl-iw-quotes' } );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, n ) => ( n === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, n ) => n !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { quote: '', name: '', rating: 5 } ] } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<RangeControl label={ __( 'Columns', 'wonderland-blocks' ) } value={ columns } min={ 1 } max={ 4 }
						onChange={ ( v ) => setAttributes( { columns: v } ) } />
				</PanelBody>
				{ items.map( ( item, i ) => (
					<PanelBody key={ i } title={ item.name || __( 'Quote', 'wonderland-blocks' ) } initialOpen={ false }>
						<TextareaControl label={ __( 'Quote', 'wonderland-blocks' ) } value={ item.quote || '' }
							onChange={ ( v ) => update( i, { quote: v } ) } />
						<TextControl label={ __( 'Attribution', 'wonderland-blocks' ) } value={ item.name || '' }
							onChange={ ( v ) => update( i, { name: v } ) } />
						<RangeControl label={ __( 'Rating', 'wonderland-blocks' ) } value={ item.rating ?? 5 } min={ 0 } max={ 5 }
							onChange={ ( v ) => update( i, { rating: v } ) } />
						<Button variant="link" isDestructive onClick={ () => remove( i ) }>{ __( 'Remove', 'wonderland-blocks' ) }</Button>
					</PanelBody>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-quotes__inner">
					<RichText tagName="p" className="wl-iw-quotes__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-quotes__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<div className="wl-iw-quotes__grid" style={ { '--wl-cols': columns } }>
						{ items.map( ( item, i ) => (
							<figure className="wl-iw-quotes__item" key={ i }>
								<p className="wl-iw-quotes__stars">{ '★'.repeat( item.rating ?? 5 ) }</p>
								<blockquote className="wl-iw-quotes__quote">{ item.quote }</blockquote>
								<figcaption className="wl-iw-quotes__name">{ item.name }</figcaption>
							</figure>
						) ) }
					</div>
					<Button variant="primary" onClick={ add } style={ { marginTop: '1rem' } }>{ __( 'Add quote', 'wonderland-blocks' ) }</Button>
				</div>
			</section>
		</>
	);
}
