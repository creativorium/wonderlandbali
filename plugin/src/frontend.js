/**
 * Front-end entry — block styles + minimal front-end behaviour.
 * Built by Vite -> build/frontend.css + build/frontend.js.
 */
import './blocks/hero/style.scss';
import './blocks/intro/style.scss';
import './blocks/divider/style.scss';
import './blocks/services/style.scss';
import './blocks/cta/style.scss';
import './blocks/portfolio/style.scss';
import './blocks/follow/style.scss';
import './blocks/page-hero/style.scss';
import './blocks/media-text/style.scss';
import './blocks/profile/style.scss';
import './blocks/contact/style.scss';
import './blocks/features/style.scss';
import './blocks/testimonials/style.scss';

/**
 * Generic crossfade slideshow: any element with [data-slideshow] cycles the
 * .is-active class across its `.js-slide` children. Used by the hero + divider.
 * Progressive enhancement — the first slide is visible without JS.
 */
function initSlideshows() {
	document.querySelectorAll( '[data-slideshow]' ).forEach( ( root ) => {
		const slides = root.querySelectorAll( '.js-slide' );
		if ( slides.length < 2 ) {
			return;
		}
		const duration = parseInt( root.dataset.slideDuration || '4000', 10 );
		let current = 0;
		setInterval( () => {
			slides[ current ].classList.remove( 'is-active' );
			current = ( current + 1 ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
		}, duration );
	} );
}

/**
 * Testimonial slider: prev/next arrows crossfade both the background photo and
 * the review text; optional autoplay via data-duration.
 */
function initTestimonials() {
	document.querySelectorAll( '[data-testi]' ).forEach( ( root ) => {
		const bgs = Array.from( root.querySelectorAll( '.js-testi-bg' ) );
		const items = Array.from( root.querySelectorAll( '.js-testi-item' ) );
		const n = Math.max( bgs.length, items.length );
		if ( n < 2 ) {
			return;
		}
		let i = 0;
		const go = ( target ) => {
			bgs[ i ]?.classList.remove( 'is-active' );
			items[ i ]?.classList.remove( 'is-active' );
			i = ( ( target % n ) + n ) % n;
			bgs[ i ]?.classList.add( 'is-active' );
			items[ i ]?.classList.add( 'is-active' );
		};
		root.querySelector( '[data-testi-prev]' )?.addEventListener( 'click', () => go( i - 1 ) );
		root.querySelector( '[data-testi-next]' )?.addEventListener( 'click', () => go( i + 1 ) );

		const dur = parseInt( root.dataset.duration || '0', 10 );
		if ( dur > 0 ) {
			let timer = setInterval( () => go( i + 1 ), dur );
			root.addEventListener( 'pointerenter', () => clearInterval( timer ) );
			root.addEventListener( 'pointerleave', () => { timer = setInterval( () => go( i + 1 ), dur ); } );
		}
	} );
}

/**
 * Minimal gallery lightbox: images inside a [data-lightbox] container open in a
 * full-screen overlay with prev/next + keyboard support.
 */
function initLightbox() {
	const containers = document.querySelectorAll( '[data-lightbox]' );
	if ( ! containers.length ) {
		return;
	}
	const ov = document.createElement( 'div' );
	ov.className = 'wl-lightbox';
	ov.innerHTML =
		'<button class="wl-lightbox__close" aria-label="Close">×</button>' +
		'<button class="wl-lightbox__nav wl-lightbox__prev" aria-label="Previous">‹</button>' +
		'<img class="wl-lightbox__img" alt="" />' +
		'<button class="wl-lightbox__nav wl-lightbox__next" aria-label="Next">›</button>';
	document.body.appendChild( ov );
	const imgEl = ov.querySelector( '.wl-lightbox__img' );
	let items = [];
	let idx = 0;
	const show = ( i ) => {
		idx = ( ( i % items.length ) + items.length ) % items.length;
		imgEl.src = items[ idx ];
	};
	const open = ( list, i ) => {
		items = list;
		ov.classList.add( 'is-open' );
		document.body.classList.add( 'lb-open' );
		show( i );
	};
	const close = () => {
		ov.classList.remove( 'is-open' );
		document.body.classList.remove( 'lb-open' );
		imgEl.removeAttribute( 'src' );
	};

	containers.forEach( ( c ) => {
		const links = Array.from( c.querySelectorAll( '[data-lb]' ) );
		const urls = links.map( ( a ) => a.getAttribute( 'href' ) );
		links.forEach( ( a, i ) =>
			a.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				open( urls, i );
			} )
		);
	} );

	ov.querySelector( '.wl-lightbox__close' ).addEventListener( 'click', close );
	ov.querySelector( '.wl-lightbox__prev' ).addEventListener( 'click', () => show( idx - 1 ) );
	ov.querySelector( '.wl-lightbox__next' ).addEventListener( 'click', () => show( idx + 1 ) );
	ov.addEventListener( 'click', ( e ) => {
		if ( e.target === ov ) {
			close();
		}
	} );
	document.addEventListener( 'keydown', ( e ) => {
		if ( ! ov.classList.contains( 'is-open' ) ) {
			return;
		}
		if ( e.key === 'Escape' ) close();
		if ( e.key === 'ArrowLeft' ) show( idx - 1 );
		if ( e.key === 'ArrowRight' ) show( idx + 1 );
	} );
}

function initAll() {
	initSlideshows();
	initTestimonials();
	initLightbox();
}

if ( document.readyState !== 'loading' ) {
	initAll();
} else {
	document.addEventListener( 'DOMContentLoaded', initAll );
}
