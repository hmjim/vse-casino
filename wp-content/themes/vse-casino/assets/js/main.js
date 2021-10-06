/*Global variables*/
function menuBtn() {
	var menu = jQuery( '.header-menu' );
	var btn = jQuery( '.menu-toggle' );

	btn.click( function( e ) {
		e.preventDefault();
		menu.toggleClass( 'on' );
		jQuery( this ).toggleClass( 'on' );
	} );
}

(
	function( $ ) {
		$( document ).ready( function() {
			if ( location.href == 'https://vse-cazinoz.ew.r.appspot.com/avtomaty/' && $(window).width() <= 1200 ) {
				$('.section-slots .container > .row').prepend($('.section-slots .container .row .sidebar'));
			}
		} );
	}
)( jQuery );

function scrollEl() {
	jQuery( '.go_to a' ).click( function() {
		let scroll_el = jQuery( this ).attr( 'href' );
		if ( jQuery( scroll_el ).length !== 0 ) {
			jQuery( 'html, body' ).animate( {scrollTop: jQuery( scroll_el ).offset().top}, 500 );
		}
		return false;
	} );
}

function stickyDiv() {
	var sticky = new Sticky( '.anchor-menu, .widget-casino, .widget-slots' );
}

function returnToTop() {
	let btn = jQuery( '.btn-top' );
	jQuery( window ).scroll( function() {
		if ( jQuery( this ).scrollTop() >= 50 ) {
			btn.fadeIn( 200 );
		} else {
			btn.fadeOut( 200 );
		}
	} );
	btn.click( function() {
		jQuery( 'body,html' ).animate( {
			scrollTop: 0,
		}, 500 );
	} );
}

function slotScreensSlider() {
	if (jQuery( '.screens-carousel').length > 0){
	var swiper = new Swiper( '.screens-carousel', {
		slidesPerView: 1,
		spaceBetween: 20,
		autoplay: true,
		pagination: {
			el: '.screens-pagination',
			clickable: true,
		},
		breakpoints: {
			575: {
				slidesPerView: 2,
			},
			767: {
				slidesPerView: 3,
			},
		},
	} );
	}
}

function initEvents() {

	/*Actions on 'DOM ready' event*/
	jQuery( function() {
		menuBtn();
		scrollEl();
		stickyDiv();
		returnToTop();
		slotScreensSlider();
	} );

	jQuery( window ).resize( function() {

	} );

}

/*Start all functions and actions*/
initEvents();

