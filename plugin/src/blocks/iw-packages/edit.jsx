import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl, TextareaControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, intro, background, tiers = [], note } = attributes;
	const blockProps = useBlockProps( { className: `wl-iw-packages is-${ background }` } );

	const update = ( i, patch ) =>
		setAttributes( { tiers: tiers.map( ( t, n ) => ( n === i ? { ...t, ...patch } : t ) ) } );
	const remove = ( i ) => setAttributes( { tiers: tiers.filter( ( _, n ) => n !== i ) } );
	const add = () => setAttributes( { tiers: [ ...tiers, { name: '', blurb: '', items: [] } ] } );

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
					<TextareaControl label={ __( 'Footnote', 'wonderland-blocks' ) } value={ note }
						onChange={ ( v ) => setAttributes( { note: v } ) } />
				</PanelBody>
				{ tiers.map( ( tier, i ) => (
					<PanelBody key={ i } title={ tier.name || __( 'Tier', 'wonderland-blocks' ) } initialOpen={ false }>
						<TextControl label={ __( 'Name', 'wonderland-blocks' ) } value={ tier.name || '' }
							onChange={ ( v ) => update( i, { name: v } ) } />
						<TextareaControl label={ __( 'Blurb', 'wonderland-blocks' ) } value={ tier.blurb || '' }
							onChange={ ( v ) => update( i, { blurb: v } ) } />
						<TextControl label={ __( 'Badge (empty = not featured)', 'wonderland-blocks' ) } value={ tier.badge || '' }
							onChange={ ( v ) => update( i, { badge: v } ) } />
						<TextareaControl
							label={ __( 'Inclusions — one per line', 'wonderland-blocks' ) }
							value={ ( tier.items || [] ).map( ( it ) => ( typeof it === 'string' ? it : it.text ) ).join( '\n' ) }
							onChange={ ( v ) => update( i, {
								items: v.split( '\n' ).filter( ( l ) => l.trim() ).map( ( text ) => ( { text: text.trim() } ) ),
							} ) }
						/>
						<TextControl label={ __( 'Button text', 'wonderland-blocks' ) } value={ tier.buttonText || '' }
							onChange={ ( v ) => update( i, { buttonText: v } ) } />
						<TextControl label={ __( 'Button URL', 'wonderland-blocks' ) } value={ tier.buttonUrl || '' }
							onChange={ ( v ) => update( i, { buttonUrl: v } ) } />
						<Button variant="link" isDestructive onClick={ () => remove( i ) }>{ __( 'Remove tier', 'wonderland-blocks' ) }</Button>
					</PanelBody>
				) ) }
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wl-iw-packages__inner">
					<RichText tagName="p" className="wl-iw-packages__eyebrow" value={ eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="h2" className="wl-iw-packages__title" value={ heading }
						onChange={ ( v ) => setAttributes( { heading: v } ) }
						placeholder={ __( 'Section heading…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<RichText tagName="p" className="wl-iw-packages__intro" value={ intro }
						onChange={ ( v ) => setAttributes( { intro: v } ) }
						placeholder={ __( 'Intro line…', 'wonderland-blocks' ) } allowedFormats={ [] } />
					<div className="wl-iw-packages__tiers">
						{ tiers.map( ( tier, i ) => (
							<article className={ `wl-iw-packages__tier${ tier.badge ? ' is-featured' : '' }` } key={ i }>
								{ tier.badge && <p className="wl-iw-packages__badge">{ tier.badge }</p> }
								<h3 className="wl-iw-packages__name">{ tier.name }</h3>
								{ tier.blurb && <p className="wl-iw-packages__blurb">{ tier.blurb }</p> }
								{ !! ( tier.items || [] ).length && (
									<ul className="wl-iw-packages__list">
										{ tier.items.map( ( it, n ) => <li key={ n }>{ typeof it === 'string' ? it : it.text }</li> ) }
									</ul>
								) }
								{ tier.buttonText && <span className="wl-iw-packages__btn">{ tier.buttonText }</span> }
							</article>
						) ) }
					</div>
					{ note && <p className="wl-iw-packages__note">{ note }</p> }
					<Button variant="primary" onClick={ add } style={ { marginTop: '1rem' } }>{ __( 'Add tier', 'wonderland-blocks' ) }</Button>
				</div>
			</section>
		</>
	);
}
