import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl } from '@wordpress/components';

const blank = { name: '', quote: '', image: '', buttonText: 'Make A Request', buttonUrl: '/request/' };

export default function Edit( { attributes, setAttributes } ) {
	const { items = [], overlayOpacity } = attributes;
	const first = items[ 0 ];
	const blockProps = useBlockProps( {
		className: 'wl-testi',
		style: first?.image
			? { backgroundImage: `url(${ first.image })`, backgroundSize: 'cover', backgroundPosition: 'center' }
			: undefined,
	} );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, idx ) => ( idx === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, idx ) => idx !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { ...blank } ] } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Testimonials', 'wonderland-blocks' ) }>
					{ items.map( ( item, i ) => (
						<div key={ i } style={ { borderBottom: '1px solid #ddd', paddingBottom: '12px', marginBottom: '12px' } }>
							<strong>#{ i + 1 }</strong>
							<MediaUploadCheck>
								<MediaUpload
									allowedTypes={ [ 'image' ] }
									onSelect={ ( m ) => update( i, { image: m.url } ) }
									render={ ( { open } ) => (
										<Button variant="secondary" onClick={ open } style={ { display: 'block', margin: '6px 0' } }>
											{ item.image ? __( 'Replace photo', 'wonderland-blocks' ) : __( 'Set photo', 'wonderland-blocks' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							<Button variant="link" isDestructive onClick={ () => remove( i ) }>
								{ __( 'Remove', 'wonderland-blocks' ) }
							</Button>
						</div>
					) ) }
					<Button variant="primary" onClick={ add }>{ __( 'Add testimonial', 'wonderland-blocks' ) }</Button>
					<RangeControl
						label={ __( 'Overlay darkness (%)', 'wonderland-blocks' ) }
						value={ overlayOpacity }
						onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) }
						min={ 0 }
						max={ 90 }
						style={ { marginTop: '16px' } }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<span className="wl-testi__overlay" style={ { opacity: overlayOpacity / 100 } } aria-hidden="true" />
				<div className="wl-testi__items">
					{ first ? (
						<blockquote className="wl-testi__item is-active">
							<RichText tagName="p" className="wl-testi__name" value={ first.name }
								onChange={ ( v ) => update( 0, { name: v } ) } placeholder={ __( 'Name…', 'wonderland-blocks' ) } allowedFormats={ [] } />
							<RichText tagName="div" className="wl-testi__quote" value={ first.quote }
								onChange={ ( v ) => update( 0, { quote: v } ) } placeholder={ __( 'Review…', 'wonderland-blocks' ) } allowedFormats={ [ 'core/bold', 'core/italic' ] } />
							<span className="wl-testi__btn">{ first.buttonText }</span>
						</blockquote>
					) : (
						<p style={ { opacity: 0.7 } }>{ __( 'Add testimonials in the sidebar.', 'wonderland-blocks' ) }</p>
					) }
				</div>
			</section>
		</>
	);
}
