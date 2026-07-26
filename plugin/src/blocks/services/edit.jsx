import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

const blank = { title: '', text: '', url: '', buttonText: 'Click here', imageUrl: '', imageId: undefined };

export default function Edit( { attributes, setAttributes } ) {
	const { heading, items = [] } = attributes;
	const blockProps = useBlockProps( { className: 'wl-services' } );

	const update = ( i, patch ) =>
		setAttributes( { items: items.map( ( it, idx ) => ( idx === i ? { ...it, ...patch } : it ) ) } );
	const remove = ( i ) => setAttributes( { items: items.filter( ( _, idx ) => idx !== i ) } );
	const add = () => setAttributes( { items: [ ...items, { ...blank } ] } );

	return (
		<section { ...blockProps }>
			<RichText
				tagName="h2"
				className="wl-services__title"
				value={ heading }
				onChange={ ( v ) => setAttributes( { heading: v } ) }
				placeholder={ __( 'Section title…', 'wonderland-blocks' ) }
				allowedFormats={ [] }
			/>

			<div className="wl-services__grid">
				{ items.map( ( item, i ) => (
					<article className="wl-services__card" key={ i }>
						<div className="wl-services__media">
							<MediaUploadCheck>
								<MediaUpload
									allowedTypes={ [ 'image' ] }
									value={ item.imageId }
									onSelect={ ( m ) => update( i, { imageId: m.id, imageUrl: m.url } ) }
									render={ ( { open } ) =>
										item.imageUrl ? (
											<button type="button" onClick={ open } style={ { border: 0, padding: 0, cursor: 'pointer', width: '100%' } }>
												<img src={ item.imageUrl } alt="" style={ { display: 'block', width: '100%' } } />
											</button>
										) : (
											<Button variant="secondary" onClick={ open } style={ { margin: '1rem' } }>
												{ __( 'Set image', 'wonderland-blocks' ) }
											</Button>
										)
									}
								/>
							</MediaUploadCheck>
						</div>
						<div className="wl-services__body">
							<RichText
								tagName="h3"
								className="wl-services__name"
								value={ item.title }
								onChange={ ( v ) => update( i, { title: v } ) }
								placeholder={ __( 'Service name…', 'wonderland-blocks' ) }
								allowedFormats={ [] }
							/>
							<RichText
								tagName="p"
								className="wl-services__text"
								value={ item.text }
								onChange={ ( v ) => update( i, { text: v } ) }
								placeholder={ __( 'Description…', 'wonderland-blocks' ) }
								allowedFormats={ [ 'core/italic' ] }
							/>
							<input
								type="text"
								value={ item.url }
								onChange={ ( e ) => update( i, { url: e.target.value } ) }
								placeholder={ __( 'Link URL', 'wonderland-blocks' ) }
								style={ { width: '100%', fontSize: '12px' } }
							/>
							<Button variant="link" isDestructive onClick={ () => remove( i ) }>
								{ __( 'Remove', 'wonderland-blocks' ) }
							</Button>
						</div>
					</article>
				) ) }
			</div>

			<Button variant="primary" onClick={ add } style={ { marginTop: '1.5rem' } }>
				{ __( 'Add service', 'wonderland-blocks' ) }
			</Button>
		</section>
	);
}
