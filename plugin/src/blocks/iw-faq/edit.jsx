import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, background, columns, items = [] } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-faq is-${ background }` } );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, n ) => ( n === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, n ) => n !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { question: '', answer: '' } ] } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'wonderland-blocks' ) }>
					<RangeControl label={ __( 'Columns', 'wonderland-blocks' ) } value={ columns } min={ 1 } max={ 3 }
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
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-faq__inner">
					<RichText tagName="p" className="wl-iw-faq__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-faq__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<dl className="wl-iw-faq__list" style={ { '--wl-cols': columns } }>
						{ items.map( ( item, i ) => (
							<div className="wl-iw-faq__item" key={ i }>
								<RichText tagName="dt" className="wl-iw-faq__q" value={ item.question }
									onChange={ ( v ) => update( i, { question: v } ) }
									placeholder={ __( 'Question…', 'wonderland-blocks' ) } allowedFormats={ [] } />
								<RichText tagName="dd" className="wl-iw-faq__a" value={ item.answer }
									onChange={ ( v ) => update( i, { answer: v } ) }
									placeholder={ __( 'Answer…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
								<Button variant="link" isDestructive onClick={ () => remove( i ) }>{ __( 'Remove', 'wonderland-blocks' ) }</Button>
							</div>
						) ) }
					</dl>
					<Button variant="primary" onClick={ add } style={ { marginTop: '1rem' } }>{ __( 'Add question', 'wonderland-blocks' ) }</Button>
				</div>
			</section>
		</>
	);
}
