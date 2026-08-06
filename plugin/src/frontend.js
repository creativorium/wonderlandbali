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
import './blocks/showcase/style.scss';
import './blocks/packages/style.scss';

// Indian Weddings — page-specific blocks, used only on /indian-weddings/.
import './blocks/iw-hero/style.scss';
import './blocks/iw-specialism/style.scss';
import './blocks/iw-included/style.scss';
import './blocks/iw-gallery/style.scss';
import './blocks/iw-quotes/style.scss';
import './blocks/iw-packages/style.scss';
import './blocks/iw-faq/style.scss';
import './blocks/iw-cta/style.scss';

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

		// Slides after the first ship without a src (they all sit in the viewport,
		// so the browser would otherwise fetch every one up front). Give a slide
		// its image just before it is due.
		const hydrate = ( slide ) => {
			const img = slide && slide.querySelector( '.js-slide-img[data-src]' );
			if ( ! img ) {
				return;
			}
			if ( img.dataset.srcset ) {
				img.srcset = img.dataset.srcset;
				img.sizes = img.dataset.sizes || '100vw';
			}
			img.src = img.dataset.src;
			img.removeAttribute( 'data-src' );
		};

		let current = 0;
		hydrate( slides[ 1 ] ); // queue the one that comes next
		setInterval( () => {
			slides[ current ].classList.remove( 'is-active' );
			current = ( current + 1 ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
			hydrate( slides[ ( current + 1 ) % slides.length ] );
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
		// The masonry renders column by column, so DOM order is not the order the
		// frames were authored in — data-i carries that, and the overlay steps
		// through the gallery the way it reads.
		const links = Array.from( c.querySelectorAll( '[data-lb]' ) ).sort(
			( a, b ) => ( +a.dataset.i || 0 ) - ( +b.dataset.i || 0 )
		);
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

/**
 * Progressive galleries: reveal the next batch of hidden frames per click.
 *
 * Hidden frames are already in the DOM but carry `hidden`, so their lazy images
 * are not fetched until they are shown — the point is to keep the initial load
 * small, not to defer the markup.
 */
function initReveal() {
	document.querySelectorAll( '[data-reveal]' ).forEach( function ( grid ) {
		const batch = parseInt( grid.getAttribute( 'data-reveal' ), 10 ) || 12;
		const wrap = grid.parentElement;
		const btn = wrap ? wrap.querySelector( '[data-reveal-btn]' ) : null;
		const shown = wrap ? wrap.querySelector( '[data-shown]' ) : null;
		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			// Reveal in authored order, not column order, so each batch tops up
			// the columns evenly instead of filling one of them.
			const hidden = Array.prototype.slice
				.call( grid.querySelectorAll( '.wl-portfolio__item.is-hidden' ) )
				.sort( ( a, b ) => ( +a.dataset.i || 0 ) - ( +b.dataset.i || 0 ) );
			const next = hidden.slice( 0, batch );

			next.forEach( function ( el ) {
				el.classList.remove( 'is-hidden' );
				el.removeAttribute( 'hidden' );
				el.removeAttribute( 'aria-hidden' );
				el.removeAttribute( 'tabindex' );
			} );

			if ( shown ) {
				shown.textContent = String(
					grid.querySelectorAll( '.wl-portfolio__item:not(.is-hidden)' ).length
				);
			}

			// Nothing left to reveal — retire the control.
			if ( ! grid.querySelector( '.wl-portfolio__item.is-hidden' ) ) {
				const more = btn.closest( '.wl-portfolio__more' );
				if ( more ) {
					more.remove();
				}
			}

			// Move focus to the first new frame so keyboard users are not
			// stranded where the button used to be.
			if ( next.length ) {
				next[ 0 ].setAttribute( 'tabindex', '-1' );
				next[ 0 ].focus( { preventScroll: true } );
			}
		} );
	} );
}

/**
 * Decorative looping videos: attach the source only once the card is near the
 * viewport, then play it. An `autoplay` video downloads in full on page load
 * however far down the page it sits — on the home page that was 70MB of the
 * total. Without JS the poster image stands in, which is the same still frame.
 */
function initLazyVideo() {
	const videos = document.querySelectorAll( '[data-lazy-video]' );
	if ( ! videos.length ) {
		return;
	}

	const start = ( video ) => {
		if ( video.dataset.loaded ) {
			return;
		}
		video.dataset.loaded = '1';
		const source = document.createElement( 'source' );
		source.type = 'video/mp4';
		source.src = video.getAttribute( 'data-lazy-video' );
		video.appendChild( source );
		video.load();
		// Autoplay can still be refused (data saver, reduced motion) — the
		// poster simply stays, which is a perfectly good fallback.
		const played = video.play();
		if ( played && played.catch ) {
			played.catch( () => {} );
		}
	};

	if ( ! ( 'IntersectionObserver' in window ) ) {
		videos.forEach( start );
		return;
	}

	const io = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( e ) => {
				if ( e.isIntersecting ) {
					start( e.target );
					io.unobserve( e.target );
				}
			} );
		},
		{ rootMargin: '200px' }
	);
	videos.forEach( ( v ) => io.observe( v ) );
}


