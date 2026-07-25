/**
 * Front-end entry — block styles + minimal front-end behaviour.
 * Built by Vite -> build/frontend.css + build/frontend.js.
 */
import './blocks/hero/style.scss';
import './blocks/intro/style.scss';

// Hero background slideshow: advance the .is-active slide on an interval.
// Progressive enhancement — the first slide is visible without JS.
function initHeroSlideshows() {
	const heroes = document.querySelectorAll( '.wl-hero[data-slideshow]' );
	heroes.forEach( ( hero ) => {
		const slides = Array.from( hero.querySelectorAll( '.wl-hero__slide' ) );
		if ( slides.length < 2 ) {
			return;
		}
		const duration = parseInt( hero.dataset.slideDuration || '4000', 10 );
		let current = 0;
		setInterval( () => {
			slides[ current ].classList.remove( 'is-active' );
			current = ( current + 1 ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
		}, duration );
	} );
}

if ( document.readyState !== 'loading' ) {
	initHeroSlideshows();
} else {
	document.addEventListener( 'DOMContentLoaded', initHeroSlideshows );
}
