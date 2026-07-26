import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, items = [], columns = 2, background } = attributes;
	const blockProps = useBlockProps( { className: `wl-features is-${ background }` } );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, idx ) => ( idx === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, idx ) => idx !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { title: '', text: '' } ] } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<RangeControl label={ __( 'Columns', 'wonderland-blocks' ) } value={ columns } min={ 1 } max={ 3 }
						onChange={ ( v ) => setAttributes( { columns: v } ) } />
					<SelectControl label={ __( 'Background', 'wonderland-blocks' ) } value={ background }
						options={ [ { label: 'White', value: 'white' }, { label: 'Greige', value: 'greige' } ] }
						onChange={ ( v ) => setAttributes( { background: v } ) } />
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<RichText tagName="h2" className="wl-features__title" value={ heading }
					onChange={ ( v ) => setAttributes( { heading: v } ) } placeholder={ __( 'Section title…', 'wonderland-blocks' ) } allowedFormats={ [] } />
				<div className="wl-features__grid" style={ { '--wl-cols': columns } }>
					{ items.map( ( item, i ) => (
						<div className="wl-features__item" key={ i }>
							<RichText tagName="h3" className="wl-features__name" value={ item.title }
								onChange={ ( v ) => update( i, { title: v } ) } placeholder={ __( 'Title…', 'wonderland-blocks' ) } allowedFormats={ [] } />
							<RichText tagName="div" className="wl-features__text" value={ item.text }
								onChange={ ( v ) => update( i, { text: v } ) } placeholder={ __( 'Text…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
							<Button variant="link" isDestructive onClick={ () => remove( i ) }>{ __( 'Remove', 'wonderland-blocks' ) }</Button>
						</div>
					) ) }
				</div>
				<Button variant="primary" onClick={ add } style={ { marginTop: '1rem' } }>{ __( 'Add item', 'wonderland-blocks' ) }</Button>
			</section>
		</>
	);
}