/**
 * Review sliders: a snap-scrolling row with arrows, dots and optional autoplay.
 *
 * The scrolling itself is native, so touch swiping and keyboard scrolling come
 * for free and the row still reads as a plain scrollable list with this script
 * absent. Hooks are data attributes rather than block classes, so the same code
 * drives the Indian page's reviews and the press page's pull-quotes.
 */
function initQuoteSliders() {
	document.querySelectorAll( '[data-quotes]' ).forEach( ( root ) => {
		const track = root.querySelector( '[data-quotes-track]' );
		if ( ! track ) {
			return;
		}

		const items = Array.from( track.querySelectorAll( '[data-quotes-item]' ) );
		const prev = root.querySelector( '[data-quotes-prev]' );
		const next = root.querySelector( '[data-quotes-next]' );
		const dots = root.querySelector( '[data-quotes-dots]' );
		if ( items.length < 2 ) {
			return;
		}

		// One card plus its gap, read from the DOM so this stays in step with
		// whatever the CSS decides at the current breakpoint.
		const step = () => {
			const gap = parseFloat( getComputedStyle( track ).columnGap || '0' ) || 0;
			return items[ 0 ].getBoundingClientRect().width + gap;
		};

		// How many fit at once — the dots count pages, not cards.
		const perView = () => Math.max( 1, Math.round( track.clientWidth / step() ) );
		const pages = () => Math.max( 1, Math.ceil( items.length / perView() ) );
		const page = () => Math.round( track.scrollLeft / ( step() * perView() ) );

		let buttons = [];
		const buildDots = () => {
			if ( ! dots ) {
				return;
			}
			const total = pages();
			if ( buttons.length === total ) {
				return;
			}
			dots.textContent = '';
			buttons = [];
			for ( let i = 0; i < total; i++ ) {
				const b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'wl-quotes-dot';
				b.setAttribute( 'aria-label', 'Reviews ' + ( i + 1 ) + ' of ' + total );
				b.addEventListener( 'click', () => {
					stop();
					track.scrollTo( { left: i * step() * perView(), behavior: 'smooth' } );
				} );
				dots.appendChild( b );
				buttons.push( b );
			}
		};

		const sync = () => {
			const max = track.scrollWidth - track.clientWidth;
			if ( prev ) {
				prev.disabled = track.scrollLeft < 4;
			}
			if ( next ) {
				next.disabled = track.scrollLeft > max - 4;
			}
			buildDots();
			const current = page();
			buttons.forEach( ( b, i ) => b.setAttribute( 'aria-current', i === current ? 'true' : 'false' ) );
		};

		// Autoplay, when asked for and the visitor has not opted out of motion.
		const delay = parseInt( root.getAttribute( 'data-quotes-autoplay' ) || '0', 10 );
		const still = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		let timer = null;

		const advance = () => {
			const max = track.scrollWidth - track.clientWidth;
			if ( track.scrollLeft > max - 4 ) {
				track.scrollTo( { left: 0, behavior: 'smooth' } );
			} else {
				track.scrollBy( { left: step() * perView(), behavior: 'smooth' } );
			}
		};
		const start = () => {
			if ( delay > 0 && ! still && ! timer ) {
				timer = setInterval( advance, delay );
			}
		};
		const stop = () => {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		};

		if ( prev ) {
			prev.addEventListener( 'click', () => { stop(); track.scrollBy( { left: -step() * perView(), behavior: 'smooth' } ); } );
		}
		if ( next ) {
			next.addEventListener( 'click', () => { stop(); track.scrollBy( { left: step() * perView(), behavior: 'smooth' } ); } );
		}

		track.addEventListener( 'scroll', sync, { passive: true } );
		window.addEventListener( 'resize', sync );

		// Leave it alone while it is being read or touched.
		root.addEventListener( 'pointerenter', stop );
		root.addEventListener( 'pointerdown', stop );
		root.addEventListener( 'focusin', stop );

		sync();
		start();
	} );
}

function initAll() {
	initSlideshows();
	initTestimonials();
	initLightbox();
	initReveal();
	initLazyVideo();
	initQuoteSliders();
}

if ( document.readyState !== 'loading' ) {
	initAll();
} else {
	document.addEventListener( 'DOMContentLoaded', initAll );
}
