import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, background, columns, items = [] } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-included is-${ background }` } );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, n ) => ( n === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, n ) => n !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { title: '', text: '' } ] } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<RangeControl label={ __( 'Columns', 'wonderland-blocks' ) } value={ columns } min={ 1 } max={ 4 }
						onChange={ ( v ) => setAttributes( { columns: v } ) } />
					<SelectControl
						label={ __( 'Background', 'wonderland-blocks' ) }
						value={ background }
						options={ [
							{ label: __( 'Greige', 'wonderland-blocks' ), value: 'greige' },
							{ label: __( 'White', 'wonderland-blocks' ), value: 'white' },
						] }
						onChange={ ( v ) => setAttributes( { background: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-included__inner">
					<RichText tagName="p" className="wl-iw-included__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-included__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<div className="wl-iw-included__grid" style={ { '--wl-cols': columns } }>
						{ items.map( ( item, i ) => (
							<article className="wl-iw-included__card" key={ i }>
								<RichText tagName="h3" className="wl-iw-included__name" value={ item.title }
									onChange={ ( v ) => update( i, { title: v } ) }
									placeholder={ __( 'Title…', 'wonderland-blocks' ) } allowedFormats={ [] } />
								<RichText tagName="p" className="wl-iw-included__text" value={ item.text }
									onChange={ ( v ) => update( i, { text: v } ) }
									placeholder={ __( 'Text…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
								<Button variant="link" isDestructive onClick={ () => remove( i ) }>{ __( 'Remove', 'wonderland-blocks' ) }</Button>
							</article>
						) ) }
					</div>
					<Button variant="primary" onClick={ add } style={ { marginTop: '1rem' } }>{ __( 'Add card', 'wonderland-blocks' ) }</Button>
				</div>
			</section>
		</>
	);
}
