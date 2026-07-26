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
import './blocks/contact/style.scss';

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

if ( document.readyState !== 'loading' ) {
	initSlideshows();
} else {
	document.addEventListener( 'DOMContentLoaded', initSlideshows );
}
